<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Compra - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <!-- Header con breadcrumb y acciones -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-900">Dashboard</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <a href="{{ route('compras.index') }}" class="hover:text-blue-900">Compras</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <span class="text-gray-700 font-medium">Compra #{{ $compra->numero_factura }}</span>
            </div>

            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-file-invoice mr-3 text-blue-900"></i>
                    Detalle de Compra
                </h1>
                <div class="flex space-x-3">
                    @if($compra->estado != 'anulado')
                        <a href="{{ route('compras.edit', $compra) }}" 
                           class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition flex items-center">
                            <i class="fas fa-edit mr-2"></i>Editar
                        </a>
                        <button onclick="anularCompra({{ $compra->id }})"
                                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center">
                            <i class="fas fa-ban mr-2"></i>Anular
                        </button>
                    @endif
                    <a href="{{ route('compras.index') }}" 
                       class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Mensajes de éxito/error -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-start">
                <i class="fas fa-check-circle mt-0.5 mr-3 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex items-start">
                <i class="fas fa-exclamation-circle mt-0.5 mr-3 text-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Grid principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna izquierda: Información de la compra -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Tarjeta de estado -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Estado de la Compra
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm text-gray-600">Estado:</span>
                            @if($compra->estado == 'completado')
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>Completado
                                </span>
                            @elseif($compra->estado == 'pendiente')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-clock mr-1"></i>Pendiente
                                </span>
                            @elseif($compra->estado == 'anulado')
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-ban mr-1"></i>Anulado
                                </span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                    {{ $compra->estado }}
                                </span>
                            @endif
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Código:</span>
                                <span class="font-medium text-gray-900">{{ $compra->codigo }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">N° Factura:</span>
                                <span class="font-medium text-gray-900">{{ $compra->numero_factura }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Fecha Emisión:</span>
                                <span class="font-medium text-gray-900">{{ $compra->fecha->format('d/m/Y') }}</span>
                            </div>
                            @if($compra->fecha_vencimiento)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Fecha Vencimiento:</span>
                                <span class="font-medium text-gray-900">{{ $compra->fecha_vencimiento->format('d/m/Y') }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Registrado por:</span>
                                <span class="font-medium text-gray-900">{{ $compra->usuario->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de proveedor -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-700 to-purple-600 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-truck mr-2"></i>
                            Proveedor
                        </h2>
                    </div>
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">{{ $compra->proveedor->nombre_comercial ?? $compra->proveedor->razon_social }}</h3>
                        <p class="text-sm text-gray-600 mt-1">RUC: {{ $compra->proveedor->ruc }}</p>
                        @if($compra->proveedor->direccion)
                            <p class="text-sm text-gray-500 mt-2">{{ $compra->proveedor->direccion }}</p>
                        @endif
                    </div>
                </div>

                <!-- Tarjeta de almacén -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-green-700 to-green-600 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-warehouse mr-2"></i>
                            Almacén Destino
                        </h2>
                    </div>
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">{{ $compra->almacen->nombre }}</h3>
                        @if($compra->almacen->ubicacion)
                            <p class="text-sm text-gray-500 mt-1">{{ $compra->almacen->ubicacion }}</p>
                        @endif
                    </div>
                </div>

                <!-- Tarjeta de pago -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-yellow-600 to-yellow-500 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-credit-card mr-2"></i>
                            Información de Pago
                        </h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Forma de Pago:</span>
                            <span class="font-medium text-gray-900">{{ ucfirst($compra->forma_pago) }}</span>
                        </div>
                        {{-- Mostrar días de crédito SOLO si forma_pago es 'credito' --}}
                            @if($compra->forma_pago === 'credito' && $compra->condicion_pago)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Crédito:</span>
                                <span class="font-medium text-gray-900">{{ $compra->condicion_pago }} días</span>
                            </div>
                            @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Moneda:</span>
                            <span class="font-medium text-gray-900">{{ $compra->tipo_moneda }} ({{ $compra->moneda_simbolo }})</span>
                        </div>
                        <div class="border-t pt-3 mt-3">
                            <div class="flex justify-between text-base font-bold">
                                <span class="text-gray-700">Total:</span>
                                <span class="text-blue-900">{{ $compra->total_formateado }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Productos -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Tarjeta de productos -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-boxes mr-2"></i>
                            Productos de la Compra
                        </h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Marca</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Modelo</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Color</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Cant.</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Precio Unit.</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($compra->detalles as $detalle)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-gray-900">{{ $detalle->producto->nombre }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600">{{ $detalle->producto->marca->nombre ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600">{{ $detalle->producto->modelo->nombre ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600">{{ $detalle->producto->color->nombre ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium">{{ $detalle->cantidad }}</td>
                                    <td class="px-6 py-4 text-right">{{ number_format($detalle->precio_unitario, 2) }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-blue-900">
                                        {{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="6" class="px-6 py-3 text-right font-bold text-gray-700">Subtotal:</td>
                                    <td class="px-6 py-3 text-right font-bold text-blue-900">{{ number_format($compra->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-6 py-3 text-right font-bold text-gray-700">IGV (18%):</td>
                                    <td class="px-6 py-3 text-right font-bold text-blue-900">{{ number_format($compra->igv, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="px-6 py-3 text-right font-bold text-gray-900 text-lg">Total:</td>
                                    <td class="px-6 py-3 text-right font-bold text-blue-900 text-lg">{{ number_format($compra->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($compra->observaciones)
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <h4 class="font-medium text-gray-700 mb-1">Observaciones:</h4>
                        <p class="text-sm text-gray-600">{{ $compra->observaciones }}</p>
                    </div>
                    @endif
                </div>

                <!-- Tarjeta de cuentas por pagar (si existe) -->
                @if($compra->cuentaPorPagar)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-red-700 to-red-600 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-credit-card mr-2"></i>
                            Cuenta por Pagar
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Monto Total</p>
                                <p class="text-lg font-bold text-gray-900">{{ number_format($compra->cuentaPorPagar->monto_total, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Monto Pagado</p>
                                <p class="text-lg font-bold text-green-600">{{ number_format($compra->cuentaPorPagar->monto_pagado, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Saldo Pendiente</p>
                                <p class="text-lg font-bold text-red-600">{{ number_format($compra->cuentaPorPagar->saldo_pendiente, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Fecha Vencimiento</p>
                                <p class="text-lg font-bold text-gray-900">{{ $compra->cuentaPorPagar->fecha_vencimiento->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <a href="{{ route('cuentas-por-pagar.show', $compra->cuentaPorPagar) }}" 
                               class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 text-sm">
                                Ver detalle de cuenta
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function anularCompra(id) {
            Swal.fire({
                title: '¿Anular compra?',
                text: 'Esta acción no se puede deshacer. El stock se revertirá.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/compras/${id}/anular`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Anulada!',
                                text: 'La compra ha sido anulada correctamente.',
                                timer: 2000
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'No se pudo conectar al servidor', 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>