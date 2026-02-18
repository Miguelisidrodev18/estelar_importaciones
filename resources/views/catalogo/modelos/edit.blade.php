{{-- resources/views/catalogo/modelos/edit.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Modelo - Catálogo</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Editar Modelo" 
            subtitle="Modificar información del modelo"
        />

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="{{ route('catalogo.modelos.update', $modelo) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-6">
                        {{-- Marca --}}
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
                                    <option value="{{ $marca->id }}" {{ old('marca_id', $modelo->marca_id) == $marca->id ? 'selected' : '' }}>
                                        {{ $marca->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('marca_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Categoría (opcional) --}}
                        <div>
                            <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Categoría
                            </label>
                            <select name="categoria_id" 
                                    id="categoria_id" 
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('categoria_id') border-red-500 @enderror">
                                <option value="">Sin categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ old('categoria_id', $modelo->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Asociar a una categoría (opcional)</p>
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
                                   value="{{ old('nombre', $modelo->nombre) }}" 
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
                                   value="{{ old('codigo_modelo', $modelo->codigo_modelo) }}" 
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
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('especificaciones_tecnicas') border-red-500 @enderror"
                                      placeholder="Ej: Pantalla 6.7', 256GB, Cámara 48MP...">{{ old('especificaciones_tecnicas', $modelo->especificaciones_tecnicas) }}</textarea>
                            @error('especificaciones_tecnicas')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Imagen Actual --}}
                        @if($modelo->imagen_referencia)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Imagen Actual
                            </label>
                            <div class="flex items-center space-x-4">
                                <img src="{{ Storage::url($modelo->imagen_referencia) }}" 
                                     alt="{{ $modelo->nombre }}" 
                                     class="h-24 w-24 object-cover rounded-lg border-2 border-gray-300">
                                <span class="text-sm text-gray-500">{{ basename($modelo->imagen_referencia) }}</span>
                            </div>
                        </div>
                        @endif

                        {{-- Nueva Imagen --}}
                        <div>
                            <label for="imagen_referencia" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $modelo->imagen_referencia ? 'Cambiar Imagen' : 'Imagen de Referencia' }}
                            </label>
                            <div class="flex items-center space-x-4">
                                <div id="imagePreviewContainer" class="hidden">
                                    <img id="imagePreview" src="" alt="Vista previa" class="h-24 w-24 object-cover rounded-lg border-2 border-gray-300">
                                </div>
                                <div class="flex-1">
                                    <input type="file" 
                                           name="imagen_referencia" 
                                           id="imagen_referencia" 
                                           accept="image/jpeg,image/jpg,image/png,image/webp"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                           onchange="previewImage(event)">
                                    <p class="mt-1 text-xs text-gray-500">Formatos: JPG, PNG, WEBP. Máx 2MB. Dejar vacío para mantener la imagen actual.</p>
                                </div>
                            </div>
                            @error('imagen_referencia')
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
                                <option value="activo" {{ old('estado', $modelo->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ old('estado', $modelo->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('estado')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('catalogo.modelos.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition-colors">
                            <i class="fas fa-save mr-2"></i>Actualizar Modelo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Preview de imagen
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    const container = document.getElementById('imagePreviewContainer');
                    preview.src = e.target.result;
                    container.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        // Validación del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const marcaId = document.getElementById('marca_id').value;
                const nombre = document.getElementById('nombre').value.trim();

                if (!marcaId) {
                    e.preventDefault();
                    alert('Debes seleccionar una marca');
                    return false;
                }

                if (!nombre) {
                    e.preventDefault();
                    alert('El nombre del modelo es obligatorio');
                    return false;
                }
            });
        });
    </script>
</body>
</html>