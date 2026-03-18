<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Traslado - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Detalle del Traslado"
            subtitle="Información completa sobre el traslado seleccionado"
        />

        <div class="flex items-center mb-6">
            <a href="{{ route('traslados.index') }}" class="text-blue-600 hover:text-blue-800 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="text-2xl font-bold text-gray-800">
                Traslado {{ $traslado->numero_guia ?? '#' . $traslado->id }}
            </h2>
            @php
                $colores = ['pendiente' => 'bg-yellow-100 text-yellow-800', 'confirmado' => 'bg-green-100 text-green-800'];
                $esSerie = $traslado->producto->tipo_inventario === 'serie';
            @endphp
            <span class="ml-3 px-2.5 py-1 text-xs font-semibold rounded-full {{ $colores[$traslado->estado] ?? 'bg-gray-100 text-gray-700' }}">
                {{ ucfirst($traslado->estado) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            {{-- Información del Traslado --}}
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-4 flex items-center gap-2">
                    <i class="fas fa-exchange-alt text-blue-400"></i>Información del Traslado
                </h3>
                <dl class="space-y-3">
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">N° Guía</dt>
                        <dd class="font-mono font-semibold text-blue-700">{{ $traslado->numero_guia ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">Producto</dt>
                        <dd class="font-medium text-gray-800 text-right max-w-[60%]">
                            {{ $traslado->producto->nombre }}
                            @if($esSerie)
                                <span class="inline-flex items-center ml-1 px-1.5 py-0.5 text-[10px] font-semibold bg-purple-100 text-purple-700 rounded">
                                    <i class="fas fa-barcode mr-0.5"></i>IMEI
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">Cantidad</dt>
                        <dd class="font-semibold text-gray-800">
                            {{ $traslado->cantidad }}
                            <span class="text-xs text-gray-400 font-normal">{{ $esSerie ? 'IMEI(s)' : 'unidades' }}</span>
                        </dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">Origen</dt>
                        <dd class="text-gray-800 flex items-center gap-1">
                            <i class="fas fa-warehouse text-orange-400 text-xs"></i>
                            {{ $traslado->almacen->nombre }}
                        </dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">Destino</dt>
                        <dd class="text-gray-800 flex items-center gap-1">
                            <i class="fas fa-store text-green-400 text-xs"></i>
                            {{ $traslado->almacenDestino->nombre ?? '—' }}
                        </dd>
                    </div>
                    @if($traslado->transportista)
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">Transportista</dt>
                        <dd class="text-gray-700">{{ $traslado->transportista }}</dd>
                    </div>
                    @endif
                    @if($traslado->observaciones)
                    <div class="pt-1">
                        <dt class="text-gray-500 text-sm mb-1">Observaciones</dt>
                        <dd class="text-gray-600 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 italic text-xs">
                            "{{ $traslado->observaciones }}"
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Estado y Seguimiento --}}
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-4 flex items-center gap-2">
                    <i class="fas fa-route text-green-400"></i>Estado y Seguimiento
                </h3>
                <dl class="space-y-3">
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">Estado</dt>
                        <dd>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $colores[$traslado->estado] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($traslado->estado) }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">Enviado por</dt>
                        <dd class="text-gray-700">{{ $traslado->usuario->name }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">Fecha envío</dt>
                        <dd class="text-gray-700">{{ $traslado->fecha_traslado ? \Carbon\Carbon::parse($traslado->fecha_traslado)->format('d/m/Y') : $traslado->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($traslado->usuarioConfirma)
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">Confirmado por</dt>
                        <dd class="text-gray-700">{{ $traslado->usuarioConfirma->name }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">Fecha recepción</dt>
                        <dd class="text-gray-700">{{ $traslado->fecha_recepcion }}</dd>
                    </div>
                    @endif
                    @if(!$esSerie && $traslado->stock_anterior !== null)
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500 text-sm">Stock origen</dt>
                        <dd class="text-xs">
                            <span class="line-through text-gray-400 mr-1">{{ $traslado->stock_anterior }}</span>
                            → <strong class="text-orange-600">{{ $traslado->stock_nuevo }}</strong>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- IMEIs Trasladados --}}
        @if($esSerie)
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-4 flex items-center gap-2">
                <i class="fas fa-barcode text-purple-400"></i>
                IMEIs Trasladados
                <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-0.5 rounded-full">
                    {{ $traslado->imeisTrasladados->count() }}
                </span>
                @if($traslado->estado === 'confirmado')
                    <span class="text-xs text-green-600 font-normal flex items-center gap-1 ml-1">
                        <i class="fas fa-check-circle"></i>
                        Movidos a {{ $traslado->almacenDestino->nombre ?? 'destino' }}
                    </span>
                @else
                    <span class="text-xs text-yellow-600 font-normal flex items-center gap-1 ml-1">
                        <i class="fas fa-clock"></i>
                        Pendientes de confirmación
                    </span>
                @endif
            </h3>

            @if($traslado->imeisTrasladados->isEmpty())
                <div class="py-6 text-center text-gray-400 text-sm">
                    <i class="fas fa-box-open text-2xl text-gray-300 block mb-2"></i>
                    No hay IMEIs registrados para este traslado.
                </div>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach($traslado->imeisTrasladados as $ti)
                        <div class="flex items-center gap-2 bg-purple-50 border border-purple-200 rounded-lg px-3 py-2">
                            <i class="fas fa-barcode text-purple-400 text-xs"></i>
                            <div>
                                <span class="font-mono text-sm font-semibold text-purple-800">
                                    {{ $ti->imei->codigo_imei ?? '—' }}
                                </span>
                                @if($ti->imei && $ti->imei->serie)
                                    <span class="block text-[11px] text-gray-400 font-mono">S/N: {{ $ti->imei->serie }}</span>
                                @endif
                            </div>
                            @if($traslado->estado === 'confirmado')
                                <span class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded font-semibold shrink-0">
                                    <i class="fas fa-check mr-0.5"></i>Recibido
                                </span>
                            @else
                                <span class="text-[10px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded font-semibold shrink-0">
                                    En tránsito
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endif

    </div>
</body>
</html>
