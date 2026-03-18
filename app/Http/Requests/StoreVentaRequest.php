<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipo         = $this->input('tipo_comprobante', 'boleta');
        $esCotizacion = $tipo === 'cotizacion';

        return [
            // Cliente: obligatorio para boleta/factura, opcional en cotización
            'cliente_id'               => $esCotizacion
                                            ? 'nullable|exists:clientes,id'
                                            : 'required|exists:clientes,id',

            'almacen_id'               => 'required|exists:almacenes,id',
            'observaciones'            => 'nullable|string',
            'tipo_comprobante'         => 'required|in:boleta,factura,cotizacion',

            // Envío
            'guia_remision'            => 'nullable|string|max:100',
            'transportista'            => 'nullable|string|max:150',
            'placa_vehiculo'           => 'nullable|string|max:20',

            // Pago
            'metodo_pago'              => 'nullable|in:efectivo,transferencia,yape,plin,mixto',
            'pagos_detalle'            => 'nullable|array',
            'pagos_detalle.*.metodo'   => 'required_with:pagos_detalle|in:efectivo,transferencia,yape,plin',
            'pagos_detalle.*.monto'    => 'required_with:pagos_detalle|numeric|min:0.01',

            // Detalle de productos
            'detalles'                       => 'required|array|min:1',
            'detalles.*.producto_id'         => 'required|exists:productos,id',
            'detalles.*.variante_id'         => 'nullable|exists:producto_variantes,id',
            'detalles.*.cantidad'            => 'required|integer|min:1',
            'detalles.*.precio_unitario'     => 'required|numeric|min:0.01',
            'detalles.*.imeis'               => 'nullable|array',
            'detalles.*.imeis.*.codigo_imei' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe seleccionar un cliente para registrar la venta.',
            'cliente_id.exists'   => 'El cliente seleccionado no es válido.',
            'detalles.required'   => 'Debe agregar al menos un producto.',
            'detalles.min'        => 'Debe agregar al menos un producto.',
        ];
    }

    /**
     * Regla extra: si el comprobante es FACTURA, el cliente debe tener RUC de 11 dígitos.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $tipo      = $this->input('tipo_comprobante');
            $clienteId = $this->input('cliente_id');

            if ($tipo !== 'factura') {
                return;
            }

            if (!$clienteId) {
                $v->errors()->add(
                    'cliente_id',
                    'Para emitir factura debe seleccionar un cliente con RUC.'
                );
                return;
            }

            $cliente = Cliente::find($clienteId);

            if (!$cliente) {
                return; // ya lo captura la regla 'exists'
            }

            if ($cliente->tipo_documento !== 'RUC') {
                $v->errors()->add(
                    'cliente_id',
                    'Para emitir factura, el cliente debe tener RUC (actualmente tiene ' . $cliente->tipo_documento . ').'
                );
                return;
            }

            if (strlen($cliente->numero_documento) !== 11) {
                $v->errors()->add(
                    'cliente_id',
                    'Para emitir factura, el RUC debe tener exactamente 11 dígitos.'
                );
            }
        });
    }
}
