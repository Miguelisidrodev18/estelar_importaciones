<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobantes — {{ $sucursal->nombre }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.sucursales.edit', $sucursal) }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Comprobantes Emitidos</h1>
            <p class="text-sm text-gray-500">{{ $sucursal->nombre }} ({{ $sucursal->codigo }})</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-file-invoice text-blue-600"></i>
                Documentos SUNAT emitidos en esta sucursal
            </h3>
            <span class="text-sm text-gray-500">{{ $ventas->total() }} comprobante(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado SUNAT</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado Pago</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($ventas as $venta)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono font-medium text-gray-900">
                                <a href="{{ route('ventas.show', $venta) }}" class="text-blue-600 hover:underline">
                                    {{ $venta->numero_documento ?? $venta->codigo }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $venta->serieComprobante?->tipo_nombre ?? $venta->tipo_comprobante ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $venta->fecha->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $venta->cliente?->nombre ?? 'Cliente genérico' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                S/ {{ number_format($venta->total, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                {{-- Estado SUNAT (placeholder) --}}
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i> Pendiente
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($venta->estado_pago === 'pagado')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Pagado</span>
                                @elseif($venta->estado_pago === 'anulado')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Anulado</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                <i class="fas fa-file-invoice text-4xl mb-3 block"></i>
                                No hay comprobantes emitidos en esta sucursal todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ventas->hasPages())
            <div class="px-4 py-4 border-t">{{ $ventas->links() }}</div>
        @endif
    </div>
</div>
</body>
</html>
