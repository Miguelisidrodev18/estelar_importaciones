<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursales</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8">
    <x-header title="Sucursales" subtitle="Gestiona las sucursales / puntos de venta de la empresa" />

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded flex items-center">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <span class="text-sm text-gray-500">{{ $sucursales->count() }} sucursal(es) registrada(s)</span>
        </div>
        <a href="{{ route('admin.sucursales.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
            <i class="fas fa-plus"></i> Nueva Sucursal
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sucursal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dirección</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Almacén</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobantes y Series</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($sucursales as $sucursal)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                {{ $sucursal->codigo }}
                            </span>
                            @if($sucursal->es_principal)
                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-star mr-1 text-xs"></i>Principal
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $sucursal->nombre }}</p>
                            @if($sucursal->telefono)
                                <p class="text-xs text-gray-400"><i class="fas fa-phone mr-1"></i>{{ $sucursal->telefono }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">
                            {{ $sucursal->direccion ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            @if($sucursal->almacen)
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-warehouse text-gray-400 text-xs"></i>
                                    {{ $sucursal->almacen->nombre }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($sucursal->series as $serie)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-gray-100 text-gray-700 border border-gray-200"
                                        title="{{ $serie->tipo_nombre }}">
                                        {{ $serie->serie }}
                                    </span>
                                @endforeach
                                @if($sucursal->series->isEmpty())
                                    <span class="text-xs text-gray-400">Sin series</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($sucursal->estado === 'activo')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.sucursales.edit', $sucursal) }}"
                                    class="bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="{{ route('admin.sucursales.comprobantes', $sucursal) }}"
                                    class="bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                                    <i class="fas fa-file-invoice"></i> Comprobantes
                                </a>
                                @if(!$sucursal->es_principal)
                                    <form action="{{ route('admin.sucursales.destroy', $sucursal) }}" method="POST"
                                        onsubmit="return confirm('¿Eliminar la sucursal {{ $sucursal->nombre }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-50 hover:bg-red-100 text-red-700 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <i class="fas fa-store text-4xl mb-3 block"></i>
                            No hay sucursales registradas aún.
                            <a href="{{ route('admin.sucursales.create') }}" class="text-blue-600 hover:underline ml-1">Crear la primera</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
