<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
@page { margin: 8mm 6mm; }
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 7.5pt;
    color: #111;
    background: #fff;
    width: 100%;
}

.center { text-align: center; }
.right  { text-align: right; }
.bold   { font-weight: bold; }

.divider { border-top: 1px dashed #999; margin: 5px 0; }
.divider-solid { border-top: 1px solid #333; margin: 5px 0; }

/* ─── HEADER ─── */
.empresa-logo { display: block; margin: 0 auto 4px; max-height: 50px; max-width: 90%; }
.empresa-nombre { font-size: 9.5pt; font-weight: bold; text-align: center; color: #1e3a5f; margin-bottom: 2px; }
.empresa-info { text-align: center; font-size: 6.5pt; color: #444; line-height: 1.5; margin-bottom: 3px; }

/* ─── DOC TITLE ─── */
.doc-tipo { text-align: center; font-weight: bold; font-size: 8pt; text-transform: uppercase;
            border-top: 1px solid #333; border-bottom: 1px solid #333;
            padding: 3px 0; margin: 5px 0; letter-spacing: 0.5px; }
.doc-numero { text-align: center; font-size: 8pt; font-weight: bold; color: #1e3a5f;
              margin-bottom: 5px; letter-spacing: 0.5px; }

/* ─── CLIENTE ─── */
.label { font-size: 6.5pt; color: #444; }
.value { font-size: 7.5pt; color: #111; }
.row { display: table; width: 100%; margin-bottom: 1px; }
.row-l { display: table-cell; font-size: 6.5pt; color: #444; width: 65px; }
.row-v { display: table-cell; font-size: 7.5pt; }

/* ─── ITEMS ─── */
table.items { width: 100%; border-collapse: collapse; margin: 4px 0; }
table.items thead th {
    font-size: 6.5pt; font-weight: bold; text-align: left;
    border-bottom: 1px solid #333; padding: 2px 1px;
}
table.items thead th.r { text-align: right; }
table.items thead th.c { text-align: center; }
table.items tbody td { font-size: 7pt; padding: 2px 1px; vertical-align: top; }
table.items tbody td.r { text-align: right; }
table.items tbody td.c { text-align: center; }
table.items tfoot td { font-size: 7pt; padding: 2px 1px; }

/* ─── TOTALES ─── */
.total-row { display: table; width: 100%; padding: 1px 0; }
.total-lbl { display: table-cell; font-size: 7.5pt; color: #333; }
.total-val { display: table-cell; font-size: 7.5pt; text-align: right; font-weight: bold; }
.total-final .total-lbl { font-size: 9pt; font-weight: bold; }
.total-final .total-val { font-size: 9pt; }

/* ─── FOOTER ─── */
.footer-text { font-size: 6pt; color: #555; text-align: center; line-height: 1.5; margin-top: 4px; }
.qr-wrap { text-align: center; margin: 6px 0; }
.qr-img  { width: 70px; height: 70px; }
</style>
</head>
<body>

{{-- ─── EMPRESA ─── --}}
@php
    $logoFile = $empresa->logo_pdf_path ?: $empresa->logo_path;
    $logoPath = $logoFile ? str_replace('\\', '/', storage_path('app/public/' . $logoFile)) : null;
@endphp
@if($logoPath && file_exists($logoPath))
    <img src="{{ $logoPath }}" class="empresa-logo" alt="Logo">
@endif
<div class="empresa-nombre">{{ $empresa->razon_social }}</div>
<div class="empresa-info">
    R.U.C.: {{ $empresa->ruc }}<br>
    @if($empresa->direccion){{ $empresa->direccion }}<br>@endif
    @if($empresa->distrito){{ implode(' - ', array_filter([$empresa->distrito, $empresa->provincia, $empresa->departamento])) }}<br>@endif
    @if($empresa->telefono)&#9990; Telf: {{ $empresa->telefono }}<br>@endif
    @if($empresa->email)&#9993; Email: {{ $empresa->email }}<br>@endif
    @if($empresa->web)&#127760; Website: {{ $empresa->web }}@endif
</div>

{{-- ─── TIPO DOCUMENTO ─── --}}
@php
    $tiposNombre = [
        'factura'   => 'Factura de Venta Electrónica',
        'boleta'    => 'Boleta de Venta Electrónica',
        'cotizacion'=> 'Nota de Entrega / Cotización',
    ];
@endphp
<div class="doc-tipo">{{ $tiposNombre[$venta->tipo_comprobante] ?? ucfirst($venta->tipo_comprobante) }}</div>
<div class="doc-numero">{{ $venta->numero_documento ?? $venta->codigo }}</div>

{{-- ─── FECHA / PAGO ─── --}}
<div class="row">
    <div class="row-l">Fecha Emisión:</div>
    <div class="row-v">{{ $venta->fecha->format('d-m-Y') }} / {{ $venta->created_at->format('H:i') }} PM</div>
</div>
@php $metodosLabel = ['efectivo'=>'Efectivo','yape'=>'Yape','plin'=>'Plin','transferencia'=>'Transferencia','mixto'=>'Mixto']; @endphp
<div class="row">
    <div class="row-l">Forma de Pago:</div>
    <div class="row-v">{{ $metodosLabel[$venta->metodo_pago] ?? ucfirst($venta->metodo_pago ?? '—') }}</div>
</div>

<div class="divider"></div>

{{-- ─── CLIENTE ─── --}}
@if($venta->cliente)
<div class="row"><div class="row-l">Razón Social:</div><div class="row-v bold">{{ $venta->cliente->nombre }}</div></div>
<div class="row"><div class="row-l">{{ $venta->tipo_comprobante === 'factura' ? 'R.U.C.' : 'Doc.' }}:</div><div class="row-v">{{ $venta->cliente->numero_documento }}</div></div>
@if($venta->cliente->direccion)
<div class="row"><div class="row-l">Dirección:</div><div class="row-v">{{ $venta->cliente->direccion }}</div></div>
@endif
<div class="divider"></div>
@endif

{{-- ─── ITEMS ─── --}}
<table class="items">
    <thead>
        <tr>
            <th style="width:50px">Código</th>
            <th>Descripción</th>
            <th class="c" style="width:22px">Cant</th>
            <th class="r" style="width:40px">Precio</th>
            <th class="r" style="width:44px">Importe</th>
        </tr>
    </thead>
    <tbody>
        @foreach($venta->detalles as $d)
        <tr>
            <td>{{ $d->producto?->codigo }}</td>
            <td>
                {{ $d->producto?->nombre }}
                @if($d->variante)<br><span style="font-size:6pt;color:#555">{{ $d->variante->nombre_completo }}</span>@endif
                @if($d->imei)<br><span style="font-size:6pt;color:#555">S/N: {{ $d->imei->codigo_imei }}</span>@endif
            </td>
            <td class="c">{{ $d->cantidad }}</td>
            <td class="r">S/{{ number_format($d->precio_unitario, 2) }}</td>
            <td class="r">S/{{ number_format($d->precio_unitario * $d->cantidad, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="divider-solid"></div>

{{-- ─── SON ─── --}}
<div style="font-size:6.5pt;color:#444;margin-bottom:3px">
    Son: <strong>{{ montoEnLetras($venta->total) }}</strong>
</div>

{{-- ─── TOTALES ─── --}}
<div class="total-row">
    <div class="total-lbl">Gravado:</div>
    <div class="total-val">S/ {{ number_format($venta->subtotal, 2) }}</div>
</div>
<div class="total-row">
    <div class="total-lbl">IGV (18%):</div>
    <div class="total-val">S/ {{ number_format($venta->igv, 2) }}</div>
</div>
<div class="total-row">
    <div class="total-lbl">Descuento Total:</div>
    <div class="total-val">S/ 0.00</div>
</div>
<div class="divider-solid"></div>
<div class="total-row total-final">
    <div class="total-lbl">Total a Pagar:</div>
    <div class="total-val">S/ {{ number_format($venta->total, 2) }}</div>
</div>

{{-- ─── INFO PAGO DIGITAL ─── --}}
@if(in_array($venta->metodo_pago, ['yape','plin']) && $pagos->get($venta->metodo_pago))
    @php $pg = $pagos->get($venta->metodo_pago); @endphp
    <div class="divider"></div>
    <div class="center bold" style="font-size:7pt">{{ ucfirst($venta->metodo_pago) }}</div>
    @if($pg->titular)<div class="center" style="font-size:6.5pt">{{ $pg->titular }}</div>@endif
    @if($pg->numero) <div class="center bold" style="font-size:8pt">{{ $pg->numero }}</div>@endif
    @if($pg->qr_imagen_path && file_exists(storage_path('app/public/' . $pg->qr_imagen_path)))
        <div class="qr-wrap">
            <img src="{{ storage_path('app/public/' . $pg->qr_imagen_path) }}" class="qr-img">
        </div>
    @endif
@elseif($venta->metodo_pago === 'transferencia' && $pagos->get('transferencia'))
    @php $pg = $pagos->get('transferencia'); @endphp
    <div class="divider"></div>
    <div class="center" style="font-size:6.5pt">
        @if($pg->banco) BANCO: {{ strtoupper($pg->banco) }}<br>@endif
        @if($pg->numero)CTA (SOLES): {{ $pg->numero }}<br>@endif
        @if($pg->cci)   CCI: {{ $pg->cci }}<br>@endif
    </div>
@endif

<div class="divider"></div>

{{-- ─── QR CONSULTA ─── --}}
@php
    $qrUrl = route('ventas.show', $venta->id);
    $qrB64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(80)->generate($qrUrl));
@endphp
<div class="qr-wrap">
    <img src="data:image/svg+xml;base64,{{ $qrB64 }}" class="qr-img">
</div>

{{-- ─── FOOTER LEGAL ─── --}}
<div class="footer-text">
    Representación Impresa de la {{ $tiposNombre[$venta->tipo_comprobante] ?? 'Comprobante' }}<br>
    Consulte su Documento en:<br>
    @if($empresa->web)<strong>{{ $empresa->web }}</strong><br>@endif
    @if($venta->vendedor)VENDEDOR: {{ $venta->vendedor->name }}<br>@endif
    @if($venta->sucursal)Sucursal: {{ $venta->sucursal->nombre }}<br>@endif
    <br>
    <em>Generado en sistema Adivon ERP</em>
</div>

</body>
</html>
