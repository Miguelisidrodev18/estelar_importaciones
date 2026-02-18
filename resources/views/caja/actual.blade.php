{{-- resources/views/caja/actual.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja Actual - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hover-scale {
            transition: transform 0.2s ease-in-out;
        }
        .hover-scale:hover {
            transform: translateY(-3px);
        }
        .receipt-paper {
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .modal-overlay {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8" x-data="{ 
        showGastoModal: false,
        showIngresoModal: false,
        showCierreModal: false,
        showComprobanteModal: false,
        selectedMovimiento: null
    }">
        
        {{-- Header --}}
        <x-header 
            title="Caja del Día" 
            subtitle="{{ now()->format('d/m/Y') }} - {{ auth()->user()->name }}"
        />

        {{-- Alertas --}}
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex justify-between items-center">
                <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex justify-between items-center">
                <span><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        {{-- Resumen de Caja --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-900 to-blue-800 rounded-xl shadow-lg p-6 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-blue-200 text-sm">Saldo Actual</p>
                        <p class="text-3xl font-bold mt-2">S/ {{ number_format($caja->monto_final, 2) }}</p>
                        <p class="text-blue-200 text-xs mt-1">Monto inicial: S/ {{ number_format($caja->monto_inicial, 2) }}</p>
                    </div>
                    <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                        <i class="fas fa-cash-register text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm">Ingresos del Día</p>
                        <p class="text-2xl font-bold text-green-600 mt-2">S/ {{ number_format($caja->total_ingresos, 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $caja->movimientos->where('tipo', 'ingreso')->count() }} transacciones</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm">Gastos del Día</p>
                        <p class="text-2xl font-bold text-red-600 mt-2">S/ {{ number_format($caja->total_egresos, 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $caja->movimientos->where('tipo', 'egreso')->count() }} gastos</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm">Almacén</p>
                        <p class="text-xl font-bold text-gray-800 mt-2">{{ $caja->almacen->nombre }}</p>
                        <p class="text-xs text-gray-500 mt-1">Abierta: {{ $caja->created_at->format('H:i') }}</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-store text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Acciones Rápidas --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <button @click="showIngresoModal = true" 
                    class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl p-4 hover-scale flex items-center justify-between group">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 p-3 rounded-lg mr-4">
                        <i class="fas fa-plus-circle text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold">Registrar Ingreso</h4>
                        <p class="text-sm opacity-90">Ventas u otros ingresos</p>
                    </div>
                </div>
                <i class="fas fa-arrow-right text-xl group-hover:translate-x-2 transition-transform"></i>
            </button>

            <button @click="showGastoModal = true"
                    class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl p-4 hover-scale flex items-center justify-between group">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 p-3 rounded-lg mr-4">
                        <i class="fas fa-minus-circle text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold">Registrar Gasto</h4>
                        <p class="text-sm opacity-90">Comidas, pasajes, etc.</p>
                    </div>
                </div>
                <i class="fas fa-arrow-right text-xl group-hover:translate-x-2 transition-transform"></i>
            </button>

            <button @click="showCierreModal = true"
                    class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-xl p-4 hover-scale flex items-center justify-between group">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 p-3 rounded-lg mr-4">
                        <i class="fas fa-lock text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold">Cerrar Caja</h4>
                        <p class="text-sm opacity-90">Realizar cierre del día</p>
                    </div>
                </div>
                <i class="fas fa-arrow-right text-xl group-hover:translate-x-2 transition-transform"></i>
            </button>
        </div>

        {{-- Tabla de Movimientos --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-history mr-2 text-blue-900"></i>
                    Movimientos del Día
                </h3>
                <div class="flex space-x-2">
                    <button onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm">
                        <i class="fas fa-print mr-2"></i>Imprimir
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Observaciones</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobante</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($caja->movimientos as $movimiento)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $movimiento->created_at->format('H:i') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $movimiento->tipo === 'ingreso' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($movimiento->tipo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $movimiento->concepto }}</p>
                                    @if($movimiento->venta_id)
                                        <p class="text-xs text-gray-500">Venta #{{ $movimiento->venta_id }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold {{ $movimiento->tipo === 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movimiento->tipo === 'ingreso' ? '+' : '-' }} S/ {{ number_format($movimiento->monto, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $movimiento->observaciones ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <button @click="selectedMovimiento = {{ $movimiento }}; showComprobanteModal = true"
                                        class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-receipt mr-1"></i>Ver
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-receipt text-4xl mb-3 text-gray-300"></i>
                                <p>No hay movimientos registrados hoy</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Registrar Ingreso --}}
        <div x-show="showIngresoModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto modal-overlay" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" @click.away="showIngresoModal = false">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Registrar Ingreso</h3>
                        <button @click="showIngresoModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form action="{{ route('caja.ingreso') }}" method="POST">
                        @csrf
                        <input type="hidden" name="caja_id" value="{{ $caja->id }}">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Monto *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">S/</span>
                                    <input type="number" name="monto" step="0.01" min="0.01" required
                                           class="pl-10 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Concepto *</label>
                                <input type="text" name="concepto" required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Ej: Venta contado, Cobro cliente, etc.">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                                <textarea name="observaciones" rows="2"
                                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Detalles adicionales..."></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                            <button type="button" @click="showIngresoModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-save mr-2"></i>Registrar Ingreso
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Registrar Gasto --}}
        <div x-show="showGastoModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto modal-overlay"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" @click.away="showGastoModal = false">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Registrar Gasto</h3>
                        <button @click="showGastoModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form action="{{ route('caja.gasto') }}" method="POST">
                        @csrf
                        <input type="hidden" name="caja_id" value="{{ $caja->id }}">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Monto *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">S/</span>
                                    <input type="number" name="monto" step="0.01" min="0.01" required
                                           class="pl-10 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Gasto *</label>
                                <select name="categoria_gasto" required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Seleccione...</option>
                                    <option value="comida">Comida / Refrigerio</option>
                                    <option value="pasaje">Pasaje / Transporte</option>
                                    <option value="movilidad">Movilidad</option>
                                    <option value="utiles">Útiles de oficina</option>
                                    <option value="servicios">Servicios</option>
                                    <option value="otros">Otros</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Concepto / Descripción *</label>
                                <input type="text" name="concepto" required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Ej: Almuerzo equipo, Taxi a cliente, etc.">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                                <textarea name="observaciones" rows="2"
                                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Detalles adicionales..."></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                            <button type="button" @click="showGastoModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                <i class="fas fa-save mr-2"></i>Registrar Gasto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Cerrar Caja --}}
        <div x-show="showCierreModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto modal-overlay"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" @click.away="showCierreModal = false">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Cerrar Caja</h3>
                        <button @click="showCierreModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <p class="text-sm text-yellow-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            Verifica que todos los movimientos estén registrados antes de cerrar.
                        </p>
                    </div>
                    
                    <form action="{{ route('caja.cerrar') }}" method="POST">
                        @csrf
                        <input type="hidden" name="caja_id" value="{{ $caja->id }}">
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">Monto según sistema:</p>
                                <p class="text-2xl font-bold text-blue-600 mb-2">S/ {{ number_format($caja->monto_final, 2) }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Monto real en caja *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">S/</span>
                                    <input type="number" name="monto_final_real" step="0.01" min="0" required
                                           class="pl-10 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="0.00">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones del cierre</label>
                                <textarea name="observaciones_cierre" rows="2"
                                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Detalles sobre el cierre..."></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                            <button type="button" @click="showCierreModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                <i class="fas fa-lock mr-2"></i>Cerrar Caja
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Comprobante --}}
        <div x-show="showComprobanteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto modal-overlay"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6" @click.away="showComprobanteModal = false">
                    <template x-if="selectedMovimiento">
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold text-gray-900">Comprobante de Movimiento</h3>
                                <button @click="showComprobanteModal = false" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            
                            <div class="border-2 border-gray-200 rounded-lg p-6 mb-4 receipt-paper">
                                <div class="text-center mb-4">
                                    <h4 class="font-bold text-lg">CORPORACIÓN ADIVON SAC</h4>
                                    <p class="text-sm text-gray-600">RUC: 20601234567</p>
                                    <p class="text-sm text-gray-600">{{ $caja->almacen->nombre }}</p>
                                </div>
                                
                                <div class="border-t border-b border-gray-200 py-3 mb-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Fecha:</span>
                                        <span class="font-medium" x-text="new Date(selectedMovimiento.created_at).toLocaleString()"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Tipo:</span>
                                        <span class="font-medium" x-text="selectedMovimiento.tipo.toUpperCase()"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Caja #:</span>
                                        <span class="font-medium" x-text="selectedMovimiento.caja_id"></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="text-sm text-gray-600">Concepto:</p>
                                    <p class="font-medium" x-text="selectedMovimiento.concepto"></p>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="text-sm text-gray-600">Monto:</p>
                                    <p class="text-2xl font-bold" :class="selectedMovimiento.tipo === 'ingreso' ? 'text-green-600' : 'text-red-600'">
                                        <span x-text="selectedMovimiento.tipo === 'ingreso' ? '+' : '-'"></span>
                                        S/ <span x-text="parseFloat(selectedMovimiento.monto).toFixed(2)"></span>
                                    </p>
                                </div>
                                
                                <template x-if="selectedMovimiento.observaciones">
                                    <div class="mb-3">
                                        <p class="text-sm text-gray-600">Observaciones:</p>
                                        <p class="text-sm" x-text="selectedMovimiento.observaciones"></p>
                                    </div>
                                </template>
                                
                                <div class="text-center text-xs text-gray-500 mt-4">
                                    <p>Comprobante generado por sistema</p>
                                    <p>Usuario: {{ auth()->user()->name }}</p>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button type="button" @click="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-print mr-2"></i>Imprimir
                                </button>
                                <button type="button" @click="showComprobanteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        @media print {
            body * { visibility: hidden; }
            .receipt-paper, .receipt-paper * { visibility: visible; }
            .receipt-paper { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
</body>
</html> 