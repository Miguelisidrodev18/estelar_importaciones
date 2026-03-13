<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traslados Pendientes - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Traslados Pendientes"
            subtitle="Confirma la recepción de los traslados entre almacenes"
        />

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-clock text-yellow-500 mr-2"></i>Traslados Pendientes de Confirmación
            </h2>
            <a href="{{ route('traslados.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                Ver todos los traslados
            </a>
        </div>

        @forelse($traslados as $traslado)
        @php $esSerie = $traslado->producto->tipo_inventario === 'serie'; @endphp

        <div class="bg-white rounded-xl shadow-md p-6 mb-4">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <span class="font-mono text-blue-600">{{ $traslado->numero_guia ?? 'Sin guía' }}</span>
                        <span class="text-gray-700">- {{ $traslado->producto->nombre }}</span>
                        @if($esSerie)
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold bg-purple-100 text-purple-700 rounded-full">
                                <i class="fas fa-barcode mr-1"></i>IMEI
                            </span>
                        @endif
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-warehouse mr-1"></i>{{ $traslado->almacen->nombre }}
                        <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                        <i class="fas fa-warehouse mr-1"></i>{{ $traslado->almacenDestino->nombre ?? '-' }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        Cantidad solicitada: <span class="font-semibold">{{ $traslado->cantidad }}</span>
                        @if($esSerie)
                            <span class="text-purple-600 font-medium">({{ $traslado->imeis_disponibles->count() }} IMEI disponibles)</span>
                        @endif
                        &nbsp;|&nbsp; Solicitado por: {{ $traslado->usuario->name }}
                        &nbsp;|&nbsp; Fecha: {{ $traslado->created_at->format('d/m/Y H:i') }}
                    </p>
                    @if($traslado->motivo)
                        <p class="text-sm text-gray-400 mt-1 italic">"{{ $traslado->motivo }}"</p>
                    @endif
                </div>

                @if($esSerie)
                    {{-- Botón que abre el modal de selección de IMEIs --}}
                    <button type="button"
                            onclick="abrirModalImei({{ $traslado->id }}, {{ $traslado->cantidad }})"
                            class="bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-3 rounded-lg whitespace-nowrap shrink-0">
                        <i class="fas fa-check-double mr-2"></i>Confirmar y asignar IMEIs
                    </button>
                @else
                    {{-- Confirmación directa para productos sin serie --}}
                    <form action="{{ route('traslados.confirmar', $traslado) }}" method="POST"
                          onsubmit="return confirm('¿Confirmar la recepción de este traslado?')">
                        @csrf
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-3 rounded-lg whitespace-nowrap shrink-0">
                            <i class="fas fa-check-double mr-2"></i>Confirmar Recepción
                        </button>
                    </form>
                @endif
            </div>

            {{-- IMEIs disponibles en origen (solo serie) --}}
            @if($esSerie && $traslado->imeis_disponibles->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-2">
                        <i class="fas fa-info-circle mr-1 text-blue-400"></i>
                        IMEIs disponibles en <strong>{{ $traslado->almacen->nombre }}</strong>:
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($traslado->imeis_disponibles as $imei)
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">
                                {{ $imei->codigo_imei }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($esSerie && $traslado->imeis_disponibles->isEmpty())
                <div class="mt-3 flex items-center gap-2 text-sm text-amber-700 bg-amber-50 px-3 py-2 rounded-lg">
                    <i class="fas fa-exclamation-triangle"></i>
                    No hay IMEIs disponibles en el almacén origen para este producto.
                </div>
            @endif
        </div>

        {{-- Modal de selección de IMEIs --}}
        @if($esSerie)
        <div id="modal-imei-{{ $traslado->id }}"
             class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col">

                {{-- Header --}}
                <div class="bg-linear-to-r from-blue-900 to-blue-700 px-6 py-4 rounded-t-2xl flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <i class="fas fa-barcode"></i> Seleccionar IMEIs a enviar
                        </h3>
                        <p class="text-blue-200 text-sm mt-0.5">{{ $traslado->producto->nombre }}</p>
                    </div>
                    <button onclick="cerrarModalImei({{ $traslado->id }})" class="text-white/80 hover:text-white text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('traslados.confirmar', $traslado) }}" method="POST" class="flex flex-col flex-1 overflow-hidden"
                      id="form-imei-{{ $traslado->id }}">
                    @csrf

                    {{-- Info del traslado --}}
                    <div class="px-6 py-3 bg-blue-50 border-b border-blue-100 text-sm text-blue-800">
                        <i class="fas fa-warehouse mr-1"></i>
                        <strong>{{ $traslado->almacen->nombre }}</strong>
                        <i class="fas fa-arrow-right mx-2 text-blue-400"></i>
                        <strong>{{ $traslado->almacenDestino->nombre }}</strong>
                        &nbsp;·&nbsp; Necesitas seleccionar
                        <span class="font-bold" id="contador-necesario-{{ $traslado->id }}">{{ $traslado->cantidad }}</span>
                        IMEI(s).
                        Seleccionados: <span class="font-bold text-green-700" id="contador-{{ $traslado->id }}">0</span>
                    </div>

                    {{-- Lista de IMEIs --}}
                    <div class="overflow-y-auto flex-1 px-6 py-4">
                        @if($traslado->imeis_disponibles->isEmpty())
                            <p class="text-center text-gray-500 py-8">
                                <i class="fas fa-box-open text-3xl text-gray-300 block mb-2"></i>
                                No hay IMEIs disponibles en el almacén origen.
                            </p>
                        @else
                            <div class="space-y-2">
                                @foreach($traslado->imeis_disponibles as $imei)
                                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-400 hover:bg-blue-50 cursor-pointer transition-colors group">
                                        <input type="checkbox"
                                               name="imei_ids[]"
                                               value="{{ $imei->id }}"
                                               class="imei-check-{{ $traslado->id }} w-4 h-4 accent-blue-600 cursor-pointer"
                                               onchange="actualizarContador({{ $traslado->id }}, {{ $traslado->cantidad }})">
                                        <div class="flex-1">
                                            <span class="font-mono text-sm font-semibold text-gray-800 group-hover:text-blue-700">
                                                {{ $imei->codigo_imei }}
                                            </span>
                                            @if($imei->serie)
                                                <span class="text-xs text-gray-400 ml-2">Serie: {{ $imei->serie }}</span>
                                            @endif
                                        </div>
                                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">En stock</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center gap-3">
                        <button type="button" onclick="cerrarModalImei({{ $traslado->id }})"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                id="btn-confirmar-{{ $traslado->id }}"
                                class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            <i class="fas fa-check-double"></i>
                            Confirmar traslado
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        @empty
        <div class="bg-white rounded-xl shadow-md p-12 text-center">
            <i class="fas fa-check-circle text-5xl text-green-300 mb-4 block"></i>
            <p class="text-gray-500 text-lg">No hay traslados pendientes</p>
        </div>
        @endforelse
    </div>

<script>
function abrirModalImei(trasladoId, cantidad) {
    const modal = document.getElementById('modal-imei-' + trasladoId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        actualizarContador(trasladoId, cantidad);
    }
}

function cerrarModalImei(trasladoId) {
    const modal = document.getElementById('modal-imei-' + trasladoId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        // Desmarcar todos los checkboxes
        document.querySelectorAll('.imei-check-' + trasladoId).forEach(cb => cb.checked = false);
        actualizarContador(trasladoId, parseInt(document.getElementById('contador-necesario-' + trasladoId)?.textContent || 0));
    }
}

function actualizarContador(trasladoId, necesario) {
    const checked = document.querySelectorAll('.imei-check-' + trasladoId + ':checked').length;
    const contadorEl = document.getElementById('contador-' + trasladoId);
    const btnEl = document.getElementById('btn-confirmar-' + trasladoId);

    if (contadorEl) contadorEl.textContent = checked;

    if (btnEl) {
        btnEl.disabled = checked !== necesario;
    }
}

// Cerrar modal al hacer click en el fondo
document.querySelectorAll('[id^="modal-imei-"]').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            const id = this.id.replace('modal-imei-', '');
            cerrarModalImei(id);
        }
    });
});
</script>
</body>
</html>
