<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />
    
    <div class="ml-64 p-8 ">
        <x-header 
            title="Gestión de Ventas" 
            subtitle="Administra las ventas realizadas y sus detalles"
        />
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Lista de Ventas</h2>
            <a href="{{ route('ventas.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Nueva Venta
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendedor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Almacén</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pago</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($ventas as $venta)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono font-semibold text-blue-600">{{ $venta->codigo }}</td>
                            <td class="px-6 py-4 text-sm">{{ $venta->vendedor->name }}</td>
                            <td class="px-6 py-4 text-sm">{{ $venta->cliente->nombre ?? 'Sin cliente' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $venta->almacen->nombre }}</td>
                            <td class="px-6 py-4 text-sm font-semibold">S/ {{ number_format($venta->total, 2) }}</td>
                            <td class="px-6 py-4 text-sm">{{ $venta->metodo_pago ? ucfirst($venta->metodo_pago) : '-' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $colores = ['pendiente' => 'bg-yellow-100 text-yellow-800', 'pagado' => 'bg-green-100 text-green-800', 'cancelado' => 'bg-red-100 text-red-800'];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $colores[$venta->estado_pago] ?? '' }}">
                                    {{ ucfirst($venta->estado_pago) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('ventas.show', $venta) }}" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-receipt text-4xl mb-3 text-gray-300"></i>
                                <p>No hay ventas registradas</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
