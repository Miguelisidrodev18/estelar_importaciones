<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar IMEI - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <!-- Header con QR y acciones -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Registrar Nuevo IMEI</h1>
                    <p class="text-sm text-gray-600 mt-1">Ingresa los datos del celular paso a paso</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" 
                            id="btnEscanearQR"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-qrcode"></i>
                        <span class="hidden md:inline">Escanear QR</span>
                    </button>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                        <i class="fas fa-sim-card mr-1"></i>
                        Registro Individual
                    </span>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                    <p class="font-medium">Por favor corrige los siguientes errores:</p>
                </div>
                <ul class="list-disc list-inside text-sm space-y-1 ml-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="max-w-5xl mx-auto">
            <form action="{{ route('inventario.imeis.store') }}" method="POST" id="imeiForm">
                @csrf

                <!-- Tarjeta principal -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Cabecera decorativa con gradiente -->
                    <div class="bg-gradient-to-r from-blue-900 to-blue-700 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-mobile-alt mr-3 text-2xl"></i>
                            Datos del Celular
                        </h2>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Columna izquierda: QR Code (si existe) -->
                            <div class="lg:col-span-1">
                                <div class="bg-gray-50 p-4 rounded-lg border-2 border-dashed border-gray-300 text-center sticky top-4">
                                    <div id="qrContainer" class="mb-3">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=IMEI-{{ uniqid() }}" 
                                             alt="QR Code"
                                             class="mx-auto w-32 h-32">
                                    </div>
                                    <p class="text-xs text-gray-500 mb-2">
                                        <i class="fas fa-qrcode mr-1"></i>
                                        Código QR del IMEI
                                    </p>
                                    <button type="button" 
                                            id="btnRegenerarQR"
                                            class="text-xs text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-sync-alt mr-1"></i> Regenerar
                                    </button>
                                </div>
                            </div>

                            <!-- Columna derecha: Formulario -->
                            <div class="lg:col-span-2 space-y-6">
                                <!-- Campo IMEI con generador -->
                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Código IMEI <span class="text-red-500">*</span>
                                        <span class="ml-2 text-xs font-normal text-gray-500">(15 dígitos numéricos)</span>
                                    </label>
                                    
                                    <div class="flex gap-2">
                                        <div class="flex-1 relative">
                                            <input type="text" 
                                                   name="codigo_imei" 
                                                   id="codigo_imei"
                                                   value="{{ old('codigo_imei') }}"
                                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 font-mono text-lg tracking-wider"
                                                   placeholder="123456789012345" 
                                                   maxlength="15"
                                                   inputmode="numeric"
                                                   required>
                                            <div id="imei-validation" class="absolute right-3 top-3 hidden">
                                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                                            </div>
                                        </div>
                                        
                                        <button type="button" 
                                                id="btnGenerarImei"
                                                class="px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center gap-2 whitespace-nowrap">
                                            <i class="fas fa-sync-alt"></i>
                                            Generar
                                        </button>
                                    </div>

                                    <!-- Validación en tiempo real -->
                                    <div class="mt-2 grid grid-cols-3 gap-2 text-xs">
                                        <div id="valid-longitud" class="text-gray-400 flex items-center">
                                            <i class="fas fa-circle mr-1 text-[8px]"></i>
                                            <span>15 dígitos</span>
                                        </div>
                                        <div id="valid-numeros" class="text-gray-400 flex items-center">
                                            <i class="fas fa-circle mr-1 text-[8px]"></i>
                                            <span>Solo números</span>
                                        </div>
                                        <div id="valid-unico" class="text-gray-400 flex items-center">
                                            <i class="fas fa-circle mr-1 text-[8px]"></i>
                                            <span>IMEI único</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Producto con búsqueda inteligente -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Producto (Celular) <span class="text-red-500">*</span>
                                    </label>
                                    
                                    <select name="producto_id" id="producto_id" class="w-full" style="width: 100%;" required>
                                        <option value="">🔍 Buscar modelo de celular...</option>
                                        @foreach($productos as $producto)
                                            @php
                                                $marca = $producto->marca?->nombre ?? 'Sin marca';
                                                $modelo = $producto->modelo?->nombre ?? '';
                                                $color = $producto->color?->nombre ?? '';
                                                $capacidad = $producto->capacidad ?? '';
                                                
                                                // Construir nombre descriptivo
                                                $nombreCompleto = trim("$marca $modelo");
                                                if ($capacidad) $nombreCompleto .= " $capacidad";
                                                if ($color) $nombreCompleto .= " ($color)";
                                            @endphp
                                            <option value="{{ $producto->id }}" 
                                                    data-marca="{{ $marca }}"
                                                    data-modelo="{{ $modelo }}"
                                                    data-color="{{ $color }}"
                                                    data-capacidad="{{ $capacidad }}"
                                                    data-precio="{{ $producto->precio_venta ?? 0 }}"
                                                    data-imagen="{{ $producto->imagen_url ?? '' }}"
                                                    {{ old('producto_id') == $producto->id ? 'selected' : '' }}>
                                                {{ $nombreCompleto }} | Código: {{ $producto->codigo }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <!-- Vista previa del producto seleccionado -->
                                    <div id="productoPreview" class="mt-4 hidden">
                                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-200">
                                            <div class="flex items-start gap-4">
                                                <div class="bg-white p-3 rounded-lg shadow-sm">
                                                    <i class="fas fa-mobile-alt text-3xl text-blue-600"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <h4 class="font-bold text-gray-900 text-lg" id="previewNombre"></h4>
                                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                                            <i class="fas fa-check-circle mr-1"></i>Seleccionado
                                                        </span>
                                                    </div>
                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3 text-sm">
                                                        <div>
                                                            <span class="text-gray-500 text-xs">Marca</span>
                                                            <p class="font-medium" id="previewMarca">-</p>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-500 text-xs">Modelo</span>
                                                            <p class="font-medium" id="previewModelo">-</p>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-500 text-xs">Color</span>
                                                            <p class="font-medium" id="previewColor">-</p>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-500 text-xs">Capacidad</span>
                                                            <p class="font-medium" id="previewCapacidad">-</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Almacén y Color en grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-warehouse mr-1 text-gray-500"></i>
                                            Almacén <span class="text-red-500">*</span>
                                        </label>
                                        <select name="almacen_id" id="almacen_id"
                                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                                required>
                                            <option value="">Seleccionar ubicación</option>
                                            @foreach($almacenes as $almacen)
                                                <option value="{{ $almacen->id }}" 
                                                        {{ old('almacen_id') == $almacen->id ? 'selected' : '' }}>
                                                    🏢 {{ $almacen->nombre }}
                                                    @if($almacen->ubicacion)
                                                        ({{ $almacen->ubicacion }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="color_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-palette mr-1 text-gray-500"></i>
                                            Color
                                        </label>
                                        <select name="color_id" id="color_id"
                                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                            <option value="">Sin especificar</option>
                                            @foreach($colores as $color)
                                                <option value="{{ $color->id }}" 
                                                        data-color="{{ $color->nombre }}"
                                                        style="background-color: {{ $color->codigo_hex ?? '#fff' }};"
                                                        {{ old('color_id') == $color->id ? 'selected' : '' }}>
                                                    {{ $color->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Número de Serie (opcional) -->
                                <div>
                                    <label for="serie" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-barcode mr-1 text-gray-500"></i>
                                        Número de Serie
                                    </label>
                                    <input type="text" 
                                           name="serie" 
                                           id="serie" 
                                           value="{{ old('serie') }}"
                                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                           placeholder="Número de serie adicional (opcional)">
                                </div>

                                <!-- Estado (con badges) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-tag mr-1 text-gray-500"></i>
                                        Estado <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="estado_imei" value="en_stock" 
                                                   class="hidden peer" 
                                                   {{ old('estado_imei', 'en_stock') == 'en_stock' ? 'checked' : '' }} required>
                                            <div class="border-2 border-gray-300 rounded-lg p-2 text-center peer-checked:border-green-500 peer-checked:bg-green-50 hover:bg-gray-50">
                                                <i class="fas fa-check-circle text-green-500"></i>
                                                <span class="block text-xs font-medium">En Stock</span>
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="estado_imei" value="vendido" 
                                                   class="hidden peer" 
                                                   {{ old('estado_imei') == 'vendido' ? 'checked' : '' }}>
                                            <div class="border-2 border-gray-300 rounded-lg p-2 text-center peer-checked:border-red-500 peer-checked:bg-red-50 hover:bg-gray-50">
                                                <i class="fas fa-shopping-cart text-red-500"></i>
                                                <span class="block text-xs font-medium">Vendido</span>
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="estado_imei" value="garantia" 
                                                   class="hidden peer" 
                                                   {{ old('estado_imei') == 'garantia' ? 'checked' : '' }}>
                                            <div class="border-2 border-gray-300 rounded-lg p-2 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50">
                                                <i class="fas fa-shield-alt text-blue-500"></i>
                                                <span class="block text-xs font-medium">Garantía</span>
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="estado_imei" value="devuelto" 
                                                   class="hidden peer" 
                                                   {{ old('estado_imei') == 'devuelto' ? 'checked' : '' }}>
                                            <div class="border-2 border-gray-300 rounded-lg p-2 text-center peer-checked:border-yellow-500 peer-checked:bg-yellow-50 hover:bg-gray-50">
                                                <i class="fas fa-undo text-yellow-500"></i>
                                                <span class="block text-xs font-medium">Devuelto</span>
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="estado_imei" value="reemplazado" 
                                                   class="hidden peer" 
                                                   {{ old('estado_imei') == 'reemplazado' ? 'checked' : '' }}>
                                            <div class="border-2 border-gray-300 rounded-lg p-2 text-center peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:bg-gray-50">
                                                <i class="fas fa-exchange-alt text-purple-500"></i>
                                                <span class="block text-xs font-medium">Reemplazado</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notas informativas -->
                        <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3 text-lg"></i>
                                <div>
                                    <p class="font-medium text-blue-900 mb-1">Información importante:</p>
                                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                                        <li>Use esta pantalla para registros individuales o ajustes manuales</li>
                                        <li>Para compras en volumen, registre los IMEIs desde el módulo de Compras</li>
                                        <li>El IMEI debe ser único (15 dígitos numéricos)</li>
                                        <li>El código QR se genera automáticamente para facilitar la gestión</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                            <a href="{{ route('inventario.imeis.index') }}"
                               class="px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-8 py-3 bg-gradient-to-r from-blue-900 to-blue-700 text-white rounded-lg hover:from-blue-800 hover:to-blue-600 font-medium shadow-lg hover:shadow-xl transition-all">
                                <i class="fas fa-save mr-2"></i>Registrar IMEI
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <!-- Select2 para mejor búsqueda -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para producto
            $('#producto_id').select2({
                placeholder: '🔍 Buscar modelo de celular...',
                allowClear: true,
                width: '100%',
                templateResult: formatProducto,
                templateSelection: formatProductoSelection
            });

            // Función para formatear resultados en el dropdown
            function formatProducto(producto) {
                if (!producto.id) return producto.text;
                
                var $producto = $(
                    '<div class="flex items-center p-2">' +
                        '<div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center mr-3">' +
                            '<i class="fas fa-mobile-alt text-gray-600"></i>' +
                        '</div>' +
                        '<div>' +
                            '<div class="font-medium">' + producto.text.split('|')[0] + '</div>' +
                            '<div class="text-xs text-gray-500">' + (producto.text.split('|')[1] || '') + '</div>' +
                        '</div>' +
                    '</div>'
                );
                return $producto;
            }

            function formatProductoSelection(producto) {
                return producto.text.split('|')[0] || producto.text;
            }

            // Mostrar preview cuando se selecciona producto
            $('#producto_id').on('change', function() {
                var selected = $(this).find('option:selected');
                if (selected.val()) {
                    $('#previewNombre').text(selected.text().split('|')[0]);
                    $('#previewMarca').text(selected.data('marca') || '-');
                    $('#previewModelo').text(selected.data('modelo') || '-');
                    $('#previewColor').text(selected.data('color') || '-');
                    $('#previewCapacidad').text(selected.data('capacidad') || '-');
                    $('#productoPreview').removeClass('hidden');
                } else {
                    $('#productoPreview').addClass('hidden');
                }
            });

            // Generar IMEI aleatorio
            $('#btnGenerarImei').click(function() {
                var imei = '';
                for (var i = 0; i < 15; i++) {
                    imei += Math.floor(Math.random() * 10);
                }
                $('#codigo_imei').val(imei).trigger('input');
            });

            // Validar IMEI en tiempo real
            $('#codigo_imei').on('input', function() {
                var imei = $(this).val();
                var soloNumeros = /^\d+$/.test(imei);
                var longitudCorrecta = imei.length === 15;
                
                // Validar longitud
                $('#valid-longitud').toggleClass('text-green-600', longitudCorrecta)
                                   .toggleClass('text-gray-400', !longitudCorrecta)
                                   .html('<i class="fas fa-' + (longitudCorrecta ? 'check-circle' : 'circle') + ' mr-1 text-[8px]"></i> 15 dígitos');
                
                // Validar números
                $('#valid-numeros').toggleClass('text-green-600', soloNumeros && imei.length > 0)
                                  .toggleClass('text-gray-400', !soloNumeros || imei.length === 0)
                                  .html('<i class="fas fa-' + (soloNumeros && imei.length > 0 ? 'check-circle' : 'circle') + ' mr-1 text-[8px]"></i> Solo números');
                
                // Aquí iría validación con AJAX para ver si es único
                if (longitudCorrecta && soloNumeros) {
                    // Simular validación única (conectar con backend)
                    $('#valid-unico').toggleClass('text-green-600', true)
                                    .toggleClass('text-gray-400', false)
                                    .html('<i class="fas fa-check-circle mr-1 text-[8px]"></i> IMEI válido');
                    $('#imei-validation').removeClass('hidden');
                } else {
                    $('#valid-unico').toggleClass('text-green-600', false)
                                    .toggleClass('text-gray-400', true)
                                    .html('<i class="fas fa-circle mr-1 text-[8px]"></i> IMEI único');
                    $('#imei-validation').addClass('hidden');
                }
            });

            // Trigger inicial si hay valor
            if ($('#codigo_imei').val()) {
                $('#codigo_imei').trigger('input');
            }
        });
    </script>
    @endpush

    <style>
        /* Estilos para Select2 */
        .select2-container--default .select2-selection--single {
            height: 48px;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.5rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
        }
        .select2-dropdown {
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
        }
    </style>
</body>
</html>