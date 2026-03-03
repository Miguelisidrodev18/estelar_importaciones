<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apertura de Caja</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8">
    <x-header title="Apertura de Caja" subtitle="Inicia tu turno registrando el monto inicial en efectivo" />

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="max-w-xl mx-auto">

        {{-- Info del usuario/turno --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">
                <i class="fas fa-id-badge mr-1"></i> Información de tu turno
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400">Cajero</p>
                    <p class="font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Fecha de apertura</p>
                    <p class="font-semibold text-gray-800">{{ now()->locale('es')->isoFormat('D [de] MMMM, YYYY') }}</p>
                </div>
                @if($almacen)
                    <div>
                        <p class="text-xs text-gray-400">Almacén asignado</p>
                        <p class="font-semibold text-gray-800">
                            <i class="fas fa-warehouse text-blue-500 mr-1 text-xs"></i>{{ $almacen->nombre }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Sucursal</p>
                        <p class="font-semibold text-gray-800">
                            @if($almacen->sucursal ?? null)
                                <i class="fas fa-store text-purple-500 mr-1 text-xs"></i>{{ $almacen->sucursal->nombre }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </p>
                    </div>
                @else
                    <div class="col-span-2">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No tienes un almacén asignado. Contacta al administrador.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Formulario de apertura --}}
        @if($almacen)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
             x-data="{ monto: '', confirmando: false }">

            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-5">
                <i class="fas fa-cash-register mr-1"></i> Monto de apertura
            </h3>

            <form action="{{ route('caja.store') }}" method="POST">
                @csrf

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Efectivo en caja al iniciar <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-lg">S/</span>
                        <input type="number" name="monto_inicial" step="0.01" min="0"
                               x-model="monto"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-xl font-bold
                                      focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                      @error('monto_inicial') border-red-400 @enderror"
                               placeholder="0.00" required autofocus>
                    </div>
                    @error('monto_inicial')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">Cuenta el efectivo físico presente en caja antes de atender.</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones (opcional)</label>
                    <textarea name="observaciones" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                              placeholder="Notas al abrir la caja...">{{ old('observaciones') }}</textarea>
                </div>

                <div x-show="!confirmando">
                    <button type="button"
                            @click="confirmando = true"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg
                                   transition-colors flex items-center justify-center gap-2 text-base">
                        <i class="fas fa-lock-open"></i> Abrir Caja
                    </button>
                </div>

                <div x-show="confirmando" x-cloak class="bg-green-50 border border-green-200 rounded-xl p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                        <p class="font-semibold text-gray-800">
                            ¿Confirmar apertura con
                            <span class="text-green-700">S/ <span x-text="parseFloat(monto || 0).toFixed(2)"></span></span>?
                        </p>
                        <p class="text-sm text-gray-500 mt-1">Almacén: {{ $almacen->nombre }}</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="confirmando = false"
                                class="flex-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                            <i class="fas fa-check mr-1"></i> Confirmar
                        </button>
                    </div>
                </div>

            </form>
        </div>
        @endif

    </div>
</div>
<style>[x-cloak] { display: none !important; }</style>
</body>
</html>
