<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de IMEIs - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="ml-64 p-8">
        <x-header 
            title="Gestión de IMEIs" 
            subtitle="Control individual de celulares por IMEI" 
        />

        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total IMEIs</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-mobile-alt text-blue-900 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Disponibles</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['disponibles'] }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Vendidos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['vendidos'] }}</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fas fa-shopping-cart text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Reservados</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['reservados'] }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form action="{{ route('inventario.imeis.index') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar IMEI/Serie</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="Código IMEI o serie...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
                        <select name="producto_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los productos</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}" {{ request('producto_id') == $producto->id ? 'selected' : '' }}>
                                    {{ $producto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Almacén</label>
                        <select name="almacen_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los almacenes</option>
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->id }}" {{ request('almacen_id') == $almacen->id ? 'selected' : '' }}>
                                    {{ $almacen->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los estados</option>
                            <option value="disponible" {{ request('estado') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                            <option value="vendido" {{ request('estado') == 'vendido' ? 'selected' : '' }}>Vendido</option>
                            <option value="reservado" {{ request('estado') == 'reservado' ? 'selected' : '' }}>Reservado</option>
                            <option value="dañado" {{ request('estado') == 'dañado' ? 'selected' : '' }}>Dañado</option>
                            <option value="garantia" {{ request('estado') == 'garantia' ? 'selected' : '' }}>En Garantía</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 mt-4">
                    <a href="{{ route('inventario.imeis.index') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-redo mr-2"></i>Limpiar
                    </a>
                    <button type="submit" class="bg-blue-900 text-white px-6 py-2 rounded-lg hover:bg-blue-800">
                        <i class="fas fa-search mr-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de IMEIs -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-list mr-2 text-blue-900"></i>
                        Listado de IMEIs
                    </h2>
                    @if(auth()->user()->role->nombre != 'Tienda')
                        <a href="{{ route('inventario.imeis.create') }}" class="bg-blue-900 text-white px-4 py-2 rounded-md hover:bg-blue-800">
                            <i class="fas fa-plus mr-2"></i>Registrar IMEI
                        </a>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IMEI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serie/Color</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Almacén</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Fecha Registro</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($imeis as $imei)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono font-bold text-gray-900">{{ $imei->codigo_imei }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $imei->nombre_producto }}</p>
                                <p class="text-xs text-gray-500">{{ $imei->producto->codigo }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($imei->serie)
                                    <p class="text-sm text-gray-900">Serie: {{ $imei->serie }}</p>
                                @endif
                                @if($imei->color)
                                    <p class="text-xs text-gray-500">{{ $imei->color }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ $imei->almacen->nombre }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($imei->estado == 'disponible')
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Disponible
                                    </span>
                                @elseif($imei->estado == 'vendido')
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-shopping-cart mr-1"></i>Vendido
                                    </span>
                                @elseif($imei->estado == 'reservado')
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>Reservado
                                    </span>
                                @elseif($imei->estado == 'dañado')
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Dañado
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        <i class="fas fa-shield-alt mr-1"></i>Garantía
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                {{ $imei->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <i class="fas fa-mobile-alt text-6xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium text-gray-500">No hay IMEIs registrados</p>
                                @if(auth()->user()->role->nombre != 'Tienda')
                                    <a href="{{ route('inventario.imeis.create') }}" class="mt-4 inline-block bg-blue-900 text-white px-4 py-2 rounded-md hover:bg-blue-800">
                                        <i class="fas fa-plus mr-2"></i>Registrar Primer IMEI
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($imeis->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $imeis->links() }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>