<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar Precio · {{ $producto->nombre }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans">

<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('precios.index') }}" class="hover:text-blue-700 transition-colors">Gestión de Precios</a>
        <i class="fas fa-chevron-right text-xs text-gray-400"></i>
        <a href="{{ route('precios.show', $producto) }}" class="hover:text-blue-700 transition-colors truncate max-w-xs">{{ $producto->nombre }}</a>
        <i class="fas fa-chevron-right text-xs text-gray-400"></i>
        <span class="text-gray-800 font-medium">Editar Precio</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Precio</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $producto->nombre }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('precios.show', $producto) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Alertas de validación --}}
    @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-exclamation-triangle text-red-500"></i>
                <span class="text-sm font-semibold">Por favor corrige los siguientes errores:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Columna izquierda: info del producto --}}
        <div class="space-y-5">

            {{-- Info del producto --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-900 to-blue-700 px-5 py-4">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-box"></i> Información del Producto
                    </h2>
                </div>
                <div class="p-5 space-y-3">
                    @foreach([
                        ['Código', $producto->codigo, 'font-mono text-xs bg-gray-100 px-2 py-0.5 rounded'],
                        ['Categoría', $producto->categoria->nombre ?? '—', ''],
                        ['Marca', $producto->marca->nombre ?? '—', ''],
                        ['Modelo', $producto->modelo->nombre ?? '—', ''],
                        ['Stock', ($producto->stock_actual ?? 0) . ' und.', 'font-semibold text-blue-700'],
                    ] as [$label, $value, $extra])
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ $label }}</span>
                        <span class="font-medium text-gray-900 {{ $extra }}">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Precio actual --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-700 to-gray-600 px-5 py-4">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-tag"></i> Precio Actual
                    </h2>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Precio compra</span>
                        <span class="font-semibold text-gray-800">S/ {{ number_format($precio->precio_compra, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Precio venta</span>
                        <span class="font-bold text-blue-700">S/ {{ number_format($precio->precio_venta, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Margen</span>
                        <span class="font-semibold {{ $precio->margen >= 20 ? 'text-green-700' : 'text-yellow-700' }}">
                            {{ $precio->margen }}%
                        </span>
                    </div>
                    @if($precio->proveedor)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Proveedor</span>
                        <span class="font-medium text-gray-800 text-right max-w-[60%] truncate">{{ $precio->proveedor->razon_social }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Info box --}}
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <h4 class="text-xs font-semibold text-blue-900 mb-2 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i> Información
                </h4>
                <ul class="text-xs text-blue-800 space-y-1.5 list-disc list-inside">
                    <li>El precio de venta se calcula automáticamente según el margen</li>
                    <li>Si defines fecha de fin, el precio vencerá automáticamente</li>
                    <li>Solo puede existir un precio activo a la vez</li>
                    <li>Este cambio quedará registrado en el historial</li>
                </ul>
            </div>

        </div>

        {{-- Columna derecha: formulario --}}
        <div class="xl:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                 x-data="editPrecio()"
                 x-init="init({{ $precio->precio_compra }}, {{ $precio->margen }})">

                <div class="bg-gradient-to-r from-yellow-600 to-yellow-500 px-5 py-4">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-edit"></i> Actualizar Precio
                    </h2>
                </div>

                <div class="p-6">
                    <form action="{{ route('precios.update', ['producto' => $producto->id, 'precio' => $precio->id]) }}"
                          method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            {{-- Proveedor --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Proveedor <span class="text-red-500">*</span>
                                </label>
                                <select name="proveedor_id" required
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                    <option value="">Seleccionar proveedor...</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id }}"
                                            {{ old('proveedor_id', $precio->proveedor_id) == $prov->id ? 'selected' : '' }}>
                                            {{ $prov->razon_social }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('proveedor_id')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Precio de compra --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Precio Compra (S/) <span class="text-red-500">*</span>
                                </label>
                                <input type="number"
                                       name="precio_compra"
                                       x-model="precioCompra"
                                       @input="calcularPrecioVenta()"
                                       step="0.01" min="0.01"
                                       value="{{ old('precio_compra', $precio->precio_compra) }}"
                                       required
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                @error('precio_compra')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Margen --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Margen (%) <span class="text-red-500">*</span>
                                </label>
                                <input type="number"
                                       name="margen"
                                       x-model="margen"
                                       @input="calcularPrecioVenta()"
                                       step="0.1" min="0" max="100"
                                       value="{{ old('margen', $precio->margen) }}"
                                       required
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                @error('margen')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Precio de venta (calculado) --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Precio Venta (S/) <span class="text-red-500">*</span>
                                </label>
                                <input type="number"
                                       name="precio_venta"
                                       x-model="precioVenta"
                                       step="0.01" min="0.01"
                                       value="{{ old('precio_venta', $precio->precio_venta) }}"
                                       required readonly
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-blue-700 font-semibold cursor-not-allowed">
                                <p class="text-xs text-gray-400 mt-1">Calculado automáticamente según margen</p>
                            </div>

                            {{-- Precio mayorista --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Precio Mayorista (S/)
                                </label>
                                <input type="number"
                                       name="precio_mayorista"
                                       step="0.01" min="0.01"
                                       value="{{ old('precio_mayorista', $precio->precio_mayorista) }}"
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                       placeholder="Opcional">
                            </div>

                            {{-- Fecha inicio --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Fecha Inicio
                                </label>
                                <input type="date"
                                       name="fecha_inicio"
                                       value="{{ old('fecha_inicio', $precio->fecha_inicio?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            </div>

                            {{-- Fecha fin --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Fecha Fin
                                </label>
                                <input type="date"
                                       name="fecha_fin"
                                       value="{{ old('fecha_fin', $precio->fecha_fin?->format('Y-m-d')) }}"
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                <p class="text-xs text-gray-400 mt-1">Dejar vacío para vigencia indefinida</p>
                            </div>

                            {{-- Estado --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                    Estado
                                </label>
                                <div class="flex items-center gap-5">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="activo" value="1"
                                               {{ old('activo', $precio->activo) ? 'checked' : '' }}
                                               class="w-4 h-4 text-yellow-600 border-gray-300 focus:ring-yellow-500">
                                        <span class="text-sm text-gray-700 font-medium">Activo</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="activo" value="0"
                                               {{ old('activo', $precio->activo) == '0' ? 'checked' : '' }}
                                               class="w-4 h-4 text-gray-500 border-gray-300 focus:ring-gray-400">
                                        <span class="text-sm text-gray-700 font-medium">Inactivo</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Observaciones --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Motivo / Observaciones
                                </label>
                                <textarea name="observaciones" rows="3"
                                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 resize-none"
                                          placeholder="Describe el motivo del cambio de precio...">{{ old('observaciones', $precio->observaciones) }}</textarea>
                            </div>

                        </div>

                        {{-- Botones --}}
                        <div class="mt-6 flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                            <a href="{{ route('precios.show', $producto) }}"
                               class="inline-flex items-center gap-2 px-5 py-2 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2 bg-yellow-500 text-white text-sm font-semibold rounded-lg hover:bg-yellow-600 transition-colors">
                                <i class="fas fa-save"></i> Actualizar Precio
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function editPrecio() {
    return {
        precioCompra: 0,
        margen: 0,
        precioVenta: 0,

        init(precioCompra, margen) {
            this.precioCompra = precioCompra;
            this.margen = margen;
            this.calcularPrecioVenta();
        },

        calcularPrecioVenta() {
            const compra = parseFloat(this.precioCompra) || 0;
            const margen = parseFloat(this.margen) || 0;
            if (compra > 0 && margen >= 0) {
                this.precioVenta = Math.round(compra * (1 + margen / 100) * 100) / 100;
            }
        }
    }
}
</script>

</body>
</html>
