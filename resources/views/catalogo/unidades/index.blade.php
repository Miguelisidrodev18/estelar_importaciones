{{-- resources/views/catalogo/unidades/index.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unidades de Medida - Catálogo</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Unidades de Medida" 
            subtitle="Gestión de unidades para productos"
        />

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Lista de Unidades</h2>
            <a href="{{ route('catalogo.unidades.create') }}" class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>Nueva Unidad
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Abreviatura</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo de Medida</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Decimales</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($unidades as $unidad)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $unidad->nombre }}</div>
                            @if($unidad->descripcion)
                                <div class="text-xs text-gray-500">{{ Str::limit($unidad->descripcion, 30) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-mono font-bold text-sm bg-gray-100 px-2 py-1 rounded">
                                {{ $unidad->abreviatura }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($unidad->categoria == 'unidad') bg-blue-100 text-blue-800
                                @elseif($unidad->categoria == 'peso') bg-green-100 text-green-800
                                @elseif($unidad->categoria == 'volumen') bg-purple-100 text-purple-800
                                @elseif($unidad->categoria == 'longitud') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($unidad->categoria) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $unidad->categoriaInventario ? $unidad->categoriaInventario->nombre : '-' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($unidad->permite_decimales)
                                <span class="text-green-600"><i class="fas fa-check-circle"></i> Sí</span>
                            @else
                                <span class="text-gray-400"><i class="fas fa-times-circle"></i> No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $unidad->estado == 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($unidad->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <div class="flex space-x-3">
                                <a href="{{ route('catalogo.unidades.edit', $unidad) }}" 
                                   class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('catalogo.unidades.destroy', $unidad) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" 
                                            onclick="return confirm('¿Estás seguro de eliminar esta unidad?')"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-ruler text-4xl mb-3 text-gray-300"></i>
                            <p class="text-lg font-medium">No hay unidades de medida registradas</p>
                            <p class="text-sm">Comienza creando una nueva unidad</p>
                            <a href="{{ route('catalogo.unidades.create') }}" class="inline-block mt-4 text-blue-600 hover:text-blue-800">
                                <i class="fas fa-plus mr-1"></i>Crear primera unidad
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $unidades->links() }}
        </div>
    </div>
</body>
</html>