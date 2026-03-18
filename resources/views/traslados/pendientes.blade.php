<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traslados Pendientes - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Traslados Pendientes"
            subtitle="Confirma la recepción y sigue el proceso de cada traslado"
        />

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                <i class="fas fa-check-circle"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Navegación rápida --}}
        <div class="flex flex-wrap gap-3 mb-6">
            <a href="{{ route('traslados.index') }}"
               class="text-sm text-gray-600 hover:text-blue-700 flex items-center gap-1">
                <i class="fas fa-exchange-alt"></i> Historial
            </a>
            <span class="text-gray-300">|</span>
            <span class="text-sm font-semibold text-yellow-600 flex items-center gap-1.5">
                <i class="fas fa-clock"></i> Pendientes
                @if(count($traslados) > 0)
                    <span class="bg-yellow-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center font-bold">
                        {{ count($traslados) }}
                    </span>
                @endif
            </span>
            <span class="text-gray-300">|</span>
            <a href="{{ route('traslados.stock') }}"
               class="text-sm text-gray-600 hover:text-blue-700 flex items-center gap-1">
                <i class="fas fa-boxes"></i> Stock por Almacén
            </a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('traslados.create') }}"
               class="text-sm text-gray-600 hover:text-blue-700 flex items-center gap-1">
                <i class="fas fa-plus-circle"></i> Nuevo Traslado
            </a>
        </div>

        @forelse($traslados as $traslado)
        @php
            $esSerie        = $traslado->producto->tipo_inventario === 'serie';
            $imeisTrasladados = $traslado->imeisTrasladados ?? collect();
            $diasEnTransito = $traslado->created_at->diffInDays(now());
            $urgente        = $diasEnTransito >= 3;
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-4 overflow-hidden"
             x-data="{ detalleAbierto: false }">

            {{-- ── Cabecera de la card ─────────────────────────────── --}}
            <div class="px-6 py-5">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">

                    <div class="flex-1 min-w-0">
                        {{-- Badges de estado --}}
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            <span class="font-mono font-bold text-blue-700 text-sm bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-200">
                                <i class="fas fa-file-alt mr-1 text-blue-400 text-xs"></i>{{ $traslado->numero_guia ?? 'Sin guía' }}
                            </span>
                            @if($esSerie)
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold bg-purple-100 text-purple-700 rounded-full">
                                    <i class="fas fa-barcode mr-1"></i>IMEI ({{ $imeisTrasladados->count() }})
                                </span>
                            @endif
                            @if($urgente)
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-600 rounded-full">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ $diasEnTransito }}d en tránsito
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">
                                <i class="fas fa-clock mr-1"></i>Pendiente
                            </span>
                        </div>

                        {{-- Producto --}}
                        <h3 class="text-base font-semibold text-gray-900">
                            {{ $traslado->producto->nombre }}
                            <span class="text-sm font-normal text-gray-400 font-mono ml-1">{{ $traslado->producto->codigo }}</span>
                        </h3>

                        {{-- Ruta + cantidad --}}
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-sm text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-warehouse text-orange-400 text-xs"></i>
                                <strong class="text-gray-700">{{ $traslado->almacen->nombre }}</strong>
                            </span>
                            <i class="fas fa-long-arrow-alt-right text-gray-300"></i>
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-store text-green-400 text-xs"></i>
                                <strong class="text-gray-700">{{ $traslado->almacenDestino->nombre ?? '—' }}</strong>
                            </span>
                            <span class="text-gray-300">·</span>
                            <span>
                                <i class="fas fa-cubes text-xs mr-1"></i>
                                <strong class="text-gray-700">{{ $traslado->cantidad }}</strong>
                                @if($esSerie) <span class="text-purple-600 text-xs">IMEIs</span>
                                @else unid. @endif
                            </span>
                            <span class="text-gray-300">·</span>
                            <span class="text-xs text-gray-400">
                                <i class="fas fa-calendar mr-1"></i>{{ $traslado->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="flex items-center gap-2 shrink-0 flex-wrap">
                        <button type="button"
                                @click="detalleAbierto = !detalleAbierto"
                                class="flex items-center gap-1.5 px-3.5 py-2 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-info-circle text-blue-400 text-xs"></i>
                            <span x-text="detalleAbierto ? 'Ocultar' : 'Ver detalle'"></span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': detalleAbierto }"></i>
                        </button>

                        {{-- Botón confirmar (funciona para ambos tipos) --}}
                        <form action="{{ route('traslados.confirmar', $traslado) }}" method="POST"
                              onsubmit="return confirm('¿Confirmar la recepción?\n\nGuía: {{ $traslado->numero_guia ?? "Sin guía" }}\nProducto: {{ addslashes($traslado->producto->nombre) }}\nCantidad: {{ $traslado->cantidad }} {{ $esSerie ? "IMEI(s)" : "unid." }}')">
                            @csrf
                            @if($esSerie && $imeisTrasladados->isEmpty())
                                <button type="button"
                                        disabled
                                        title="Sin IMEIs asignados a este traslado"
                                        class="flex items-center gap-1.5 bg-gray-300 text-white font-semibold px-5 py-2 rounded-lg text-sm cursor-not-allowed">
                                    <i class="fas fa-exclamation-triangle"></i>Sin IMEIs
                                </button>
                            @else
                                <button type="submit"
                                        class="flex items-center gap-1.5 bg-green-500 hover:bg-green-600 text-white font-semibold px-5 py-2 rounded-lg text-sm transition-colors">
                                    <i class="fas fa-check-double"></i>Confirmar Recepción
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Panel de detalle expandible ────────────────────── --}}
            <div x-show="detalleAbierto"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="border-t border-gray-100 bg-gray-50/60"
                 style="display:none">

                <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Datos del traslado --}}
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Datos del Traslado</p>
                        <dl class="space-y-2.5 bg-white rounded-xl border border-gray-200 p-4">
                            <div class="flex justify-between items-center text-sm">
                                <dt class="text-gray-500 flex items-center gap-1.5">
                                    <i class="fas fa-file-alt text-blue-400 w-4 text-center"></i>N° Guía
                                </dt>
                                <dd class="font-mono font-bold text-blue-700 text-sm">{{ $traslado->numero_guia ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <dt class="text-gray-500 flex items-center gap-1.5">
                                    <i class="fas fa-box text-gray-400 w-4 text-center"></i>Producto
                                </dt>
                                <dd class="font-medium text-gray-800 text-right max-w-[55%]">{{ $traslado->producto->nombre }}</dd>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <dt class="text-gray-500 flex items-center gap-1.5">
                                    <i class="fas fa-layer-group text-gray-400 w-4 text-center"></i>Categoría
                                </dt>
                                <dd class="text-gray-700">{{ $traslado->producto->categoria->nombre ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <dt class="text-gray-500 flex items-center gap-1.5">
                                    <i class="fas fa-cubes text-gray-400 w-4 text-center"></i>Cantidad
                                </dt>
                                <dd class="font-bold text-gray-800">{{ $traslado->cantidad }} {{ $esSerie ? 'IMEI(s)' : 'unid.' }}</dd>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <dt class="text-gray-500 flex items-center gap-1.5">
                                    <i class="fas fa-user text-gray-400 w-4 text-center"></i>Solicitado por
                                </dt>
                                <dd class="text-gray-700">{{ $traslado->usuario->name }}</dd>
                            </div>
                            @if($traslado->transportista)
                            <div class="flex justify-between items-center text-sm">
                                <dt class="text-gray-500 flex items-center gap-1.5">
                                    <i class="fas fa-truck text-gray-400 w-4 text-center"></i>Transportista
                                </dt>
                                <dd class="text-gray-700">{{ $traslado->transportista }}</dd>
                            </div>
                            @endif
                            @if($traslado->stock_anterior !== null && !$esSerie)
                            <div class="flex justify-between items-center text-sm">
                                <dt class="text-gray-500 flex items-center gap-1.5">
                                    <i class="fas fa-chart-bar text-gray-400 w-4 text-center"></i>Stock origen
                                </dt>
                                <dd class="text-gray-700 text-xs">
                                    <span class="line-through text-gray-400 mr-1">{{ $traslado->stock_anterior }}</span>
                                    → <strong class="text-orange-600">{{ $traslado->stock_nuevo }}</strong>
                                </dd>
                            </div>
                            @endif
                            @if($traslado->observaciones)
                            <div class="text-sm pt-1">
                                <dt class="text-gray-500 flex items-center gap-1.5 mb-1">
                                    <i class="fas fa-comment-alt text-gray-400 w-4 text-center"></i>Observaciones
                                </dt>
                                <dd class="text-gray-600 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 italic text-xs">
                                    "{{ $traslado->observaciones }}"
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Timeline del proceso --}}
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Proceso del Traslado</p>

                        <ol class="relative border-l-2 border-gray-200 ml-3 space-y-0">

                            {{-- 1. Creado --}}
                            <li class="mb-5 ml-5">
                                <span class="absolute -left-3.25 flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 ring-4 ring-white shadow">
                                    <i class="fas fa-plus text-white" style="font-size:9px"></i>
                                </span>
                                <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                                    <p class="text-xs font-bold text-blue-700">Traslado registrado</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Por <strong>{{ $traslado->usuario->name }}</strong>
                                    </p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">
                                        {{ $traslado->created_at->format('d/m/Y H:i') }}
                                        <span class="text-gray-300 mx-1">·</span>
                                        {{ $traslado->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </li>

                            {{-- 2. Stock / IMEIs descontados --}}
                            <li class="mb-5 ml-5">
                                <span class="absolute -left-3.25 flex h-6 w-6 items-center justify-center rounded-full bg-orange-500 ring-4 ring-white shadow">
                                    <i class="fas fa-minus text-white" style="font-size:9px"></i>
                                </span>
                                <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                                    @if($esSerie)
                                        <p class="text-xs font-bold text-orange-600">IMEIs asignados al traslado</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <strong>{{ $imeisTrasladados->count() }}</strong> IMEI(s) de
                                            <strong>{{ $traslado->almacen->nombre }}</strong>
                                        </p>
                                    @else
                                        <p class="text-xs font-bold text-orange-600">Stock descontado del origen</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <strong>{{ $traslado->cantidad }}</strong> unid. de
                                            <strong>{{ $traslado->almacen->nombre }}</strong>
                                        </p>
                                    @endif
                                </div>
                            </li>

                            {{-- 3. En tránsito --}}
                            <li class="mb-5 ml-5">
                                <span class="absolute -left-3.25 flex h-6 w-6 items-center justify-center rounded-full ring-4 ring-white shadow
                                    {{ $urgente ? 'bg-red-400' : 'bg-yellow-400' }}">
                                    <i class="fas fa-truck text-white" style="font-size:9px"></i>
                                </span>
                                <div class="rounded-xl px-4 py-3 border shadow-sm
                                    {{ $urgente ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200' }}">
                                    <p class="text-xs font-bold {{ $urgente ? 'text-red-600' : 'text-yellow-700' }} flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full animate-pulse {{ $urgente ? 'bg-red-500' : 'bg-yellow-500' }}"></span>
                                        En tránsito — pendiente de confirmación
                                    </p>
                                    <p class="text-xs {{ $urgente ? 'text-red-500' : 'text-yellow-600' }} mt-0.5">
                                        Rumbo a: <strong>{{ $traslado->almacenDestino->nombre ?? '—' }}</strong>
                                    </p>
                                    <p class="text-[11px] {{ $urgente ? 'text-red-400 font-semibold' : 'text-gray-400' }} mt-0.5">
                                        <i class="fas fa-hourglass-half mr-1"></i>
                                        {{ $diasEnTransito }} día(s) en tránsito
                                        @if($urgente) — confirmar urgente @endif
                                    </p>
                                </div>
                            </li>

                            {{-- 4. Confirmación (pendiente) --}}
                            <li class="ml-5">
                                <span class="absolute -left-3.25 flex h-6 w-6 items-center justify-center rounded-full bg-gray-200 ring-4 ring-white">
                                    <i class="fas fa-check text-gray-400" style="font-size:9px"></i>
                                </span>
                                <div class="bg-white border border-dashed border-gray-300 rounded-xl px-4 py-3">
                                    <p class="text-xs font-medium text-gray-400">Recepción confirmada en destino</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">
                                        Pendiente — {{ $esSerie ? 'los IMEIs se moverán a' : 'el stock se acreditará en' }}
                                        {{ $traslado->almacenDestino->nombre ?? 'destino' }}
                                    </p>
                                </div>
                            </li>

                        </ol>
                    </div>
                </div>

                {{-- IMEIs asignados al traslado --}}
                @if($esSerie && $imeisTrasladados->isNotEmpty())
                    <div class="px-6 pb-5">
                        <div class="bg-white border border-purple-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-purple-700 mb-3 flex items-center gap-1.5">
                                <i class="fas fa-barcode text-purple-500"></i>
                                IMEIs asignados a este traslado
                                <span class="bg-purple-100 text-purple-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                    {{ $imeisTrasladados->count() }}
                                </span>
                                <span class="text-gray-400 font-normal ml-1">→ se moverán a {{ $traslado->almacenDestino->nombre ?? 'destino' }} al confirmar</span>
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($imeisTrasladados as $ti)
                                    <span class="inline-flex items-center gap-1.5 font-mono text-xs bg-purple-50 text-purple-700 border border-purple-200 px-2.5 py-1.5 rounded-lg">
                                        <i class="fas fa-barcode text-purple-400 text-[10px]"></i>
                                        {{ $ti->imei->codigo_imei ?? '—' }}
                                        @if($ti->imei && $ti->imei->serie)
                                            <span class="text-purple-400">· {{ $ti->imei->serie }}</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if($esSerie && $imeisTrasladados->isEmpty())
                    <div class="px-6 pb-5">
                        <div class="flex items-center gap-2 text-sm text-red-700 bg-red-50 border border-red-200 px-4 py-3 rounded-xl">
                            <i class="fas fa-exclamation-triangle shrink-0"></i>
                            Este traslado no tiene IMEIs asignados. No es posible confirmarlo. Contacte al administrador.
                        </div>
                    </div>
                @endif

            </div>{{-- /panel detalle --}}
        </div>{{-- /card --}}

        @empty
        <div class="bg-white rounded-2xl shadow-sm p-14 text-center border border-gray-100">
            <div class="bg-green-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-4xl text-green-400"></i>
            </div>
            <p class="text-lg font-semibold text-gray-600">No hay traslados pendientes</p>
            <p class="text-sm text-gray-400 mt-1">Todos los traslados han sido confirmados</p>
            <a href="{{ route('traslados.create') }}"
               class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-colors">
                <i class="fas fa-plus-circle"></i>Nuevo Traslado
            </a>
        </div>
        @endforelse
    </div>
</body>
</html>
