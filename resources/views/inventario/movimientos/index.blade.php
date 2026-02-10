<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos de Inventario - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="ml-64 p-8">
        <x-header 
            title="Movimientos de Inventario" 
            subtitle="Historial completo de entradas, salidas y ajustes de stock" 
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
                        <p class="text-sm text-gray-600 font-medium">Total Movimientos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_movimientos'] }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-exchange-alt text-blue-900 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Movimientos Hoy</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['movimientos_hoy'] }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-calendar-day text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Ingresos Hoy</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['ingresos_hoy'] }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-arrow-down text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Salidas Hoy</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['salidas_hoy'] }}</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fas fa-arrow-up text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form action="{{ route('inventario.movimientos.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Tipo de Movimiento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select name="tipo_movimiento" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los tipos</option>
                            <option value="ingreso" {{ request('tipo_movimiento') == 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                            <option value="salida" {{ request('tipo_movimiento') == 'salida' ? 'selected' : '' }}>Salida</option>
                            <option value="ajuste" {{ request('tipo_movimiento') == 'ajuste' ? 'selected' : '' }}>Ajuste</option>
                            <option value="transferencia" {{ request('tipo_movimiento') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            <option value="devolucion" {{ request('tipo_movimiento') == 'devolucion' ? 'selected' : '' }}>Devolución</option>
                            <option value="merma" {{ request('tipo_movimiento') == 'merma' ? 'selected' : '' }}>Merma</option>
                        </select>
                    </div>

                    <!-- Producto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
                        <select name="producto_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los productos</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}" {{ request('producto_id') == $producto->id ? 'selected' : '' }}>
                                    {{ $producto->codigo }} - {{ $producto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Almacén -->
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

                    <!-- Fecha Desde -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Desde</label>
                        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Fecha Hasta -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="{{ route('inventario.movimientos.index') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-redo mr-2"></i>Limpiar filtros
                    </a>
                    <button type="submit" class="bg-blue-900 text-white px-6 py-2 rounded-lg hover:bg-blue-800">
                        <i class="fas fa-search mr-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla de Movimientos -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-list mr-2 text-blue-900"></i>
                        Historial de Movimientos
                    </h2>
                    <a href="{{ route('inventario.movimientos.create') }}" class="bg-blue-900 text-white px-4 py-2 rounded-md hover:bg-blue-800 transition-colors flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Nuevo Movimiento
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Almacén</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($movimientos as $movimiento)
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
                                    <i class="fas {{ $movimiento->icono_tipo_movimiento }} mr-1"></i>
                                    {{ $movimiento->tipo_movimiento_nombre }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $movimiento->producto->nombre }}</p>
                                <p class="text-xs text-gray-500">{{ $movimiento->producto->codigo }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ $movimiento->nombre_almacen }}</span>
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs text-gray-500">{{ $movimiento->stock_anterior }} → </span>
                                <span class="text-sm font-medium text-gray-900">{{ $movimiento->stock_nuevo }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600">{{ $movimiento->nombre_usuario }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">{{ Str::limit($movimiento->motivo, 40) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-inbox text-6xl mb-4"></i>
                                    <p class="text-lg font-medium">No hay movimientos registrados</p>
                                    <a href="{{ route('inventario.movimientos.create') }}" class="mt-4 bg-blue-900 text-white px-4 py-2 rounded-md hover:bg-blue-800">
                                        <i class="fas fa-plus mr-2"></i>
                                        Registrar Primer Movimiento
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($movimientos->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $movimientos->links() }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>