{{-- resources/views/catalogo/colores/create.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Color - Catálogo</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Nuevo Color" 
            subtitle="Registrar un nuevo color en el catálogo"
        />

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="{{ route('catalogo.colores.store') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-6">
                        {{-- Nombre del Color --}}
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre del Color <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   id="nombre" 
                                   value="{{ old('nombre') }}" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror"
                                   placeholder="Ej: Rojo, Azul Marino, Verde Esmeralda">
                            @error('nombre')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Código Hexadecimal --}}
                        <div>
                            <label for="codigo_hex" class="block text-sm font-medium text-gray-700 mb-1">
                                Código Hexadecimal
                            </label>
                            <div class="flex items-center space-x-3">
                                <input type="color" 
                                       id="color_picker" 
                                       value="{{ old('codigo_hex', '#000000') }}"
                                       class="h-10 w-20 rounded border border-gray-300 cursor-pointer">
                                <input type="text" 
                                       name="codigo_hex" 
                                       id="codigo_hex" 
                                       value="{{ old('codigo_hex', '#000000') }}" 
                                       class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono"
                                       placeholder="#000000"
                                       pattern="^#[a-fA-F0-9]{6}$"
                                       title="Formato: #RRGGBB (ej: #FF0000)">
                                <button type="button" 
                                        onclick="document.getElementById('color_picker').value = '{{ old('codigo_hex', '#000000') }}'; document.getElementById('codigo_hex').value = '{{ old('codigo_hex', '#000000') }}';"
                                        class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Selecciona el color o ingresa el código hexadecimal</p>
                            @error('codigo_hex')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Código Interno --}}
                        <div>
                            <label for="codigo_color" class="block text-sm font-medium text-gray-700 mb-1">
                                Código Interno
                            </label>
                            <input type="text" 
                                   name="codigo_color" 
                                   id="codigo_color" 
                                   value="{{ old('codigo_color') }}" 
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Ej: COL001, RED-01">
                            <p class="mt-1 text-xs text-gray-500">Código interno para identificación en el sistema</p>
                            @error('codigo_color')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Descripción --}}
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea name="descripcion" 
                                      id="descripcion" 
                                      rows="3"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Descripción adicional del color...">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
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

                    {{-- Botones de Acción --}}
                    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('catalogo.colores.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition-colors">
                            <i class="fas fa-save mr-2"></i>Guardar Color
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script para sincronizar color picker con input hex --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const colorPicker = document.getElementById('color_picker');
            const hexInput = document.getElementById('codigo_hex');

            // Actualizar input hex cuando se selecciona color en el picker
            colorPicker.addEventListener('input', function() {
                hexInput.value = this.value;
            });

            // Actualizar color picker cuando se ingresa un hex válido
            hexInput.addEventListener('input', function() {
                const hexPattern = /^#[a-fA-F0-9]{6}$/;
                if (hexPattern.test(this.value)) {
                    colorPicker.value = this.value;
                }
            });

            // Validar formato hex en submit
            document.querySelector('form').addEventListener('submit', function(e) {
                const hexValue = hexInput.value;
                if (hexValue && !/^#[a-fA-F0-9]{6}$/.test(hexValue)) {
                    e.preventDefault();
                    alert('El código hexadecimal debe tener el formato #RRGGBB (ej: #FF0000)');
                }
            });
        });
    </script>
</body>
</html>