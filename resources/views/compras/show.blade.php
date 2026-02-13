<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Compra - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="ml-64 p-8">
        <x-header 
            title="Detalle de Compra" 
            subtitle="Información completa de la compra registrada" 
        />

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="max-w-5xl">
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('compras.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Volver a compras
                </a>
                @php
                    $ec = match($compra->estado) {
                        'completada' => 'bg-green-100 text-green-800',
                        'pendiente' => 'bg-yellow-100 text-yellow-800',
                        'anulada' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                @endphp
                <span class="inline-flex px-3 py-1.5 text-sm font-semibold rounded-full {{ $ec }}">
                    {{ ucfirst($compra->estado) }}
                </span>
            </div>

            {{-- Info General y Proveedor --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>Información General
                    </h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Código:</dt><dd class="font-mono font-bold text-blue-900">{{ $compra->codigo }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">N° Factura:</dt><dd class="font-semibold">{{ $compra->numero_factura }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Fecha:</dt><dd>{{ $compra->fecha->format('d/m/Y') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Registrado por:</dt><dd>{{ $compra->usuario->name ?? '-' }}</dd></div>
                    </dl>
                </div>

                <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-truck mr-2 text-green-600"></i>Proveedor & Almacén
                    </h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Proveedor:</dt>
                            <dd class="font-semibold">
                                <a href="{{ route('proveedores.show', $compra->proveedor) }}" class="text-blue-600 hover:underline">
                                    {{ $compra->proveedor->razon_social ?? '-' }}
                                </a>
                            </dd>
                        </div>
                        <div class="flex justify-between"><dt class="text-gray-500">RUC:</dt><dd class="font-mono">{{ $compra->proveedor->ruc ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Almacén Destino:</dt><dd class="font-semibold">{{ $compra->almacen->nombre ?? '-' }}</dd></div>
                    </dl>
                </div>
            </div>

            {{-- Detalle de Productos --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="bg-blue-900 px-6 py-4">
                    <h2 class="text-lg font-bold text-white">
                        <i class="fas fa-boxes mr-2"></i>Detalle de Productos
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($compra->detalles as $i => $detalle)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="font-medium text-gray-900">{{ $detalle->producto->nombre ?? '-' }}</span>
                                        <span class="text-gray-400 text-xs ml-1">({{ $detalle->producto->codigo ?? '' }})</span>
                                        @if($detalle->producto && $detalle->producto->tipo_producto === 'celular')
                                            <span class="ml-2 inline-flex px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-700 font-semibold">
                                                <i class="fas fa-mobile-alt mr-1"></i>Celular
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-center font-semibold">{{ $detalle->cantidad }}</td>
                                    <td class="px-6 py-4 text-sm text-right">S/ {{ number_format($detalle->precio_unitario, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-bold">S/ {{ number_format($detalle->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totales --}}
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <div class="w-72 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal:</span>
                            <span class="font-semibold">S/ {{ number_format($compra->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">IGV (18%):</span>
                            <span class="font-semibold">S/ {{ number_format($compra->igv, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xl border-t-2 border-gray-200 pt-3">
                            <span class="font-bold text-gray-700">Total:</span>
                            <span class="font-bold text-blue-900">S/ {{ number_format($compra->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($compra->observaciones)
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border-l-4 border-yellow-400">
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-sticky-note mr-2 text-yellow-500"></i>Observaciones
                    </h3>
                    <p class="text-gray-600 text-sm">{{ $compra->observaciones }}</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>