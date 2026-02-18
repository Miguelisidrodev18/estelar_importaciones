{{-- resources/views/catalogo/unidades/create.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Unidad de Medida - Catálogo</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Nueva Unidad de Medida" 
            subtitle="Registrar una nueva unidad de medida"
        />

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="{{ route('catalogo.unidades.store') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-6">
                        {{-- Nombre --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   value="{{ old('nombre') }}" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Ej: Kilogramo, Unidad, Litro">
                            @error('nombre')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Abreviatura --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Abreviatura <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="abreviatura" 
                                   value="{{ old('abreviatura') }}" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm font-mono"
                                   placeholder="Ej: KG, UND, LT">
                            @error('abreviatura')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tipo de Medida --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tipo de Medida <span class="text-red-500">*</span>
                            </label>
                            <select name="categoria" 
                                    required 
                                    class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">Seleccione...</option>
                                <option value="unidad" {{ old('categoria') == 'unidad' ? 'selected' : '' }}>Unidad</option>
                                <option value="peso" {{ old('categoria') == 'peso' ? 'selected' : '' }}>Peso</option>
                                <option value="volumen" {{ old('categoria') == 'volumen' ? 'selected' : '' }}>Volumen</option>
                                <option value="longitud" {{ old('categoria') == 'longitud' ? 'selected' : '' }}>Longitud</option>
                                <option value="otros" {{ old('categoria') == 'otros' ? 'selected' : '' }}>Otros</option>
                            </select>
                            @error('categoria')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Categoría de Inventario --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Categoría de Producto
                            </label>
                            <select name="categoria_inventario_id" 
                                    class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">Sin categoría específica</option>
                                @if(isset($categorias) && $categorias->count() > 0)
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" {{ old('categoria_inventario_id') == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Asociar a una categoría (opcional)</p>
                        </div>

                        {{-- Descripción --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea name="descripcion" 
                                      rows="3"
                                      class="w-full rounded-lg border-gray-300 shadow-sm">{{ old('descripcion') }}</textarea>
                        </div>

                        {{-- Permite Decimales --}}
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="permite_decimales" 
                                       value="1" 
                                       {{ old('permite_decimales') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-900 shadow-sm">
                                <span class="ml-2 text-sm text-gray-700">Permite valores decimales</span>
                            </label>
                        </div>

                        {{-- Estado --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Estado
                            </label>
                            <select name="estado" class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="activo" {{ old('estado', 'activo') == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('catalogo.unidades.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                            <i class="fas fa-save mr-2"></i>Guardar Unidad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>