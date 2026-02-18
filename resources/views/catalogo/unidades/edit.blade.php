{{-- resources/views/catalogo/unidades/edit.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Unidad de Medida - Catálogo</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Editar Unidad de Medida" 
            subtitle="Modificar unidad de medida"
        />

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="{{ route('catalogo.unidades.update', $unidade) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-6">
                        {{-- Nombre --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   value="{{ old('nombre', $unidade->nombre) }}" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm">
                        </div>

                        {{-- Abreviatura --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Abreviatura <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="abreviatura" 
                                   value="{{ old('abreviatura', $unidade->abreviatura) }}" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm font-mono">
                        </div>

                        {{-- Tipo de Medida --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tipo de Medida <span class="text-red-500">*</span>
                            </label>
                            <select name="categoria" required class="w-full rounded-lg border-gray-300">
                                <option value="unidad" {{ $unidade->categoria == 'unidad' ? 'selected' : '' }}>Unidad</option>
                                <option value="peso" {{ $unidade->categoria == 'peso' ? 'selected' : '' }}>Peso</option>
                                <option value="volumen" {{ $unidade->categoria == 'volumen' ? 'selected' : '' }}>Volumen</option>
                                <option value="longitud" {{ $unidade->categoria == 'longitud' ? 'selected' : '' }}>Longitud</option>
                                <option value="otros" {{ $unidade->categoria == 'otros' ? 'selected' : '' }}>Otros</option>
                            </select>
                        </div>

                        {{-- Categoría de Inventario --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Categoría de Producto
                            </label>
                            <select name="categoria_inventario_id" class="w-full rounded-lg border-gray-300">
                                <option value="">Sin categoría específica</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" 
                                        {{ $unidade->categoria_inventario_id == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Descripción --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea name="descripcion" rows="3" class="w-full rounded-lg border-gray-300">{{ old('descripcion', $unidade->descripcion) }}</textarea>
                        </div>

                        {{-- Permite Decimales --}}
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="permite_decimales" 
                                       value="1" 
                                       {{ $unidade->permite_decimales ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-900">
                                <span class="ml-2 text-sm text-gray-700">Permite valores decimales</span>
                            </label>
                        </div>

                        {{-- Estado --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Estado
                            </label>
                            <select name="estado" class="w-full rounded-lg border-gray-300">
                                <option value="activo" {{ $unidade->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ $unidade->estado == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                        <a href="{{ route('catalogo.unidades.index') }}" class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                            <i class="fas fa-save mr-2"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>