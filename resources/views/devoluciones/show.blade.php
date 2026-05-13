<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Devolución - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Detalle de Devolución" subtitle="Información del movimiento de devolución" />

        <div class="flex items-center mb-6 gap-2">
            <a href="{{ route('devoluciones.index') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
            <h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-file-alt text-red-500"></i> Información General
            </h2>
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500 font-medium">N° Guía</dt>
                    <dd class="font-mono text-gray-800 mt-0.5">{{ $devolucion->numero_guia }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 font-medium">Almacén Destino</dt>
                    <dd class="text-gray-800 mt-0.5">{{ $devolucion->almacen?->nombre }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 font-medium">Registrado por</dt>
                    <dd class="text-gray-800 mt-0.5">{{ $devolucion->usuario?->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 font-medium">Fecha</dt>
                    <dd class="text-gray-800 mt-0.5">{{ $devolucion->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                @if($devolucion->observaciones)
                    <div class="col-span-2 md:col-span-4">
                        <dt class="text-gray-500 font-medium">Observaciones</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $devolucion->observaciones }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
            <h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-boxes text-orange-500"></i> Productos Devueltos
            </h2>
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Producto</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Cantidad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Doc. Referencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($todosMovimientos as $mov)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $mov->producto?->nombre }}</td>
                            <td class="px-4 py-3 text-center">{{ $mov->cantidad }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $mov->documento_referencia }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($guia)
        <div class="bg-white rounded-2xl shadow-md p-6">
            <h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-file-invoice text-emerald-500"></i> Guía de Remisión
            </h2>
            <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500 font-medium">Modalidad</dt>
                    <dd class="text-gray-800 mt-0.5">{{ ucfirst($guia->modalidad) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 font-medium">Fecha Traslado</dt>
                    <dd class="text-gray-800 mt-0.5">{{ $guia->fecha_traslado ? \Carbon\Carbon::parse($guia->fecha_traslado)->format('d/m/Y') : '—' }}</dd>
                </div>
                @if($guia->conductor_nombre)
                    <div>
                        <dt class="text-gray-500 font-medium">Conductor</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $guia->conductor_nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">DNI Conductor</dt>
                        <dd class="font-mono text-gray-800 mt-0.5">{{ $guia->conductor_dni }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Placa</dt>
                        <dd class="font-mono text-gray-800 mt-0.5">{{ $guia->placa_vehiculo }}</dd>
                    </div>
                @endif
                @if($guia->direccion_partida)
                    <div>
                        <dt class="text-gray-500 font-medium">Dirección Partida</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $guia->direccion_partida }}</dd>
                    </div>
                @endif
                @if($guia->direccion_llegada)
                    <div>
                        <dt class="text-gray-500 font-medium">Dirección Llegada</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $guia->direccion_llegada }}</dd>
                    </div>
                @endif
            </dl>
        </div>
        @endif
    </div>
</body>
</html>
