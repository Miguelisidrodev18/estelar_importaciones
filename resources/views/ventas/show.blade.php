<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Venta - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8 ">
        <x-header 
            title="Detalle de Venta" 
            subtitle="Revisa la información completa de esta venta, cliente, pago y productos vendidos"
        />
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
        @endif

        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <a href="{{ route('ventas.index') }}" class="text-blue-600 hover:text-blue-800 mr-4"><i class="fas fa-arrow-left"></i></a>
                <h2 class="text-2xl font-bold text-gray-800">Venta {{ $venta->codigo }}</h2>
            </div>

            @if($venta->estado_pago === 'pendiente' && in_array(auth()->user()->role->nombre, ['Administrador', 'Tienda']))
            <div x-data="{ showModal: false }">
                <button @click="showModal = true" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>Confirmar Pago
                </button>

                <div x-show="showModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-xl p-6 w-96" @click.outside="showModal = false">
                        <h3 class="text-lg font-bold mb-4">Confirmar Pago</h3>
                        <form action="{{ route('ventas.confirmar-pago', $venta) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago *</label>
                                <select name="metodo_pago" required class="w-full rounded-lg border-gray-300 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="yape">Yape</option>
                                    <option value="plin">Plin</option>
                                </select>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">Total a cobrar: <span class="font-bold text-lg text-blue-600">S/ {{ number_format($venta->total, 2) }}</span></p>
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="showModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancelar</button>
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">Confirmar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Venta</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Código:</dt><dd class="font-mono font-semibold">{{ $venta->codigo }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Fecha:</dt><dd>{{ $venta->fecha->format('d/m/Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Vendedor:</dt><dd>{{ $venta->vendedor->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Almacén:</dt><dd>{{ $venta->almacen->nombre }}</dd></div>
                </dl>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Cliente</h3>
                @if($venta->cliente)
                    <dl class="space-y-2">
                        <div class="flex justify-between"><dt class="text-gray-500 text-sm">Nombre:</dt><dd>{{ $venta->cliente->nombre }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500 text-sm">Documento:</dt><dd class="font-mono">{{ $venta->cliente->tipo_documento }} {{ $venta->cliente->numero_documento }}</dd></div>
                    </dl>
                @else
                    <p class="text-gray-400 text-sm">Venta sin cliente registrado</p>
                @endif
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Pago</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Total:</dt><dd class="text-xl font-bold text-blue-600">S/ {{ number_format($venta->total, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Método:</dt><dd>{{ $venta->metodo_pago ? ucfirst($venta->metodo_pago) : 'Pendiente' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Estado:</dt>
                        <dd>
                            @php $colores = ['pendiente' => 'bg-yellow-100 text-yellow-800', 'pagado' => 'bg-green-100 text-green-800', 'cancelado' => 'bg-red-100 text-red-800']; @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $colores[$venta->estado_pago] ?? '' }}">{{ ucfirst($venta->estado_pago) }}</span>
                        </dd>
                    </div>
                    @if($venta->confirmador)
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Confirmado por:</dt><dd>{{ $venta->confirmador->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 text-sm">Fecha conf.:</dt><dd>{{ $venta->fecha_confirmacion?->format('d/m/Y H:i') }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IMEI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cant.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($venta->detalles as $i => $detalle)
                    <tr>
                        <td class="px-6 py-4 text-sm">{{ $i + 1 }}</td>
                        <td class="px-6 py-4 text-sm font-medium">{{ $detalle->producto->nombre }}</td>
                        <td class="px-6 py-4 text-sm font-mono text-purple-600">{{ $detalle->imei?->codigo_imei ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $detalle->cantidad }}</td>
                        <td class="px-6 py-4 text-sm">S/ {{ number_format($detalle->precio_unitario, 2) }}</td>
                        <td class="px-6 py-4 text-sm font-semibold">S/ {{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
