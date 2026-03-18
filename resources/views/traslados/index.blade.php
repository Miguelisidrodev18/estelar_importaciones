<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traslados - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Gestión de Traslados"
            subtitle="Historial de traslados entre almacenes"
        />

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Nav --}}
        <div class="flex flex-wrap gap-3 mb-6">
            <span class="text-sm font-semibold text-blue-700 flex items-center gap-1">
                <i class="fas fa-exchange-alt"></i> Historial
            </span>
            <span class="text-gray-300">|</span>
            <a href="{{ route('traslados.pendientes') }}" class="text-sm text-gray-600 hover:text-blue-700 flex items-center gap-1.5">
                <i class="fas fa-clock"></i> Pendientes
            </a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('traslados.stock') }}" class="text-sm text-gray-600 hover:text-blue-700 flex items-center gap-1">
                <i class="fas fa-boxes"></i> Stock por Almacén
            </a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('traslados.create') }}" class="text-sm text-gray-600 hover:text-blue-700 flex items-center gap-1">
                <i class="fas fa-plus-circle"></i> Nuevo Traslado
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Guía</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Productos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Origen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destino</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creado por</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $colores = ['pendiente' => 'bg-yellow-100 text-yellow-800', 'confirmado' => 'bg-green-100 text-green-800']; @endphp

                    @forelse($traslados as $guia => $movimientos)
                    @php
                        $primero   = $movimientos->first();
                        $esGuia    = !str_starts_with($guia, 'id:');
                        $todosPendientes = $movimientos->every(fn($m) => $m->estado === 'pendiente');
                        $todosConfirmados = $movimientos->every(fn($m) => $m->estado === 'confirmado');
                        $estado = $todosConfirmados ? 'confirmado' : ($todosPendientes ? 'pendiente' : 'mixto');
                    @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono font-semibold text-blue-600">
                                {{ $esGuia ? $guia : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 text-xs font-semibold">
                                    <i class="fas fa-boxes text-[10px]"></i>
                                    {{ $movimientos->count() }} producto(s)
                                </span>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($movimientos as $mov)
                                        <span class="text-[11px] text-gray-500 font-medium">{{ $mov->producto->nombre }}@if(!$loop->last),@endif</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <i class="fas fa-warehouse text-orange-400 mr-1 text-xs"></i>
                                {{ $primero->almacen->nombre }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <i class="fas fa-store text-green-400 mr-1 text-xs"></i>
                                {{ $primero->almacenDestino->nombre ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $primero->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $primero->usuario->name }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $colores[$estado] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('traslados.show', $primero) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-truck-loading text-4xl mb-3 text-gray-300 block"></i>
                                <p>No hay traslados registrados</p>
                                <a href="{{ route('traslados.create') }}"
                                   class="mt-4 inline-flex items-center gap-2 px-5 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-colors">
                                    <i class="fas fa-plus-circle"></i> Crear primer traslado
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
