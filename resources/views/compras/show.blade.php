<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Compra - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="ml-64 p-8">
        <x-header
            title="Detalle de la Compra"
            subtitle="Información completa de la compra registrada"
        />

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="max-w-6xl mx-auto">
            {{-- Información de la Compra --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                {{-- Información General --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-blue-900 px-6 py-4">
                            <h2 class="text-xl font-bold text-white">
                                <i class="fas fa-info-circle mr-2"></i>Información General
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Código</label>
                                    <p class="text-lg font-mono font-bold text-blue-900">{{ $compra->codigo }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                                    @php
                                        $ec = match($compra->estado) {
                                            'completada' => 'bg-green-100 text-green-800',
                                            'pendiente' => 'bg-yellow-100 text-yellow-800',
                                            'anulada' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-bold rounded-full {{ $ec }}">
                                        <i class="fas fa-circle text-xs mr-2"></i>
                                        {{ ucfirst($compra->estado) }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">N° Factura</label>
                                    <p class="text-sm font-semibold text-gray-900">{{ $compra->numero_factura }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Fecha</label>
                                    <p class="text-sm text-gray-900">
                                        <i class="fas fa-calendar mr-1 text-blue-600"></i>{{ $compra->fecha->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Proveedor</label>
                                    <p class="text-sm text-gray-900">
                                        <a href="{{ route('proveedores.show', $compra->proveedor) }}" class="text-blue-600 hover:underline font-semibold">
                                            <i class="fas fa-building mr-1"></i>{{ $compra->proveedor->razon_social ?? '-' }}
                                        </a>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">RUC</label>
                                    <p class="text-sm font-mono text-gray-900">{{ $compra->proveedor->ruc ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Almacén Destino</label>
                                    <p class="text-sm text-gray-900">
                                        <i class="fas fa-warehouse mr-1 text-blue-600"></i>{{ $compra->almacen->nombre ?? '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Registrado por</label>
                                    <p class="text-sm text-gray-900">
                                        <i class="fas fa-user mr-1 text-blue-600"></i>{{ $compra->usuario->name ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($compra->observaciones)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="bg-gradient-to-r from-yellow-600 to-yellow-500 px-6 py-4">
                                <h3 class="text-lg font-bold text-white">
                                    <i class="fas fa-sticky-note mr-2"></i>Observaciones
                                </h3>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-700">{{ $compra->observaciones }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Estadísticas y Acciones --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-chart-pie mr-2"></i>Resumen
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-blue-900">Productos</p>
                                            <p class="text-3xl font-bold text-blue-600">{{ $compra->detalles->count() }}</p>
                                        </div>
                                        <div class="bg-blue-100 rounded-full p-3">
                                            <i class="fas fa-boxes text-2xl text-blue-600"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-green-900">Total Compra</p>
                                            <p class="text-2xl font-bold text-green-600">S/ {{ number_format($compra->total, 2) }}</p>
                                        </div>
                                        <div class="bg-green-100 rounded-full p-3">
                                            <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-900 to-purple-800 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                <i class="fas fa-cogs mr-2"></i>Acciones
                            </h3>
                        </div>
                        <div class="p-4 space-y-2">
                            <a href="{{ route('compras.index') }}"
                               class="flex items-center justify-center w-full px-4 py-2 border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold rounded-lg transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>Volver al Listado
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detalle de Productos --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-blue-900 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-boxes mr-2"></i>Detalle de Productos
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Precio Unit.</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($compra->detalles as $i => $detalle)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $i + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <span class="font-semibold text-gray-900">{{ $detalle->producto->nombre ?? '-' }}</span>
                                                <span class="text-gray-400 text-xs ml-1">({{ $detalle->producto->codigo ?? '' }})</span>
                                                @if($detalle->producto && $detalle->producto->tipo_producto === 'celular')
                                                    <span class="ml-2 inline-flex px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-700 font-semibold">
                                                        <i class="fas fa-mobile-alt mr-1"></i>Celular
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm font-bold text-gray-900">{{ $detalle->cantidad }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                        S/ {{ number_format($detalle->precio_unitario, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-bold text-gray-900">S/ {{ number_format($detalle->subtotal, 2) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totales --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-end">
                        <div class="w-80 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 font-medium">Subtotal:</span>
                                <span class="font-semibold text-gray-900">S/ {{ number_format($compra->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 font-medium">IGV (18%):</span>
                                <span class="font-semibold text-gray-900">S/ {{ number_format($compra->igv, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-xl border-t-2 border-gray-300 pt-3">
                                <span class="font-bold text-gray-700">Total:</span>
                                <span class="font-bold text-blue-900">S/ {{ number_format($compra->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- IMEIs Section (Only for phone products) --}}
            @php
                $hasPhones = $compra->detalles->contains(function($detalle) {
                    return $detalle->producto && $detalle->producto->tipo_producto === 'celular';
                });
            @endphp

            @if($hasPhones)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-900 to-purple-800 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-mobile-alt mr-2"></i>IMEIs Registrados
                        </h2>
                    </div>
                    <div class="p-6">
                        @foreach($compra->detalles as $detalle)
                            @if($detalle->producto && $detalle->producto->tipo_producto === 'celular')
                                @php
                                    $imeisProducto = $compra->imeis->where('producto_id', $detalle->producto_id);
                                @endphp
                                <div class="mb-6 last:mb-0">
                                    <h3 class="text-sm font-bold text-gray-700 mb-3">
                                        <i class="fas fa-mobile-alt mr-1 text-purple-600"></i>{{ $detalle->producto->nombre }}
                                    </h3>
                                    @if($imeisProducto->count() > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @foreach($imeisProducto as $imei)
                                                <div class="bg-purple-50 border border-purple-200 rounded-lg px-4 py-2">
                                                    <div class="text-sm font-mono font-semibold text-purple-900">{{ $imei->codigo_imei }}</div>
                                                    @if($imei->serie || $imei->color)
                                                        <div class="text-xs text-purple-600 mt-1">
                                                            @if($imei->serie)<span>Serie: {{ $imei->serie }}</span>@endif
                                                            @if($imei->serie && $imei->color) | @endif
                                                            @if($imei->color)<span>Color: {{ $imei->color }}</span>@endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500 italic">No hay IMEIs registrados para este producto</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>