<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cola de Caja</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8"
         x-data="colaApp()"
         x-init="iniciarPolling()">

        <div class="flex items-center justify-between mb-1">
            <a href="{{ route('cajero.dashboard') }}" class="text-sm text-gray-500 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-1"></i> Mi Panel
            </a>
        </div>
        <x-header title="Cola de Caja" subtitle="Ventas pendientes de cobro — se actualiza automáticamente cada 30 s" />

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm flex items-center gap-2">
                <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Counter badge --}}
        <div class="flex items-center gap-3 mb-6">
            <span class="inline-flex items-center gap-2 bg-amber-100 text-amber-800 text-sm font-semibold px-4 py-2 rounded-full">
                <i class="fas fa-clock"></i>
                <span x-text="ventas.length"></span> venta(s) en cola
            </span>
            <span class="text-xs text-gray-400">
                <i class="fas fa-sync-alt mr-1"></i>Última actualización: <span x-text="ultimaActualizacion"></span>
            </span>
        </div>

        {{-- Grid de ventas --}}
        <div x-show="ventas.length === 0" x-cloak
             class="bg-white rounded-2xl shadow-sm p-12 text-center text-gray-400">
            <i class="fas fa-check-circle text-4xl text-green-400 block mb-3"></i>
            <p class="font-semibold text-gray-600">Cola vacía</p>
            <p class="text-sm mt-1">No hay ventas pendientes de cobro en este momento.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="v in ventas" :key="v.id">
                <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition">
                    {{-- Header card --}}
                    <div class="bg-gradient-to-r from-amber-500 to-amber-400 px-4 py-3 flex items-center justify-between">
                        <span class="font-mono text-white font-bold text-sm" x-text="v.codigo"></span>
                        <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded-full" x-text="v.hora_creacion"></span>
                    </div>
                    <div class="p-4 space-y-3">
                        {{-- Cliente --}}
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs shrink-0"
                                 x-text="(v.cliente ?? '?').charAt(0).toUpperCase()"></div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm" x-text="v.cliente || 'Sin cliente'"></p>
                                <p class="text-xs text-gray-400" x-text="'Vendedor: ' + v.vendedor"></p>
                            </div>
                        </div>

                        {{-- Productos --}}
                        <div class="text-xs text-gray-600 bg-gray-50 rounded-lg p-2 max-h-24 overflow-y-auto">
                            <template x-for="d in v.detalles" :key="d.id">
                                <div class="flex justify-between py-0.5">
                                    <span x-text="d.cantidad + 'x ' + d.producto"></span>
                                    <span class="font-mono font-semibold" x-text="'S/ ' + d.subtotal"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Total --}}
                        <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                            <span class="text-sm text-gray-600 font-medium">Total</span>
                            <span class="text-xl font-bold text-gray-800" x-text="'S/ ' + v.total"></span>
                        </div>

                        {{-- Acciones --}}
                        <div class="flex gap-2">
                            <button type="button"
                                    @click="abrirModalCobro(v)"
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2 rounded-xl transition flex items-center justify-center gap-2">
                                <i class="fas fa-money-bill-wave"></i> Cobrar
                            </button>
                            <a :href="'/ventas/' + v.id + '/pdf'" target="_blank"
                               class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition" title="Ver comprobante">
                                <i class="fas fa-file-pdf text-red-500"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Modal de cobro --}}
        <div x-show="modalCobro" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="modalCobro = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10">
                <div class="bg-gradient-to-r from-green-700 to-green-600 px-6 py-4 rounded-t-2xl flex items-center justify-between">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fas fa-money-bill-wave"></i> Confirmar Pago
                    </h3>
                    <button @click="modalCobro = false" class="text-white/70 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6" x-show="ventaActual">
                    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <p class="text-sm text-gray-700">Venta: <span class="font-mono font-bold" x-text="ventaActual?.codigo"></span></p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">S/ <span x-text="ventaActual?.total"></span></p>
                    </div>

                    <form :action="'/ventas/' + ventaActual?.id + '/confirmar-pago'" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Método de Pago</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach(['efectivo' => 'Efectivo', 'yape' => 'Yape', 'plin' => 'Plin', 'transferencia' => 'Transferencia'] as $val => $label)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="metodo_pago" value="{{ $val }}" x-model="metodoPago" class="sr-only">
                                        <div class="text-center py-2 border-2 rounded-xl text-sm font-medium transition"
                                             :class="metodoPago === '{{ $val }}' ? 'border-green-600 bg-green-50 text-green-700' : 'border-gray-200 text-gray-600 hover:border-green-300'">
                                            {{ $label }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <input type="hidden" name="redirect_to" value="{{ route('cajero.cola') }}">
                        <div class="flex gap-3">
                            <button type="button" @click="modalCobro = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-xl text-sm text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit"
                                    :disabled="!metodoPago"
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition disabled:opacity-50">
                                <i class="fas fa-check mr-1"></i> Confirmar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

<script>
function colaApp() {
    const ventasData = @json($ventas->map(fn($v) => [
        'id'             => $v->id,
        'codigo'         => $v->codigo,
        'total'          => number_format($v->total, 2),
        'cliente'        => $v->cliente ? $v->cliente->nombre . ' ' . ($v->cliente->apellido ?? '') : null,
        'vendedor'       => $v->usuario?->name ?? '—',
        'hora_creacion'  => $v->created_at->format('H:i'),
        'detalles'       => $v->detalles->map(fn($d) => [
            'id'       => $d->id,
            'cantidad' => $d->cantidad,
            'producto' => $d->producto?->nombre ?? '—',
            'subtotal' => number_format($d->subtotal, 2),
        ])->values(),
    ])->values());

    return {
        ventas:             ventasData,
        modalCobro:         false,
        ventaActual:        null,
        metodoPago:         'efectivo',
        ultimaActualizacion: new Date().toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit'}),
        pollingInterval:    null,

        iniciarPolling() {
            this.pollingInterval = setInterval(() => this.recargar(), 30000);
        },

        async recargar() {
            try {
                const resp = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (resp.ok) {
                    this.ultimaActualizacion = new Date().toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit'});
                }
            } catch {}
        },

        abrirModalCobro(venta) {
            this.ventaActual = venta;
            this.metodoPago  = 'efectivo';
            this.modalCobro  = true;
        },
    };
}
</script>
</body>
</html>
