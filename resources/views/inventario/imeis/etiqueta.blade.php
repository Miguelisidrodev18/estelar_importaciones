<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Etiqueta IMEI - {{ $imei->codigo_imei }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .etiqueta {
            width: 5cm;
            height: 3cm;
            border: 1px solid #ccc;
            padding: 5px;
            position: relative;
            page-break-inside: avoid;
        }
        .qr {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 60px;
            height: 60px;
        }
        .qr img {
            width: 60px;
            height: 60px;
        }
        .info {
            font-size: 10px;
            margin-right: 65px;
        }
        .imei {
            font-size: 12px;
            font-weight: bold;
            font-family: monospace;
            margin: 2px 0;
        }
        .producto {
            font-size: 10px;
            margin: 2px 0;
        }
        .marca {
            font-size: 9px;
            color: #666;
        }
        .estado {
            position: absolute;
            bottom: 5px;
            left: 5px;
            right: 5px;
            font-size: 8px;
            text-align: center;
            color: #999;
            border-top: 1px dashed #ccc;
            padding-top: 2px;
        }
        @media print {
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="etiqueta">
        <div class="qr">
            <img src="{{ $qrUrl }}" alt="QR">
        </div>
        <div class="info">
            <div class="imei">{{ $imei->codigo_imei }}</div>
            <div class="producto">{{ $imei->producto->nombre }}</div>
            <div class="marca">
                {{ $imei->producto->marca->nombre ?? '' }} 
                {{ $imei->producto->modelo->nombre ?? '' }}
                @if($imei->color)
                    - {{ $imei->color->nombre }}
                @endif
            </div>
            @if($imei->serie)
                <div class="marca">Serie: {{ $imei->serie }}</div>
            @endif
        </div>
        <div class="estado">
            {{ strtoupper(str_replace('_', ' ', $imei->estado_imei)) }}
        </div>
    </div>
</body>
</html>