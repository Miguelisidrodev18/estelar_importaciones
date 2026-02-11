<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Inventario - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="ml-64 p-8">
        <x-header 
            title="Consulta de Inventario" 
            subtitle="Consulta disponibilidad y precios de productos" 
        />

        <!-- Buscador y Filtros -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form action="{{ route('inventario.consulta-cajero') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Búsqueda -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar Producto</label>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="Buscar por código, nombre o código de barras...">
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                        <select name="categoria_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="{{ route('inventario.consulta-cajero') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-redo mr-2"></i>Limpiar búsqueda
                    </a>
                    <button type="submit" class="bg-blue-900 text-white px-6 py-2 rounded-lg hover:bg-blue-800">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de Productos -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-boxes mr-2 text-blue-900"></i>
                    Productos Disponibles
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Disponibilidad</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($productos as $producto)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $producto->codigo }}</span>
                                @if($producto->codigo_barras)
                                    <span class="block text-xs text-gray-500">{{ $producto->codigo_barras }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($producto->imagen)
                                        <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" 
                                             class="h-12 w-12 rounded object-cover mr-3">
                                    @else
                                        <div class="h-12 w-12 rounded bg-gray-200 flex items-center justify-center mr-3">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $producto->nombre }}</p>
                                        @if($producto->marca)
                                            <p class="text-xs text-gray-500">{{ $producto->marca }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600">{{ $producto->nombre_categoria }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-base font-bold
                                    @if($producto->stock_actual == 0) text-red-600
                                    @elseif($producto->stock_actual <= $producto->stock_minimo) text-yellow-600
                                    @else text-green-600
                                    @endif">
                                    {{ $producto->stock_actual }}
                                </span>
                                <span class="text-xs text-gray-500 ml-1">{{ $producto->unidad_medida }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div>
                                    <p class="text-lg font-bold text-gray-900">S/ {{ number_format($producto->precio_venta, 2) }}</p>
                                    @if($producto->precio_mayorista)
                                        <p class="text-xs text-gray-500">Mayor: S/ {{ number_format($producto->precio_mayorista, 2) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($producto->stock_actual > $producto->stock_minimo)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Disponible
                                    </span>
                                @elseif($producto->stock_actual > 0)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Stock Bajo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>Agotado
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-search text-6xl mb-4"></i>
                                    <p class="text-lg font-medium">No se encontraron productos</p>
                                    <p class="text-sm text-gray-400 mt-2">Intenta con otros términos de búsqueda</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($productos->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $productos->links() }}
                </div>
            @endif
        </div>

        <!-- Leyenda -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                <div class="text-sm text-blue-900">
                    <p class="font-medium mb-2">Información para consulta:</p>
                    <ul class="list-disc list-inside space-y-1 text-blue-800">
                        <li><strong class="text-green-600">Disponible:</strong> Stock suficiente para la venta</li>
                        <li><strong class="text-yellow-600">Stock Bajo:</strong> Producto con stock limitado</li>
                        <li><strong class="text-red-600">Agotado:</strong> Sin unidades disponibles</li>
                    </ul>
                    <p class="mt-3 text-xs text-blue-700">
                        <i class="fas fa-lock mr-1"></i>
                        Esta es una vista de <strong>solo consulta</strong>. No puedes modificar el inventario desde aquí.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>