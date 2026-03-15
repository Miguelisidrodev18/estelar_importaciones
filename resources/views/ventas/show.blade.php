<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta {{ $venta->codigo }} - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .md\:ml-64 { margin-left: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" class="no-print" />

    <div class="md:ml-64 p-4 md:p-10">

        {{-- Modal de confirmación de venta nueva --}}
        @if(request()->has('nuevo'))
        <div x-data="{ show: true }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="show = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-6 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white">
                        @if($venta->tipo_comprobante === 'cotizacion')
                            ¡Cotización Guardada!
                        @else
                            ¡Venta Registrada!
                        @endif
                    </h3>
                    <p class="text-green-100 text-sm mt-1">{{ $venta->codigo }}</p>
                </div>
                <div class="p-6 text-center">
                    <div class="text-gray-600 dark:text-gray-300 text-sm mb-1">Total cobrado</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        S/ {{ number_format($venta->total, 2) }}
                    </div>
                    @if($venta->tipo_comprobante !== 'cotizacion' && $venta->metodo_pago)
                    <div class="inline-flex items-center gap-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-full px-3 py-1 text-xs font-medium mb-4">
                        <i class="fas fa-credit-card"></i>
                        {{ ucfirst($venta->metodo_pago) }}
                    </div>
                    @endif
                    <div class="flex gap-3">
                        @if($venta->tipo_comprobante !== 'cotizacion')
                        <a href="{{ route('ventas.pdf', [$venta, 'formato' => 'ticket']) }}" target="_blank"
                           class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 text-sm font-semibold transition flex items-center justify-center gap-2">
                            <i class="fas fa-receipt"></i> Ticket
                        </a>
                        @endif
                        <button @click="show = false"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white rounded-xl py-2.5 text-sm font-semibold transition">
                            Aceptar
                        </button>
                    </div>
                    <a href="{{ route('ventas.create') }}"
                       class="block mt-3 text-sm text-blue-600 hover:text-blue-700 font-medium transition">
                        <i class="fas fa-plus-circle mr-1"></i> Nueva Venta
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Flash --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2 shadow-sm">
                <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2 shadow-sm">
                <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Page header --}}
        <div class="flex items-start justify-between mb-6 no-print">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
                    <a href="{{ route('ventas.index') }}" class="hover:text-blue-600 transition-colors">Ventas</a>
                    <i class="fas fa-chevron-right text-xs"></i>
                    <span class="text-gray-700 font-medium">{{ $venta->codigo }}</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Detalle de Venta</h1>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if($venta->tipo_comprobante !== 'cotizacion')
                <a href="{{ route('ventas.pdf', [$venta, 'formato' => 'a4']) }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors shadow-sm">
                    <i class="fas fa-file-pdf"></i> PDF A4
                </a>
                <a href="{{ route('ventas.pdf', [$venta, 'formato' => 'ticket']) }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors shadow-sm">
                    <i class="fas fa-receipt"></i> Ticket 80mm
                </a>
                <button onclick="window.print()"
                        class="inline-flex items-center gap-2 border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:border-gray-300 px-4 py-2 rounded-xl text-sm font-medium transition-colors shadow-sm">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                @endif
                <a href="{{ $venta->tipo_comprobante === 'cotizacion' ? route('ventas.cotizaciones') : route('ventas.index') }}"
                   class="inline-flex items-center gap-2 border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-xl text-sm font-medium transition-colors shadow-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>

                @if($venta->tipo_comprobante === 'cotizacion' && in_array(auth()->user()->role->nombre, ['Administrador', 'Tienda']))
                <div x-data="{ showConvertir: false }">
                    <button @click="showConvertir = true"
                            class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                        <i class="fas fa-file-invoice"></i> Convertir a Venta
                    </button>
                    <div x-show="showConvertir" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
                        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showConvertir = false"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-5">
                                <h3 class="text-lg font-bold text-white">Convertir Cotización</h3>
                                <p class="text-purple-200 text-sm mt-0.5">{{ $venta->codigo }}</p>
                            </div>
                            <form action="{{ route('ventas.convertir', $venta) }}" method="POST" class="p-6">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Comprobante *</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="flex items-center gap-2 border border-gray-200 rounded-xl p-3 cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50">
                                            <input type="radio" name="tipo_comprobante" value="boleta" class="text-purple-600" required checked>
                                            <span class="text-sm font-medium">Boleta</span>
                                        </label>
                                        <label class="flex items-center gap-2 border border-gray-200 rounded-xl p-3 cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50">
                                            <input type="radio" name="tipo_comprobante" value="factura" class="text-purple-600">
                                            <span class="text-sm font-medium">Factura</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-5">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Método de Pago *</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach(['efectivo' => 'fa-money-bill-wave', 'transferencia' => 'fa-university', 'yape' => 'fa-mobile-alt', 'plin' => 'fa-mobile-alt'] as $metodo => $icono)
                                        <label class="flex items-center gap-2 border border-gray-200 rounded-xl p-3 cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50">
                                            <input type="radio" name="metodo_pago" value="{{ $metodo }}" class="text-purple-600" required>
                                            <i class="fas {{ $icono }} text-gray-500 text-sm"></i>
                                            <span class="text-sm font-medium capitalize">{{ $metodo }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-xl px-4 py-3 mb-5 flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Total a cobrar</span>
                                    <span class="text-xl font-bold text-gray-900">S/ {{ number_format($venta->total, 2) }}</span>
                                </div>
                                <div class="flex gap-3">
                                    <button type="button" @click="showConvertir = false"
                                            class="flex-1 border border-gray-200 text-gray-600 hover:bg-gray-50 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                                        Cancelar
                                    </button>
                                    <button type="submit"
                                            class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-2.5 rounded-xl font-bold text-sm transition-colors">
                                        <i class="fas fa-check mr-1"></i> Convertir
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                @if($venta->estado_pago === 'pendiente' && in_array(auth()->user()->role->nombre, ['Administrador', 'Tienda']))
                <div x-data="{ showModal: false }">
                    <button @click="showModal = true"
                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                        <i class="fas fa-check-circle"></i> Confirmar Pago
                    </button>

                    {{-- Modal confirmar pago --}}
                    <div x-show="showModal" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center">
                        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showModal = false"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-5">
                                <h3 class="text-lg font-bold text-white">Confirmar Pago</h3>
                                <p class="text-green-100 text-sm mt-0.5">Venta {{ $venta->codigo }}</p>
                            </div>
                            <form action="{{ route('ventas.confirmar-pago', $venta) }}" method="POST" class="p-6">
                                @csrf
                                <div class="mb-5">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Método de Pago *</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach(['efectivo' => 'fa-money-bill-wave', 'transferencia' => 'fa-university', 'yape' => 'fa-mobile-alt', 'plin' => 'fa-mobile-alt'] as $metodo => $icono)
                                        <label class="flex items-center gap-2 border border-gray-200 rounded-xl p-3 cursor-pointer hover:border-green-400 hover:bg-green-50 transition-all has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                            <input type="radio" name="metodo_pago" value="{{ $metodo }}" class="text-green-600 focus:ring-green-500" required>
                                            <i class="fas {{ $icono }} text-gray-500 text-sm"></i>
                                            <span class="text-sm font-medium capitalize">{{ $metodo }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-xl px-4 py-3 mb-5 flex justify-between items-center">
                                    <span class="text-sm text-gray-500">Total a cobrar</span>
                                    <span class="text-xl font-bold text-gray-900">S/ {{ number_format($venta->total, 2) }}</span>
                                </div>
                                <div class="flex gap-3">
                                    <button type="button" @click="showModal = false"
                                            class="flex-1 border border-gray-200 text-gray-600 hover:bg-gray-50 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                                        Cancelar
                                    </button>
                                    <button type="submit"
                                            class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl font-bold text-sm transition-colors">
                                        <i class="fas fa-check mr-1"></i> Confirmar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Estado badge --}}
        @php
            $estadoConfig = [
                'pendiente'   => ['bg-amber-50 text-amber-700 border-amber-200', 'fa-clock', 'Pago Pendiente'],
                'pagado'      => ['bg-green-50 text-green-700 border-green-200', 'fa-check-circle', 'Pagado'],
                'cancelado'   => ['bg-red-50 text-red-700 border-red-200', 'fa-times-circle', 'Cancelado'],
                'cotizacion'  => ['bg-purple-50 text-purple-700 border-purple-200', 'fa-file-contract', 'Cotización'],
            ];
            [$badgeClass, $badgeIcon, $badgeLabel] = $estadoConfig[$venta->estado_pago] ?? ['bg-gray-50 text-gray-700 border-gray-200', 'fa-circle', ucfirst($venta->estado_pago)];
        @endphp
        <div class="inline-flex items-center gap-2 border {{ $badgeClass }} px-4 py-1.5 rounded-full text-sm font-semibold mb-6">
            <i class="fas {{ $badgeIcon }}"></i>
            {{ $badgeLabel }}
            @if($venta->estado_pago === 'pagado' && $venta->fecha_confirmacion)
                <span class="text-xs opacity-70">· {{ $venta->fecha_confirmacion->format('d/m/Y H:i') }}</span>
            @endif
        </div>

        {{-- Info cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-7">
            {{-- Venta --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-receipt text-blue-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-700 uppercase tracking-wider">Venta</h3>
                </div>
                <dl class="space-y-3">
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-400">Código</dt>
                        <dd class="font-mono font-bold text-blue-600">{{ $venta->codigo }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-400">Fecha</dt>
                        <dd class="text-sm text-gray-700 font-medium">{{ $venta->fecha->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-400">Hora</dt>
                        <dd class="text-sm text-gray-700">{{ $venta->created_at->format('H:i') }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-400">Almacén</dt>
                        <dd class="text-sm text-gray-700 font-medium">{{ $venta->almacen->nombre }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-400">Vendedor</dt>
                        <dd class="text-sm text-gray-700">{{ $venta->vendedor->name }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Cliente --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user text-indigo-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-700 uppercase tracking-wider">Cliente</h3>
                </div>
                @if($venta->cliente)
                    <dl class="space-y-2.5">
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-400">Nombre</dt>
                            <dd class="text-sm text-gray-700 font-medium text-right max-w-[60%]">{{ $venta->cliente->nombre }}</dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-400">Documento</dt>
                            <dd class="text-sm font-mono text-gray-700">
                                {{ strtoupper($venta->cliente->tipo_documento ?? '') }}
                                {{ $venta->cliente->numero_documento }}
                            </dd>
                        </div>
                        @if($venta->cliente->telefono)
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-400">Teléfono</dt>
                            <dd class="text-sm text-gray-700">{{ $venta->cliente->telefono }}</dd>
                        </div>
                        @endif
                    </dl>
                @else
                    <div class="flex flex-col items-center justify-center h-24 text-gray-300">
                        <i class="fas fa-user-slash text-3xl mb-2"></i>
                        <p class="text-sm text-gray-400">Venta sin cliente</p>
                    </div>
                @endif
            </div>

            {{-- Pago --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-credit-card text-green-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-700 uppercase tracking-wider">Pago</h3>
                </div>
                <dl class="space-y-3">
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-400">Subtotal</dt>
                        <dd class="text-sm text-gray-700">S/ {{ number_format($venta->subtotal, 2) }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-400">IGV (18%)</dt>
                        <dd class="text-sm text-gray-700">S/ {{ number_format($venta->igv, 2) }}</dd>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-100 pt-3">
                        <dt class="font-bold text-gray-700">Total</dt>
                        <dd class="text-2xl font-bold text-gray-900">S/ {{ number_format($venta->total, 2) }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-400">Método</dt>
                        <dd class="text-sm text-gray-700 font-medium">
                            {{ $venta->metodo_pago ? ucfirst($venta->metodo_pago) : '—' }}
                        </dd>
                    </div>
                    @if($venta->confirmador)
                    <div class="flex justify-between items-center">
                        <dt class="text-sm text-gray-400">Confirmado por</dt>
                        <dd class="text-sm text-gray-700">{{ $venta->confirmador->name }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Observaciones --}}
        @if($venta->observaciones)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 mb-6 flex items-start gap-3">
            <i class="fas fa-sticky-note text-amber-500 mt-0.5"></i>
            <div>
                <p class="text-sm font-semibold text-amber-700">Observaciones</p>
                <p class="text-sm text-amber-600 mt-0.5">{{ $venta->observaciones }}</p>
            </div>
        </div>
        @endif

        {{-- Products table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100">
                <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-box text-purple-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $venta->tipo_comprobante === 'cotizacion' ? 'Productos cotizados' : 'Productos vendidos' }}</h3>
                    <p class="text-sm text-gray-400">{{ $venta->detalles->count() }} ítem(s)</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">IMEI / Serie</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Cant.</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Precio unit.</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($venta->detalles as $i => $detalle)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-5 text-sm text-gray-400 font-medium">{{ $i + 1 }}</td>
                            <td class="px-6 py-5">
                                <span class="font-semibold text-gray-900">{{ $detalle->producto->nombre }}</span>
                                {{-- Variante (color + capacidad) --}}
                                @if($detalle->variante)
                                    <span class="flex items-center gap-1.5 mt-1">
                                        @if($detalle->variante->color?->codigo_hex)
                                            <span class="w-3 h-3 rounded-full border border-gray-300 shrink-0"
                                                  style="background-color: {{ $detalle->variante->color->codigo_hex }}"></span>
                                        @endif
                                        <span class="text-xs text-indigo-600 font-medium">{{ $detalle->variante->nombre_completo }}</span>
                                        <span class="text-xs text-gray-400 font-mono">({{ $detalle->variante->sku }})</span>
                                    </span>
                                @elseif($detalle->producto->categoria)
                                    <span class="block text-xs text-gray-400 mt-0.5">{{ $detalle->producto->categoria->nombre }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                @if($detalle->imei)
                                    <span class="inline-flex items-center gap-1.5 bg-purple-50 text-purple-700 border border-purple-200 text-sm px-3 py-1 rounded-full font-mono font-semibold">
                                        <i class="fas fa-microchip text-xs"></i>
                                        {{ $detalle->imei->codigo_imei }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="font-bold text-gray-700">{{ $detalle->cantidad }}</span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <span class="text-gray-700">S/ {{ number_format($detalle->precio_unitario, 2) }}</span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <span class="font-bold text-gray-900">S/ {{ number_format($detalle->subtotal, 2) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-100">
                        <tr>
                            <td colspan="4" class="px-6 py-4"></td>
                            <td class="px-6 py-4 text-right text-sm font-semibold text-gray-500">Subtotal</td>
                            <td class="px-6 py-4 text-right font-bold text-gray-700">S/ {{ number_format($venta->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-6 py-1"></td>
                            <td class="px-6 py-1 text-right text-sm font-semibold text-gray-500">IGV (18%)</td>
                            <td class="px-6 py-1 text-right font-bold text-gray-700">S/ {{ number_format($venta->igv, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-6 py-4"></td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-gray-900">Total</td>
                            <td class="px-6 py-4 text-right text-2xl font-bold text-blue-600">S/ {{ number_format($venta->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</body>
</html>
