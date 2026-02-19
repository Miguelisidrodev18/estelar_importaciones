<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $producto->nombre }} - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <!-- Header con navegación -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detalle del Producto</h1>
                    <p class="text-sm text-gray-600 mt-1">Información completa de {{ $producto->nombre }}</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('inventario.productos.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                    @if(in_array(auth()->user()->role->nombre, ['Administrador', 'Almacenero']))
                        <a href="{{ route('inventario.productos.edit', $producto) }}" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                            <i class="fas fa-edit mr-2"></i>Editar
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <!-- Grid principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna izquierda: Imagen e información básica -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Imagen -->
                    <div class="p-6 flex justify-center bg-gray-50 border-b">
                        @if($producto->imagen)
                            <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="max-h-64 object-contain">
                        @else
                            <div class="h-48 w-48 bg-gray-200 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-6xl text-gray-400"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Información básica -->
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $producto->nombre }}</h2>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-500">Código:</span>
                                <span class="text-sm text-gray-900 font-mono">{{ $producto->codigo }}</span>
                            </div>
                            
                            @if($producto->codigo_barras)
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-500">Código Barras:</span>
                                <span class="text-sm text-gray-900 font-mono">{{ $producto->codigo_barras }}</span>
                            </div>
                            @endif
                            
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-500">Tipo Inventario:</span>
                                <span class="text-sm">
                                    @if($producto->tipo_inventario === 'serie')
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                            <i class="fas fa-mobile-alt mr-1"></i> Serie/IMEI
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                            <i class="fas fa-boxes mr-1"></i> Cantidad
                                        </span>
                                    @endif
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-500">Estado:</span>
                                <span class="text-sm">
                                    @if($producto->estado === 'activo')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Activo</span>
                                    @elseif($producto->estado === 'inactivo')
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">Inactivo</span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Descontinuado</span>
                                    @endif
                                </span>
                            </div>
                            
                            <div class="pt-3 border-t">
                                <span class="text-sm font-medium text-gray-500 block mb-2">Descripción:</span>
                                <p class="text-sm text-gray-700">{{ $producto->descripcion ?: 'Sin descripción' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Detalles y movimientos -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Tarjeta de clasificación -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-blue-900 px-6 py-3">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-tags mr-2"></i>
                            Clasificación
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Categoría</p>
                                <p class="font-medium">{{ $producto->categoria->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Marca</p>
                                <p class="font-medium">{{ $producto->marca->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Modelo</p>
                                <p class="font-medium">{{ $producto->modelo->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Color</p>
                                <p class="font-medium">{{ $producto->color->nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Unidad Medida</p>
                                <p class="font-medium">{{ $producto->unidadMedida->nombre ?? 'N/A' }}</p>
                            </div>
                            @if($producto->tipo_inventario === 'serie')
                            <div>
                                <p class="text-xs text-gray-500">Garantía</p>
                                <p class="font-medium">{{ $producto->dias_garantia ?? 0 }} días</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Tipo Garantía</p>
                                <p class="font-medium">{{ ucfirst($producto->tipo_garantia ?? 'N/A') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de stock -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-green-600 px-6 py-3">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-boxes mr-2"></i>
                            Control de Stock
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Stock Actual</p>
                                <p class="text-2xl font-bold {{ $producto->estado_stock === 'bajo' ? 'text-yellow-600' : ($producto->estado_stock === 'sin_stock' ? 'text-red-600' : 'text-green-600') }}">
                                    {{ $producto->stock_actual }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Stock Mínimo</p>
                                <p class="text-xl font-semibold">{{ $producto->stock_minimo }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Stock Máximo</p>
                                <p class="text-xl font-semibold">{{ $producto->stock_maximo }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Ubicación</p>
                                <p class="font-medium">{{ $producto->ubicacion ?: 'No definida' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-700 px-6 py-3">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-bolt mr-2"></i>
                            Acciones Rápidas
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-wrap gap-3">
                            @if($producto->tipo_inventario === 'serie')
                                <a href="{{ route('inventario.imeis.index', ['producto_id' => $producto->id]) }}" 
                                   class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                    <i class="fas fa-sim-card mr-2"></i>
                                    Gestionar IMEIs
                                </a>
                            @endif
                            
                            <a href="{{ route('inventario.productos.codigos-barras', $producto) }}" 
                               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-barcode mr-2"></i>
                                Códigos de Barras
                            </a>
                            
                            @if($producto->tipo_inventario === 'cantidad')
                                <a href="{{ route('inventario.movimientos.create', ['producto_id' => $producto->id]) }}" 
                                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-exchange-alt mr-2"></i>
                                    Movimiento de Stock
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Últimos movimientos -->
                @if($producto->movimientos && $producto->movimientos->count() > 0)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-800 px-6 py-3">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-history mr-2"></i>
                            Últimos Movimientos
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($producto->movimientos as $movimiento)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4">
                                        @if($movimiento->tipo_movimiento === 'ingreso')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Ingreso</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Salida</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm {{ $movimiento->tipo_movimiento === 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $movimiento->tipo_movimiento === 'ingreso' ? '+' : '-' }}{{ $movimiento->cantidad }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $movimiento->motivo }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($producto->movimientos->count() >= 10)
                    <div class="px-6 py-3 bg-gray-50 border-t">
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-900">Ver todos los movimientos →</a>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Información de costos (solo administradores) -->
                @if(auth()->user()->role->nombre === 'Administrador')
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-yellow-600 px-6 py-3">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-chart-line mr-2"></i>
                            Información de Costos
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Costo Promedio</p>
                                <p class="text-lg font-semibold">S/ {{ number_format($producto->costo_promedio, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Último Costo Compra</p>
                                <p class="text-lg font-semibold">S/ {{ number_format($producto->ultimo_costo_compra, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Última Compra</p>
                                <p class="text-lg font-semibold">{{ $producto->fecha_ultima_compra ? $producto->fecha_ultima_compra->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>