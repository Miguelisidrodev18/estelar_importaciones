<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Proveedor - Sistema de Importaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />
    <x-header title="Detalle de Proveedor" />

    <div class="ml-64 p-8 pt-24">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center mb-6">
                <a href="{{ route('proveedores.index') }}" class="text-blue-600 hover:text-blue-800 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="text-2xl font-bold text-gray-800">{{ $proveedor->razon_social }}</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4"><i class="fas fa-info-circle mr-2"></i>Información</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between"><dt class="text-gray-500">RUC:</dt><dd class="font-mono font-semibold">{{ $proveedor->ruc }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Nombre Comercial:</dt><dd>{{ $proveedor->nombre_comercial ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Dirección:</dt><dd>{{ $proveedor->direccion ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Teléfono:</dt><dd>{{ $proveedor->telefono ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Email:</dt><dd>{{ $proveedor->email ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Contacto:</dt><dd>{{ $proveedor->contacto_nombre ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Estado:</dt><dd><span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $proveedor->estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ ucfirst($proveedor->estado) }}</span></dd></div>
                    </dl>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4"><i class="fas fa-chart-bar mr-2"></i>Resumen</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ $proveedor->compras->count() }}</p>
                            <p class="text-sm text-gray-500">Compras</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-green-600">{{ $proveedor->pedidos->count() }}</p>
                            <p class="text-sm text-gray-500">Pedidos</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($proveedor->compras->count())
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Últimas Compras</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($proveedor->compras as $compra)
                        <tr>
                            <td class="px-4 py-2 text-sm"><a href="{{ route('compras.show', $compra) }}" class="text-blue-600 hover:underline">{{ $compra->codigo }}</a></td>
                            <td class="px-4 py-2 text-sm">{{ $compra->fecha->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm font-semibold">S/ {{ number_format($compra->total, 2) }}</td>
                            <td class="px-4 py-2 text-sm">{{ ucfirst($compra->estado) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
