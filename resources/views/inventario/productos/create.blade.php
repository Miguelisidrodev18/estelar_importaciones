<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-300 rounded-lg p-4">
                            <p class="text-sm font-semibold text-red-700 mb-2">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Por favor corrige los siguientes errores:
                            </p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="text-sm text-red-600">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- SECCIÓN 1: TIPO DE INVENTARIO -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-mobile-alt mr-2 text-blue-900"></i>
                            Tipo de Inventario
                        </h3>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- CANTIDAD -->
                            <label class="cursor-pointer">
                                <input type="radio" 
                                        name="tipo_inventario" 
                                        value="cantidad" 
                                        id="tipo_cantidad"
                                        class="peer hidden" 
                                        {{ old('tipo_inventario', 'cantidad') == 'cantidad' ? 'checked' : '' }}
                                        required>
                                <div class="border-2 border-gray-300 rounded-lg p-6 text-center hover:border-green-500 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all">
                                    <i class="fas fa-boxes text-5xl text-green-600 mb-3"></i>
                                    <p class="text-lg font-semibold text-gray-900">Stock por Cantidad</p>
                                    <p class="text-sm text-gray-500 mt-2">Accesorios, repuestos, consumibles</p>
                                </div>
                            </label>

                            <!-- SERIE/IMEI -->
                            <label class="cursor-pointer">
                                <input type="radio" 
                                        name="tipo_inventario" 
                                        value="serie" 
                                        id="tipo_serie"
                                        class="peer hidden" 
                                        {{ old('tipo_inventario') == 'serie' ? 'checked' : '' }}>
                                <div class="border-2 border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                                    <i class="fas fa-mobile-alt text-5xl text-blue-600 mb-3"></i>
                                    <p class="text-lg font-semibold text-gray-900">Stock por Serie/IMEI</p>
                                    <p class="text-sm text-gray-500 mt-2">Celulares, equipos con número único</p>
                                </div>
                            </label>
                        </div>

                        @error('tipo_inventario')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SECCIÓN 1.1: GARANTÍA (SOLO PARA TIPO SERIE) -->
                    <div id="garantiaSection" class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-200" style="display: none;">
                        <h4 class="font-semibold text-blue-900 mb-3">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Configuración de Garantía
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="dias_garantia" class="block text-sm font-medium text-gray-700 mb-2">
                                    Días de Garantía
                                </label>
                                <input type="number" name="dias_garantia" id="dias_garantia" 
                                       value="{{ old('dias_garantia', 365) }}" min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="tipo_garantia" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Garantía
                                </label>
                                <select name="tipo_garantia" id="tipo_garantia" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="proveedor">Proveedor</option>
                                    <option value="tienda">Tienda</option>
                                    <option value="fabricante">Fabricante</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: INFORMACIÓN BÁSICA (CON SELECTORES EN CADENA) -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                            <i class="fas fa-info-circle mr-2 text-blue-900"></i>
                            Información Básica
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                                                    
                           <!-- Nombre del Producto (sugerido automáticamente) -->
                        <div class="md:col-span-2">
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del Producto <span class="text-red-500">*</span>
                            </label>
                            <div class="flex space-x-2">
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="Se generará automáticamente"
                                    required>
                                <button type="button" 
                                        id="btnSugerirNombre"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 whitespace-nowrap">
                                    <i class="fas fa-magic mr-2"></i>Sugerir
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Puedes editar el nombre o hacer clic en "Sugerir" para generarlo automáticamente
                            </p>
                        </div>

                            <!-- 🔴 CATEGORÍA (ahora filtra marcas) -->
                            <div>
                                <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoría <span class="text-red-500">*</span>
                                </label>
                                <select name="categoria_id" id="categoria_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        required>
                                    <option value="">Seleccione una categoría</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" 
                                                data-marcas="{{ $categoria->marcas->pluck('id')->join(',') }}"
                                                {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 🔴 MARCA (se filtra por categoría) -->
                            <div>
                                <label for="marca_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Marca <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <select name="marca_id" id="marca_id"
                                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            required>
                                        <option value="">Primero seleccione una categoría</option>
                                    </select>
                                    <button type="button" onclick="abrirModalMarca()"
                                            title="Crear nueva marca"
                                            class="px-3 py-2 bg-blue-100 text-blue-800 border border-blue-300
                                                   rounded-lg hover:bg-blue-200 transition shrink-0">
                                        <i class="fas fa-plus text-sm"></i>
                                    </button>
                                </div>
                                @error('marca_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- 🔴 MODELO (se filtra por marca) -->
                            <div>
                                <label for="modelo_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Modelo <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <select name="modelo_id" id="modelo_id"
                                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                            required>
                                        <option value="">Primero seleccione una marca</option>
                                    </select>
                                    <button type="button" onclick="abrirModalModelo()"
                                            title="Crear nuevo modelo"
                                            class="px-3 py-2 bg-indigo-100 text-indigo-800 border border-indigo-300
                                                   rounded-lg hover:bg-indigo-200 transition shrink-0">
                                        <i class="fas fa-plus text-sm"></i>
                                    </button>
                                </div>
                                @error('modelo_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Color -->
                            <div>
                                <label for="color_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Color
                                </label>
                                <div class="flex gap-2">
                                    <select name="color_id" id="color_id"
                                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="">Seleccione un color</option>
                                        @foreach($colores as $color)
                                            <option value="{{ $color->id }}" {{ old('color_id') == $color->id ? 'selected' : '' }}>
                                                {{ $color->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="abrirModalColor()"
                                            title="Crear nuevo color"
                                            class="px-3 py-2 bg-pink-100 text-pink-700 border border-pink-300
                                                   rounded-lg hover:bg-pink-200 transition shrink-0">
                                        <i class="fas fa-plus text-sm"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- SECCIÓN DE UNIDADES DE MEDIDA -->
                            <div class="md:col-span-2 bg-gray-50 rounded-xl border border-gray-200 p-5">
                                <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-balance-scale text-blue-900"></i>
                                    Unidades de Medida
                                </h3>

                                <!-- Unidad base -->
                                <div class="mb-5">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Unidad Base <span class="text-red-500">*</span>
                                        <span class="ml-1 text-xs font-normal text-gray-400">— unidad principal de inventario</span>
                                    </label>
                                    <div class="flex gap-2">
                                        <select name="unidad_medida_id" id="unidad_medida_id"
                                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white"
                                                required>
                                            <option value="">Seleccionar unidad base...</option>
                                            @foreach($unidades as $unidad)
                                                <option value="{{ $unidad->id }}" {{ old('unidad_medida_id') == $unidad->id ? 'selected' : '' }}>
                                                    {{ $unidad->nombre }} ({{ $unidad->abreviatura }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button"
                                                onclick="abrirModalUnidad()"
                                                title="Crear nueva unidad de medida"
                                                class="px-3 py-2 bg-green-100 text-green-700 border border-green-300 rounded-lg hover:bg-green-200 transition shrink-0">
                                            <i class="fas fa-plus text-sm"></i>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <i class="fas fa-info-circle mr-1 text-blue-400"></i>
                                        Ej: Unidad, Kilogramo, Litro
                                    </p>
                                </div>

                                <!-- Presentaciones alternativas -->
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Presentaciones Alternativas</span>
                                            <span class="ml-1 text-xs text-gray-400">(opcional)</span>
                                        </div>
                                        <button type="button" onclick="agregarUnidadAlternativa()"
                                                class="text-xs bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg border border-blue-200 hover:bg-blue-100 transition flex items-center gap-1">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </div>

                                    <!-- Cabecera de columnas -->
                                    <div id="unidades-header" class="hidden grid grid-cols-12 gap-2 px-3 mb-1 text-xs font-medium text-gray-500 uppercase">
                                        <div class="col-span-7">Unidad de presentación</div>
                                        <div class="col-span-4">Factor de conversión</div>
                                        <div class="col-span-1"></div>
                                    </div>

                                    <!-- Filas dinámicas -->
                                    <div id="unidades-alternativas-container" class="space-y-2"></div>

                                    <!-- Estado vacío -->
                                    <div id="sin-unidades-msg" class="text-center py-5 bg-white rounded-lg border-2 border-dashed border-gray-200">
                                        <i class="fas fa-layer-group text-2xl text-gray-300 mb-1 block"></i>
                                        <p class="text-xs text-gray-400">Sin presentaciones alternativas</p>
                                        <p class="text-xs text-gray-300">Ej: Pack, Caja, Docena…</p>
                                    </div>

                                    <!-- Template fila -->
                                    <template id="template-unidad-alternativa">
                                        <div class="grid grid-cols-12 gap-2 items-center bg-white px-3 py-2 rounded-lg border border-gray-200 unidad-item">
                                            <div class="col-span-7">
                                                <select name="unidades_alternativas[][unidad_id]"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                                                        required>
                                                    <option value="">Seleccionar presentación...</option>
                                                    @foreach($unidades as $unidad)
                                                        <option value="{{ $unidad->id }}">{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-span-4 flex items-center gap-1">
                                                <span class="text-xs text-gray-400 whitespace-nowrap">×</span>
                                                <input type="number"
                                                       name="unidades_alternativas[][factor]"
                                                       placeholder="Factor"
                                                       step="0.0001"
                                                       min="0.0001"
                                                       title="Cuántas unidades base equivale esta presentación"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                                                       required>
                                                <span class="text-xs text-gray-400 whitespace-nowrap">u.b.</span>
                                            </div>
                                            <div class="col-span-1 flex justify-end">
                                                <button type="button" onclick="eliminarUnidad(this)"
                                                        class="text-red-500 hover:text-red-700 p-1.5 hover:bg-red-50 rounded-lg transition"
                                                        title="Eliminar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Nota sobre precios -->
                                    <p class="mt-3 text-xs text-amber-600 flex items-start gap-1">
                                        <i class="fas fa-tag mt-0.5 shrink-0"></i>
                                        Los precios por presentación se configuran en el módulo de <strong>Gestión de Precios</strong>.
                                    </p>
                                </div>
                            </div>

                            <!-- Código de Barras -->
                            <div class="md:col-span-2">
                                <label for="codigo_barras" class="block text-sm font-medium text-gray-700 mb-2">
                                    Código de Barras
                                </label>
                                <div class="flex space-x-2">
                                    <div class="flex-1">
                                        <input type="text" 
                                               name="codigo_barras" 
                                               id="codigo_barras" 
                                               value="{{ old('codigo_barras') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                               placeholder="Código único del producto">
                                    </div>
                                    <button type="button" 
                                            id="btnGenerarCodigo"
                                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                        <i class="fas fa-sync-alt mr-2"></i>Generar
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Puedes ingresar manualmente o generar automáticamente
                                </p>
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

                    <!-- SECCIÓN 3: CONTROL DE STOCK (sin cambios) -->
                    <div class="mb-8">
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
                            </div>

                            <div>
                                <label for="stock_maximo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Máximo <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="stock_maximo" id="stock_maximo" value="{{ old('stock_maximo', 1000) }}" min="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       required>
                            </div>

                            <div>
                                <label for="ubicacion" class="block text-sm font-medium text-gray-700 mb-2">Ubicación Física</label>
                                <input type="text" name="ubicacion" id="ubicacion" value="{{ old('ubicacion') }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ej: Estante A-5, Pasillo 3">
                            </div>

                            <!-- Stock Inicial (SOLO PARA TIPO CANTIDAD) -->
                            <div id="stockInicialDiv">
                                <label for="stock_inicial" class="block text-sm font-medium text-gray-700 mb-2">Stock Inicial</label>
                                <input type="number" name="stock_inicial" id="stock_inicial" value="{{ old('stock_inicial') }}" min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="Cantidad inicial">
                            </div>

                            <!-- Almacén -->
                            <div id="almacenInicialDiv" class="md:col-span-2">
                                <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-2">Almacén</label>
                                <select name="almacen_id" id="almacen_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleccione un almacén</option>
                                    @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                            <p class="text-sm text-yellow-700">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Nota:</strong> Para productos con stock por serie/IMEI, el stock se controlará mediante registros IMEI individuales.
                            </p>
                        </div>
                    </div>

                    <!-- SECCIÓN 4: IMAGEN Y ESTADO -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="imagen" class="block text-sm font-medium text-gray-700 mb-2">
                                Imagen del Producto
                            </label>
                            <div class="flex items-center space-x-4">
                                <div id="imagePreviewContainer" class="hidden">
                                    <img id="imagePreview" src="" alt="Vista previa" class="h-20 w-20 object-cover rounded-lg border">
                                </div>
                                <input type="file" name="imagen" id="imagen" accept="image/*"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                       onchange="previewImage(event)">
                            </div>
                        </div>

                        <div>
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
                        </div>
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

    @include('inventario.productos.partials.modales-rapidos')

    <script>
        // Preview de imagen
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreviewContainer').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        // Función para alternar campos según tipo de inventario
        function toggleFieldsByType() {
            const tipo = document.querySelector('input[name="tipo_inventario"]:checked')?.value;
            const stockInicialDiv = document.getElementById('stockInicialDiv');
            const almacenInicialDiv = document.getElementById('almacenInicialDiv');
            const garantiaSection = document.getElementById('garantiaSection');
            
            if (tipo === 'serie') {
                stockInicialDiv?.style.setProperty('display', 'none');
                almacenInicialDiv?.style.setProperty('display', 'none');
                garantiaSection?.style.setProperty('display', 'block');
            } else {
                stockInicialDiv?.style.setProperty('display', 'block');
                almacenInicialDiv?.style.setProperty('display', 'block');
                garantiaSection?.style.setProperty('display', 'none');
            }
        }

        // 🔴 NUEVO: Carga de marcas por categoría
        function cargarMarcasPorCategoria(categoriaId, marcaSeleccionada = null) {
            const marcaSelect = document.getElementById('marca_id');
            const modeloSelect = document.getElementById('modelo_id');
            
            marcaSelect.innerHTML = '<option value="">Cargando marcas...</option>';
            marcaSelect.disabled = true;
            modeloSelect.innerHTML = '<option value="">Primero seleccione una marca</option>';
            modeloSelect.disabled = true;

            if (!categoriaId) {
                marcaSelect.innerHTML = '<option value="">Primero seleccione una categoría</option>';
                marcaSelect.disabled = false;
                return;
            }

            fetch(`/catalogo/marcas-por-categoria/${categoriaId}`)
                .then(response => response.json())
                .then(data => {
                    marcaSelect.innerHTML = '<option value="">Seleccione una marca</option>';
                    
                    if (data.length === 0) {
                        marcaSelect.innerHTML += '<option value="" disabled>No hay marcas para esta categoría</option>';
                    } else {
                        data.forEach(marca => {
                            const selected = (marcaSeleccionada && marca.id == marcaSeleccionada) ? 'selected' : '';
                            marcaSelect.innerHTML += `<option value="${marca.id}" ${selected}>${marca.nombre}</option>`;
                        });
                    }
                    
                    marcaSelect.disabled = false;
                    
                    // Si hay marca seleccionada, cargar sus modelos
                    if (marcaSeleccionada) {
                        cargarModelosPorMarca(marcaSeleccionada);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    marcaSelect.innerHTML = '<option value="">Error al cargar marcas</option>';
                    marcaSelect.disabled = false;
                });
        }

        // 🔴 NUEVO: Carga de modelos por marca (actualizado)
        function cargarModelosPorMarca(marcaId, modeloSeleccionado = null) {
            const modeloSelect = document.getElementById('modelo_id');
            
            modeloSelect.innerHTML = '<option value="">Cargando modelos...</option>';
            modeloSelect.disabled = true;

            if (!marcaId) {
                modeloSelect.innerHTML = '<option value="">Primero seleccione una marca</option>';
                modeloSelect.disabled = false;
                return;
            }

            fetch(`/catalogo/modelos-por-marca/${marcaId}`)
                .then(response => response.json())
                .then(data => {
                    modeloSelect.innerHTML = '<option value="">Seleccione un modelo</option>';
                    
                    if (data.length === 0) {
                        modeloSelect.innerHTML += '<option value="" disabled>No hay modelos para esta marca</option>';
                    } else {
                        data.forEach(modelo => {
                            const selected = (modeloSeleccionado && modelo.id == modeloSeleccionado) ? 'selected' : '';
                            modeloSelect.innerHTML += `<option value="${modelo.id}" ${selected}>${modelo.nombre}</option>`;
                        });
                    }
                    
                    modeloSelect.disabled = false;
                    sugerirNombreAuto();
                })
                .catch(error => {
                    console.error('Error:', error);
                    modeloSelect.innerHTML = '<option value="">Error al cargar modelos</option>';
                    modeloSelect.disabled = false;
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar toggle de tipo inventario
            toggleFieldsByType();
            
            // Event listeners para radios
            document.querySelectorAll('input[name="tipo_inventario"]').forEach(radio => {
                radio.addEventListener('change', toggleFieldsByType);
            });

            // 🔴 Evento cambio de categoría
            const categoriaSelect = document.getElementById('categoria_id');
            const marcaSelect = document.getElementById('marca_id');
            const modeloSelect = document.getElementById('modelo_id');

            if (categoriaSelect) {
                categoriaSelect.addEventListener('change', function() {
                    const categoriaId = this.value;
                    cargarMarcasPorCategoria(categoriaId);
                });
            }

            // 🔴 Evento cambio de marca
            if (marcaSelect) {
                marcaSelect.addEventListener('change', function() {
                    const marcaId = this.value;
                    cargarModelosPorMarca(marcaId);
                });
            }

            // Si hay valores antiguos (por validación fallida), restaurar
            const oldCategoria = "{{ old('categoria_id') }}";
            const oldMarca = "{{ old('marca_id') }}";
            const oldModelo = "{{ old('modelo_id') }}";

            if (oldCategoria) {
                cargarMarcasPorCategoria(oldCategoria, oldMarca);
            }

            // Detectar edición manual del nombre
            document.getElementById('nombre')?.addEventListener('input', function () {
                nombreEditadoManual = true;
            });

            // Auto-sugerir al cambiar color
            document.getElementById('color_id')?.addEventListener('change', sugerirNombreAuto);

            // Auto-sugerir al cambiar modelo (el select se rellena dinámicamente,
            // pero el listener sobre el elemento ya existente funciona igual)
            document.getElementById('modelo_id')?.addEventListener('change', sugerirNombreAuto);
        });
        // Bandera: true cuando el usuario editó el nombre manualmente
        let nombreEditadoManual = {{ old('nombre') ? 'true' : 'false' }};

        function sugerirNombre() {
            const marcaOpt  = document.getElementById('marca_id').selectedOptions[0];
            const modeloOpt = document.getElementById('modelo_id').selectedOptions[0];
            const colorOpt  = document.getElementById('color_id').selectedOptions[0];

            const marca  = (marcaOpt  && marcaOpt.value)  ? marcaOpt.text  : '';
            const modelo = (modeloOpt && modeloOpt.value)  ? modeloOpt.text : '';
            const color  = (colorOpt  && colorOpt.value)  ? colorOpt.text  : '';

            let partes = [];
            if (marca)  partes.push(marca);
            if (modelo) partes.push(modelo);
            if (color)  partes.push(color);

            if (partes.length > 0) {
                document.getElementById('nombre').value = partes.join(' ');
                nombreEditadoManual = false;
            } else {
                alert('Selecciona al menos marca o modelo para generar el nombre');
            }
        }

        // Auto-sugerir solo si el usuario no editó manualmente
        function sugerirNombreAuto() {
            if (!nombreEditadoManual) {
                const marcaOpt  = document.getElementById('marca_id').selectedOptions[0];
                const modeloOpt = document.getElementById('modelo_id').selectedOptions[0];
                const colorOpt  = document.getElementById('color_id').selectedOptions[0];

                const marca  = (marcaOpt  && marcaOpt.value)  ? marcaOpt.text  : '';
                const modelo = (modeloOpt && modeloOpt.value)  ? modeloOpt.text : '';
                const color  = (colorOpt  && colorOpt.value)  ? colorOpt.text  : '';

                let partes = [];
                if (marca)  partes.push(marca);
                if (modelo) partes.push(modelo);
                if (color)  partes.push(color);

                if (partes.length > 0) {
                    document.getElementById('nombre').value = partes.join(' ');
                }
            }
        }

        // Botón "Sugerir" siempre fuerza la generación y resetea la bandera
        document.getElementById('btnSugerirNombre')?.addEventListener('click', function () {
            nombreEditadoManual = false;
            sugerirNombre();
        });

        // Botón Generar código de barras
        document.getElementById('btnGenerarCodigo')?.addEventListener('click', function() {
            const btn = this;
            const codigoInput = document.getElementById('codigo_barras');
            const tipo = document.querySelector('input[name="tipo_inventario"]:checked')?.value;
            const tipoBarras = tipo === 'serie' ? 'celular' : 'accesorio';

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generando...';

            fetch('{{ route("inventario.productos.generar-codigo-barras") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tipo: tipoBarras })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    codigoInput.value = data.codigo;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(() => alert('Error al generar código'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Generar';
            });
        });
    </script>
</body>
</html>