<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta por Pagar - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-900">Dashboard</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <a href="{{ route('cuentas-por-pagar.index') }}" class="hover:text-blue-900">Cuentas por Pagar</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <span class="text-gray-700 font-medium">Factura {{ $cuenta->numero_factura }}</span>
            </div>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-credit-card mr-3 text-blue-900"></i>
                    Detalle de Cuenta por Pagar
                </h1>
                <div class="flex space-x-2">
                    <a href="{{ route('compras.show', $cuenta->compra_id) }}" 
                       class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-file-invoice mr-2"></i>Ver Compra
                    </a>
                    <a href="{{ route('cuentas-por-pagar.index') }}" 
                       class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Grid principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna izquierda: Resumen de la cuenta -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Tarjeta de estado -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Estado de la Cuenta
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm text-gray-600">Estado:</span>
                            @if($cuenta->estado == 'pagado')
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>Pagado
                                </span>
                            @elseif($cuenta->estado == 'pendiente')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-clock mr-1"></i>Pendiente
                                </span>
                            @elseif($cuenta->estado == 'parcial')
                                <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-adjust mr-1"></i>Parcial
                                </span>
                            @elseif($cuenta->estado == 'vencido')
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-exclamation-circle mr-1"></i>Vencido
                                </span>
                            @endif
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Factura:</span>
                                <span class="font-medium text-gray-900">{{ $cuenta->numero_factura }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Emisión:</span>
                                <span class="font-medium text-gray-900">{{ $cuenta->fecha_emision->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Vencimiento:</span>
                                <span class="font-medium {{ $cuenta->esta_vencida ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $cuenta->fecha_vencimiento->format('d/m/Y') }}
                                </span>
                            </div>
                            @if($cuenta->dias_credito)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Crédito:</span>
                                <span class="font-medium text-gray-900">{{ $cuenta->dias_credito }} días</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Moneda:</span>
                                <span class="font-medium text-gray-900">{{ $cuenta->moneda }}</span>
                            </div>
                            @if($cuenta->tipo_cambio && $cuenta->tipo_cambio != 1)
                            <div class="flex justify-between">
                                <span class="text-gray-600">T.C.:</span>
                                <span class="font-medium text-gray-900">{{ $cuenta->tipo_cambio }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de proveedor -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-700 to-purple-600 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-building mr-2"></i>
                            Proveedor
                        </h2>
                    </div>
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">{{ $cuenta->proveedor->razon_social }}</h3>
                        <p class="text-sm text-gray-600 mt-1">RUC: {{ $cuenta->proveedor->ruc }}</p>
                        @if($cuenta->proveedor->direccion)
                            <p class="text-sm text-gray-500 mt-2">{{ $cuenta->proveedor->direccion }}</p>
                        @endif
                        @if($cuenta->proveedor->telefono)
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-phone mr-1"></i>{{ $cuenta->proveedor->telefono }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Acciones rápidas -->
                @if($cuenta->saldo_pendiente > 0)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-green-700 to-green-600 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-bolt mr-2"></i>
                            Acciones
                        </h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <button onclick="registrarPago()" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition flex items-center justify-center">
                            <i class="fas fa-credit-card mr-2"></i>
                            Registrar Pago
                        </button>
                        <button onclick="programarPago()" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition flex items-center justify-center">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Programar Pago
                        </button>
                    </div>
                </div>
                @endif
            </div>

            <!-- Columna derecha: Información financiera y pagos -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Tarjeta de montos -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-yellow-600 to-yellow-500 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-chart-pie mr-2"></i>
                            Información Financiera
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Monto Total</p>
                                <p class="text-2xl font-bold text-gray-900">S/ {{ number_format($cuenta->monto_total, 2) }}</p>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Monto Pagado</p>
                                <p class="text-2xl font-bold text-green-600">S/ {{ number_format($cuenta->monto_pagado, 2) }}</p>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Saldo Pendiente</p>
                                <p class="text-2xl font-bold text-red-600">S/ {{ number_format($cuenta->saldo_pendiente, 2) }}</p>
                            </div>
                        </div>
                        
                        @if($cuenta->saldo_pendiente > 0 && $cuenta->fecha_vencimiento)
                        @php
                            $hoy = now()->startOfDay();
                            $vencimiento = \Carbon\Carbon::parse($cuenta->fecha_vencimiento)->startOfDay();
                            $diasRestantes = $hoy->diffInDays($vencimiento, false);
                        @endphp
                        <div class="mt-4 p-4 
                            {{ $diasRestantes < 0 ? 'bg-red-100' : ($diasRestantes <= 7 ? 'bg-yellow-100' : 'bg-blue-100') }} 
                            rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">
                                    @if($diasRestantes < 0)
                                        <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                        Vencida hace {{ abs($diasRestantes) }} días
                                    @elseif($diasRestantes == 0)
                                        <i class="fas fa-clock text-yellow-600 mr-2"></i>
                                        Vence hoy
                                    @else
                                        <i class="fas fa-calendar-check text-blue-600 mr-2"></i>
                                        Vence en {{ $diasRestantes }} días
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endif
                    </div>
                </div>

                <!-- Historial de pagos -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-history mr-2"></i>
                            Historial de Pagos
                        </h2>
                    </div>
                    
                    @if($cuenta->pagos->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cuota</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referencia</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registrado por</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobante</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($cuenta->pagos as $pago)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm">{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-sm text-right font-medium text-green-600">
                                            S/ {{ number_format($pago->monto, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @if($pago->numero_cuota && $pago->total_cuotas)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                                    {{ $pago->numero_cuota }}/{{ $pago->total_cuotas }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm">{{ ucfirst($pago->metodo_pago) }}</td>
                                        <td class="px-6 py-4 text-sm">{{ $pago->referencia ?? '-' }}</td>
                                        <td class="px-6 py-4">
                                            @if($pago->estado == 'procesado')
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Procesado</span>
                                            @elseif($pago->estado == 'programado')
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Programado</span>
                                            @elseif($pago->estado == 'fallido')
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Fallido</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm">{{ $pago->usuario->name }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            @if($pago->comprobante_path)
                                                <a href="{{ Storage::url($pago->comprobante_path) }}" 
                                                target="_blank"
                                                class="inline-flex items-center text-blue-600 hover:text-blue-800"
                                                title="Ver comprobante">
                                                    <i class="fas fa-file-image mr-1"></i> 
                                                    Ver
                                                </a>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="2" class="px-6 py-3 text-right font-bold text-gray-700">Total Pagado:</td>
                                        <td colspan="6" class="px-6 py-3 text-left font-bold text-green-600">
                                            S/ {{ number_format($cuenta->pagos->where('estado', 'procesado')->sum('monto'), 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <i class="fas fa-credit-card text-5xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 mb-2">No hay pagos registrados</p>
                            <p class="text-sm text-gray-400">Los pagos aparecerán aquí cuando se registren</p>
                        </div>
                    @endif
                </div>

    <script>
    function registrarPago() {
        Swal.fire({
            title: 'Registrar Pago',
            html: `
                <form id="pagoForm" enctype="multipart/form-data">
                    <div class="text-left space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Monto a pagar</label>
                            <input type="number" id="monto" step="0.01" min="0.01" max="{{ $cuenta->saldo_pendiente }}" value="{{ $cuenta->saldo_pendiente }}" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Fecha de pago</label>
                            <input type="date" id="fecha_pago" value="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Método de pago</label>
                            <select id="metodo_pago" class="w-full px-3 py-2 border rounded-lg">
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Referencia</label>
                            <input type="text" id="referencia" placeholder="N° operación, cheque, etc." class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mt-2">
                            <h4 class="font-medium text-gray-900 mb-3">Pago en cuotas (opcional)</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">N° de cuota</label>
                                    <input type="number" id="numero_cuota" min="1" class="w-full px-3 py-2 border rounded-lg" placeholder="Ej: 1">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Total cuotas</label>
                                    <input type="number" id="total_cuotas" min="1" class="w-full px-3 py-2 border rounded-lg" placeholder="Ej: 3">
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Comprobante (opcional)</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-500 transition">
                                <input type="file" id="comprobante" accept="image/*" class="hidden" onchange="mostrarNombreArchivo(this)">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-600">
                                    <button type="button" onclick="document.getElementById('comprobante').click()" class="text-blue-600 hover:text-blue-800">
                                        Haz clic para seleccionar
                                    </button>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF (Max. 5MB)</p>
                                <div id="nombre_comprobante" class="mt-2 text-sm text-gray-600 hidden"></div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Observaciones</label>
                            <textarea id="observaciones" rows="2" class="w-full px-3 py-2 border rounded-lg" placeholder="Notas adicionales..."></textarea>
                        </div>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar Pago',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            width: '600px',
            didOpen: () => {
                // No necesitamos hacer nada especial aquí
            },
            preConfirm: () => {
                const formData = new FormData();
                
                formData.append('monto', document.getElementById('monto').value);
                formData.append('fecha_pago', document.getElementById('fecha_pago').value);
                formData.append('metodo_pago', document.getElementById('metodo_pago').value);
                formData.append('referencia', document.getElementById('referencia').value);
                formData.append('observaciones', document.getElementById('observaciones').value);
                formData.append('numero_cuota', document.getElementById('numero_cuota').value || '');
                formData.append('total_cuotas', document.getElementById('total_cuotas').value || '');
                
                const comprobante = document.getElementById('comprobante').files[0];
                if (comprobante) {
                    formData.append('comprobante', comprobante);
                }

                if (!formData.get('monto') || formData.get('monto') <= 0) {
                    Swal.showValidationMessage('El monto debe ser mayor a 0');
                    return false;
                }

                return fetch('{{ route("cuentas-por-pagar.registrar-pago", $cuenta) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en el servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire('¡Pago registrado!', '', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'No se pudo conectar al servidor: ' + error.message, 'error');
                });
            }
        });
    }

    // Función auxiliar para mostrar nombre del archivo
    function mostrarNombreArchivo(input) {
        const nombreDiv = document.getElementById('nombre_comprobante');
        if (input.files && input.files[0]) {
            nombreDiv.textContent = 'Archivo: ' + input.files[0].name;
            nombreDiv.classList.remove('hidden');
        }
    }
    function programarPago() {
        Swal.fire({
            title: 'Programar Pago',
            html: `
                <div class="text-left">
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Monto a pagar</label>
                        <input type="number" id="monto_programado" step="0.01" min="0.01" max="{{ $cuenta->saldo_pendiente }}" value="{{ $cuenta->saldo_pendiente }}" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Fecha programada</label>
                        <input type="date" id="fecha_programada" min="{{ now()->format('Y-m-d') }}" value="{{ now()->addDays(7)->format('Y-m-d') }}" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-1">Observaciones</label>
                        <textarea id="observaciones" rows="2" class="w-full px-3 py-2 border rounded-lg" placeholder="Notas adicionales..."></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Programar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
            preConfirm: () => {
                const monto = document.getElementById('monto_programado').value;
                const fecha_programada = document.getElementById('fecha_programada').value;
                const observaciones = document.getElementById('observaciones').value;

                if (!monto || monto <= 0) {
                    Swal.showValidationMessage('El monto debe ser mayor a 0');
                    return false;
                }

                return fetch('{{ route("cuentas-por-pagar.guardar-programacion", $cuenta) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        monto: monto, 
                        fecha_programada: fecha_programada, 
                        observaciones: observaciones 
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en el servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire('¡Pago programado!', '', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'No se pudo conectar al servidor: ' + error.message, 'error');
                });
            }
        });
    }
    </script>
</body>
</html>