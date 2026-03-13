<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotizaciones - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">

        {{-- Flash --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2 shadow-sm">
                <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2 shadow-sm">
                <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-file-contract text-purple-600"></i> Cotizaciones
                </h1>
                <p class="text-sm text-gray-400 mt-0.5">Presupuestos pendientes de conversión a venta</p>
            </div>
            <a href="{{ route('ventas.create') }}"
               class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                <i class="fas fa-plus"></i> Nueva Cotización
            </a>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-7">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas fa-file-contract text-purple-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Total cotizaciones</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas fa-calendar-day text-amber-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Hoy</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['hoy']) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas fa-coins text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Monto total</p>
                    <p class="text-2xl font-bold text-gray-900">S/ {{ number_format($stats['monto'], 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            @if($cotizaciones->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-file-contract text-purple-300 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-1">No hay cotizaciones</h3>
                    <p class="text-sm text-gray-400 mb-5">Las cotizaciones que crees desde el POS aparecerán aquí</p>
                    <a href="{{ route('ventas.create') }}"
                       class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        <i class="fas fa-plus"></i> Crear primera cotización
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Código</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Almacén</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Vendedor</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($cotizaciones as $cot)
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-mono font-bold text-purple-600 text-sm">{{ $cot->codigo }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $cot->fecha->format('d/m/Y') }}
                                    <span class="block text-xs text-gray-400">{{ $cot->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $cot->cliente?->nombre ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $cot->almacen->nombre }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $cot->vendedor->name }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold text-gray-900 text-sm">S/ {{ number_format($cot->total, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 border border-purple-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                                        <i class="fas fa-file-contract text-[10px]"></i> Cotización
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('ventas.show', $cot) }}"
                                       class="inline-flex items-center gap-1 text-sm text-purple-600 hover:text-purple-800 font-medium transition-colors">
                                        <i class="fas fa-eye text-xs"></i> Ver detalle
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($cotizaciones->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $cotizaciones->links() }}
                </div>
                @endif
            @endif
        </div>

        {{-- Info box --}}
        <div class="mt-5 bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 flex items-start gap-3 text-sm text-blue-800">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 shrink-0"></i>
            <span>Las cotizaciones <strong>no descuentan stock</strong>. Para formalizar una cotización como venta real, entra al detalle y usa el botón <strong>"Convertir a Venta"</strong>.</span>
        </div>

    </div>
</body>
</html>
