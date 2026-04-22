<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Variantes · {{ $producto->nombre }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" x-data="editModal()">
    <x-sidebar :role="auth()->user()->role->nombre" />

    {{-- Modal Editar Variante --}}
    <div x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="open = false"
             class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="bg-gradient-to-r from-indigo-700 to-indigo-600 px-5 py-4 rounded-t-2xl flex items-center justify-between">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fas fa-pencil-alt"></i> Editar Variante
                </h3>
                <button @click="open = false" class="text-white/70 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form :action="formAction" method="POST" class="p-5 space-y-4">
                @csrf
                @method('PUT')

                {{-- Color --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <select name="color_id" x-model="form.color_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">Sin color específico</option>
                        @foreach($colores as $color)
                            <option value="{{ $color->id }}">{{ $color->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Capacidad --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacidad / Almacenamiento</label>
                    <input type="text" name="capacidad" x-model="form.capacidad"
                           placeholder="Ej: 64GB, 128GB, 256GB"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Stock Mínimo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" x-model="form.stock_minimo"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" @click="open = false"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="md:ml-64 p-4 md:p-8">
        {{-- Breadcrumb --}}
        <div class="flex items-center text-sm text-gray-500 mb-4">
            <a href="{{ route('inventario.productos.index') }}" class="hover:text-blue-900">Productos</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <a href="{{ route('inventario.productos.show', $producto) }}" class="hover:text-blue-900 truncate max-w-xs">{{ $producto->nombre }}</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span class="text-gray-700 font-medium">Variantes</span>
        </div>

        {{-- Mensajes --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ─── INFO DEL PRODUCTO BASE ─────────────────────────── --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-5 py-4">
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fas fa-box"></i> Producto Base
                        </h2>
                    </div>
                    <div class="p-5 space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Nombre</p>
                            <p class="font-semibold text-gray-900">{{ $producto->nombre }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Código</p>
                                <p class="font-mono text-gray-700">{{ $producto->codigo }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Tipo</p>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $producto->tipo_inventario === 'serie' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $producto->tipo_inventario === 'serie' ? 'Serie/IMEI' : 'Cantidad' }}
                                </span>
                            </div>
                        </div>
                        @if($producto->marca || $producto->modelo)
                        <div class="grid grid-cols-2 gap-3">
                            @if($producto->marca)
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Marca</p>
                                <p class="text-gray-700">{{ $producto->marca->nombre }}</p>
                            </div>
                            @endif
                            @if($producto->modelo)
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Modelo</p>
                                <p class="text-gray-700">{{ $producto->modelo->nombre }}</p>
                            </div>
                            @endif
                        </div>
                        @endif
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Stock Total</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $stockTotalReal }}
                                <span class="text-sm font-normal text-gray-400">unidades</span>
                            </p>
                            @if($producto->tipo_inventario === 'serie')
                                <p class="text-xs text-indigo-500 mt-0.5">
                                    <i class="fas fa-sim-card mr-1"></i>Unidades en stock
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ─── AGREGAR VARIANTE ─────────────────────────────── --}}
                <div class="bg-white rounded-2xl shadow-md overflow-hidden mt-4"
                     x-data="{ abierto: {{ $errors->any() ? 'true' : 'false' }} }">
                    <button @click="abierto = !abierto"
                            class="w-full bg-gradient-to-r from-indigo-600 to-indigo-500 px-5 py-4 flex items-center justify-between">
                        <span class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fas fa-plus-circle"></i> Nueva Variante
                        </span>
                        <i class="fas text-white transition-transform" :class="abierto ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>

                    <div x-show="abierto" x-collapse>
                        <form action="{{ route('inventario.productos.variantes.store', $producto) }}"
                              method="POST" class="p-5 space-y-4">
                            @csrf

                            {{-- Color --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Color <span class="text-gray-400 text-xs">(opcional)</span>
                                </label>
                                <select name="color_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                                    <option value="">Sin color específico</option>
                                    @foreach($colores as $color)
                                        <option value="{{ $color->id }}"
                                                {{ old('color_id') == $color->id ? 'selected' : '' }}>
                                            {{ $color->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('color_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Capacidad --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Capacidad / Almacenamiento <span class="text-gray-400 text-xs">(opcional)</span>
                                </label>
                                <input type="text" name="capacidad" value="{{ old('capacidad') }}"
                                       placeholder="Ej: 64GB, 128GB, 256GB"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                                @error('capacidad')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($producto->tipo_inventario === 'cantidad')
                            {{-- Stock inicial (solo para tipo cantidad) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Stock Inicial <span class="text-gray-400 text-xs">(opcional)</span>
                                </label>
                                <input type="number" name="stock_inicial" value="{{ old('stock_inicial', 0) }}"
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                            </div>
                            @endif

                            <button type="submit"
                                    class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm transition">
                                <i class="fas fa-plus mr-2"></i>Agregar Variante
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ─── LISTA DE VARIANTES ──────────────────────────────── --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-5 py-4 flex items-center justify-between">
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fas fa-layer-group"></i>
                            Variantes del Producto
                            <span class="bg-white/20 text-white text-xs px-2 py-0.5 rounded-full ml-1">
                                {{ $producto->variantes->count() }}
                            </span>
                        </h2>
                    </div>

                    @if($producto->variantes->isEmpty())
                        <div class="p-10 text-center text-gray-400">
                            <i class="fas fa-layer-group text-4xl mb-3 text-gray-200"></i>
                            <p class="font-medium">Sin variantes registradas</p>
                            <p class="text-sm mt-1">Agrega la primera variante usando el formulario</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($producto->variantes->sortBy('estado') as $variante)
                                <div class="p-4 flex items-center justify-between gap-4
                                    {{ $variante->estado === 'inactivo' ? 'opacity-50 bg-gray-50' : '' }}">
                                    <div class="flex items-center gap-4 min-w-0">
                                        {{-- Color dot --}}
                                        @if($variante->color && $variante->color->codigo_hex)
                                            <div class="w-9 h-9 rounded-full border-2 border-white shadow flex-shrink-0"
                                                 style="background-color: {{ $variante->color->codigo_hex }};"
                                                 title="{{ $variante->color->nombre }}"></div>
                                        @else
                                            <div class="w-9 h-9 rounded-full bg-gray-200 border-2 border-white shadow flex-shrink-0 flex items-center justify-center">
                                                <i class="fas fa-palette text-gray-400 text-xs"></i>
                                            </div>
                                        @endif

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-semibold text-gray-900 text-sm">{{ $variante->nombre_completo }}</span>
                                                <span class="font-mono text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded">{{ $variante->sku }}</span>
                                                @if($variante->estado === 'inactivo')
                                                    <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium">Inactiva</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                                @if($variante->color)
                                                    <span><i class="fas fa-circle mr-1 text-gray-300"></i>{{ $variante->color->nombre }}</span>
                                                @endif
                                                @if($variante->capacidad)
                                                    <span><i class="fas fa-hdd mr-1 text-gray-300"></i>{{ $variante->capacidad }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4 flex-shrink-0">
                                        {{-- Stock --}}
                                        <div class="text-right">
                                            @php
                                                $stockVar = $producto->tipo_inventario === 'serie'
                                                    ? ($stockRealPorVariante[$variante->id] ?? 0)
                                                    : $variante->stock_actual;
                                            @endphp
                                            <p class="text-lg font-bold {{ $stockVar <= $variante->stock_minimo ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ $stockVar }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                en stock
                                            </p>
                                        </div>

                                        {{-- Acciones --}}
                                        @if($variante->estado === 'activo')
                                            {{-- Editar --}}
                                            <button type="button"
                                                    @click="openEdit({
                                                        id: {{ $variante->id }},
                                                        color_id: '{{ $variante->color_id ?? '' }}',
                                                        capacidad: '{{ $variante->capacidad ?? '' }}',
                                                        stock_minimo: '{{ $variante->stock_minimo }}'
                                                    })"
                                                    class="text-indigo-400 hover:text-indigo-600 p-2 rounded-lg hover:bg-indigo-50 transition"
                                                    title="Editar">
                                                <i class="fas fa-pencil-alt text-sm"></i>
                                            </button>

                                            {{-- Desactivar --}}
                                            <form action="{{ route('inventario.productos.variantes.destroy', $variante) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Desactivar esta variante?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition"
                                                        title="Desactivar">
                                                    <i class="fas fa-ban text-sm"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
<script>
function editModal() {
    return {
        open: false,
        formAction: '',
        form: { color_id: '', capacidad: '', stock_minimo: 0 },
        openEdit(variante) {
            this.formAction = '{{ url("inventario/productos/variantes") }}/' + variante.id;
            this.form.color_id     = variante.color_id;
            this.form.capacidad    = variante.capacidad;
            this.form.stock_minimo = variante.stock_minimo;
            this.open = true;
        }
    }
}
</script>
</body>
</html>
