<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Precios · ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans">

<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8">
    <x-header title="Gestión de Precios" subtitle="Administra los precios de venta y márgenes por producto" />

    {{-- Alertas --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
            <i class="fas fa-check-circle text-green-500 text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Stats --}}
    @php
        $totalProductos   = $productos->total();
        $conPrecio        = $productos->getCollection()->filter(fn($p) => $p->precios->isNotEmpty())->count();
        $sinPrecio        = $productos->getCollection()->filter(fn($p) => $p->precios->isEmpty())->count();
        $margenPromedio   = $productos->getCollection()->flatMap(fn($p) => $p->precios)->avg('margen');
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                <i class="fas fa-boxes text-blue-600 text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total productos</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalProductos }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                <i class="fas fa-tag text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Con precio</p>
                <p class="text-2xl font-bold text-gray-900">{{ $conPrecio }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Sin precio</p>
                <p class="text-2xl font-bold text-gray-900">{{ $sinPrecio }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                <i class="fas fa-percentage text-purple-600 text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide">Margen promedio</p>
                <p class="text-2xl font-bold text-gray-900">{{ $margenPromedio ? number_format($margenPromedio, 1) : '—' }}%</p>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Buscar</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           placeholder="Nombre o código..."
                           class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="min-w-48">
                <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Categoría</label>
                <select name="categoria_id"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('precios.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Producto</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Categoría</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">P. Compra</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">P. Venta</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Margen</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($productos as $producto)
                    @php $precioActual = $producto->precios->first(); @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                    <i class="fas fa-box text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $producto->nombre }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $producto->codigo }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $producto->categoria->nombre ?? '—' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm text-right text-gray-700">
                            @if($precioActual)
                                S/ {{ number_format($precioActual->precio_compra, 2) }}
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($precioActual)
                                <span class="text-sm font-bold text-blue-700">S/ {{ number_format($precioActual->precio_venta, 2) }}</span>
                            @else
                                <span class="text-gray-300 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($precioActual)
                                <span class="inline-flex items-center gap-1 text-sm font-semibold
                                    {{ $precioActual->margen >= 30 ? 'text-green-700' : ($precioActual->margen >= 15 ? 'text-yellow-700' : 'text-red-600') }}">
                                    <i class="fas fa-arrow-{{ $precioActual->margen >= 15 ? 'up' : 'down' }} text-xs"></i>
                                    {{ $precioActual->margen }}%
                                </span>
                            @else
                                <span class="text-gray-300 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if(!$precioActual)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                    <i class="fas fa-times-circle text-xs"></i> Sin precio
                                </span>
                            @elseif($precioActual->fecha_fin && $precioActual->fecha_fin->isPast())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-orange-50 text-orange-700 border border-orange-200">
                                    <i class="fas fa-clock text-xs"></i> Vencido
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                    <i class="fas fa-check-circle text-xs"></i> Vigente
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('precios.show', $producto) }}"
                                   class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"
                                   title="Ver precios">
                                    <i class="fas fa-tags text-sm"></i>
                                </a>
                                <a href="{{ route('precios.historial', $producto) }}"
                                   class="w-8 h-8 flex items-center justify-center rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors"
                                   title="Historial">
                                    <i class="fas fa-history text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                <i class="fas fa-tags text-5xl"></i>
                                <p class="text-lg font-medium">No se encontraron productos</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($productos->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $productos->links() }}
        </div>
        @endif
    </div>
</div>
</body>
</html>
