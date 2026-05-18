<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Comisiones - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8" x-data="{ selected: [] }">
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('comisiones.index') }}" class="text-sm text-gray-500 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-1"></i> Configuración
            </a>
        </div>
        <x-header title="Reporte de Comisiones" subtitle="Consulta y paga las comisiones generadas por ventas" />

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm flex items-center gap-2">
                <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white rounded-2xl shadow-sm p-5 mb-6">
            <form method="GET" action="{{ route('comisiones.reporte') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Vendedor</label>
                    <select name="user_id" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        @foreach($vendedores as $v)
                            <option value="{{ $v->id }}" {{ request('user_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Estado</label>
                    <select name="estado" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="pagado" {{ request('estado') === 'pagado' ? 'selected' : '' }}>Pagado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                           class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                           class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition-colors">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <a href="{{ route('comisiones.reporte') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-times"></i>
                </a>
            </form>
        </div>

        {{-- Summary cards per seller --}}
        @if($resumen->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @foreach($resumen as $r)
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm">
                        {{ strtoupper(substr($r->vendedor?->name ?? '?', 0, 1)) }}
                    </div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $r->vendedor?->name ?? 'Desconocido' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-amber-50 rounded-lg p-2 text-center">
                        <p class="font-bold text-amber-700">S/ {{ number_format($r->pendiente, 2) }}</p>
                        <p class="text-amber-600">Pendiente</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-2 text-center">
                        <p class="font-bold text-green-700">S/ {{ number_format($r->pagado, 2) }}</p>
                        <p class="text-green-600">Pagado</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Bulk pay action --}}
        <form action="{{ route('comisiones.marcar-pagado') }}" method="POST" id="formPagado">
            @csrf
            <div class="flex justify-between items-center mb-3 flex-wrap gap-3">
                <p class="text-sm text-gray-600">
                    <span x-text="selected.length"></span> seleccionadas
                </p>
                <button type="submit"
                        :disabled="selected.length === 0"
                        x-bind:class="selected.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700'"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold transition-colors">
                    <i class="fas fa-check-double"></i> Marcar Pagado
                </button>
            </div>

            {{-- Detail table --}}
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-center w-10">
                                    <input type="checkbox"
                                           @change="selected = $event.target.checked ? Array.from(document.querySelectorAll('.cb-comision')).map(el => el.value) : []"
                                           class="rounded border-gray-300">
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vendedor</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Venta</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Regla</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Comisión</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Fecha pago</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($comisiones as $com)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-center">
                                        @if($com->estado === 'pendiente')
                                            <input type="checkbox" name="ids[]" value="{{ $com->id }}"
                                                   class="cb-comision rounded border-gray-300"
                                                   x-on:change="$event.target.checked ? selected.push('{{ $com->id }}') : selected.splice(selected.indexOf('{{ $com->id }}'), 1)">
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $com->vendedor?->name }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-600">
                                        {{ $com->detalleVenta?->venta?->codigo }}
                                        <br>
                                        <span class="text-gray-400">{{ $com->detalleVenta?->venta?->fecha?->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600">{{ $com->detalleVenta?->producto?->nombre }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $com->regla?->nombre ?? 'Sin regla' }}
                                        <br>
                                        @if($com->tipo_calculo === 'porcentaje')
                                            <span class="text-blue-600">{{ $com->valor_configurado }}%</span>
                                        @else
                                            <span class="text-green-600">S/ {{ $com->valor_configurado }} por ud</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-800">
                                        S/ {{ number_format($com->monto_comision, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($com->estado === 'pagado')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                <i class="fas fa-check-circle"></i> Pagado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                <i class="fas fa-clock"></i> Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                                        {{ $com->fecha_pago?->format('d/m/Y') ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                        <i class="fas fa-chart-bar text-4xl mb-3 block"></i>
                                        No hay comisiones con los filtros aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($comisiones->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $comisiones->links() }}
                    </div>
                @endif
            </div>
        </form>
    </div>
</body>
</html>
