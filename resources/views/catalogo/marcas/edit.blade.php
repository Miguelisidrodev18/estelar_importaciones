{{-- resources/views/catalogo/marcas/edit.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Marca - Catálogo</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Editar Marca" 
            subtitle="Modificar información de la marca"
        />

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="{{ route('catalogo.marcas.update', $marca) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Marca *</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $marca->nombre) }}" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('nombre')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sitio Web</label>
                            <input type="url" name="sitio_web" value="{{ old('sitio_web', $marca->sitio_web) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        @if($marca->logo)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Logo Actual</label>
                            <img src="{{ Storage::url($marca->logo) }}" alt="{{ $marca->nombre }}" class="h-20 w-20 object-contain border rounded-lg p-2">
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo Logo (opcional)</label>
                            <input type="file" name="logo" accept="image/*"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea name="descripcion" rows="3"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('descripcion', $marca->descripcion) }}</textarea>
                        </div>

                        <div>
                            @php
                                $selectedCategorias = old('categorias', $marca->categorias->pluck('id')->toArray());
                            @endphp
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categorías</label>
                            <p class="text-xs text-gray-500 mb-2">Selecciona las categorías a las que pertenece esta marca</p>
                            @if($categorias->isEmpty())
                                <p class="text-sm text-gray-400 italic">No hay categorías activas registradas.</p>
                            @else
                                <div class="grid grid-cols-2 gap-2 border border-gray-200 rounded-lg p-3 max-h-48 overflow-y-auto">
                                    @foreach($categorias as $categoria)
                                        <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 rounded p-1">
                                            <input type="checkbox"
                                                   name="categorias[]"
                                                   value="{{ $categoria->id }}"
                                                   {{ in_array($categoria->id, $selectedCategorias) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">{{ $categoria->nombre }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                            @error('categorias')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="estado" class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="activo" {{ $marca->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ $marca->estado == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                        <a href="{{ route('catalogo.marcas.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                            <i class="fas fa-save mr-2"></i>Actualizar Marca
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>