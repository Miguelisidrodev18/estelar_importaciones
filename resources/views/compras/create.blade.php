<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nueva Compra - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <!-- Header con breadcrumb -->
        <div class="mb-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-900">Dashboard</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <a href="{{ route('compras.index') }}" class="hover:text-blue-900">Compras</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <span class="text-gray-700 font-medium">Nueva Compra</span>
            </div>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-file-invoice mr-3 text-blue-900"></i>
                    Registrar Nueva Compra
                </h1>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                    <i class="fas fa-clock mr-1"></i>
                    {{ now()->format('d/m/Y H:i') }}
                </span>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex items-start">
                <i class="fas fa-exclamation-circle mt-0.5 mr-3 text-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Formulario principal -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Cabecera decorativa -->
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-8 py-5">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-shopping-cart mr-3"></i>
                    Datos de la Compra
                </h2>
            </div>

            <form action="{{ route('compras.store') }}" method="POST" id="compraForm" class="p-8">
                @csrf

                <!-- SECCIÓN 1: INFORMACIÓN PRINCIPAL -->
                <div class="mb-10">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-2">
                            <i class="fas fa-file-invoice text-blue-900 text-sm"></i>
                        </span>
                        Información de la Factura
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Proveedor -->
                        <div class="relative">
                            <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Proveedor <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="proveedor_id" id="proveedor_id" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 appearance-none bg-white">
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                            {{ $proveedor->nombre_comercial ?? $proveedor->razon_social }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
                            @error('proveedor_id')
                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Número de Factura -->
                        <div>
                            <label for="numero_factura" class="block text-sm font-medium text-gray-700 mb-1.5">
                                N° Factura/Boleta <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="numero_factura" id="numero_factura"
                                   value="{{ old('numero_factura') }}" required
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                   placeholder="Ej: F001-000001">
                            @error('numero_factura')
                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Almacén -->
                        <div class="relative">
                            <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Almacén Destino <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="almacen_id" id="almacen_id" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 appearance-none bg-white">
                                    <option value="">Seleccione un almacén</option>
                                    @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}" {{ old('almacen_id') == $almacen->id ? 'selected' : '' }}>
                                            {{ $almacen->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
                            @error('almacen_id')
                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Fecha -->
                        <div>
                            <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Fecha de Compra <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha" id="fecha" required
                                   value="{{ old('fecha', date('Y-m-d')) }}"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        </div>

                        <!-- Tipo Comprobante -->
                        <div class="relative">
                            <label for="tipo_comprobante" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Tipo Comprobante
                            </label>
                            <div class="relative">
                                <select name="tipo_comprobante" id="tipo_comprobante"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 appearance-none bg-white">
                                    <option value="factura">Factura</option>
                                    <option value="boleta">Boleta</option>
                                    <option value="nota_credito">Nota de Crédito</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Forma de Pago -->
                        <div class="relative">
                            <label for="forma_pago" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Forma de Pago <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="forma_pago" id="forma_pago" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 appearance-none bg-white"
                                        onchange="toggleCondicionPago(this.value)">
                                    <option value="contado" {{ old('forma_pago') == 'contado' ? 'selected' : '' }}>Contado</option>
                                    <option value="credito" {{ old('forma_pago') == 'credito' ? 'selected' : '' }}>Crédito</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Condición de Pago (crédito) -->
                        <div id="condicion_pago_div" class="hidden">
                            <label for="condicion_pago" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Días de Crédito
                            </label>
                            <input type="number" name="condicion_pago" id="condicion_pago"
                                   value="{{ old('condicion_pago', 30) }}" min="1" max="90"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        </div>

                        <!-- Moneda -->
                        <div class="relative">
                            <label for="tipo_moneda" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Moneda
                            </label>
                            <div class="relative">
                                <select name="tipo_moneda" id="tipo_moneda"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 appearance-none bg-white"
                                        onchange="toggleTipoCambio(this.value)">
                                    <option value="PEN" {{ old('tipo_moneda', 'PEN') == 'PEN' ? 'selected' : '' }}>PEN (S/)</option>
                                    <option value="USD" {{ old('tipo_moneda') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Tipo de Cambio -->
                        <div id="tipo_cambio_div" class="hidden">
                            <label for="tipo_cambio" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Tipo de Cambio (S/ por $)
                            </label>
                            <input type="number" name="tipo_cambio" id="tipo_cambio"
                                   value="{{ old('tipo_cambio', '3.80') }}" min="0.001" step="0.001"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        </div>
                    </div>
                </div>
                {{-- Tipo de Operación SUNAT --}}
                <div class="md:col-span-2">
                    <label for="tipo_operacion" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-invoice mr-1 text-blue-600"></i>
                        Tipo de Operación SUNAT <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo_operacion" id="tipo_operacion" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="01" {{ old('tipo_operacion', '01') == '01' ? 'selected' : '' }}>Gravado - Operación gravada (IGV 18%)</option>
                        <option value="02" {{ old('tipo_operacion') == '02' ? 'selected' : '' }}>Exonerado - Operación exonerada</option>
                        <option value="03" {{ old('tipo_operacion') == '03' ? 'selected' : '' }}>Inafecto - Operación inafecta</option>
                        <option value="04" {{ old('tipo_operacion') == '04' ? 'selected' : '' }}>Exportación - Operación de exportación</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1 flex items-center">
                        <i class="fas fa-info-circle mr-1 text-blue-400"></i>
                        Según catálogo SUNAT: Código 01 = Gravado, 02 = Exonerado, 03 = Inafecto, 04 = Exportación
                    </p>
                </div>

                <!-- SECCIÓN 2: PRODUCTOS (MEJORADA) -->
                <div class="mb-10">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-2">
                                <i class="fas fa-boxes text-green-700 text-sm"></i>
                            </span>
                            Productos de la Compra
                        </h3>
                        <button type="button" onclick="abrirModalProductos()"
                                class="px-4 py-2.5 bg-blue-900 text-white rounded-xl hover:bg-blue-800 transition shadow-md hover:shadow-lg flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Agregar Productos
                        </button>
                    </div>

                    <!-- Tabla de productos -->
                    <div class="bg-gray-50 rounded-xl border-2 border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200" id="tablaProductos">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Producto</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Marca</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Modelo</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Color</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cant.</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Precio Unit.</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Subtotal</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">IMEIs</th>
                                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-20">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="detallesBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Los productos se agregarán dinámicamente aquí -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Mensaje cuando no hay productos -->
                        <div id="emptyProductos" class="text-center py-12 bg-white">
                            <div class="flex flex-col items-center">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-box-open text-3xl text-gray-400"></i>
                                </div>
                                <p class="text-gray-500 text-sm mb-2">No hay productos agregados</p>
                                <p class="text-xs text-gray-400">Haz clic en "Agregar Productos" para comenzar</p>
                            </div>
                        </div>

                        <!-- Totales -->
                        <div class="bg-gray-50 px-6 py-4 border-t-2 border-gray-200">
                            <div class="flex justify-end">
                                <div class="w-80 space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span id="subtotal" class="font-medium text-gray-900">S/ 0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" id="incluir_igv" name="incluye_igv" checked
                                                   class="w-4 h-4 rounded border-gray-300 text-blue-900 focus:ring-blue-900">
                                            <span class="text-gray-600">IGV (18%):</span>
                                        </label>
                                        <span id="igv" class="font-medium text-gray-900">S/ 0.00</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-base pt-3 border-t-2 border-gray-200">
                                        <span class="text-gray-900">Total:</span>
                                        <span id="total" class="text-blue-900">S/ 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: OBSERVACIONES -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-2">
                            <i class="fas fa-comment text-yellow-600 text-sm"></i>
                        </span>
                        Observaciones
                    </h3>
                    <textarea name="observaciones" rows="3"
                              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                              placeholder="Notas adicionales sobre la compra...">{{ old('observaciones') }}</textarea>
                </div>

                <!-- Botones de acción -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t-2 border-gray-100">
                    <a href="{{ route('compras.index') }}" 
                       class="px-6 py-3 border-2 border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-xl hover:from-blue-800 hover:to-blue-700 transition shadow-lg font-medium">
                        <i class="fas fa-save mr-2"></i>
                        Registrar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>

   <!-- MODAL DE SELECCIÓN DE PRODUCTOS (MÚLTIPLE) -->
    <div id="modalProductos" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="cerrarModalProductos()"></div>
        
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Header del modal -->
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-search mr-3"></i>
                    Buscar y Seleccionar Productos
                </h3>
                <button onclick="cerrarModalProductos()" class="text-white/80 hover:text-white transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <!-- Cuerpo del modal -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-180px)]">
                <!-- Buscador en vivo -->
                <div class="mb-6">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="text" 
                            id="buscadorProductos"
                            placeholder="Buscar producto por nombre, código, marca o modelo..."
                            class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    </div>
                    <p class="text-xs text-gray-500 mt-2 flex items-center">
                        <i class="fas fa-info-circle mr-1 text-blue-400"></i>
                        Mínimo 2 caracteres para buscar
                    </p>
                </div>

                <!-- Grid de resultados con checkboxes -->
                <div id="resultadosProductos" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Los resultados se cargarán dinámicamente -->
                </div>

                <!-- Mensaje de carga -->
                <div id="cargandoProductos" class="hidden text-center py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-900"></i>
                    <p class="mt-2 text-gray-500">Buscando productos...</p>
                </div>

                <!-- Mensaje sin resultados -->
                <div id="sinResultados" class="hidden text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box-open text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 mb-2">No se encontraron productos</p>
                    <p class="text-xs text-gray-400">Prueba con otros términos de búsqueda</p>
                </div>
            </div>

            <!-- Footer con acciones -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-between items-center">
                <div>
                    <span id="productosSeleccionadosCount" class="text-sm font-medium text-blue-900">0 productos seleccionados</span>
                </div>
                <div class="flex space-x-3">
                    <button onclick="cerrarModalProductos()"
                            class="px-6 py-2 border-2 border-gray-200 rounded-lg text-gray-700 hover:bg-white transition">
                        Cancelar
                    </button>
                    <button onclick="agregarProductosSeleccionados()"
                            class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition shadow-md flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        Agregar Seleccionados
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE IMEIs MEJORADO --}}
    <div id="imeiModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="cerrarModalIMEI()"></div>
        
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <!-- Header con gradiente -->
            <div class="bg-gradient-to-r from-purple-700 to-purple-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white flex items-center" id="imeiModalTitle">
                    <i class="fas fa-microchip mr-3"></i>
                    Registrar IMEIs
                </h3>
                <button onclick="cerrarModalIMEI()" class="text-white/80 hover:text-white transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <!-- Cuerpo del modal -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-180px)]">
                <!-- Barra de herramientas -->
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6 p-4 bg-gray-50 rounded-xl">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700">
                            <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                            Total: <span id="imeiTotalCount" class="font-bold text-purple-700">0</span> IMEIs
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="importarIMEIs()" 
                                class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-sm flex items-center">
                            <i class="fas fa-file-import mr-1"></i>
                            Importar
                        </button>
                        <button type="button" onclick="generarIMEIsAleatorios()" 
                                class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 text-sm flex items-center">
                            <i class="fas fa-magic mr-1"></i>
                            Generar
                        </button>
                        <button type="button" onclick="limpiarIMEIs()" 
                                class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm flex items-center">
                            <i class="fas fa-eraser mr-1"></i>
                            Limpiar
                        </button>
                    </div>
                </div>

                <!-- Contenedor de inputs de IMEI -->
                <div id="imeiContainer" class="space-y-3">
                    {{-- Los inputs se generarán dinámicamente --}}
                </div>

                <!-- Mensaje de ayuda -->
                <div class="mt-4 text-xs text-gray-500 flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <span><i class="fas fa-info-circle mr-1 text-blue-500"></i> Cada IMEI debe tener exactamente 15 dígitos numéricos</span>
                    <span><i class="fas fa-keyboard mr-1 text-blue-500"></i> Presiona Tab para navegar entre campos</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="cerrarModalIMEI()"
                        class="px-6 py-2 border-2 border-gray-200 rounded-lg text-gray-700 hover:bg-white transition">
                    Cancelar
                </button>
                <button type="button" onclick="guardarIMEIs()"
                        class="px-6 py-2 bg-gradient-to-r from-purple-700 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-500 transition shadow-md">
                    <i class="fas fa-check-circle mr-2"></i>
                    Guardar IMEIs
                </button>
            </div>
        </div>
    </div>
<script>
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let timeoutBusqueda;
    let contadorProductos = 0;
    let imeisPorFila = {}; // { rowIndex: ['imei1', 'imei2', ...] }
    let productoEnEdicion = null;
    let productosSeleccionadosIds = new Set();


    // Datos de catálogo (cargados desde PHP)
    const catalogoProductos = @json($productos);
    const marcasCatalogo = @json($marcas);
    const coloresCatalogo = @json($colores);

    // Elementos del DOM
    const modalProductos = document.getElementById('modalProductos');
    const buscador = document.getElementById('buscadorProductos');
    const resultadosDiv = document.getElementById('resultadosProductos');
    const cargandoDiv = document.getElementById('cargandoProductos');
    const sinResultadosDiv = document.getElementById('sinResultados');

    // ============================================
    // FUNCIONES DE UTILIDAD
    // ============================================
    function toggleCondicionPago(valor) {
        const div = document.getElementById('condicion_pago_div');
        div.style.display = valor === 'credito' ? 'block' : 'none';
    }

    function toggleTipoCambio(valor) {
        const div = document.getElementById('tipo_cambio_div');
        div.style.display = valor === 'USD' ? 'block' : 'none';
    }

    // ============================================
    // FUNCIONES PARA AGREGAR PRODUCTOS
    // ============================================
    function agregarProducto() {
        const tbody = document.getElementById('detallesBody');
        const idx = contadorProductos;
        const rowId = `producto_${idx}`;

        const opcionesProductos = catalogoProductos.map(p =>
            `<option value="${p.id}" data-tipo="${p.tipo_inventario}">${p.nombre}</option>`
        ).join('');

        const opcionesMarcas = marcasCatalogo.map(m =>
            `<option value="${m.id}">${m.nombre}</option>`
        ).join('');

        const opcionesColores = coloresCatalogo.map(c =>
            `<option value="${c.id}">${c.nombre}</option>`
        ).join('');

        const row = document.createElement('tr');
        row.id = rowId;
        row.className = 'border-b border-gray-100 hover:bg-gray-50';
        row.innerHTML = `
            <td class="px-4 py-3">
                <select name="detalles[${idx}][producto_id]"
                        id="producto_select_${idx}"
                        onchange="cargarDetallesProducto(this, ${idx})"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        required>
                    <option value="">Seleccione producto</option>
                    ${opcionesProductos}
                </select>
            </td>
            <td class="px-4 py-3">
                <select id="marca_select_${idx}"
                        onchange="cambiarMarca(${idx})"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        disabled>
                    <option value="">— Marca —</option>
                    ${opcionesMarcas}
                </select>
            </td>
            <td class="px-4 py-3">
                <select name="detalles[${idx}][modelo_id]"
                        id="modelo_select_${idx}"
                        onchange="actualizarTrasCambioModelo(${idx})"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        disabled>
                    <option value="">— Modelo —</option>
                </select>
            </td>
            <td class="px-4 py-3">
                <select name="detalles[${idx}][color_id]"
                        id="color_${idx}"
                        onchange="actualizarVistaIMEI(${idx})"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        disabled>
                    <option value="">No aplica</option>
                    ${opcionesColores}
                </select>
            </td>
            <td class="px-4 py-3">
                <input type="number" name="detalles[${idx}][cantidad]"
                       id="cantidad_${idx}"
                       value="1" min="1" step="1"
                       onchange="actualizarCantidad(${idx})"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       required>
            </td>
            <td class="px-4 py-3">
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500 text-sm">S/</span>
                    <input type="number" name="detalles[${idx}][precio_unitario]"
                           id="precio_${idx}"
                           value="0.00" min="0" step="0.01"
                           onchange="calcularSubtotal(${idx})"
                           class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-sm"
                           required>
                </div>
            </td>
            <td class="px-4 py-3 font-semibold text-sm" id="subtotal_${idx}">S/ 0.00</td>
            <td class="px-4 py-3">
                <div id="imei_info_${idx}" class="hidden text-xs text-gray-500 mb-1">
                    <span id="imei_count_${idx}">0</span> IMEI(s)
                </div>
                <button type="button" onclick="gestionarIMEIs(${idx})"
                        id="btn_imei_${idx}"
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium disabled:opacity-40 disabled:cursor-not-allowed"
                        disabled>
                    <i class="fas fa-microchip mr-1"></i>IMEIs
                </button>
            </td>
            <td class="px-4 py-3">
                <button type="button" onclick="eliminarProducto('${rowId}')"
                        class="text-red-600 hover:text-red-800 text-sm">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
        contadorProductos++;

        // Ocultar mensaje de productos vacíos
        const emptyDiv = document.getElementById('emptyProductos');
        if (emptyDiv) emptyDiv.style.display = 'none';
    }

    function eliminarProducto(rowId) {
        if (confirm('¿Eliminar este producto?')) {
            document.getElementById(rowId).remove();
            calcularTotales();
        }
    }

    function cargarDetallesProducto(select, index) {
        const productoId = select.value;
        const producto = catalogoProductos.find(p => p.id == productoId);
        const marcaSelect = document.getElementById(`marca_select_${index}`);
        const modeloSelect = document.getElementById(`modelo_select_${index}`);
        const colorSelect = document.getElementById(`color_${index}`);
        const btnIMEI = document.getElementById(`btn_imei_${index}`);

        // Resetear dependientes
        marcaSelect.value = '';
        marcaSelect.disabled = true;
        modeloSelect.innerHTML = '<option value="">— Modelo —</option>';
        modeloSelect.disabled = true;
        colorSelect.value = '';
        colorSelect.disabled = true;
        btnIMEI.disabled = true;

        if (!producto) {
            calcularSubtotal(index);
            return;
        }

        // Habilitar marca
        marcaSelect.disabled = false;

        // Si el producto ya tiene marca asignada, pre-seleccionarla
        if (producto.marca_id) {
            marcaSelect.value = producto.marca_id;
            cambiarMarca(index);
        }

        calcularSubtotal(index);
    }

    function cambiarMarca(index) {
        const marcaSelect = document.getElementById(`marca_select_${index}`);
        const modeloSelect = document.getElementById(`modelo_select_${index}`);
        const colorSelect = document.getElementById(`color_${index}`);
        const btnIMEI = document.getElementById(`btn_imei_${index}`);

        modeloSelect.innerHTML = '<option value="">— Modelo —</option>';
        modeloSelect.disabled = true;
        colorSelect.disabled = true;
        btnIMEI.disabled = true;

        const marcaId = marcaSelect.value;
        if (!marcaId) return;

        // Cargar modelos de la marca seleccionada
        fetch(`/catalogo/modelos-por-marca/${marcaId}`)
            .then(response => response.json())
            .then(modelos => {
                if (modelos.length === 0) {
                    modeloSelect.innerHTML = '<option value="">Sin modelos</option>';
                    return;
                }
                modeloSelect.innerHTML = '<option value="">— Seleccione modelo —</option>';
                modelos.forEach(m => {
                    modeloSelect.innerHTML += `<option value="${m.id}">${m.nombre}</option>`;
                });
                modeloSelect.disabled = false;
            })
            .catch(() => {
                modeloSelect.innerHTML = '<option value="">Error al cargar</option>';
            });
    }

    function actualizarTrasCambioModelo(index) {
        const modeloSelect = document.getElementById(`modelo_select_${index}`);
        const colorSelect = document.getElementById(`color_${index}`);
        const btnIMEI = document.getElementById(`btn_imei_${index}`);
        const productoSelect = document.getElementById(`producto_select_${index}`);
        const producto = catalogoProductos.find(p => p.id == productoSelect.value);

        if (!producto || producto.tipo_inventario !== 'serie') return;

        if (modeloSelect.value) {
            colorSelect.disabled = false;
            colorSelect.required = true;
        } else {
            colorSelect.disabled = true;
            colorSelect.required = false;
            btnIMEI.disabled = true;
        }
    }

    function actualizarVistaIMEI(index) {
        const colorSelect = document.getElementById(`color_${index}`);
        const btnIMEI = document.getElementById(`btn_imei_${index}`);
        btnIMEI.disabled = !colorSelect.value;
    }

    function actualizarCantidad(index) {
        calcularSubtotal(index);
        actualizarInfoIMEI(index);
    }

    function calcularSubtotal(index) {
        const cantidad = parseFloat(document.getElementById(`cantidad_${index}`).value) || 0;
        const precio = parseFloat(document.getElementById(`precio_${index}`).value) || 0;
        document.getElementById(`subtotal_${index}`).innerText = `S/ ${(cantidad * precio).toFixed(2)}`;
        calcularTotales();
    }

    function calcularTotales() {
    let subtotalGeneral = 0;
    document.querySelectorAll('#detallesBody tr').forEach(row => {
        const match = row.id.match(/producto_(\d+)/);
        if (match) {
            const el = document.getElementById(`subtotal_${match[1]}`);
            if (el) subtotalGeneral += parseFloat(el.innerText.replace('S/ ', '')) || 0;
        }
    });

    // Obtener tipo de operación SUNAT
    const tipoOperacion = document.getElementById('tipo_operacion').value;
    const incluyeIGV = document.getElementById('incluir_igv').checked;
    
    let igv = 0;
    let total = subtotalGeneral;

    // Solo aplicar IGV si es gravado (01)
    if (tipoOperacion === '01') {
        if (incluyeIGV) {
            // Si el checkbox está marcado, el precio YA INCLUYE IGV
            // Subtotal ya incluye IGV, así que separamos
            igv = subtotalGeneral * (0.18 / 1.18); // IGV incluido
            total = subtotalGeneral;
        } else {
            // Si no incluye IGV, lo agregamos
            igv = subtotalGeneral * 0.18;
            total = subtotalGeneral + igv;
        }
    } else {
        // Exonerado, inafecto o exportación: No aplica IGV
        igv = 0;
        total = subtotalGeneral;
    }

    document.getElementById('subtotal').innerText = `S/ ${subtotalGeneral.toFixed(2)}`;
    document.getElementById('igv').innerText = `S/ ${igv.toFixed(2)}`;
    document.getElementById('total').innerText = `S/ ${total.toFixed(2)}`;
    document.getElementById('tipo_operacion').addEventListener('change', calcularTotales);
}
    // ============================================
    // FUNCIONES DEL MODAL DE PRODUCTOS
    // ============================================
    function abrirModalProductos() {
        modalProductos.classList.remove('hidden');
        modalProductos.classList.add('flex');
        setTimeout(() => {
            buscador.focus();
            buscarProductos('');
        }, 100);
    }

    function cerrarModalProductos() {
        modalProductos.classList.add('hidden');
        modalProductos.classList.remove('flex');
        buscador.value = '';
        resultadosDiv.innerHTML = '';
    }

    if (buscador) {
        buscador.addEventListener('input', function() {
            clearTimeout(timeoutBusqueda);
            const termino = this.value.trim();
            
            this.classList.add('border-blue-500');
            
            timeoutBusqueda = setTimeout(() => {
                this.classList.remove('border-blue-500');
                buscarProductos(termino);
            }, 300);
        });
    }

    function buscarProductos(termino) {
        resultadosDiv.innerHTML = '';
        cargandoDiv.classList.remove('hidden');
        sinResultadosDiv.classList.add('hidden');

        fetch(`/compras/buscar-productos?q=${encodeURIComponent(termino)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(productos => {
                cargandoDiv.classList.add('hidden');
                
                if (productos.length === 0) {
                    sinResultadosDiv.classList.remove('hidden');
                    return;
                }
                
                mostrarResultados(productos);
            })
            .catch(error => {
                console.error('Error:', error);
                cargandoDiv.classList.add('hidden');
                resultadosDiv.innerHTML = `
                    <div class="col-span-3 text-center py-8">
                        <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-2"></i>
                        <p class="text-red-600">Error al cargar productos</p>
                        <p class="text-xs text-gray-500 mt-2">${error.message}</p>
                    </div>
                `;
            });
    }

    // Actualizar la función mostrarResultados para incluir checkboxes
    function mostrarResultados(productos) {
        resultadosDiv.innerHTML = productos.map(p => `
            <div class="bg-white border-2 border-gray-200 rounded-xl p-4 hover:border-blue-500 hover:shadow-lg transition-all group">
                <div class="flex items-start gap-3">
                    <div class="flex items-center mt-1">
                        <input type="checkbox" 
                            class="producto-checkbox w-5 h-5 rounded border-gray-300 text-blue-900 focus:ring-blue-500"
                            value="${p.id}"
                            onchange="actualizarSeleccion(this, ${p.id})">
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="fas fa-box text-blue-900"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">${p.nombre}</h4>
                        <p class="text-sm text-gray-600">${p.marca || ''} ${p.modelo || ''}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs px-2 py-0.5 bg-gray-100 rounded-full text-gray-600">
                                ${p.categoria || 'Sin categoría'}
                            </span>
                            ${p.tipo_inventario === 'serie' ? 
                                '<span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full"><i class="fas fa-microchip mr-1"></i>IMEI</span>' : 
                                '<span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full"><i class="fas fa-boxes mr-1"></i>Stock</span>'}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
    // Función para actualizar selección
    function actualizarSeleccion(checkbox, productoId) {
        if (checkbox.checked) {
            productosSeleccionadosIds.add(productoId);
        } else {
            productosSeleccionadosIds.delete(productoId);
        }
        document.getElementById('productosSeleccionadosCount').innerText = 
            `${productosSeleccionadosIds.size} productos seleccionados`;
    }

    // Función para agregar productos seleccionados
    function agregarProductosSeleccionados() {
        if (productosSeleccionadosIds.size === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Selecciona al menos un producto'
            });
            return;
        }

        let procesados = 0;
        productosSeleccionadosIds.forEach(id => {
            fetch(`/compras/producto/${id}`)
                .then(response => response.json())
                .then(producto => {
                    agregarProductoConDatos(producto);
                    procesados++;
                    if (procesados === productosSeleccionadosIds.size) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Productos agregados',
                            text: `${procesados} producto(s) agregados correctamente`,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        productosSeleccionadosIds.clear();
                        document.getElementById('productosSeleccionadosCount').innerText = '0 productos seleccionados';
                        cerrarModalProductos();
                    }
                });
        });
    }

    // Limpiar selección al cerrar modal
    function cerrarModalProductos() {
        modalProductos.classList.add('hidden');
        modalProductos.classList.remove('flex');
        buscador.value = '';
        resultadosDiv.innerHTML = '';
        productosSeleccionadosIds.clear();
        document.getElementById('productosSeleccionadosCount').innerText = '0 productos seleccionados';
    }

    function seleccionarProductoModal(id) {
        const productoDiv = event?.currentTarget;
        if (productoDiv) {
            productoDiv.classList.add('opacity-50', 'pointer-events-none');
        }
        
        fetch(`/compras/producto/${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al cargar el producto');
                }
                return response.json();
            })
            .then(producto => {
                agregarProductoConDatos(producto);
                cerrarModalProductos();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar el producto: ' + error.message);
            })
            .finally(() => {
                if (productoDiv) {
                    productoDiv.classList.remove('opacity-50', 'pointer-events-none');
                }
            });
    }

    function agregarProductoConDatos(producto) {
        agregarProducto();
        const index = contadorProductos - 1;
        
        const selectProducto = document.getElementById(`producto_select_${index}`);
        if (selectProducto) {
            selectProducto.value = producto.id;
            
            const event = new Event('change', { bubbles: true });
            selectProducto.dispatchEvent(event);
            
            if (producto.marca_id) {
                setTimeout(() => {
                    const selectMarca = document.getElementById(`marca_select_${index}`);
                    if (selectMarca) {
                        selectMarca.value = producto.marca_id;
                        selectMarca.dispatchEvent(event);
                    }
                }, 500);
            }
        }
    }

    // ============================================
    // FUNCIONES DEL MODAL DE IMEIs
    // ============================================
    function gestionarIMEIs(index) {
        const select = document.getElementById(`producto_select_${index}`);
        const modeloSelect = document.getElementById(`modelo_select_${index}`);
        const colorSelect = document.getElementById(`color_${index}`);
        const cantidad = parseInt(document.getElementById(`cantidad_${index}`).value) || 1;

        if (!select.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Primero seleccione un producto'
            });
            return;
        }
        
        if (!modeloSelect.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Primero seleccione un modelo'
            });
            return;
        }
        
        if (!colorSelect.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Primero seleccione un color'
            });
            return;
        }

        const producto = catalogoProductos.find(p => p.id == select.value);
        const modeloNombre = modeloSelect.options[modeloSelect.selectedIndex].text;
        const colorNombre = colorSelect.options[colorSelect.selectedIndex].text;

        document.getElementById('imeiModalTitle').innerHTML = `
            <i class="fas fa-microchip mr-3"></i>
            ${producto.nombre} · ${modeloNombre} · ${colorNombre}
        `;

        productoEnEdicion = index;
        
        const imeisGuardados = imeisPorFila[index] || [];
        generarInputsIMEI(cantidad, imeisGuardados);
        
        document.getElementById('imeiModal').classList.remove('hidden');
        document.getElementById('imeiModal').classList.add('flex');
        
        actualizarContadorIMEI();
    }

    function generarInputsIMEI(cantidad, imeisGuardados = []) {
        const container = document.getElementById('imeiContainer');
        let html = '';
        
        for (let i = 0; i < cantidad; i++) {
            const valor = imeisGuardados[i] || '';
            const esValido = valor.length === 15 ? 'border-green-500 bg-green-50' : '';
            
            html += `
                <div class="grid grid-cols-12 gap-3 items-center">
                    <div class="col-span-1 text-sm font-medium text-gray-600 text-center bg-gray-100 py-2 rounded-lg">
                        ${i + 1}
                    </div>
                    <div class="col-span-11">
                        <input type="text"
                            class="imei-input w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-2 focus:ring-purple-200 font-mono text-lg tracking-wider ${esValido}"
                            placeholder="Ingrese IMEI de 15 dígitos"
                            value="${valor}"
                            maxlength="15"
                            oninput="this.value = this.value.replace(/[^0-9]/g, ''); validarIMEIInput(this)">
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = html;
        actualizarContadorIMEI();
    }

    function validarIMEIInput(input) {
        const valor = input.value.trim();
        if (valor.length === 15) {
            input.classList.add('border-green-500', 'bg-green-50');
            input.classList.remove('border-red-500', 'bg-red-50');
        } else if (valor.length > 0) {
            input.classList.remove('border-green-500', 'bg-green-50');
            input.classList.add('border-red-500', 'bg-red-50');
        } else {
            input.classList.remove('border-red-500', 'bg-red-50', 'border-green-500', 'bg-green-50');
        }
        actualizarContadorIMEI();
    }

    function actualizarContadorIMEI() {
        const inputs = document.querySelectorAll('.imei-input');
        const total = inputs.length;
        const validos = Array.from(inputs).filter(input => input.value.trim().length === 15).length;
        
        document.getElementById('imeiTotalCount').innerText = `${validos}/${total}`;
    }

    function guardarIMEIs() {
        const inputs = document.querySelectorAll('.imei-input');
        const imeis = [];
        let valido = true;
        let primerError = null;

        inputs.forEach((input, index) => {
            const valor = input.value.trim();
            if (valor.length !== 15) {
                input.classList.add('border-red-500', 'bg-red-50');
                valido = false;
                if (!primerError) primerError = input;
            } else {
                input.classList.remove('border-red-500', 'bg-red-50');
                input.classList.add('border-green-500', 'bg-green-50');
                imeis.push(valor);
            }
        });

        if (!valido) {
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Todos los IMEI deben tener exactamente 15 dígitos numéricos',
                confirmButtonColor: '#d33'
            });
            if (primerError) primerError.focus();
            return;
        }

        if (productoEnEdicion !== null) {
            const idx = productoEnEdicion;

            document.querySelectorAll(`[data-imei-row="${idx}"]`).forEach(el => el.remove());

            imeisPorFila[idx] = imeis;

            imeis.forEach((imei, i) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `detalles[${idx}][imeis][${i}][codigo_imei]`;
                input.setAttribute('data-imei-row', idx);
                input.value = imei;
                document.getElementById('compraForm').appendChild(input);
            });

            actualizarInfoIMEI(idx);
            
            Swal.fire({
                icon: 'success',
                title: 'IMEIs guardados',
                text: `${imeis.length} IMEI(s) registrados correctamente`,
                timer: 1500,
                showConfirmButton: false
            });
        }

        cerrarModalIMEI();
    }

    function generarIMEIsAleatorios() {
        const inputs = document.querySelectorAll('.imei-input');
        
        inputs.forEach(input => {
            let imei = '';
            for (let i = 0; i < 14; i++) {
                imei += Math.floor(Math.random() * 10);
            }
            
            let suma = 0;
            for (let i = 0; i < 14; i++) {
                let digito = parseInt(imei[i]);
                if (i % 2 === 0) {
                    digito *= 2;
                    if (digito > 9) digito -= 9;
                }
                suma += digito;
            }
            let verificador = (10 - (suma % 10)) % 10;
            imei += verificador;
            
            input.value = imei;
            validarIMEIInput(input);
        });
        
        Swal.fire({
            icon: 'success',
            title: 'IMEIs generados',
            text: `${inputs.length} IMEI(s) generados aleatoriamente`,
            timer: 1500,
            showConfirmButton: false
        });
    }

    function importarIMEIs() {
        const inputs = document.querySelectorAll('.imei-input');
        
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = '.txt,.csv';
        fileInput.onchange = function(e) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const contenido = e.target.result;
                const lineas = contenido.split('\n')
                    .map(line => line.trim())
                    .filter(line => line.length > 0);
                
                let importados = 0;
                lineas.forEach((linea, index) => {
                    if (index < inputs.length) {
                        const imei = linea.substring(0, 15);
                        if (imei.length === 15 && /^\d+$/.test(imei)) {
                            inputs[index].value = imei;
                            validarIMEIInput(inputs[index]);
                            importados++;
                        }
                    }
                });
                
                Swal.fire({
                    icon: 'success',
                    title: 'Importación completada',
                    text: `Se importaron ${importados} de ${Math.min(lineas.length, inputs.length)} IMEIs válidos`,
                    confirmButtonColor: '#2563eb'
                });
            };
            
            reader.readAsText(file);
        };
        
        fileInput.click();
    }

    function limpiarIMEIs() {
        Swal.fire({
            title: '¿Limpiar todos?',
            text: 'Esta acción eliminará todos los IMEI ingresados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelectorAll('.imei-input').forEach(input => {
                    input.value = '';
                    input.classList.remove('border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50');
                });
                actualizarContadorIMEI();
            }
        });
    }

    function cerrarModalIMEI() {
        document.getElementById('imeiModal').classList.add('hidden');
        document.getElementById('imeiModal').classList.remove('flex');
        productoEnEdicion = null;
    }

    function actualizarInfoIMEI(index) {
        const infoDiv = document.getElementById(`imei_info_${index}`);
        const countSpan = document.getElementById(`imei_count_${index}`);
        const btnImei = document.getElementById(`btn_imei_${index}`);
        const guardados = imeisPorFila[index] || [];

        if (guardados.length > 0) {
            countSpan.innerText = guardados.length;
            infoDiv.classList.remove('hidden');
            btnImei.innerHTML = `<i class="fas fa-check-circle mr-1 text-green-600"></i>${guardados.length} IMEI(s)`;
            btnImei.classList.add('text-green-700', 'font-medium');
        } else {
            infoDiv.classList.add('hidden');
            btnImei.innerHTML = '<i class="fas fa-microchip mr-1"></i>IMEIs';
            btnImei.classList.remove('text-green-700', 'font-medium');
        }
    }

    // ============================================
    // INICIALIZACIÓN
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Agregar un producto por defecto al cargar la página
        agregarProducto();

        // Event listeners para toggles
        document.getElementById('incluir_igv').addEventListener('change', calcularTotales);
    });
</script>
    
</body>
</html>