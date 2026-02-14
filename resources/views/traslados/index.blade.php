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

    <div class="ml-64 p-8 ">
        <x-header 
            title="Gestión de Traslados" 
            subtitle="Administra los traslados entre almacenes y su estado actual"
        />
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Traslados entre Almacenes</h2>
            <div class="flex gap-3">
                <a href="{{ route('traslados.pendientes') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-clock mr-2"></i>Pendientes
                </a>
                <a href="{{ route('traslados.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Nuevo Traslado
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Guía</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Origen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destino</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($traslados as $traslado)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono font-semibold text-blue-600">{{ $traslado->numero_guia ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $traslado->producto->nombre }}</td>
                            <td class="px-6 py-4 text-sm">{{ $traslado->almacen->nombre }}</td>
                            <td class="px-6 py-4 text-sm">{{ $traslado->almacenDestino->nombre ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-semibold">{{ $traslado->cantidad }}</td>
                            <td class="px-6 py-4 text-sm">{{ $traslado->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                @php $colores = ['pendiente' => 'bg-yellow-100 text-yellow-800', 'confirmado' => 'bg-green-100 text-green-800', 'rechazado' => 'bg-red-100 text-red-800']; @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $colores[$traslado->estado] ?? 'bg-gray-100' }}">
                                    {{ ucfirst($traslado->estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('traslados.show', $traslado) }}" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-truck-loading text-4xl mb-3 text-gray-300"></i>
                                <p>No hay traslados registrados</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
