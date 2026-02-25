<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Precios · {{ $producto->nombre }}</title>
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
        <span class="text-gray-800 font-medium truncate">{{ $producto->nombre }}</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $producto->nombre }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestión de precios y márgenes</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('precios.historial', $producto) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-history"></i> Historial
            </a>
            <a href="{{ route('precios.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
            <i class="fas fa-check-circle text-green-500"></i>
            <span class="text-sm">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
            <i class="fas fa-exclamation-circle text-red-500"></i>
            <span class="text-sm">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Columna izquierda --}}
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

            {{-- Calculadora --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                 x-data="{
                     proveedorId: '',
                     precioCompra: '',
                     margen: 30,
                     impuestos: 0,
                     resultado: null,
                     async calcular() {
                         if (!this.proveedorId || !this.precioCompra) return;
                         const res = await fetch('{{ route('precios.calcular', $producto) }}', {
                             method: 'POST',
                             headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                             body: JSON.stringify({proveedor_id:this.proveedorId,precio_compra:this.precioCompra,margen:this.margen,impuestos:this.impuestos})
                         });
                         const data = await res.json();
                         if (data.success) this.resultado = data;
                     }
                 }">
                <div class="bg-gradient-to-r from-emerald-700 to-emerald-500 px-5 py-4">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-calculator"></i> Calculadora de Precios
                    </h2>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Proveedor</label>
                        <select x-model="proveedorId"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Seleccionar...</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Precio compra (S/)</label>
                        <input type="number" x-model="precioCompra" step="0.01" min="0.01"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Margen %</label>
                            <input type="number" x-model="margen" step="0.1" min="0" max="100"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Impuestos %</label>
                            <input type="number" x-model="impuestos" step="0.1" min="0" max="100"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <template x-if="resultado">
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Precio base</span>
                                <span class="font-medium" x-text="'S/ ' + resultado.precio_base.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between text-sm border-t border-emerald-200 pt-2">
                                <span class="text-gray-600 font-semibold">Precio final</span>
                                <span class="font-bold text-emerald-700 text-base" x-text="'S/ ' + resultado.precio_final.toFixed(2)"></span>
                            </div>
                        </div>
                    </template>

                    <button @click="calcular()"
                            :disabled="!proveedorId || !precioCompra"
                            class="w-full py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fas fa-calculator mr-2"></i>Calcular
                    </button>
                </div>
            </div>
        </div>

        {{-- Columna derecha: tabla de precios --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Precios actuales --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-900 to-blue-700 px-5 py-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-tags"></i> Precios Registrados
                    </h2>
                    <span class="text-xs text-blue-200">{{ $producto->precios->count() }} registro(s)</span>
                </div>

                @if($producto->precios->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Proveedor</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">P. Compra</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">P. Venta</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">P. Mayor.</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Margen</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($producto->precios as $precio)
                            <tr class="hover:bg-blue-50/30 transition-colors {{ $precio->activo ? '' : 'opacity-60' }}">
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $precio->proveedor->razon_social ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">
                                    S/ {{ number_format($precio->precio_compra, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold text-blue-700">
                                        S/ {{ number_format($precio->precio_venta, 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600">
                                    {{ $precio->precio_mayorista ? 'S/ ' . number_format($precio->precio_mayorista, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold {{ $precio->margen >= 20 ? 'text-green-700' : 'text-yellow-700' }}">
                                        {{ $precio->margen }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($precio->activo)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                            <i class="fas fa-circle text-[6px]"></i> Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                            <i class="fas fa-circle text-[6px]"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('precios.edit', ['producto' => $producto->id, 'precio' => $precio->id]) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-lg text-xs font-medium hover:bg-yellow-100 transition-colors">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="py-16 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-tag text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Sin precios registrados</p>
                    <p class="text-gray-400 text-sm mt-1">Aún no se ha configurado ningún precio para este producto</p>
                </div>
                @endif
            </div>

            {{-- Vigencia visual --}}
            @if($producto->precios->count())
            <div class="grid grid-cols-3 gap-4">
                @php
                    $precioActivo = $producto->precios->where('activo', true)->first();
                @endphp
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Precio Venta</p>
                    <p class="text-xl font-bold text-blue-700">
                        {{ $precioActivo ? 'S/ ' . number_format($precioActivo->precio_venta, 2) : '—' }}
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Precio Compra</p>
                    <p class="text-xl font-bold text-gray-700">
                        {{ $precioActivo ? 'S/ ' . number_format($precioActivo->precio_compra, 2) : '—' }}
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Margen</p>
                    <p class="text-xl font-bold {{ ($precioActivo?->margen ?? 0) >= 20 ? 'text-green-700' : 'text-yellow-600' }}">
                        {{ $precioActivo ? $precioActivo->margen . '%' : '—' }}
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
