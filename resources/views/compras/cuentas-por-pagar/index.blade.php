<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuentas por Pagar - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Cuentas por Pagar" 
            subtitle="Gestión de obligaciones con proveedores" 
        />

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Pendiente</p>
                        <p class="text-2xl font-bold text-gray-800">S/ {{ number_format($stats['total_pendiente'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-clock text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-red-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Vencido</p>
                        <p class="text-2xl font-bold text-gray-800">S/ {{ number_format($stats['total_vencido'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Próximos 7 días</p>
                        <p class="text-2xl font-bold text-gray-800">S/ {{ number_format($stats['proximos_7_dias'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-calendar-alt text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pagado este mes</p>
                        <p class="text-2xl font-bold text-gray-800">S/ {{ number_format($stats['pagado_mes'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listado de cuentas -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-list mr-2 text-blue-600"></i>Cuentas por Pagar
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Factura</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emisión</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pagado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($cuentas as $cuenta)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $cuenta->numero_factura }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $cuenta->proveedor->razon_social }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $cuenta->fecha_emision->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm {{ $cuenta->esta_vencida ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                {{ $cuenta->fecha_vencimiento->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-medium">S/ {{ number_format($cuenta->monto_total, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-green-600">S/ {{ number_format($cuenta->monto_pagado, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold {{ $cuenta->saldo_pendiente > 0 ? 'text-red-600' : 'text-green-600' }}">
                                S/ {{ number_format($cuenta->saldo_pendiente, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($cuenta->estado == 'pagado')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Pagado</span>
                                @elseif($cuenta->estado == 'pendiente')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pendiente</span>
                                @elseif($cuenta->estado == 'parcial')
                                    <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs">Parcial</span>
                                @elseif($cuenta->estado == 'vencido')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Vencido</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('cuentas-por-pagar.show', $cuenta) }}" 
                                   class="text-blue-600 hover:text-blue-800" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($cuenta->saldo_pendiente > 0)
                                <button onclick="registrarPago({{ $cuenta->id }}, {{ $cuenta->saldo_pendiente }})" 
                                        class="ml-2 text-green-600 hover:text-green-800" title="Registrar pago">
                                    <i class="fas fa-credit-card"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-credit-card text-4xl mb-3 text-gray-300 block"></i>
                                <p>No hay cuentas por pagar</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function registrarPago(cuentaId, saldoPendiente) {
        Swal.fire({
            title: 'Registrar Pago',
            html: `
                <div class="text-left">
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Monto a pagar</label>
                        <input type="number" id="monto" step="0.01" min="0.01" max="${saldoPendiente}" value="${saldoPendiente}" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Fecha de pago</label>
                        <input type="date" id="fecha_pago" value="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Método de pago</label>
                        <select id="metodo_pago" class="w-full px-3 py-2 border rounded-lg">
                            <option value="transferencia">Transferencia</option>
                            <option value="cheque">Cheque</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Referencia</label>
                        <input type="text" id="referencia" placeholder="N° operación" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            preConfirm: () => {
                const monto = document.getElementById('monto').value;
                const fecha_pago = document.getElementById('fecha_pago').value;
                const metodo_pago = document.getElementById('metodo_pago').value;
                const referencia = document.getElementById('referencia').value;

                if (!monto || monto <= 0) {
                    Swal.showValidationMessage('El monto debe ser mayor a 0');
                    return false;
                }

                return fetch(`/cuentas-por-pagar/${cuentaId}/registrar-pago`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ monto, fecha_pago, metodo_pago, referencia })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('¡Pago registrado!', '', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudo conectar al servidor', 'error');
                });
            }
        });
    }
    </script>
</body>
</html>