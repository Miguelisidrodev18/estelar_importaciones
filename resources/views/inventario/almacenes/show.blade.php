<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Almacén - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Detalle del Almacén" 
            subtitle="{{ $almacen->nombre }}" 
        />

        <!-- Información del Almacén -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="bg-blue-900 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-warehouse mr-2"></i>
                        Información General
                    </h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('inventario.almacenes.edit', $almacen) }}" class="bg-white text-blue-900 px-4 py-2 rounded-md hover:bg-gray-100 text-sm">
                            <i class="fas fa-edit mr-2"></i>Editar
                        </a>
                        <a href="{{ route('inventario.almacenes.index') }}" class="bg-white/20 text-white px-4 py-2 rounded-md hover:bg-white/30 text-sm">
                            <i class="fas fa-arrow-left mr-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Columna 1 -->
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Código</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $almacen->codigo }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Nombre</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $almacen->nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Tipo</p>
                            @if($almacen->tipo === 'principal')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-star mr-1"></i>Principal
                                </span>
                            @elseif($almacen->tipo === 'sucursal')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-building mr-1"></i>Sucursal
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-clock mr-1"></i>Temporal
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Columna 2 -->
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Encargado</p>
                            @if($almacen->encargado)
                                <p class="text-lg font-semibold text-gray-900">{{ $almacen->nombre_encargado }}</p>
                                <p class="text-sm text-gray-500">{{ $almacen->encargado->role->nombre }}</p>
                            @else
                                <p class="text-gray-400 italic">Sin asignar</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Teléfono</p>
                            @if($almacen->telefono)
                                <p class="text-lg font-semibold text-gray-900">
                                    <i class="fas fa-phone mr-2 text-gray-400"></i>{{ $almacen->telefono }}
                                </p>
                            @else
                                <p class="text-gray-400 italic">Sin teléfono</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Estado</p>
                            @if($almacen->estado === 'activo')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-times-circle mr-1"></i>Inactivo
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Columna 3 -->
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Dirección</p>
                            @if($almacen->direccion)
                                <p class="text-gray-900">{{ $almacen->direccion }}</p>
                            @else
                                <p class="text-gray-400 italic">Sin dirección registrada</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Fecha de creación</p>
                            <p class="text-gray-900">{{ $almacen->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock por Producto en este Almacén -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-boxes mr-2 text-blue-900"></i>
                    Stock de Productos en este Almacén
                </h2>
            </div>

            <div class="overflow-x-auto">
                @if($stockDetalle->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock en Almacén</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($stockDetalle as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $item['producto']->codigo }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        @if($item['producto']->imagen)
                                            <img src="{{ $item['producto']->imagen_url }}" alt="{{ $item['producto']->nombre }}" class="h-10 w-10 rounded object-cover mr-3">
                                        @else
                                            <div class="h-10 w-10 rounded bg-gray-200 flex items-center justify-center mr-3">
                                                <i class="fas fa-box text-gray-400"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $item['producto']->nombre }}</p>
                                            @if($item['producto']->marca)
                                                <p class="text-xs text-gray-500">{{ $item['producto']->marca }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600">{{ $item['producto']->nombre_categoria }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ number_format($item['stock'], 0) }} {{ $item['producto']->unidad_medida }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-sm text-gray-600">
                                        {{ $item['producto']->stock_actual }} {{ $item['producto']->unidad_medida }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-6 py-12 text-center">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-lg font-medium text-gray-500">No hay productos con stock en este almacén</p>
                        <p class="text-sm text-gray-400 mt-2">Los productos aparecerán aquí cuando se registren movimientos de inventario</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Últimos Movimientos -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-history mr-2 text-blue-900"></i>
                    Últimos 20 Movimientos
                </h2>
            </div>

            <div class="overflow-x-auto">
                @if($almacen->movimientos->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($almacen->movimientos as $movimiento)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $movimiento->created_at->format('d/m/Y') }}</span>
                                    <span class="block text-xs text-gray-500">{{ $movimiento->created_at->format('H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($movimiento->tipo_movimiento == 'ingreso') bg-green-100 text-green-800
                                        @elseif($movimiento->tipo_movimiento == 'salida') bg-red-100 text-red-800
                                        @elseif($movimiento->tipo_movimiento == 'ajuste') bg-blue-100 text-blue-800
                                        @elseif($movimiento->tipo_movimiento == 'transferencia') bg-purple-100 text-purple-800
                                        @elseif($movimiento->tipo_movimiento == 'devolucion') bg-orange-100 text-orange-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($movimiento->tipo_movimiento) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $movimiento->producto->nombre }}</p>
                                    <p class="text-xs text-gray-500">{{ $movimiento->producto->codigo }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-sm font-medium
                                        @if(in_array($movimiento->tipo_movimiento, ['ingreso', 'devolucion'])) text-green-600
                                        @elseif(in_array($movimiento->tipo_movimiento, ['salida', 'merma'])) text-red-600
                                        @else text-blue-600
                                        @endif">
                                        @if(in_array($movimiento->tipo_movimiento, ['ingreso', 'devolucion'])) +
                                        @elseif(in_array($movimiento->tipo_movimiento, ['salida', 'merma'])) -
                                        @endif
                                        {{ $movimiento->cantidad }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600">{{ Str::limit($movimiento->motivo, 50) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-6 py-12 text-center">
                        <i class="fas fa-history text-6xl text-gray-300 mb-4"></i>
                        <p class="text-lg font-medium text-gray-500">No hay movimientos registrados en este almacén</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>