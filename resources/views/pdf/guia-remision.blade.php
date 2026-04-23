<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guía de Remisión - {{ $venta->codigo }}</title>
    <style>
        @page { size: A4; margin: 10mm 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8px; color: #1e293b; background: #fff; }
        .page { padding: 0; }

        /* ── Header ── */
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1e40af; padding-bottom: 8px; margin-bottom: 8px; }
        .empresa-name { font-size: 12px; font-weight: 700; color: #1e40af; line-height: 1.2; }
        .empresa-info { font-size: 7.5px; color: #64748b; margin-top: 2px; line-height: 1.5; }
        .doc-box { border: 2px solid #1e40af; border-radius: 5px; padding: 6px 12px; text-align: center; min-width: 155px; }
        .doc-box .tipo { font-size: 7px; font-weight: 700; color: #1e40af; letter-spacing: 1px; text-transform: uppercase; line-height: 1.3; }
        .doc-box .codigo { font-size: 14px; font-weight: 700; color: #0f172a; margin: 2px 0; font-family: monospace; }
        .doc-box .fecha-label { font-size: 7px; color: #64748b; }
        .doc-box .fecha-val  { font-size: 9px; font-weight: 600; color: #0f172a; }
        .badge { background: #dbeafe; color: #1e40af; border-radius: 3px; padding: 1px 6px; font-size: 7px; font-weight: 700; display: inline-block; margin-top: 2px; }

        /* ── Sections ── */
        .row { display: flex; gap: 6px; margin-bottom: 6px; }
        .row > .box { flex: 1; border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px 7px; }
        .section-title { background: #1e40af; color: #fff; font-size: 7px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 2px 7px; border-radius: 2px; margin-bottom: 5px; }
        .fields { display: flex; flex-wrap: wrap; gap: 4px 10px; }
        .field { min-width: 80px; }
        .field.wide { min-width: 160px; flex: 1; }
        .field-label { font-size: 6.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 1px; }
        .field-value { font-size: 8.5px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px; min-height: 12px; }
        .field-value.bold { font-weight: 700; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; font-size: 7.5px; margin-top: 0; }
        thead tr { background: #1e40af; }
        thead th { color: #fff; font-weight: 600; padding: 3px 6px; text-align: left; font-size: 7px; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 2.5px 6px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tfoot td { border-top: 1.5px solid #cbd5e1; font-weight: 700; padding: 3px 6px; background: #f1f5f9; font-size: 7.5px; }

        /* ── Footer / Firmas ── */
        .footer { margin-top: 8px; border-top: 1px solid #e2e8f0; padding-top: 7px; }
        .firma-row { display: flex; gap: 10px; }
        .firma-row > div { flex: 1; }
        .firma-area { border: 1px solid #cbd5e1; border-radius: 3px; height: 38px; margin-top: 3px; }
        .firma-sub { text-align: center; font-size: 7px; color: #94a3b8; margin-top: 2px; }
        .generated { text-align: center; margin-top: 6px; color: #94a3b8; font-size: 7px; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page { page-break-after: avoid; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ENCABEZADO --}}
    @php
        $logoFile = $empresa?->logo_pdf_path ?: $empresa?->logo_path;
        $logoPath = $logoFile ? storage_path('app/public/' . $logoFile) : null;
        $logoSrc  = null;
        if ($logoPath && file_exists($logoPath)) {
            $ext     = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $mime    = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : "image/$ext";
            $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
        $numSec = 1;
    @endphp
    <div class="header">
        <div>
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Logo" style="height:30px; margin-bottom:3px; display:block;">
            @endif
            <div class="empresa-name">{{ $empresa?->nombre_display ?? config('app.name') }}</div>
            <div class="empresa-info">
                RUC: {{ $empresa?->ruc ?? '—' }} &nbsp;|&nbsp; {{ $empresa?->direccion ?? '—' }}
                @if($empresa?->telefono) &nbsp;|&nbsp; Tel: {{ $empresa->telefono }}@endif
            </div>
        </div>
        <div class="doc-box">
            <div class="tipo">Guía de Remisión<br>Remitente</div>
            <div class="codigo">{{ $venta->codigo }}</div>
            <div class="fecha-label">Fecha de traslado</div>
            <div class="fecha-val">{{ $guia->fecha_traslado?->format('d/m/Y') ?? '—' }}</div>
            <div><span class="badge">{{ $guia->motivo_label }}</span></div>
        </div>
    </div>

    {{-- FILA 1: Remitente + Destinatario --}}
    <div class="row">
        <div class="box" style="flex:1">
            <div class="section-title">{{ $numSec++ }}. Remitente</div>
            <div class="fields">
                <div class="field wide">
                    <div class="field-label">Razón Social</div>
                    <div class="field-value bold">{{ $empresa?->razon_social ?? '—' }}</div>
                </div>
                <div class="field">
                    <div class="field-label">RUC</div>
                    <div class="field-value">{{ $empresa?->ruc ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="box" style="flex:1.6">
            <div class="section-title">{{ $numSec++ }}. Destinatario</div>
            <div class="fields">
                <div class="field wide">
                    <div class="field-label">Nombre / Razón Social</div>
                    <div class="field-value bold">{{ $venta->cliente?->nombre ?? 'Consumidor Final' }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Tipo Doc.</div>
                    <div class="field-value">{{ strtoupper($venta->cliente?->tipo_documento ?? '—') }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Nro. Documento</div>
                    <div class="field-value">{{ $venta->cliente?->numero_documento ?? '—' }}</div>
                </div>
                @if($venta->cliente?->direccion)
                <div class="field wide">
                    <div class="field-label">Dirección</div>
                    <div class="field-value">{{ $venta->cliente->direccion }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- FILA 2: Traslado + Transportista (si aplica) --}}
    <div class="row">
        <div class="box" style="flex:2">
            <div class="section-title">{{ $numSec++ }}. Datos del Traslado</div>
            <div class="fields">
                <div class="field wide">
                    <div class="field-label">Motivo</div>
                    <div class="field-value bold">{{ $guia->motivo_label }}</div>
                </div>
                <div class="field wide">
                    <div class="field-label">Modalidad de Transporte</div>
                    <div class="field-value">{{ $guia->modalidad_label }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Fecha Traslado</div>
                    <div class="field-value">{{ $guia->fecha_traslado?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Peso Bruto</div>
                    <div class="field-value">{{ $guia->peso_total ? number_format($guia->peso_total,2).' kg' : '—' }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Nro. Bultos</div>
                    <div class="field-value">{{ $guia->bultos ?? '—' }}</div>
                </div>
            </div>
        </div>
        @if($guia->transportista_nombre)
        <div class="box" style="flex:1.4">
            <div class="section-title">{{ $numSec++ }}. Transportista</div>
            <div class="fields">
                <div class="field">
                    <div class="field-label">Tipo Doc.</div>
                    <div class="field-value">{{ $guia->transportista_tipo_doc ?? '—' }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Nro. Doc.</div>
                    <div class="field-value">{{ $guia->transportista_doc ?? '—' }}</div>
                </div>
                <div class="field wide">
                    <div class="field-label">Razón Social / Nombre</div>
                    <div class="field-value bold">{{ $guia->transportista_nombre }}</div>
                </div>
                @if($venta->placa_vehiculo)
                <div class="field">
                    <div class="field-label">Placa</div>
                    <div class="field-value">{{ $venta->placa_vehiculo }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- FILA 3: Puntos de Partida y Llegada --}}
    <div class="row">
        <div class="box">
            <div class="section-title">{{ $numSec++ }}. Punto de Partida (Origen)</div>
            <div class="fields">
                <div class="field wide">
                    <div class="field-label">Dirección</div>
                    <div class="field-value">{{ $guia->direccion_partida ?? '—' }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Ubigeo</div>
                    <div class="field-value">{{ $guia->ubigeo_partida ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="section-title">{{ $numSec++ }}. Punto de Llegada (Destino)</div>
            <div class="fields">
                <div class="field wide">
                    <div class="field-label">Dirección</div>
                    <div class="field-value">{{ $guia->direccion_llegada ?? '—' }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Ubigeo</div>
                    <div class="field-value">{{ $guia->ubigeo_llegada ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLA DE BIENES --}}
    <div style="border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px 7px; margin-bottom: 8px;">
        <div class="section-title" style="margin-bottom:5px">{{ $numSec++ }}. Detalle de Bienes a Trasladar</div>
        <table>
            <thead>
                <tr>
                    <th style="width:24px">#</th>
                    <th>Descripción del Bien</th>
                    <th style="width:55px; text-align:center">Cantidad</th>
                    <th style="width:42px; text-align:center">Unidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $i => $detalle)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        {{ $detalle->producto->nombre }}
                        @if($detalle->variante)
                            <span style="color:#6366f1"> — {{ $detalle->variante->nombre_completo }}</span>
                        @endif
                        @if($detalle->imei)
                            <span style="color:#64748b"> [{{ $detalle->imei->codigo_imei }}]</span>
                        @endif
                    </td>
                    <td style="text-align:center; font-weight:700">{{ $detalle->cantidad }}</td>
                    <td style="text-align:center">UND</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align:right; font-size:7px">Total unidades:</td>
                    <td style="text-align:center">{{ $venta->detalles->sum('cantidad') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- FIRMAS --}}
    <div class="footer">
        <div class="firma-row">
            <div>
                <div class="field-label">Firma y Sello del Remitente</div>
                <div class="firma-area"></div>
                <div class="firma-sub">{{ $empresa?->razon_social ?? '' }}</div>
            </div>
            <div>
                <div class="field-label">Firma del Destinatario / Conformidad de Recepción</div>
                <div class="firma-area"></div>
                <div class="firma-sub">Fecha de recepción: ___/___/______</div>
            </div>
            <div>
                <div class="field-label">Chofer / Transportista</div>
                <div class="firma-area"></div>
                <div class="firma-sub">DNI: ___________________</div>
            </div>
        </div>
        <div class="generated">
            Documento generado el {{ now()->format('d/m/Y H:i') }} · {{ config('app.name') }}
        </div>
    </div>

</div>
</body>
</html>
