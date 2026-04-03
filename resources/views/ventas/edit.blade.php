<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Comprobante {{ $venta->codigo }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-10 max-w-2xl">

        <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
            <a href="{{ route('ventas.index') }}" class="hover:text-blue-600 transition-colors">Ventas</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="{{ route('ventas.show', $venta) }}" class="hover:text-blue-600 transition-colors">{{ $venta->codigo }}</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-700 font-medium">Editar</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Editar Comprobante</h1>

        {{-- Advertencia --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-start gap-3">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 shrink-0"></i>
            <div class="text-sm text-amber-800">
                <p class="font-semibold mb-0.5">Edición limitada</p>
                <p>Solo se pueden modificar campos no contables. Los productos, cantidades y totales no pueden cambiarse (requieren Nota de Crédito SUNAT). Esta edición queda registrada en el sistema.</p>
                <p class="mt-1 text-xs">Ventana de edición: {{ $ventanaMaxima }} horas desde la emisión. Emitido: {{ $venta->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Resumen no editable --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Datos del comprobante</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400">Código</p>
                    <p class="font-semibold text-gray-800">{{ $venta->codigo }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Tipo</p>
                    <p class="font-semibold text-gray-800 capitalize">{{ $venta->tipo_comprobante }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Cliente</p>
                    <p class="font-semibold text-gray-800">{{ $venta->cliente?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Total</p>
                    <p class="font-bold text-blue-600 text-lg">S/ {{ number_format($venta->total, 2) }}</p>
                </div>
            </div>
        </div>

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <ul class="text-sm text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-times-circle mr-1"></i>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Formulario de edición --}}
        <form action="{{ route('ventas.update', $venta) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Campos editables</h2>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Fecha</label>
                    <input type="date" name="fecha" value="{{ old('fecha', $venta->fecha->format('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                @if(!$venta->es_credito)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Método de pago</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['efectivo' => '💵 Efectivo', 'transferencia' => '🏦 Transferencia', 'yape' => '📱 Yape', 'plin' => '📱 Plin', 'mixto' => '🔀 Mixto'] as $metodo => $label)
                        <label class="flex items-center gap-2 border border-gray-200 rounded-xl p-3 cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="metodo_pago" value="{{ $metodo }}"
                                   {{ old('metodo_pago', $venta->metodo_pago) === $metodo ? 'checked' : '' }}
                                   class="text-blue-600">
                            <span class="text-sm font-medium">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Observaciones</label>
                    <textarea name="observaciones" rows="3"
                              placeholder="Notas adicionales sobre esta venta..."
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none">{{ old('observaciones', $venta->observaciones) }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <a href="{{ route('ventas.show', $venta) }}"
                   class="flex-1 border border-gray-200 text-gray-600 hover:bg-gray-50 py-3 rounded-xl font-semibold text-sm transition-colors text-center">
                    Cancelar
                </a>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold text-sm transition-colors">
                    <i class="fas fa-save mr-1"></i> Guardar cambios
                </button>
            </div>
        </form>

    </div>
</body>
</html>
