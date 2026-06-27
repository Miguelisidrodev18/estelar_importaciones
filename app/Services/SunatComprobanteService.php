<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Empresa;
use Greenter\See;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Client\Client;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SunatComprobanteService
{
    private function crearSee(Empresa $empresa): See
    {
        if (!$empresa->tieneCertificado()) {
            throw new \Exception('Certificado digital no configurado. Suba el archivo .pfx en Configuración de Empresa.');
        }
        if (!$empresa->tieneCredencialesSunat()) {
            throw new \Exception('Credenciales SOL no configuradas. Configure Usuario y Clave SOL en Configuración de Empresa.');
        }

        $see = new See();
        $see->setCertificate($empresa->certificado_pem_content);
        $see->setClaveSOL($empresa->ruc, $empresa->sunat_usuario_sol, $empresa->sunat_clave_sol);

        $endpoint = $empresa->isSunatBeta()
            ? SunatEndpoints::FE_BETA
            : SunatEndpoints::FE_PRODUCCION;
        $see->setService($endpoint);

        $cachePath = storage_path('app/greenter/cache');
        if (!is_dir($cachePath)) mkdir($cachePath, 0755, true);
        $see->setCachePath($cachePath);

        return $see;
    }

    public function enviar(Venta $venta): array
    {
        $empresa = Empresa::instancia();
        if (!$empresa) {
            return ['success' => false, 'message' => 'Empresa no configurada.'];
        }

        if ($venta->estado_sunat === 'aceptado') {
            return ['success' => false, 'message' => 'Este comprobante ya fue aceptado por SUNAT.'];
        }
        if ($venta->tipo_comprobante === 'cotizacion') {
            return ['success' => false, 'message' => 'Las cotizaciones no se envían a SUNAT.'];
        }

        $venta->load(['cliente', 'serieComprobante', 'detalles.producto.unidadMedida', 'detalles.variante']);

        try {
            $see = $this->crearSee($empresa);

            if (in_array($venta->tipo_comprobante, ['nota_credito', 'nota_debito'])) {
                $document = $this->buildNote($venta, $empresa);
            } else {
                $document = $this->buildInvoice($venta, $empresa);
            }

            $result = $see->send($document);
            $venta->sunat_enviado_at = now();

            if ($result->isSuccess()) {
                $cdr = $result->getCdrResponse();
                $venta->estado_sunat      = 'aceptado';
                $venta->sunat_descripcion = $cdr->getDescription();

                $this->guardarXml($see, $venta);
                $this->guardarCdr($result, $venta);
            } else {
                $error = $result->getError();
                $venta->estado_sunat      = 'rechazado';
                $venta->sunat_descripcion = $error ? $error->getMessage() : 'Error desconocido';
            }

            $venta->save();

            return [
                'success' => $venta->estado_sunat === 'aceptado',
                'message' => $venta->sunat_descripcion,
            ];

        } catch (\Exception $e) {
            Log::error('SunatComprobanteService::enviar', ['venta' => $venta->id, 'error' => $e->getMessage()]);
            $venta->estado_sunat      = 'rechazado';
            $venta->sunat_descripcion = $e->getMessage();
            $venta->save();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function buildInvoice(Venta $venta, Empresa $empresa): Invoice
    {
        $serie       = $venta->serieComprobante?->serie ?? 'B001';
        $correlativo = str_pad($venta->correlativo ?? 1, 8, '0', STR_PAD_LEFT);
        $tipoDoc     = $venta->tipo_comprobante === 'factura' ? '01' : '03';

        $invoice = new Invoice();
        $invoice->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($tipoDoc)
            ->setSerie($serie)
            ->setCorrelativo($correlativo)
            ->setFechaEmision($venta->fecha)
            ->setTipoMoneda('PEN')
            ->setCompany($this->buildCompany($empresa))
            ->setClient($this->buildClient($venta))
            ->setMtoOperGravadas(round((float) $venta->subtotal / 1.18, 2))
            ->setMtoIGV(round((float) $venta->igv, 2))
            ->setTotalImpuestos(round((float) $venta->igv, 2))
            ->setValorVenta(round((float) $venta->subtotal / 1.18, 2))
            ->setSubTotal(round((float) $venta->total, 2))
            ->setMtoImpVenta(round((float) $venta->total, 2));

        $details = [];
        foreach ($venta->detalles as $det) {
            $nombre = $det->producto?->nombre ?? 'Producto';
            if ($det->variante?->nombre_completo) $nombre .= ' - ' . $det->variante->nombre_completo;

            $precioUnit    = (float) $det->precio_unitario;
            $subtotalDet   = (float) $det->subtotal;
            $baseImponible = round($subtotalDet / 1.18, 2);
            $igvLinea      = round($subtotalDet - $baseImponible, 2);
            $precioSinIgv  = round($precioUnit / 1.18, 6);

            $detail = new SaleDetail();
            $detail->setCodProducto($det->producto?->codigo ?? 'P001')
                ->setUnidad($det->producto?->unidadMedida?->codigo_sunat ?? 'NIU')
                ->setCantidad((float) $det->cantidad)
                ->setDescripcion($nombre)
                ->setMtoBaseIgv($baseImponible)
                ->setPorcentajeIgv(18.00)
                ->setIgv($igvLinea)
                ->setTipAfeIgv('10')
                ->setTotalImpuestos($igvLinea)
                ->setMtoValorVenta($baseImponible)
                ->setMtoValorUnitario($precioSinIgv)
                ->setMtoPrecioUnitario($precioUnit);

            $details[] = $detail;
        }

        $invoice->setDetails($details);

        $legend = new Legend();
        $legend->setCode('1000')
            ->setValue($this->numberToWords(round((float) $venta->total, 2)));
        $invoice->setLegends([$legend]);

        return $invoice;
    }

    private function buildNote(Venta $venta, Empresa $empresa): Note
    {
        $serie       = $venta->serieComprobante?->serie ?? 'BC01';
        $correlativo = str_pad($venta->correlativo ?? 1, 8, '0', STR_PAD_LEFT);
        $tipoDoc     = $venta->tipo_comprobante === 'nota_credito' ? '07' : '08';

        $note = new Note();
        $note->setUblVersion('2.1')
            ->setTipoDoc($tipoDoc)
            ->setSerie($serie)
            ->setCorrelativo($correlativo)
            ->setFechaEmision($venta->fecha)
            ->setTipoMoneda('PEN')
            ->setCompany($this->buildCompany($empresa))
            ->setClient($this->buildClient($venta))
            ->setMtoOperGravadas(round((float) $venta->subtotal / 1.18, 2))
            ->setMtoIGV(round((float) $venta->igv, 2))
            ->setTotalImpuestos(round((float) $venta->igv, 2))
            ->setMtoImpVenta(round((float) $venta->total, 2));

        if ($venta->venta_origen_id) {
            $origen = Venta::with('serieComprobante')->find($venta->venta_origen_id);
            if ($origen) {
                $tipDocAfectado = $origen->tipo_comprobante === 'factura' ? '01' : '03';
                $numDocAfectado = ($origen->serieComprobante?->serie ?? 'B001') . '-' . str_pad($origen->correlativo ?? 1, 8, '0', STR_PAD_LEFT);
                $note->setTipDocAfectado($tipDocAfectado)
                    ->setNumDocfectado($numDocAfectado)
                    ->setCodMotivo($venta->motivo_nc_codigo ?? '01')
                    ->setDesMotivo($venta->motivo_nc_descripcion ?? 'Anulación de la operación');
            }
        }

        $details = [];
        foreach ($venta->detalles as $det) {
            $nombre = $det->producto?->nombre ?? 'Producto';
            if ($det->variante?->nombre_completo) $nombre .= ' - ' . $det->variante->nombre_completo;

            $subtotalDet   = (float) $det->subtotal;
            $baseImponible = round($subtotalDet / 1.18, 2);
            $igvLinea      = round($subtotalDet - $baseImponible, 2);

            $detail = new SaleDetail();
            $detail->setCodProducto($det->producto?->codigo ?? 'P001')
                ->setUnidad($det->producto?->unidadMedida?->codigo_sunat ?? 'NIU')
                ->setCantidad((float) $det->cantidad)
                ->setDescripcion($nombre)
                ->setMtoBaseIgv($baseImponible)
                ->setPorcentajeIgv(18.00)
                ->setIgv($igvLinea)
                ->setTipAfeIgv('10')
                ->setTotalImpuestos($igvLinea)
                ->setMtoValorVenta($baseImponible)
                ->setMtoValorUnitario(round((float) $det->precio_unitario / 1.18, 6))
                ->setMtoPrecioUnitario((float) $det->precio_unitario);
            $details[] = $detail;
        }

        $note->setDetails($details);

        $legend = new Legend();
        $legend->setCode('1000')
            ->setValue($this->numberToWords(round((float) $venta->total, 2)));
        $note->setLegends([$legend]);

        return $note;
    }

    private function buildCompany(Empresa $empresa): Company
    {
        $company = new Company();
        $company->setRuc($empresa->ruc)
            ->setRazonSocial($empresa->razon_social)
            ->setNombreComercial($empresa->nombre_comercial ?? $empresa->razon_social);

        $address = new Address();
        $address->setUbigueo($empresa->ubigeo ?? '150101')
            ->setDireccion($empresa->direccion ?? '');
        $company->setAddress($address);

        return $company;
    }

    private function buildClient(Venta $venta): Client
    {
        $tipoDoc = match (strtolower($venta->cliente?->tipo_documento ?? 'dni')) {
            'ruc' => '6', 'dni' => '1', 'pasaporte' => '7', 'ce' => '4', default => '0',
        };

        $client = new Client();
        $client->setTipoDoc($tipoDoc)
            ->setNumDoc($venta->cliente?->numero_documento ?? $venta->cliente?->documento ?? '00000000')
            ->setRznSocial($venta->cliente?->nombre ?? 'CLIENTE VARIOS');

        return $client;
    }

    private function guardarXml(See $see, Venta $venta): void
    {
        try {
            $xml = $see->getFactory()->getLastXml();
            if ($xml) {
                $serie = $venta->serieComprobante?->serie ?? 'B001';
                $corr  = str_pad($venta->correlativo ?? 1, 8, '0', STR_PAD_LEFT);
                Storage::disk('local')->put("sunat/xml/{$serie}-{$corr}.xml", $xml);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo guardar XML', ['error' => $e->getMessage()]);
        }
    }

    private function guardarCdr($result, Venta $venta): void
    {
        try {
            $cdrZip = $result->getCdrZip();
            if ($cdrZip) {
                $serie = $venta->serieComprobante?->serie ?? 'B001';
                $corr  = str_pad($venta->correlativo ?? 1, 8, '0', STR_PAD_LEFT);
                Storage::disk('local')->put("sunat/cdr/R-{$serie}-{$corr}.zip", $cdrZip);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo guardar CDR', ['error' => $e->getMessage()]);
        }
    }

    private function numberToWords(float $amount): string
    {
        $entero = (int) $amount;
        $decimal = round(($amount - $entero) * 100);
        return strtoupper("SON {$entero} CON {$decimal}/100 SOLES");
    }
}
