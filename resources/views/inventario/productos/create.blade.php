<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header title="Nuevo Producto" subtitle="Registra un nuevo producto en el inventario" />

        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-900 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-box mr-2"></i>
                        Información del Producto
                    </h2>
                </div>

                <form action="{{ route('inventario.productos.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf

                    <!-- SECCIÓN 1: TIPO DE PRODUCTO -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-mobile-alt mr-2 text-blue-900"></i>
                            Tipo de Producto
                        </h3>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- CELULAR -->
                            <label class="cursor-pointer">
                                <input type="radio" 
                                        name="tipo_producto" 
                                        value="celular" 
                                        id="tipo_celular"
                                        class="peer hidden" 
                                        {{ old('tipo_producto') == 'celular' ? 'checked' : '' }}
                                        required>
                                <div class="border-2 border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                                    <i class="fas fa-mobile-alt text-5xl text-blue-600 mb-3"></i>
                                    <p class="text-lg font-semibold text-gray-900">Celular</p>
                                    <p class="text-sm text-gray-500 mt-2">Requiere IMEI único</p>
                                </div>
                            </label>

                            <!-- ACCESORIO -->
                            <label class="cursor-pointer">
                                <input type="radio" 
                                        name="tipo_producto" 
                                        value="accesorio" 
                                        id="tipo_accesorio"
                                        class="peer hidden" 
                                        {{ old('tipo_producto', 'accesorio') == 'accesorio' ? 'checked' : '' }}>
                                <div class="border-2 border-gray-300 rounded-lg p-6 text-center hover:border-green-500 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all">
                                    <i class="fas fa-headphones text-5xl text-green-600 mb-3"></i>
                                    <p class="text-lg font-semibold text-gray-900">Accesorio</p>
                                    <p class="text-sm text-gray-500 mt-2">Stock numérico</p>
                                </div>
                            </label>
                        </div>

                        @error('tipo_producto')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SECCIÓN 2: INFORMACIÓN BÁSICA -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-info-circle mr-2 text-blue-900"></i>
                            Información Básica
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div class="md:col-span-2">
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre del Producto <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                                @error('nombre')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Categoría (del catálogo) -->
                            <div>
                                <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoría <span class="text-red-500">*</span>
                                </label>
                                <select name="categoria_id" id="categoria_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="">Seleccione una categoría</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Marca (del catálogo) -->
                            <div>
                                <label for="marca_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Marca
                                </label>
                                <select name="marca_id" id="marca_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleccione una marca</option>
                                    @foreach($marcas as $marca)
                                        <option value="{{ $marca->id }}" {{ old('marca_id') == $marca->id ? 'selected' : '' }}>
                                            {{ $marca->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Modelo (del catálogo) -->
                            <div>
                                <label for="modelo_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Modelo
                                </label>
                                <select name="modelo_id" id="modelo_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleccione un modelo</option>
                                    @foreach($modelos as $modelo)
                                        <option value="{{ $modelo->id }}" {{ old('modelo_id') == $modelo->id ? 'selected' : '' }}>
                                            {{ $modelo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Color (del catálogo) -->
                            <div>
                                <label for="color_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Color
                                </label>
                                <select name="color_id" id="color_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleccione un color</option>
                                    @foreach($colores as $color)
                                        <option value="{{ $color->id }}" {{ old('color_id') == $color->id ? 'selected' : '' }}>
                                            {{ $color->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Unidad de Medida (del catálogo) -->
                            <div>
                                <label for="unidad_medida_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Unidad de Medida <span class="text-red-500">*</span>
                                </label>
                                <select name="unidad_medida_id" id="unidad_medida_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="">Seleccione una unidad</option>
                                    @foreach($unidades as $unidad)
                                        <option value="{{ $unidad->id }}" {{ old('unidad_medida_id') == $unidad->id ? 'selected' : '' }}>
                                            {{ $unidad->nombre }} ({{ $unidad->abreviatura }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Código de Barras -->
                            <div>
                                <label for="codigo_barras" class="block text-sm font-medium text-gray-700 mb-2">Código de Barras</label>
                                <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Código único del producto">
                            </div>

                            <!-- Descripción -->
                            <div class="md:col-span-2">
                                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                <textarea name="descripcion" id="descripcion" rows="2"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                          placeholder="Descripción detallada del producto">{{ old('descripcion') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: CONTROL DE STOCK -->
                    <div id="stockSection" class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-boxes mr-2 text-blue-900"></i>
                            Control de Stock
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Mínimo <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="stock_minimo" id="stock_minimo" value="{{ old('stock_minimo', 10) }}" min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                                <p class="text-xs text-gray-500 mt-1">Cantidad mínima antes de alerta</p>
                            </div>

                            <div>
                                <label for="stock_maximo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Máximo <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="stock_maximo" id="stock_maximo" value="{{ old('stock_maximo', 1000) }}" min="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                                <p class="text-xs text-gray-500 mt-1">Capacidad máxima de almacenamiento</p>
                            </div>

                            <div>
                                <label for="ubicacion" class="block text-sm font-medium text-gray-700 mb-2">Ubicación Física</label>
                                <input type="text" name="ubicacion" id="ubicacion" value="{{ old('ubicacion') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ej: Estante A-5, Pasillo 3">
                                <p class="text-xs text-gray-500 mt-1">Ubicación en el almacén</p>
                            </div>

                            <!-- Stock Inicial (SOLO ACCESORIOS) -->
                            <div id="stockInicialDiv">
                                <label for="stock_inicial" class="block text-sm font-medium text-gray-700 mb-2">Stock Inicial (Opcional)</label>
                                <input type="number" name="stock_inicial" id="stock_inicial" value="{{ old('stock_inicial') }}" min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Cantidad inicial">
                                <p class="text-xs text-gray-500 mt-1">Stock con el que se registra el producto</p>
                            </div>

                            <!-- Almacén (si hay stock inicial) -->
                            <div id="almacenInicialDiv" class="md:col-span-2">
                                <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-2">Almacén para Stock Inicial</label>
                                <select name="almacen_id" id="almacen_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleccione un almacén</option>
                                    @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Obligatorio si se ingresa stock inicial</p>
                            </div>
                        </div>

                        <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                            <p class="text-sm text-yellow-700">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Nota:</strong> Para celulares, el stock se controlará mediante registros IMEI individuales en el módulo de movimientos. Los precios se gestionarán en el módulo de ventas.
                            </p>
                        </div>
                    </div>

                    <!-- SECCIÓN 4: IMAGEN -->
                    <div class="mb-6">
                        <label for="imagen" class="block text-sm font-medium text-gray-700 mb-2">
                            Imagen del Producto
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

                    <!-- Estado -->
                    <div class="mb-6">
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado <span class="text-red-500">*</span>
                        </label>
                        <select name="estado" id="estado" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="activo" selected>Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="descontinuado">Descontinuado</option>
                        </select>
                        @error('estado')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('inventario.productos.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                            <i class="fas fa-save mr-2"></i>Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    // Función para alternar campos según tipo de producto
    function toggleFieldsByType() {
        const tipoSeleccionado = document.querySelector('input[name="tipo_producto"]:checked');
        
        if (!tipoSeleccionado) return;
        
        const tipo = tipoSeleccionado.value;
        const stockInicialDiv = document.getElementById('stockInicialDiv');
        const almacenInicialDiv = document.getElementById('almacenInicialDiv');
        
        if (tipo === 'celular') {
            if (stockInicialDiv) stockInicialDiv.style.display = 'none';
            if (almacenInicialDiv) almacenInicialDiv.style.display = 'none';
        } else {
            if (stockInicialDiv) stockInicialDiv.style.display = 'block';
            if (almacenInicialDiv) almacenInicialDiv.style.display = 'block';
        }
    }

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

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar toggle
        toggleFieldsByType();
        
        // Event listeners para radios
        const radioButtons = document.querySelectorAll('input[name="tipo_producto"]');
        radioButtons.forEach(radio => {
            radio.addEventListener('change', toggleFieldsByType);
        });

        // Carga dinámica de modelos según marca seleccionada
        const marcaSelect = document.getElementById('marca_id');
        const modeloSelect = document.getElementById('modelo_id');

        if (marcaSelect && modeloSelect) {
            // Guardar el HTML original solo la primera vez
            const modelosOriginales = modeloSelect.innerHTML;
            
            marcaSelect.addEventListener('change', function() {
                const marcaId = this.value;
                
                // Limpiar y deshabilitar modelo mientras carga
                modeloSelect.innerHTML = '<option value="">Cargando modelos...</option>';
                modeloSelect.disabled = true;

                if (marcaId) {
                    // Usar la ruta correcta que definimos en web.php
                    fetch(`/catalogo/modelos-por-marca/${marcaId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`Error HTTP: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            modeloSelect.innerHTML = '<option value="">Seleccione un modelo</option>';
                            if (data.length > 0) {
                                data.forEach(modelo => {
                                    modeloSelect.innerHTML += `<option value="${modelo.id}">${modelo.nombre}</option>`;
                                });
                            } else {
                                modeloSelect.innerHTML += '<option value="" disabled>No hay modelos para esta marca</option>';
                            }
                            modeloSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error al cargar modelos:', error);
                            modeloSelect.innerHTML = '<option value="">Error al cargar modelos</option>';
                            modeloSelect.disabled = false;
                        });
                } else {
                    // Si no hay marca seleccionada, restaurar opciones originales (vacío)
                    modeloSelect.innerHTML = '<option value="">Seleccione un modelo</option>';
                    modeloSelect.disabled = false;
                }
            });
        }

        // Interceptar envío del formulario
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const radioChecked = document.querySelector('input[name="tipo_producto"]:checked');
            
            if (!radioChecked) {
                e.preventDefault();
                alert('⚠️ Debes seleccionar un tipo de producto (Celular o Accesorio)');
                return false;
            }
            
            // Eliminar inputs tipo_producto anteriores
            const inputsAntiguos = form.querySelectorAll('input[name="tipo_producto"]:not([type="radio"])');
            inputsAntiguos.forEach(input => input.remove());
            
            // Crear input hidden con el valor correcto
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'tipo_producto';
            hiddenInput.value = radioChecked.value;
            form.appendChild(hiddenInput);
        });
    });
</script>
</body>
</html>