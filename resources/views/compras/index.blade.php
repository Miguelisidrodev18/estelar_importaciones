<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Compras" 
            subtitle="Registro de compras a proveedores" 
        />

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Estadísticas --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Compras</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $compras->count() }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pagadas</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $compras->filter(fn($c) => $c->cuentaPorPagar && $c->cuentaPorPagar->estado == 'pagado')->count() }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Por Pagar</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $compras->filter(fn($c) => $c->cuentaPorPagar && $c->cuentaPorPagar->saldo_pendiente > 0)->count() }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-purple-500 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Invertido</p>
                        <p class="text-2xl font-bold text-gray-800">S/ {{ number_format($compras->sum('total'), 2) }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-list mr-2 text-blue-600"></i>Historial de Compras
                </h3>
                <a href="{{ route('compras.create') }}" class="bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2 px-4 rounded-lg transition-colors text-sm">
                    <i class="fas fa-plus mr-2"></i>Nueva Compra
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Compra</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Pago</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($compras as $compra)
                            @php
                                $cuenta = $compra->cuentaPorPagar;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-mono font-semibold text-blue-700">{{ $compra->codigo }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $compra->proveedor->razon_social ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $compra->numero_factura }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $compra->fecha->format('d/m/Y') }}</td>

                                {{-- Tipo de Compra --}}
                                <td class="px-6 py-4">
                                    @php
                                        $tc = match($compra->tipo_compra ?? 'local') {
                                            'local'       => ['label' => 'Local',       'class' => 'bg-green-100 text-green-800',  'icon' => 'fa-store'],
                                            'importacion' => ['label' => 'Importación', 'class' => 'bg-orange-100 text-orange-800', 'icon' => 'fa-ship'],
                                            default       => ['label' => ucfirst($compra->tipo_compra ?? 'local'), 'class' => 'bg-gray-100 text-gray-800', 'icon' => 'fa-tag'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full {{ $tc['class'] }}">
                                        <i class="fas {{ $tc['icon'] }} mr-1"></i>{{ $tc['label'] }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">S/ {{ number_format($compra->total, 2) }}</td>
                                
                                {{-- Estado Compra --}}
                                <td class="px-6 py-4">
                                    @php
                                        $ec = match($compra->estado) {
                                            'completado' => 'bg-green-100 text-green-800',
                                            'pendiente' => 'bg-yellow-100 text-yellow-800',
                                            'anulado' => 'bg-red-100 text-red-800',
                                            'registrado' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $ec }}">
                                        {{ ucfirst($compra->estado) }}
                                    </span>
                                </td>
                                
                                {{-- Estado Pago --}}
                                <td class="px-6 py-4">
                                    @if($cuenta)
                                        @if($cuenta->estado == 'pagado')
                                            <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>Pagado
                                            </span>
                                        @elseif($cuenta->estado == 'pendiente')
                                            <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>Pendiente
                                            </span>
                                        @elseif($cuenta->estado == 'parcial')
                                            <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                                <i class="fas fa-adjust mr-1"></i>Parcial
                                            </span>
                                        @elseif($cuenta->estado == 'vencido')
                                            <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-circle mr-1"></i>Vencido
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Sin cuenta
                                        </span>
                                    @endif
                                </td>
                                
                                {{-- Saldo --}}
                                <td class="px-6 py-4 text-sm">
                                    @if($cuenta && $cuenta->saldo_pendiente > 0)
                                        <span class="font-semibold text-red-600">
                                            S/ {{ number_format($cuenta->saldo_pendiente, 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                
                                {{-- Vencimiento --}}
                                <td class="px-6 py-4 text-sm">
                                    @if($cuenta && $cuenta->fecha_vencimiento)
                                        <span class="{{ $cuenta->esta_vencida ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                            {{ $cuenta->fecha_vencimiento->format('d/m/Y') }}
                                            @if($cuenta->esta_vencida)
                                                <i class="fas fa-exclamation-triangle text-red-500 ml-1" title="Vencida"></i>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                
                                {{-- Acciones --}}
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('compras.show', $compra) }}" 
                                           class="text-blue-600 hover:text-blue-800" 
                                           title="Ver detalle de compra">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($cuenta)
                                            <a href="{{ route('cuentas-por-pagar.show', $cuenta) }}" 
                                               class="text-green-600 hover:text-green-800 ml-2" 
                                               title="Ver cuenta por pagar">
                                                <i class="fas fa-credit-card"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-shopping-cart text-4xl mb-3 text-gray-300 block"></i>
                                    <p>No hay compras registradas</p>
                                    <a href="{{ route('compras.create') }}" class="text-blue-600 hover:underline mt-2 inline-block text-sm">Registrar primera compra</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>