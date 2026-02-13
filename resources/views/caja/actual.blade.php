<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja Actual - Sistema de Importaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />
    <x-header title="Caja Actual" />

    <div class="ml-64 p-8 pt-24">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-cash-register mr-2 text-green-500"></i>Caja Abierta</h2>
            <div x-data="{ showCerrar: false }">
                <button @click="showCerrar = true" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-lock mr-2"></i>Cerrar Caja
                </button>

                <div x-show="showCerrar" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-xl p-6 w-96" @click.outside="showCerrar = false">
                        <h3 class="text-lg font-bold mb-4">Cerrar Caja</h3>
                        <form action="{{ route('caja.cerrar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="caja_id" value="{{ $caja->id }}">
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-2">Monto calculado del sistema:</p>
                                <p class="text-xl font-bold text-blue-600 mb-4">S/ {{ number_format($caja->monto_final, 2) }}</p>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Monto real en caja (S/) *</label>
                                <input type="number" name="monto_final_real" min="0" step="0.01" required
                                       class="w-full rounded-lg border-gray-300 shadow-sm" placeholder="0.00">
                            </div>
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="showCerrar = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancelar</button>
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">Cerrar Caja</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-md p-6 text-center">
                <p class="text-sm text-gray-500">Almacén</p>
                <p class="text-lg font-bold text-gray-800">{{ $caja->almacen->nombre }}</p>
            </div>
            <div class="bg-blue-50 rounded-xl shadow-md p-6 text-center">
                <p class="text-sm text-gray-500">Monto Inicial</p>
                <p class="text-lg font-bold text-blue-600">S/ {{ number_format($caja->monto_inicial, 2) }}</p>
            </div>
            <div class="bg-green-50 rounded-xl shadow-md p-6 text-center">
                <p class="text-sm text-gray-500">Ingresos</p>
                <p class="text-lg font-bold text-green-600">S/ {{ number_format($caja->total_ingresos, 2) }}</p>
            </div>
            <div class="bg-purple-50 rounded-xl shadow-md p-6 text-center">
                <p class="text-sm text-gray-500">Saldo Actual</p>
                <p class="text-xl font-bold text-purple-600">S/ {{ number_format($caja->monto_final, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-700">Movimientos del Día</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($caja->movimientos as $mov)
                    <tr>
                        <td class="px-6 py-4 text-sm">{{ $mov->created_at->format('H:i') }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $mov->tipo === 'ingreso' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($mov->tipo) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $mov->concepto }}</td>
                        <td class="px-6 py-4 text-sm font-semibold {{ $mov->tipo === 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $mov->tipo === 'ingreso' ? '+' : '-' }} S/ {{ number_format($mov->monto, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No hay movimientos todavía</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
