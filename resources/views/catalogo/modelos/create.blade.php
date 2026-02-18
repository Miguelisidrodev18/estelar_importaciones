{{-- resources/views/catalogo/modelos/create.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Modelo - Catálogo</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Nuevo Modelo" 
            subtitle="Registrar un nuevo modelo de producto"
        />

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="{{ route('catalogo.modelos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="space-y-6">
                        {{-- Marca (del catálogo de marcas) --}}
                        <div>
                            <label for="marca_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Marca <span class="text-red-500">*</span>
                            </label>
                            <select name="marca_id" 
                                    id="marca_id" 
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('marca_id') border-red-500 @enderror">
                                <option value="">Seleccione una marca...</option>
                                @foreach($marcas as $marca)
                                    <option value="{{ $marca->id }}" {{ old('marca_id') == $marca->id ? 'selected' : '' }}>
                                        {{ $marca->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('marca_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Categoría (del módulo inventario/categorias) --}}
                        <div>
                            <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Categoría
                            </label>
                            <select name="categoria_id" 
                                    id="categoria_id" 
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('categoria_id') border-red-500 @enderror">
                                <option value="">Seleccione una categoría (opcional)...</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Categoría del inventario (opcional)</p>
                            @error('categoria_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nombre del Modelo --}}
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre del Modelo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   id="nombre" 
                                   value="{{ old('nombre') }}" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                                   placeholder="Ej: iPhone 14 Pro Max, Galaxy S23 Ultra">
                            @error('nombre')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Código de Modelo --}}
                        <div>
                            <label for="codigo_modelo" class="block text-sm font-medium text-gray-700 mb-1">
                                Código de Modelo
                            </label>
                            <input type="text" 
                                   name="codigo_modelo" 
                                   id="codigo_modelo" 
                                   value="{{ old('codigo_modelo') }}" 
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Ej: A2896, SM-S918B">
                            <p class="mt-1 text-xs text-gray-500">Código de fábrica o referencia del modelo</p>
                            @error('codigo_modelo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Especificaciones Técnicas --}}
                        <div>
                            <label for="especificaciones_tecnicas" class="block text-sm font-medium text-gray-700 mb-1">
                                Especificaciones Técnicas
                            </label>
                            <textarea name="especificaciones_tecnicas" 
                                      id="especificaciones_tecnicas" 
                                      rows="4"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Ej: Pantalla 6.7', 256GB, Cámara 48MP...">{{ old('especificaciones_tecnicas') }}</textarea>
                            @error('especificaciones_tecnicas')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Imagen de Referencia --}}
                         <div class="mb-6">
                        <label for="imagen" class="block text-sm font-medium text-gray-700 mb-2">
                            Imagen de Referencia
                        </label>
                        <div class="flex items-center space-x-4">
                            <div id="imagePreviewContainer" class="hidden">
                                <img id="imagePreview" src="" alt="Vista previa" class="h-32 w-32 object-cover rounded-lg border-2 border-gray-300">
                            </div>
                            <div class="flex-1">
                                <input type="file" 
                                        name="imagen" 
                                        id="imagen" 
                                        accept="image/jpeg,image/jpg,image/png,image/webp"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                        onchange="previewImage(event)">
                                <p class="mt-1 text-xs text-gray-500">Formatos: JPG, JPEG, PNG, WEBP. Máximo 2MB</p>
                            </div>
                        </div>
                        @error('imagen')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                        {{-- Estado --}}
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                                Estado
                            </label>
                            <select name="estado" 
                                    id="estado" 
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="activo" {{ old('estado', 'activo') == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('estado')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('catalogo.modelos.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                            <i class="fas fa-save mr-2"></i>Guardar Modelo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script para carga dinámica de categorías si es necesario --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Si necesitas carga dinámica de categorías por marca, aquí va el código
        });
    </script>
</body>
</html>