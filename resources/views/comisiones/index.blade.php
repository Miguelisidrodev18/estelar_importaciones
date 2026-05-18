<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comisiones - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8" x-data="{ modalOpen: false, tipoAplicacion: 'usuario' }">
        <x-header title="Comisiones por Personal" subtitle="Configura las reglas de comisión para vendedores" />

        @foreach(['success','error'] as $t)
            @if(session($t))
                <div class="mb-4 p-4 {{ $t === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' }} border rounded-xl text-sm flex items-center gap-2">
                    <i class="fas fa-{{ $t === 'success' ? 'check-circle' : 'exclamation-circle' }}"></i>
                    {{ session($t) }}
                </div>
            @endif
        @endforeach

        {{-- Header bar --}}
        <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
            <div class="flex items-center gap-4">
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2 text-sm">
                    <span class="text-amber-700 font-semibold">S/ {{ number_format($totalPendiente, 2) }}</span>
                    <span class="text-amber-600 ml-1">comisiones pendientes</span>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('comisiones.reporte') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                    <i class="fas fa-chart-bar"></i> Ver Reporte
                </a>
                <button @click="modalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition-colors">
                    <i class="fas fa-plus"></i> Nueva Regla
                </button>
            </div>
        </div>

        {{-- Rules Table --}}
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-blue-900 to-blue-700 px-6 py-4 flex items-center gap-3">
                <div class="bg-white/20 rounded-xl p-2.5"><i class="fas fa-percentage text-white text-xl"></i></div>
                <div>
                    <h2 class="text-base font-bold text-white">Reglas de Comisión</h2>
                    <p class="text-blue-200 text-xs">Prioridad: Producto > Categoría > Usuario</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aplicado a</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Destino</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Valor</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reglas as $r)
                            <tr class="hover:bg-gray-50 {{ !$r->activo ? 'opacity-50' : '' }}">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $r->nombre }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $r->tipo_aplicacion === 'usuario' ? 'bg-purple-100 text-purple-700' : ($r->tipo_aplicacion === 'categoria' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700') }}">
                                        <i class="fas fa-{{ $r->tipo_aplicacion === 'usuario' ? 'user' : ($r->tipo_aplicacion === 'categoria' ? 'tag' : 'box') }} text-xs"></i>
                                        {{ $r->tipo_aplicacion_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $r->usuario?->name ?? $r->categoria?->nombre ?? $r->producto?->nombre ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-gray-600">
                                    {{ $r->tipo_calculo_label }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold">
                                    @if($r->tipo_calculo === 'porcentaje')
                                        <span class="text-blue-700">{{ $r->valor }}%</span>
                                    @else
                                        <span class="text-green-700">S/ {{ number_format($r->valor, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <form action="{{ route('comisiones.toggle', $r) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors
                                                {{ $r->activo ? 'bg-green-100 text-green-700 hover:bg-red-100 hover:text-red-700' : 'bg-gray-100 text-gray-500 hover:bg-green-100 hover:text-green-700' }}">
                                            <i class="fas fa-{{ $r->activo ? 'check-circle' : 'pause-circle' }}"></i>
                                            {{ $r->activo ? 'Activo' : 'Inactivo' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <form action="{{ route('comisiones.destroy', $r) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar esta regla?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                    <i class="fas fa-percentage text-4xl mb-3 block"></i>
                                    No hay reglas de comisión. Crea la primera.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal nueva regla --}}
        <div x-show="modalOpen" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none">
            <div class="absolute inset-0 bg-black/50" @click="modalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10">
                <div class="bg-gradient-to-r from-blue-900 to-blue-700 px-6 py-4 rounded-t-2xl flex items-center justify-between">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i> Nueva Regla de Comisión
                    </h3>
                    <button @click="modalOpen = false" class="text-white/70 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('comisiones.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre de la regla</label>
                        <input type="text" name="nombre" required placeholder="Ej: Comisión celulares Samsung"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Aplicar a</label>
                        <div class="flex gap-2">
                            @foreach(['usuario' => ['fas fa-user','Vendedor'], 'categoria' => ['fas fa-tag','Categoría'], 'producto' => ['fas fa-box','Producto']] as $val => $info)
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="tipo_aplicacion" value="{{ $val }}"
                                           x-model="tipoAplicacion" class="sr-only">
                                    <div class="text-center py-2 px-3 border-2 rounded-lg text-xs font-medium transition-all"
                                         :class="tipoAplicacion === '{{ $val }}' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-blue-300'">
                                        <i class="{{ $info[0] }} block text-base mb-0.5"></i>
                                        {{ $info[1] }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="tipoAplicacion === 'usuario'">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Vendedor</label>
                        <select name="user_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">— Selecciona vendedor —</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->role->nombre }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="tipoAplicacion === 'categoria'">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Categoría</label>
                        <select name="categoria_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">— Selecciona categoría —</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="tipoAplicacion === 'producto'">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Producto</label>
                        <select name="producto_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">— Selecciona producto —</option>
                            @foreach($productos as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }} ({{ $p->codigo }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de cálculo</label>
                            <select name="tipo_calculo" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="porcentaje">Porcentaje (%)</option>
                                <option value="monto_fijo">Monto fijo (S/ por unidad)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Valor</label>
                            <input type="number" name="valor" min="0.01" step="0.01" required placeholder="Ej: 2.5"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="modalOpen = false"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</body>
</html>
