<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guía de Remisión - {{ $venta->codigo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; background: #fff; }
        .page { padding: 22px 28px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2.5px solid #1e40af; padding-bottom: 14px; margin-bottom: 16px; }
        .empresa-name { font-size: 15px; font-weight: 700; color: #1e40af; }
        .empresa-info { font-size: 9px; color: #64748b; margin-top: 3px; line-height: 1.5; }
        .doc-box { border: 2px solid #1e40af; border-radius: 6px; padding: 10px 16px; text-align: center; min-width: 170px; }
        .doc-box .tipo { font-size: 8px; font-weight: 700; color: #1e40af; letter-spacing: 1.5px; text-transform: uppercase; line-height: 1.3; }
        .doc-box .codigo { font-size: 17px; font-weight: 700; color: #0f172a; margin: 4px 0; font-family: monospace; }
        .doc-box .fecha-label { font-size: 8px; color: #64748b; margin-top: 4px; }
        .doc-box .fecha-val  { font-size: 10px; font-weight: 600; color: #0f172a; }
        .badge { background: #dbeafe; color: #1e40af; border-radius: 3px; padding: 2px 7px; font-size: 8px; font-weight: 700; display: inline-block; margin-top: 4px; }

        /* Sections */
        .section { margin-bottom: 12px; }
        .section-title { background: #1e40af; color: #fff; font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 9px; border-radius: 3px; margin-bottom: 7px; }
        .grid-2 { display: flex; gap: 8px; }
        .grid-2 > * { flex: 1; }
        .grid-3 { display: flex; gap: 8px; }
        .grid-3 > * { flex: 1; }
        .field { margin-bottom: 5px; }
        .field-label { font-size: 7.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .field-value { font-size: 10px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; min-height: 14px; }
        .field-value.bold { font-weight: 700; }

        /* Puntos */
        .punto-box { border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px; }
        .punto-title { font-size: 8px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; border-bottom: 1px solid #f1f5f9; padding-bottom: 3px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        thead tr { background: #1e40af; }
        thead th { color: #fff; font-weight: 600; padding: 5px 8px; text-align: left; font-size: 8.5px; }
        thead th:last-child { text-align: center; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 4px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        tbody td:last-child { text-align: center; }
        tfoot td { border-top: 2px solid #cbd5e1; font-weight: 700; padding: 5px 8px; background: #f8fafc; }

        /* Footer */
        .footer { margin-top: 22px; border-top: 1px solid #e2e8f0; padding-top: 14px; }
        .firma-area { border: 1px solid #cbd5e1; border-radius: 4px; height: 55px; margin-top: 5px; }
        .generated { text-align: center; margin-top: 12px; color: #94a3b8; font-size: 8px; }
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
    @endphp
    <div class="header">
        <div>
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Logo" style="height:38px; margin-bottom:5px; display:block;">
            @endif
            <div class="empresa-name">{{ $empresa?->nombre_display ?? config('app.name') }}</div>
            <div class="empresa-info">
                RUC: {{ $empresa?->ruc ?? '—' }}<br>
                {{ $empresa?->direccion ?? '—' }}<br>
                @if($empresa?->telefono) Tel: {{ $empresa->telefono }}@endif
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

    {{-- 1. REMITENTE --}}
    <div class="section">
        <div class="section-title">1. Datos del Remitente</div>
        <div class="grid-2">
            <div class="field">
                <div class="field-label">Razón Social / Nombre</div>
                <div class="field-value bold">{{ $empresa?->razon_social ?? '—' }}</div>
            </div>
            <div class="field">
                <div class="field-label">RUC</div>
                <div class="field-value">{{ $empresa?->ruc ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- 2. DESTINATARIO --}}
    <div class="section">
        <div class="section-title">2. Destinatario</div>
        <div class="grid-3">
            <div class="field" style="flex:2">
                <div class="field-label">Nombre / Razón Social</div>
                <div class="field-value bold">{{ $venta->cliente?->nombre ?? 'Consumidor Final' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Tipo Documento</div>
                <div class="field-value">{{ strtoupper($venta->cliente?->tipo_documento ?? '—') }}</div>
            </div>
            <div class="field">
                <div class="field-label">Nro. Documento</div>
                <div class="field-value">{{ $venta->cliente?->numero_documento ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- 3. DATOS DEL TRASLADO --}}
    <div class="section">
        <div class="section-title">3. Datos del Traslado</div>
        <div class="grid-3">
            <div class="field" style="flex:2">
                <div class="field-label">Motivo de Traslado</div>
                <div class="field-value bold">{{ $guia->motivo_label }}</div>
            </div>
            <div class="field" style="flex:2">
                <div class="field-label">Modalidad de Transporte</div>
                <div class="field-value">{{ $guia->modalidad_label }}</div>
            </div>
            <div class="field">
                <div class="field-label">Fecha de Traslado</div>
                <div class="field-value">{{ $guia->fecha_traslado?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Peso Bruto Total</div>
                <div class="field-value">{{ $guia->peso_total ? number_format($guia->peso_total, 2) . ' kg' : '—' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Nro. de Bultos</div>
                <div class="field-value">{{ $guia->bultos ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- 4. TRANSPORTISTA (solo si aplica) --}}
    @if($guia->transportista_nombre)
    <div class="section">
        <div class="section-title">4. Datos del Transportista</div>
        <div class="grid-3">
            <div class="field">
                <div class="field-label">Tipo Documento</div>
                <div class="field-value">{{ $guia->transportista_tipo_doc ?? '—' }}</div>
            </div>
            <div class="field">
                <div class="field-label">Nro. Documento</div>
                <div class="field-value">{{ $guia->transportista_doc ?? '—' }}</div>
            </div>
            <div class="field" style="flex:2">
                <div class="field-label">Razón Social / Nombre</div>
                <div class="field-value bold">{{ $guia->transportista_nombre }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- 5. PUNTOS DE PARTIDA Y LLEGADA --}}
    <div class="section">
        <div class="section-title">{{ $guia->transportista_nombre ? '5' : '4' }}. Puntos de Partida y Llegada</div>
        <div class="grid-2">
            <div class="punto-box">
                <div class="punto-title">&#x1F4CD; Punto de Partida (Origen)</div>
                <div class="field">
                    <div class="field-label">Dirección</div>
                    <div class="field-value">{{ $guia->direccion_partida ?? '—' }}</div>
                </div>
                <div class="field" style="margin-top:4px">
                    <div class="field-label">Ubigeo</div>
                    <div class="field-value">{{ $guia->ubigeo_partida ?? '—' }}</div>
                </div>
            </div>
            <div class="punto-box">
                <div class="punto-title">&#x1F3C1; Punto de Llegada (Destino)</div>
                <div class="field">
                    <div class="field-label">Dirección</div>
                    <div class="field-value">{{ $guia->direccion_llegada ?? '—' }}</div>
                </div>
                <div class="field" style="margin-top:4px">
                    <div class="field-label">Ubigeo</div>
                    <div class="field-value">{{ $guia->ubigeo_llegada ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 6. DETALLE DE BIENES --}}
    <div class="section">
        <div class="section-title">{{ $guia->transportista_nombre ? '6' : '5' }}. Detalle de Bienes a Trasladar</div>
        <table>
            <thead>
                <tr>
                    <th style="width:30px">#</th>
                    <th>Descripción del Bien</th>
                    <th style="width:60px; text-align:center">Cantidad</th>
                    <th style="width:50px; text-align:center">Unidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $i => $detalle)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        {{ $detalle->producto->nombre }}
                        @if($detalle->variante)
                            <span style="color:#6366f1; font-size:8px"> — {{ $detalle->variante->nombre_completo }}</span>
                        @endif
                        @if($detalle->imei)
                            <span style="color:#64748b; font-size:8px"> [{{ $detalle->imei->codigo_imei }}]</span>
                        @endif
                    </td>
                    <td style="text-align:center; font-weight:700">{{ $detalle->cantidad }}</td>
                    <td style="text-align:center">UND</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align:right">Total unidades:</td>
                    <td style="text-align:center">{{ $venta->detalles->sum('cantidad') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- FIRMAS --}}
    <div class="footer">
        <div class="grid-2">
            <div>
                <div class="field-label">Firma y Sello del Remitente</div>
                <div class="firma-area"></div>
                <div style="text-align:center; font-size:8px; color:#94a3b8; margin-top:3px;">{{ $empresa?->razon_social ?? '' }}</div>
            </div>
            <div>
                <div class="field-label">Firma del Destinatario / Conformidad de Recepción</div>
                <div class="firma-area"></div>
                <div style="text-align:center; font-size:8px; color:#94a3b8; margin-top:3px;">Fecha de recepción: ___/___/______</div>
            </div>
        </div>
        <div class="generated">
            Documento generado el {{ now()->format('d/m/Y H:i') }} · {{ config('app.name') }}
        </div>
    </div>

</div>
</body>
</html>
