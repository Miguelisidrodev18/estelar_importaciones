<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacenes - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Gestión de Almacenes" 
            subtitle="Administra los almacenes y sus ubicaciones" 
        />

        <!-- Mensajes -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-xl mr-3"></i>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Almacenes</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-warehouse text-blue-900 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Almacenes Activos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['activos'] }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Principal</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['principal'] }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-star text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Sucursales</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['sucursales'] }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-building text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form action="{{ route('inventario.almacenes.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Tipo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select name="tipo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los tipos</option>
                            <option value="principal" {{ request('tipo') == 'principal' ? 'selected' : '' }}>Principal</option>
                            <option value="sucursal" {{ request('tipo') == 'sucursal' ? 'selected' : '' }}>Sucursal</option>
                            <option value="temporal" {{ request('tipo') == 'temporal' ? 'selected' : '' }}>Temporal</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los estados</option>
                            <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-900 text-white px-6 py-2 rounded-lg hover:bg-blue-800">
                            <i class="fas fa-search mr-2"></i>Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de Almacenes -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-list mr-2 text-blue-900"></i>
                        Listado de Almacenes
                    </h2>
                    @if($canCreate)
                        <a href="{{ route('inventario.almacenes.create') }}" class="bg-blue-900 text-white px-4 py-2 rounded-md hover:bg-blue-800 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Nuevo Almacén
                        </a>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Almacén</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Encargado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dirección</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($almacenes as $almacen)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $almacen->codigo }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $almacen->nombre }}</p>
                                    @if($almacen->telefono)
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-phone mr-1"></i>{{ $almacen->telefono }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($almacen->tipo === 'principal')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-star mr-1"></i>Principal
                                    </span>
                                @elseif($almacen->tipo === 'sucursal')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-building mr-1"></i>Sucursal
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-clock mr-1"></i>Temporal
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($almacen->encargado)
                                    <span class="text-sm text-gray-900">{{ $almacen->nombre_encargado }}</span>
                                @else
                                    <span class="text-sm text-gray-400 italic">Sin asignar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($almacen->direccion)
                                    <span class="text-sm text-gray-600">{{ Str::limit($almacen->direccion, 40) }}</span>
                                @else
                                    <span class="text-sm text-gray-400 italic">Sin dirección</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($almacen->estado === 'activo')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('inventario.almacenes.show', $almacen) }}" class="text-purple-600 hover:text-purple-900" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($canEdit)
                                        <a href="{{ route('inventario.almacenes.edit', $almacen) }}" class="text-blue-600 hover:text-blue-900" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    
                                    @if($canDelete)
                                        <form action="{{ route('inventario.almacenes.destroy', $almacen) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este almacén?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-warehouse text-6xl mb-4"></i>
                                    <p class="text-lg font-medium">No hay almacenes registrados</p>
                                    @if($canCreate)
                                        <a href="{{ route('inventario.almacenes.create') }}" class="mt-4 bg-blue-900 text-white px-4 py-2 rounded-md hover:bg-blue-800">
                                            <i class="fas fa-plus mr-2"></i>
                                            Crear Almacén
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($almacenes->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $almacenes->links() }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>