<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Traslado - Sistema de Importaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />
    <x-header title="Detalle del Traslado" />

    <div class="ml-64 p-8 pt-24">
        <div class="flex items-center mb-6">
            <a href="{{ route('traslados.index') }}" class="text-blue-600 hover:text-blue-800 mr-4"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-2xl font-bold text-gray-800">Traslado {{ $traslado->numero_guia ?? '#' . $traslado->id }}</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Información del Traslado</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">N° Guía:</dt><dd class="font-mono font-semibold">{{ $traslado->numero_guia ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Producto:</dt><dd>{{ $traslado->producto->nombre }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Cantidad:</dt><dd class="font-semibold">{{ $traslado->cantidad }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Origen:</dt><dd>{{ $traslado->almacen->nombre }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Destino:</dt><dd>{{ $traslado->almacenDestino->nombre ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Transportista:</dt><dd>{{ $traslado->transportista ?? '-' }}</dd></div>
                    @if($traslado->imei)
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">IMEI:</dt><dd class="font-mono text-purple-600">{{ $traslado->imei->codigo_imei }}</dd></div>
                    @endif
                </dl>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Estado y Seguimiento</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Estado:</dt>
                        <dd>
                            @php $colores = ['pendiente' => 'bg-yellow-100 text-yellow-800', 'confirmado' => 'bg-green-100 text-green-800']; @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $colores[$traslado->estado] ?? 'bg-gray-100' }}">{{ ucfirst($traslado->estado) }}</span>
                        </dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Enviado por:</dt><dd>{{ $traslado->usuario->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Fecha envío:</dt><dd>{{ $traslado->fecha_traslado ?? $traslado->created_at->format('d/m/Y') }}</dd></div>
                    @if($traslado->usuarioConfirma)
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Confirmado por:</dt><dd>{{ $traslado->usuarioConfirma->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Fecha recepción:</dt><dd>{{ $traslado->fecha_recepcion }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</body>
</html>
