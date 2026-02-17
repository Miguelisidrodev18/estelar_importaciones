<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Pedidos a Proveedor" 
            subtitle="Gestión de pedidos de mercadería" 
        />

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Estadísticas --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Pedidos</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $pedidos->count() }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pendientes</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $pedidos->where('estado', 'pendiente')->count() }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-indigo-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Aprobados</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $pedidos->where('estado', 'aprobado')->count() }}</p>
                    </div>
                    <div class="bg-indigo-100 rounded-full p-3">
                        <i class="fas fa-check text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Recibidos</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $pedidos->where('estado', 'recibido')->count() }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-box-open text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-list mr-2 text-blue-600"></i>Lista de Pedidos
                </h3>
                <a href="{{ route('pedidos.create') }}" class="bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2 px-4 rounded-lg transition-colors text-sm">
                    <i class="fas fa-plus mr-2"></i>Nuevo Pedido
                </a>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Esperada</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado por</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pedidos as $pedido)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-mono font-semibold text-blue-700">{{ $pedido->codigo }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $pedido->proveedor->razon_social ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $pedido->fecha->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $pedido->fecha_esperada ? $pedido->fecha_esperada->format('d/m/Y') : '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $pedido->usuario->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $ep = match($pedido->estado) {
                                        'pendiente' => 'bg-yellow-100 text-yellow-800',
                                        'aprobado' => 'bg-blue-100 text-blue-800',
                                        'recibido' => 'bg-green-100 text-green-800',
                                        'cancelado' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $ep }}">
                                    {{ ucfirst($pedido->estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('pedidos.show', $pedido) }}" class="text-blue-600 hover:text-blue-800" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-clipboard-list text-4xl mb-3 text-gray-300 block"></i>
                                <p>No hay pedidos registrados</p>
                                <a href="{{ route('pedidos.create') }}" class="text-blue-600 hover:underline mt-2 inline-block text-sm">Crear primer pedido</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>