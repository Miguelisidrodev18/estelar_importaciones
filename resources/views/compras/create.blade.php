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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-triangle mr-2 text-lg"></i>
                    <strong>Por favor corrige los siguientes errores:</strong>
                </div>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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
                        <!-- Proveedor (búsqueda en vivo) -->
                        <div class="relative" id="proveedor_container">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Proveedor <span class="text-red-500">*</span>
                            </label>
                            {{-- Campo oculto que envía el ID al servidor --}}
                            <input type="hidden" name="proveedor_id" id="proveedor_id" value="{{ old('proveedor_id') }}">

                            {{-- Input de búsqueda --}}
                            <div class="relative" id="proveedor_busqueda_wrap" style="{{ old('proveedor_id') ? 'display:none' : '' }}">
                                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400 pointer-events-none"></i>
                                <input type="text"
                                       id="buscar_proveedor"
                                       placeholder="Escribe RUC, razón social o nombre (mín. 3 caracteres)..."
                                       autocomplete="off"
                                       class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm">
                            </div>

                            {{-- Dropdown de resultados --}}
                            <div id="proveedor_resultados"
                                 class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-xl mt-1 hidden max-h-64 overflow-y-auto">
                            </div>

                            {{-- Proveedor seleccionado (card) --}}
                            @php
                                $provSeleccionado = old('proveedor_id') ? $proveedores->firstWhere('id', old('proveedor_id')) : null;
                            @endphp
                            <div id="proveedor_seleccionado"
                                 class="{{ $provSeleccionado ? '' : 'hidden' }} mt-2 p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2 min-w-0">
                                    <i class="fas fa-building text-blue-700 text-sm shrink-0"></i>
                                    <div class="min-w-0">
                                        <p id="proveedor_nombre_display" class="text-sm font-semibold text-blue-900 truncate">
                                            {{ $provSeleccionado?->nombre_comercial ?? $provSeleccionado?->razon_social ?? '' }}
                                        </p>
                                        <p id="proveedor_ruc_display" class="text-xs text-blue-600">
                                            @if($provSeleccionado)
                                                {{ $provSeleccionado->razon_social !== $provSeleccionado->nombre_comercial ? $provSeleccionado->razon_social . ' · ' : '' }}RUC: {{ $provSeleccionado->ruc }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <button type="button" onclick="limpiarProveedorSeleccionado()"
                                        class="shrink-0 ml-2 text-xs text-blue-600 hover:text-red-600 transition flex items-center gap-1 border border-blue-300 hover:border-red-400 rounded-lg px-2 py-1">
                                    <i class="fas fa-times"></i> Cambiar
                                </button>
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

                        <!-- Sucursal -->
                        <div class="relative">
                            <label for="sucursal_sel" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Sucursal
                            </label>
                            <div class="relative">
                                <select id="sucursal_sel"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 appearance-none bg-white"
                                        onchange="filtrarAlmacenesPorSucursal(this.value)">
                                    <option value="">— Todas las sucursales —</option>
                                    @foreach($sucursales as $suc)
                                        <option value="{{ $suc->id }}" data-almacen="{{ $suc->almacen_id }}">
                                            {{ $suc->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
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
                        <div id="condicion_pago_div" class="{{ old('forma_pago') == 'credito' ? '' : 'hidden' }}">
                            <label for="condicion_pago" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Días de Crédito
                            </label>
                            <input type="number" name="condicion_pago" id="condicion_pago"
                                value="{{ old('condicion_pago', 30) }}" min="1" max="90"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                {{ old('forma_pago') == 'credito' ? '' : 'disabled' }}>
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
                            <div class="flex gap-2 items-center">
                                <input type="number" name="tipo_cambio" id="tipo_cambio"
                                       value="{{ old('tipo_cambio', '') }}" min="0.001" step="0.001"
                                       placeholder="Ej: 3.750"
                                       oninput="calcularTotales()"
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                <button type="button" id="btnCargarTC" onclick="cargarTipoCambioSUNAT()"
                                        title="Cargar tipo de cambio desde SUNAT"
                                        class="flex-shrink-0 px-3 py-3 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl border-2 border-blue-200 transition text-sm font-medium whitespace-nowrap">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <div id="tcInfo" class="hidden mt-1.5 px-3 py-2 bg-green-50 rounded-lg text-xs text-gray-600 flex flex-wrap gap-3">
                                <span>Compra: <strong id="tcCompra" class="text-gray-800"></strong></span>
                                <span>Venta: <strong id="tcVenta" class="text-gray-800"></strong></span>
                                <span class="text-gray-400 italic" id="tcFecha"></span>
                                <span class="text-green-600 font-medium"><i class="fas fa-check-circle mr-0.5"></i>SUNAT</span>
                            </div>
                        </div>

                        <!-- Tipo de Operación SUNAT (dentro del grid, fila completa) -->
                        <div class="lg:col-span-3">
                            <label for="tipo_operacion" class="block text-sm font-medium text-gray-700 mb-1.5">
                                <i class="fas fa-file-invoice mr-1 text-blue-600"></i>
                                Tipo de Operación SUNAT <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="tipo_operacion" id="tipo_operacion" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 appearance-none bg-white">
                                    <option value="01" {{ old('tipo_operacion', '01') == '01' ? 'selected' : '' }}>01 — Gravado (aplica IGV 18%)</option>
                                    <option value="02" {{ old('tipo_operacion') == '02' ? 'selected' : '' }}>02 — Exonerado (sin IGV)</option>
                                    <option value="03" {{ old('tipo_operacion') == '03' ? 'selected' : '' }}>03 — Inafecto (sin IGV)</option>
                                    <option value="04" {{ old('tipo_operacion') == '04' ? 'selected' : '' }}>04 — Exportación (sin IGV)</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 flex items-center">
                                <i class="fas fa-info-circle mr-1 text-blue-400"></i>
                                Catálogo SUNAT. Solo operaciones gravadas (01) aplican IGV del 18%.
                            </p>
                        </div>
                    </div>
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
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" id="thPrecioUnit">Precio Unit. (S/)</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" id="thSubtotal">Subtotal</th>
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

                                    {{-- Toggle: precio incluye IGV --}}
                                    <div id="togglePrecioIgvWrap" class="flex items-center justify-between bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                                        <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-semibold text-amber-800" for="precio_incluye_igv">
                                            <i class="fas fa-tags text-amber-500"></i>
                                            ¿El precio ingresado ya incluye IGV?
                                        </label>
                                        <input type="hidden" name="precio_incluye_igv" value="0">
                                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                            <input type="checkbox" id="precio_incluye_igv" name="precio_incluye_igv" value="1"
                                                   class="sr-only peer">
                                            <div class="w-9 h-5 bg-gray-300 peer-checked:bg-amber-500 rounded-full transition-colors"></div>
                                            <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                                        </label>
                                    </div>

                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal <span id="lblSinIgv" class="text-xs text-gray-400 hidden">(sin IGV)</span>:</span>
                                        <span id="subtotal" class="font-medium text-gray-900">S/ 0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="hidden" name="incluye_igv" value="0">
                                            <input type="checkbox" id="incluir_igv" name="incluye_igv" value="1" checked
                                                   class="w-4 h-4 rounded border-gray-300 text-blue-900 focus:ring-blue-900">
                                            <span class="text-gray-600">IGV (18%):</span>
                                        </label>
                                        <span id="igv" class="font-medium text-gray-900">S/ 0.00</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-base pt-3 border-t-2 border-gray-200">
                                        <span class="text-gray-900">Total:</span>
                                        <span id="total" class="text-blue-900">S/ 0.00</span>
                                    </div>
                                    <!-- Equivalente en PEN (solo visible cuando moneda = USD) -->
                                    <div id="equivalentePEN" class="hidden pt-2 border-t border-dashed border-blue-200">
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-gray-500 flex items-center gap-1">
                                                <i class="fas fa-exchange-alt text-xs text-blue-500"></i>
                                                Equivalente en soles:
                                            </span>
                                            <span id="totalPEN" class="font-semibold text-blue-900">S/ 0.00</span>
                                        </div>
                                        <p class="text-xs text-gray-400 text-right mt-0.5">TC: <span id="tcUsado">—</span></p>
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

    <!-- MODAL DE SELECCIÓN DE PRODUCTOS (MEJORADO) -->
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

                <!-- FILTROS POR CATEGORÍA (NUEVO) -->
                <div class="mb-6 overflow-x-auto pb-2">
                    <div class="flex gap-2 min-w-max">
                        <button type="button" 
                                class="categoria-filter active px-4 py-2 rounded-full text-sm font-medium transition-all"
                                data-categoria="todos"
                                style="background-color: #1e3a8a; color: white;">
                            <i class="fas fa-boxes mr-1"></i>Todos
                        </button>
                        
                        @foreach($categorias as $categoria)
                            <button type="button" 
                                    class="categoria-filter px-4 py-2 rounded-full text-sm font-medium transition-all bg-gray-100 text-gray-700 hover:bg-gray-200"
                                    data-categoria="{{ $categoria->id }}">
                                <i class="fas fa-tag mr-1"></i>{{ $categoria->nombre }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Grid de resultados mejorado -->
                <div id="resultadosProductos" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Los resultados se cargarán dinámicamente -->
                </div>

                <!-- Mensaje de carga -->
                <div id="cargandoProductos" class="hidden text-center py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-900"></i>
                    <p class="mt-2 text-gray-500">Buscando productos...</p>
                </div>

                <!-- Mensaje sin resultados -->
                <div id="sinResultados" class="hidden text-center py-10">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box-open text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 mb-1">No se encontraron productos</p>
                    <p class="text-xs text-gray-400 mb-4">Prueba con otros términos o crea el producto ahora</p>
                    <button type="button" onclick="abrirModalCrearProducto(document.getElementById('buscadorProductos').value)"
                            class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-green-700 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-500 transition shadow-md text-sm font-medium">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Crear este producto en el catálogo
                    </button>
                </div>
            </div>

            <!-- Footer con acciones (mejorado) -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-between items-center">
                <div>
                    <span id="productosSeleccionadosCount" class="text-sm font-medium text-blue-900">0 productos seleccionados</span>
                    <span id="totalUnidadesCount" class="ml-2 text-sm text-gray-500">(0 unidades)</span>
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
    let categoriaActual = 'todos';
    let productosFiltrados = [];
    let productosOriginales = []; // Para mantener la lista completa
    let cantidadesSeleccionadas = {}; // { productoId: cantidad }
    let variantesSeleccionadas  = {}; // { "prodId_varId": { productoId, varianteId, cantidad, producto, variante } }
    let productosModal  = {};  // prodId → producto (registro para handlers sin JSON inline)
    let variantesModal  = {};  // "prodId_varId" → variante




    // Datos de catálogo (cargados desde PHP)
    const categoriasDisponibles = @json($categorias);
    const catalogoProductos   = @json($productos);
    const marcasCatalogo      = @json($marcas);
    const coloresCatalogo     = @json($colores);
    const proveedoresCatalogo = {!! json_encode($proveedores->map(fn($p) => ['id' => $p->id, 'ruc' => $p->ruc, 'razon_social' => $p->razon_social, 'nombre_comercial' => $p->nombre_comercial])) !!};

    // Elementos del DOM
    const modalProductos = document.getElementById('modalProductos');
    const buscador = document.getElementById('buscadorProductos');
    const resultadosDiv = document.getElementById('resultadosProductos');
    const cargandoDiv = document.getElementById('cargandoProductos');
    const sinResultadosDiv = document.getElementById('sinResultados');

    // ============================================
    // BÚSQUEDA EN VIVO DE PROVEEDOR
    // ============================================
    (function initProveedorSearch() {
        const provInput    = document.getElementById('buscar_proveedor');
        const provResultados = document.getElementById('proveedor_resultados');
        let timeoutProv;

        if (!provInput) return;

        provInput.addEventListener('input', function () {
            clearTimeout(timeoutProv);
            const termino = this.value.trim();

            if (termino.length < 3) {
                provResultados.classList.add('hidden');
                provResultados.innerHTML = '';
                return;
            }

            timeoutProv = setTimeout(() => {
                const tl = termino.toLowerCase();
                const resultados = proveedoresCatalogo.filter(p =>
                    (p.ruc             && p.ruc.toLowerCase().includes(tl)) ||
                    (p.razon_social    && p.razon_social.toLowerCase().includes(tl)) ||
                    (p.nombre_comercial && p.nombre_comercial.toLowerCase().includes(tl))
                );

                if (resultados.length === 0) {
                    provResultados.innerHTML =
                        '<div class="px-4 py-3 text-sm text-gray-500 text-center">' +
                        '<i class="fas fa-search mr-1"></i>No se encontraron proveedores</div>';
                } else {
                    provResultados.innerHTML = resultados.map(p => {
                        const nombre = p.nombre_comercial || p.razon_social || '';
                        const extra  = (p.nombre_comercial && p.nombre_comercial !== p.razon_social)
                            ? p.razon_social : '';
                        // Escape para el onclick
                        const n  = nombre.replace(/\\/g,'\\\\').replace(/'/g, "\\'");
                        const r  = (p.ruc || '').replace(/'/g, "\\'");
                        const rs = (p.razon_social || '').replace(/\\/g,'\\\\').replace(/'/g, "\\'");
                        return `
                            <div onclick="seleccionarProveedor(${p.id},'${n}','${r}','${rs}')"
                                 class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                <div class="font-medium text-gray-900 text-sm">${nombre}</div>
                                <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-3">
                                    ${extra ? `<span>${extra}</span>` : ''}
                                    <span class="font-mono text-blue-600">RUC: ${p.ruc || '—'}</span>
                                </div>
                            </div>`;
                    }).join('');
                }
                provResultados.classList.remove('hidden');
            }, 300);
        });

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function (e) {
            const container = document.getElementById('proveedor_container');
            if (container && !container.contains(e.target)) {
                provResultados.classList.add('hidden');
            }
        });

        // Cerrar con Escape
        provInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                provResultados.classList.add('hidden');
                provResultados.innerHTML = '';
            }
        });
    })();

    function seleccionarProveedor(id, nombre, ruc, razonSocial) {
        document.getElementById('proveedor_id').value = id;
        document.getElementById('proveedor_nombre_display').textContent = nombre;
        document.getElementById('proveedor_ruc_display').textContent =
            (razonSocial && razonSocial !== nombre)
                ? `${razonSocial} · RUC: ${ruc}`
                : `RUC: ${ruc}`;

        // Mostrar card, ocultar buscador
        document.getElementById('proveedor_seleccionado').classList.remove('hidden');
        document.getElementById('proveedor_busqueda_wrap').style.display = 'none';
        document.getElementById('proveedor_resultados').classList.add('hidden');
        document.getElementById('buscar_proveedor').value = '';
    }

    function limpiarProveedorSeleccionado() {
        document.getElementById('proveedor_id').value = '';
        document.getElementById('proveedor_seleccionado').classList.add('hidden');
        document.getElementById('proveedor_busqueda_wrap').style.display = '';
        document.getElementById('buscar_proveedor').value = '';
        document.getElementById('buscar_proveedor').focus();
    }

    // ============================================
    // FUNCIONES DE UTILIDAD
    // ============================================
    // ============================================
    // SUCURSAL → ALMACÉN
    // ============================================
    // Mapa: sucursal_id → almacen_id (viene del servidor)
    const sucursalAlmacenMap = @json($sucursales->pluck('almacen_id', 'id'));
    // Todas las opciones de almacén (guardadas al cargar)
    let todasOpcionesAlmacen = null;

    function filtrarAlmacenesPorSucursal(sucursalId) {
        const sel = document.getElementById('almacen_id');
        if (!todasOpcionesAlmacen) {
            // Guardar snapshot la primera vez
            todasOpcionesAlmacen = Array.from(sel.options).map(o => ({ value: o.value, text: o.text }));
        }

        // Restaurar todas las opciones
        sel.innerHTML = '';
        todasOpcionesAlmacen.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o.value;
            opt.textContent = o.text;
            sel.appendChild(opt);
        });

        if (!sucursalId) return;  // sin filtro, mostrar todos

        const almacenId = sucursalAlmacenMap[sucursalId];
        if (!almacenId) return;  // sucursal sin almacén asignado

        // Auto-seleccionar el almacén de la sucursal y ocultar el resto
        Array.from(sel.options).forEach(opt => {
            if (opt.value && String(opt.value) !== String(almacenId)) {
                opt.style.display = 'none';
            }
        });
        sel.value = almacenId;
        sel.dispatchEvent(new Event('change'));
    }

    function toggleCondicionPago(valor) {
        const div = document.getElementById('condicion_pago_div');
        const input = document.getElementById('condicion_pago');
        
        if (valor === 'credito') {
            div.style.display = 'block';
            input.disabled = false;
        } else {
            div.style.display = 'none';
            input.disabled = true;
            input.value = ''; // Limpiar valor cuando no es crédito
        }
    }
    // ============================================
    // FILTRO POR CATEGORÍA
    // ============================================
    function filtrarPorCategoria(categoriaId) {
        categoriaActual = categoriaId;
        
        // Actualizar estilos de los botones
        document.querySelectorAll('.categoria-filter').forEach(btn => {
            const btnCategoria = btn.dataset.categoria;
            if (btnCategoria == categoriaId) {
                btn.style.backgroundColor = '#1e3a8a';
                btn.style.color = 'white';
            } else {
                btn.style.backgroundColor = '#f3f4f6';
                btn.style.color = '#374151';
            }
        });
        
        // Filtrar productos según categoría
        if (productosOriginales && productosOriginales.length > 0) {
            let productosFiltrados = productosOriginales;
            if (categoriaId !== 'todos') {
                productosFiltrados = productosOriginales.filter(p => p.categoria_id == categoriaId);
            }
            
            if (productosFiltrados.length === 0) {
                resultadosDiv.innerHTML = '';
                sinResultadosDiv.classList.remove('hidden');
            } else {
                sinResultadosDiv.classList.add('hidden');
                mostrarResultadosConSeleccion(productosFiltrados);
            }   
        }
    }

    // Asignar eventos a los botones de categoría
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.categoria-filter').forEach(btn => {
            btn.addEventListener('click', function() {
                filtrarPorCategoria(this.dataset.categoria);
            });
        });
    });

    function toggleTipoCambio(valor) {
        const div = document.getElementById('tipo_cambio_div');
        if (valor === 'USD') {
            div.style.display = 'block';
            // Si el campo está vacío, cargar automáticamente
            const tc = document.getElementById('tipo_cambio');
            if (!tc.value) cargarTipoCambioSUNAT();
        } else {
            div.style.display = 'none';
        }
        actualizarSimbolosMoneda(valor);
        calcularTotales();
    }

    async function cargarTipoCambioSUNAT() {
        const btn = document.getElementById('btnCargarTC');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;

        try {
            const res = await fetch('{{ route("compras.tipo-cambio") }}').then(r => r.json());
            if (res.success) {
                // Usamos el precio de venta: es lo que pagamos al comprar dólares
                document.getElementById('tipo_cambio').value = res.venta;
                document.getElementById('tcCompra').textContent = res.compra;
                document.getElementById('tcVenta').textContent = res.venta;
                document.getElementById('tcFecha').textContent = res.fecha ? `(${res.fecha})` : '';
                document.getElementById('tcInfo').classList.remove('hidden');
                calcularTotales();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin conexión a SUNAT',
                    text: res.message,
                    confirmButtonColor: '#1e3a8a',
                });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'Ingresa el tipo de cambio manualmente.', confirmButtonColor: '#d33' });
        } finally {
            btn.innerHTML = '<i class="fas fa-sync-alt"></i>';
            btn.disabled = false;
        }
    }

    function actualizarSimbolosMoneda(moneda) {
        const simbolo = moneda === 'USD' ? '$' : 'S/';
        const thPrecio = document.getElementById('thPrecioUnit');
        if (thPrecio) thPrecio.textContent = `Precio Unit. (${simbolo})`;

        // Actualizar prefijos en filas existentes
        document.querySelectorAll('[id^="precio_prefix_"]').forEach(el => {
            el.textContent = simbolo;
        });

        // Recalcular subtotales con nuevo símbolo
        document.querySelectorAll('#detallesBody tr').forEach(row => {
            const match = row.id?.match(/producto_(\d+)/);
            if (match) calcularSubtotal(parseInt(match[1]));
        });
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
                <div id="variante_container_${idx}"></div>
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
                    <span class="absolute left-3 top-2 text-gray-500 text-sm" id="precio_prefix_${idx}">${document.getElementById('tipo_moneda').value === 'USD' ? '$' : 'S/'}</span>
                    <input type="number" name="detalles[${idx}][precio_unitario]"
                           id="precio_${idx}"
                           value="" min="0.01" step="0.01"
                           placeholder="0.00"
                           onfocus="if(this.value==='0'||this.value==='0.00')this.value=''"
                           onblur="if(this.value===''||parseFloat(this.value)===0){this.value='';}"
                           oninput="calcularSubtotal(${idx})"
                           onchange="calcularSubtotal(${idx})"
                           class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-sm"
                           required>
                </div>
            </td>
            <td class="px-4 py-3 font-semibold text-sm" id="subtotal_${idx}">${document.getElementById('tipo_moneda').value === 'USD' ? '$' : 'S/'} 0.00</td>
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

        // Limpiar selector de variante si existe
        const varianteContainer = document.getElementById(`variante_container_${index}`);
        if (varianteContainer) varianteContainer.innerHTML = '';

        // Asegurarse de restaurar el colspan cuando se deselecciona un producto
        const _productTd = select.closest('td');
        if (_productTd) _productTd.removeAttribute('colspan');
        const _marcaTd = marcaSelect.closest('td');
        const _modeloTd = modeloSelect.closest('td');
        const _colorTd = colorSelect.closest('td');
        if (_marcaTd)  _marcaTd.style.display  = '';
        if (_modeloTd) _modeloTd.style.display = '';
        if (_colorTd)  _colorTd.style.display  = '';

        if (!producto) {
            calcularSubtotal(index);
            return;
        }

        // ── LÓGICA DE VARIANTES ────────────────────────────────────────────────
        const productTd   = select.closest('td');
        const marcaTd     = marcaSelect.closest('td');
        const modeloTd    = modeloSelect.closest('td');
        const colorTd     = colorSelect.closest('td');

        if (producto.tiene_variantes && producto.variantes && producto.variantes.length > 0) {
            renderVarianteSelector(index, producto.variantes, producto.tipo_inventario);

            // Expandir celda de Producto para cubrir MARCA + MODELO + COLOR
            if (productTd) productTd.setAttribute('colspan', '4');
            // Ocultar las 3 celdas → con colspan=4 el layout queda alineado
            if (marcaTd)  marcaTd.style.display  = 'none';
            if (modeloTd) modeloTd.style.display = 'none';
            if (colorTd)  colorTd.style.display  = 'none';
        } else {
            // Restaurar celda de Producto a tamaño normal
            if (productTd) productTd.removeAttribute('colspan');
            if (marcaTd)  marcaTd.style.display  = '';
            if (modeloTd) modeloTd.style.display = '';
            if (colorTd)  colorTd.style.display  = '';

            marcaSelect.disabled = false;
            colorSelect.disabled = false;

            if (producto.marca_id) {
                marcaSelect.value = producto.marca_id;
                cambiarMarca(index, producto.modelo_id || null);
            }
            if (producto.color_id) colorSelect.value = producto.color_id;
        }

        calcularSubtotal(index);
    }

    /**
     * Renderiza el selector de variante dentro de la celda `variante_container_{index}`.
     */
    function renderVarianteSelector(index, variantes, tipoInventario) {
        const container = document.getElementById(`variante_container_${index}`);
        if (!container) return;

        const opciones = variantes.map(v => {
            const partes = [];
            if (v.color_nombre) partes.push(v.color_nombre);
            if (v.capacidad)    partes.push(v.capacidad);
            const label = partes.join(' / ') || 'Base';
            return `<option value="${v.id}"
                             data-color="${v.color_id || ''}"
                             data-color-nombre="${v.color_nombre || ''}"
                             data-capacidad="${v.capacidad || ''}"
                             data-stock="${v.stock_actual}"
                             data-tipo="${tipoInventario}">
                        ${label} — Stock: ${v.stock_actual}
                    </option>`;
        }).join('');

        container.innerHTML = `
            <div class="mt-2">
                <label class="block text-xs text-gray-500 mb-1 font-medium">
                    <i class="fas fa-layer-group mr-1 text-indigo-500"></i>Variante
                </label>
                <select name="detalles[${index}][variante_id]"
                        id="variante_select_${index}"
                        onchange="seleccionarVariante(${index})"
                        class="w-full px-2 py-1.5 border border-indigo-300 rounded-lg text-sm bg-indigo-50 focus:ring-2 focus:ring-indigo-400"
                        required>
                    <option value="">— Seleccione variante —</option>
                    ${opciones}
                </select>
                <p id="variante_stock_badge_${index}" class="text-xs mt-1 hidden"></p>
            </div>`;
    }

    function seleccionarVariante(index) {
        const varianteSelect = document.getElementById(`variante_select_${index}`);
        const opt = varianteSelect.selectedOptions[0];
        if (!opt || !opt.value) return;

        const stock  = parseInt(opt.dataset.stock) || 0;
        const tipo   = opt.dataset.tipo;
        const badge  = document.getElementById(`variante_stock_badge_${index}`);
        const btnIMEI = document.getElementById(`btn_imei_${index}`);

        // Actualizar campo color_id oculto si existe
        const colorHidden = document.querySelector(`[name="detalles[${index}][color_id]"]`);
        if (colorHidden) colorHidden.value = opt.dataset.color || '';

        // Badge de stock
        if (badge) {
            badge.classList.remove('hidden');
            badge.className = `text-xs mt-1 font-semibold ${stock > 0 ? 'text-green-600' : 'text-red-600'}`;
            badge.textContent = stock > 0 ? `✓ ${stock} en stock` : '✗ Sin stock';
        }

        // Habilitar IMEIs si aplica
        if (btnIMEI && tipo === 'serie') {
            btnIMEI.disabled = !opt.value;
        }
    }

    function cambiarMarca(index, preseleccionarModeloId = null) {
        const marcaSelect = document.getElementById(`marca_select_${index}`);
        const modeloSelect = document.getElementById(`modelo_select_${index}`);
        const colorSelect = document.getElementById(`color_${index}`);
        const btnIMEI = document.getElementById(`btn_imei_${index}`);

        modeloSelect.innerHTML = '<option value="">— Modelo —</option>';
        modeloSelect.disabled = true;
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

                // Pre-seleccionar modelo si se indicó (ej: al cargar producto existente)
                if (preseleccionarModeloId) {
                    modeloSelect.value = preseleccionarModeloId;
                    modeloSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
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

        if (!producto) return;

        // El botón de IMEIs requiere tipo 'serie' + modelo seleccionado
        if (producto.tipo_inventario === 'serie') {
            btnIMEI.disabled = !modeloSelect.value;
        }
    }

    function actualizarVistaIMEI(index) {
        const colorSelect   = document.getElementById(`color_${index}`);
        const btnIMEI       = document.getElementById(`btn_imei_${index}`);
        const productoSelect = document.getElementById(`producto_select_${index}`);
        const producto = catalogoProductos.find(p => p.id == productoSelect.value);

        // Solo habilitar el botón IMEI si el producto es de tipo 'serie' y tiene modelo
        if (producto && producto.tipo_inventario === 'serie') {
            const modeloSelect = document.getElementById(`modelo_select_${index}`);
            btnIMEI.disabled = !modeloSelect.value;
        }
        // Para productos regulares el botón queda siempre deshabilitado
    }

    function actualizarCantidad(index) {
        calcularSubtotal(index);
        actualizarInfoIMEI(index);
    }
    function actualizarCantidadProducto(productoId, cantidad) {
        if (productosSeleccionadosIds.has(productoId)) {
            cantidadesSeleccionadas[productoId] = parseInt(cantidad) || 1;
        }
        actualizarContadorUnidades();
    }


    function calcularSubtotal(index) {
        const cantidad = parseFloat(document.getElementById(`cantidad_${index}`).value) || 0;
        const precio   = parseFloat(document.getElementById(`precio_${index}`).value) || 0;
        const moneda   = document.getElementById('tipo_moneda').value;
        const simbolo  = moneda === 'USD' ? '$' : 'S/';
        document.getElementById(`subtotal_${index}`).innerText = `${simbolo} ${(cantidad * precio).toFixed(2)}`;
        calcularTotales();
    }

    function calcularTotales() {
        const moneda  = document.getElementById('tipo_moneda').value;
        const simbolo = moneda === 'USD' ? '$' : 'S/';
        const tc      = parseFloat(document.getElementById('tipo_cambio')?.value) || 0;

        // Suma bruta de (cantidad × precio) tal como el usuario los ingresó
        let sumaBruta = 0;
        document.querySelectorAll('#detallesBody tr').forEach(row => {
            const match = row.id?.match(/producto_(\d+)/);
            if (match) {
                const el = document.getElementById(`subtotal_${match[1]}`);
                if (el) sumaBruta += parseFloat(el.innerText.replace(/[^\d.]/g, '')) || 0;
            }
        });

        // Opciones de IGV
        const tipoOperacion  = document.getElementById('tipo_operacion').value;
        const incluyeIGV     = document.getElementById('incluir_igv').checked;
        const precioConIGV   = document.getElementById('precio_incluye_igv').checked;

        let subtotalNeto = sumaBruta;
        let igv          = 0;
        let total        = sumaBruta;

        if (tipoOperacion === '01' && incluyeIGV) {
            if (precioConIGV) {
                // El precio YA incluye IGV → extraer
                subtotalNeto = sumaBruta / 1.18;
                igv          = sumaBruta - subtotalNeto;
                total        = sumaBruta;               // precio ingresado = total final
            } else {
                // El precio NO incluye IGV → sumar
                subtotalNeto = sumaBruta;
                igv          = sumaBruta * 0.18;
                total        = sumaBruta + igv;
            }
        }

        // Mostrar etiqueta de subtotal
        const lblSinIgv = document.getElementById('lblSinIgv');
        if (precioConIGV && tipoOperacion === '01' && incluyeIGV) {
            lblSinIgv.classList.remove('hidden');
        } else {
            lblSinIgv.classList.add('hidden');
        }

        // Mostrar valores
        document.getElementById('subtotal').innerText = `${simbolo} ${subtotalNeto.toFixed(2)}`;
        document.getElementById('igv').innerText      = `${simbolo} ${igv.toFixed(2)}`;
        document.getElementById('total').innerText    = `${simbolo} ${total.toFixed(2)}`;

        // Equivalente en PEN solo si moneda es USD y hay tipo de cambio
        const eqDiv = document.getElementById('equivalentePEN');
        if (moneda === 'USD' && tc > 0) {
            const totalPEN = total * tc;
            document.getElementById('totalPEN').textContent = `S/ ${totalPEN.toFixed(2)}`;
            document.getElementById('tcUsado').textContent  = `1 $ = S/ ${tc.toFixed(3)}`;
            eqDiv.classList.remove('hidden');
        } else {
            eqDiv.classList.add('hidden');
        }

        // Deshabilitar opciones IGV si tipo de operación no es gravado
        const igvCheckbox = document.getElementById('incluir_igv');
        const igvLabel    = igvCheckbox.parentElement;
        const toggleWrap  = document.getElementById('togglePrecioIgvWrap');
        if (tipoOperacion !== '01') {
            igvCheckbox.checked = false;
            igvLabel.classList.add('opacity-40', 'pointer-events-none');
            toggleWrap.classList.add('opacity-40', 'pointer-events-none');
        } else {
            igvLabel.classList.remove('opacity-40', 'pointer-events-none');
            toggleWrap.classList.remove('opacity-40', 'pointer-events-none');
        }
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
                if (!response.ok) throw new Error(`Error ${response.status} del servidor`);
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    // La sesión expiró y el servidor redirigió a la página de login
                    window.location.href = '/login';
                    throw new Error('Sesión expirada. Redirigiendo al login...');
                }
                return response.json();
            })
            .then(productos => {
                cargandoDiv.classList.add('hidden');
                
                // Guardar TODOS los productos originales
                productosOriginales = productos;

                // Actualizar el catálogo local con los datos frescos (variantes incluidas)
                productos.forEach(p => {
                    const idx = catalogoProductos.findIndex(c => c.id === p.id);
                    if (idx !== -1) {
                        catalogoProductos[idx] = { ...catalogoProductos[idx], ...p };
                    } else {
                        catalogoProductos.push(p);
                    }
                });

                // Filtrar por categoría si no es 'todos'
                let productosFiltrados = productos;
                if (categoriaActual !== 'todos') {
                    productosFiltrados = productos.filter(p => p.categoria_id == categoriaActual);
                }

                if (productosFiltrados.length === 0) {
                    sinResultadosDiv.classList.remove('hidden');
                    return;
                }

                // Mostrar resultados manteniendo selecciones
                mostrarResultadosConSeleccion(productosFiltrados);
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
    // Actualizar la función mostrarResultados para incluir checkboxes y cantidad
    function mostrarResultados(productos) {
        productosFiltrados = productos;
        
        resultadosDiv.innerHTML = productos.map(p => {
            const cantidadGuardada = cantidadesSeleccionadas[p.id] || 1;
            
            return `
                <div class="bg-white border-2 border-gray-200 rounded-xl p-4 hover:border-blue-500 hover:shadow-lg transition-all group">
                    <div class="flex items-start gap-3">
                        <div class="flex items-center mt-1">
                            <input type="checkbox"
                                class="producto-checkbox w-5 h-5 rounded border-gray-300 text-blue-900 focus:ring-blue-500"
                                value="${p.id}"
                                data-producto-id="${p.id}"
                                onchange="actualizarSeleccion(this, ${p.id})"
                                ${productosSeleccionadosIds.has(p.id) ? 'checked' : ''}>
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
                            <!-- NUEVO: Selector de cantidad -->
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs text-gray-500">Cantidad:</span>
                                <input type="number" 
                                    class="cantidad-input w-20 px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    value="${cantidadGuardada}"
                                    min="1"
                                    data-producto-id="${p.id}"
                                    onchange="actualizarCantidadProducto(${p.id}, this.value)"
                                    ${productosSeleccionadosIds.has(p.id) ? '' : 'disabled'}>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Agregar el botón de crear producto al final
        resultadosDiv.innerHTML += `
            <div class="col-span-full mt-3 pt-3 border-t border-gray-200 text-center">
                <button type="button"
                        onclick="abrirModalCrearProducto(document.getElementById('buscadorProductos').value)"
                        class="inline-flex items-center text-sm text-green-700 hover:text-green-900 transition font-medium">
                    <i class="fas fa-plus-circle mr-1"></i>
                    ¿No está el producto? Créalo aquí
                </button>
            </div>`;
    }
    function mostrarResultadosConSeleccion(productos) {
        resultadosDiv.innerHTML = productos.map(p => {
            // ── Producto CON variantes ────────────────────────────────────────
            if (p.tiene_variantes && p.variantes && p.variantes.length > 0) {
                // Registrar producto en el registry (evita JSON en handlers inline)
                productosModal[p.id] = p;
                const variantesHTML = p.variantes.map(v => {
                    const key  = `${p.id}_${v.id}`;
                    variantesModal[key] = v;  // registrar variante
                    const sel  = !!variantesSeleccionadas[key];
                    const cant = sel ? variantesSeleccionadas[key].cantidad : 1;
                    const label = [v.color_nombre, v.capacidad].filter(Boolean).join(' / ') || 'Base';
                    const dot = v.color_hex
                        ? `<span class="w-3 h-3 rounded-full border border-white shadow shrink-0 inline-block" style="background-color:${v.color_hex}"></span>`
                        : '<i class="fas fa-circle text-gray-300 text-xs shrink-0"></i>';
                    const stockClr = v.stock_actual > 0 ? 'text-green-600' : 'text-red-500';
                    const stockTxt = v.stock_actual > 0 ? `${v.stock_actual} u.` : 'Sin stock';
                    const bordClr  = sel ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 bg-gray-50';
                    return `
                        <div id="vcard_${key}" class="flex items-center gap-2 px-2 py-1.5 rounded-lg border ${bordClr} transition-all">
                            <input type="checkbox"
                                   class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-400"
                                   ${sel ? 'checked' : ''}
                                   onchange="actualizarSeleccionVariante(this,${p.id},${v.id})">
                            ${dot}
                            <span class="text-xs font-medium text-gray-800 flex-1 truncate">${label}</span>
                            <span class="text-xs ${stockClr} font-medium shrink-0">${stockTxt}</span>
                            <input type="number"
                                   class="w-12 px-1 py-0.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-indigo-400"
                                   value="${cant}" min="1"
                                   onchange="actualizarCantidadVariante('${key}', this.value)"
                                   ${sel ? '' : 'disabled'}>
                        </div>`;
                }).join('');

                const totalSelVariantes = p.variantes.filter(v => !!variantesSeleccionadas[`${p.id}_${v.id}`]).length;
                const badgeSel = totalSelVariantes > 0
                    ? `<span class="text-xs px-2 py-0.5 bg-indigo-600 text-white rounded-full font-semibold">${totalSelVariantes} sel.</span>` : '';

                return `
                    <div class="bg-white border-2 border-gray-200 rounded-xl p-3 hover:border-indigo-300 hover:shadow-lg transition-all">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-mobile-alt text-indigo-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 text-sm truncate">${p.nombre}</h4>
                                <p class="text-xs text-gray-500">${p.marca || ''} ${p.modelo ? '· ' + p.modelo : ''}</p>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full">
                                    <i class="fas fa-layer-group mr-1"></i>${p.variantes.length} var.
                                </span>
                                ${badgeSel}
                            </div>
                        </div>
                        <div class="space-y-1 border-t border-gray-100 pt-2">
                            ${variantesHTML}
                        </div>
                    </div>`;

            // ── Producto SIN variantes ────────────────────────────────────────
            } else {
                const sel  = productosSeleccionadosIds.has(p.id);
                const cant = cantidadesSeleccionadas[p.id] || 1;
                return `
                    <div class="bg-white border-2 border-gray-200 rounded-xl p-4 hover:border-blue-500 hover:shadow-lg transition-all group">
                        <div class="flex items-start gap-3">
                            <input type="checkbox"
                                   class="w-5 h-5 rounded border-gray-300 text-blue-900 focus:ring-blue-500 mt-1 shrink-0"
                                   value="${p.id}"
                                   onchange="actualizarSeleccion(this, ${p.id})"
                                   ${sel ? 'checked' : ''}>
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-box text-blue-900"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 text-sm truncate">${p.nombre}</h4>
                                <p class="text-xs text-gray-500">${p.marca || ''} ${p.modelo ? '· ' + p.modelo : ''}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 rounded-full text-gray-600">${p.categoria || '—'}</span>
                                    ${p.tipo_inventario === 'serie'
                                        ? '<span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full"><i class="fas fa-microchip mr-1"></i>IMEI</span>'
                                        : '<span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full"><i class="fas fa-boxes mr-1"></i>Stock</span>'}
                                </div>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-xs text-gray-500">Cant.:</span>
                                    <input type="number"
                                           class="cantidad-input w-16 px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                           value="${cant}" min="1"
                                           data-producto-id="${p.id}"
                                           onchange="actualizarCantidadProducto(${p.id}, this.value)"
                                           ${sel ? '' : 'disabled'}>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }
        }).join('');

        // Botón crear producto
        resultadosDiv.innerHTML += `
            <div class="col-span-full mt-3 pt-3 border-t border-gray-200 text-center">
                <button type="button"
                        onclick="abrirModalCrearProducto(document.getElementById('buscadorProductos').value)"
                        class="inline-flex items-center text-sm text-green-700 hover:text-green-900 transition font-medium">
                    <i class="fas fa-plus-circle mr-1"></i>¿No está el producto? Créalo aquí
                </button>
            </div>`;
    }
    function actualizarSeleccion(checkbox, productoId) {
        const cantidadInput = document.querySelector(`.cantidad-input[data-producto-id="${productoId}"]`);
        
        if (checkbox.checked) {
            productosSeleccionadosIds.add(productoId);
            cantidadInput.disabled = false;
            const cantidad = parseInt(cantidadInput.value) || 1;
            cantidadesSeleccionadas[productoId] = cantidad;
        } else {
            productosSeleccionadosIds.delete(productoId);
            cantidadInput.disabled = true;
            delete cantidadesSeleccionadas[productoId];
        }
        
        document.getElementById('productosSeleccionadosCount').innerText = 
            `${productosSeleccionadosIds.size} productos seleccionados`;
        actualizarContadorUnidades();
    }

    // Función para agregar productos seleccionados (simples + variantes)
    function agregarProductosSeleccionados() {
        const tieneSimples   = productosSeleccionadosIds.size > 0;
        const tieneVariantes = Object.keys(variantesSeleccionadas).length > 0;

        if (!tieneSimples && !tieneVariantes) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Selecciona al menos un producto o variante'
            });
            return;
        }

        // Capturar estado antes de cerrar el modal
        const cantidadesCapturadas = { ...cantidadesSeleccionadas };
        const variantesCapturadas  = { ...variantesSeleccionadas };
        const ids = Array.from(productosSeleccionadosIds);

        // Cerrar modal (limpia cantidadesSeleccionadas y variantesSeleccionadas)
        cerrarModalProductos();

        // ── Agregar variantes directamente (sin fetch — datos ya disponibles) ──
        Object.values(variantesCapturadas).forEach(({ producto, varianteId, cantidad }) => {
            agregarFilaConVariante(producto, varianteId, cantidad);
        });

        // Si no hay productos simples, mostrar éxito de inmediato
        if (ids.length === 0) {
            const total = Object.keys(variantesCapturadas).length;
            Swal.fire({
                icon: 'success',
                title: 'Variantes agregadas',
                text: `${total} variante(s) agregadas correctamente`,
                timer: 1500,
                showConfirmButton: false
            });
            return;
        }

        // ── Agregar productos simples (requiere fetch) ──
        Swal.fire({
            title: 'Agregando productos...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const totalIds   = ids.length;
        let procesados   = 0;

        ids.forEach(id => {
            fetch(`/compras/producto/${id}`)
                .then(response => {
                    if (!response.ok) throw new Error('Error al cargar producto');
                    return response.json();
                })
                .then(producto => {
                    const cantidad = cantidadesCapturadas[id] || 1;
                    agregarProductoConCantidad(producto, cantidad);
                    procesados++;
                    if (procesados === totalIds) {
                        Swal.close();
                        const total = totalIds + Object.keys(variantesCapturadas).length;
                        Swal.fire({
                            icon: 'success',
                            title: 'Productos agregados',
                            text: `${total} elemento(s) agregados correctamente`,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    procesados++;
                    if (procesados === totalIds) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudieron agregar algunos productos'
                        });
                    }
                });
        });
    }

 // NUEVA FUNCIÓN para agregar producto con cantidad específica (N filas)
    function agregarProductoConCantidad(producto, cantidad) {
        const filas = parseInt(cantidad) || 1;
        
        // Validar que la cantidad sea válida
        if (filas < 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Cantidad inválida',
                text: 'La cantidad debe ser al menos 1',
                confirmButtonColor: '#1e3a8a'
            });
            return;
        }
        
        // Crear las filas
        for (let i = 0; i < filas; i++) {
            agregarProducto();
            const index = contadorProductos - 1;

            const selectProducto = document.getElementById(`producto_select_${index}`);
            const inputCantidad = document.getElementById(`cantidad_${index}`);
            const inputPrecio = document.getElementById(`precio_${index}`);

            if (selectProducto) {
                selectProducto.value = producto.id;
                inputCantidad.value = 1; // Cada fila es 1 unidad

                const event = new Event('change', { bubbles: true });
                selectProducto.dispatchEvent(event);
            }
        }
        
        // Mostrar mensaje de éxito
        Swal.fire({
            icon: 'success',
            title: `${filas} unidades agregadas`,
            text: `Se agregaron ${filas} filas para ${producto.nombre}`,
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
    // Limpiar selección al cerrar modal
    function cerrarModalProductos() {
        modalProductos.classList.add('hidden');
        modalProductos.classList.remove('flex');
        buscador.value = '';
        resultadosDiv.innerHTML = '';
        productosSeleccionadosIds.clear();
        cantidadesSeleccionadas  = {};
        variantesSeleccionadas   = {};
        productosModal           = {};
        variantesModal           = {};
        document.getElementById('productosSeleccionadosCount').innerText = '0 productos seleccionados';
        document.getElementById('totalUnidadesCount').innerText = '(0 unidades)';

        // Resetear filtro a 'todos'
        if (document.querySelector('.categoria-filter.active')) {
            filtrarPorCategoria('todos');
        }
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
        const selectMarca = document.getElementById(`marca_select_${index}`);
        const selectModelo = document.getElementById(`modelo_select_${index}`);
        const selectColor = document.getElementById(`color_${index}`);
        
        if (selectProducto) {
            selectProducto.value = producto.id;

            const event = new Event('change', { bubbles: true });
            // Disparar change en producto — esto llama a cargarDetallesProducto,
            // que ya se encarga de seleccionar marca, cargar modelos y pre-seleccionar modelo/color
            selectProducto.dispatchEvent(event);
        }
    }

    // ============================================
    // FUNCIONES DEL MODAL DE IMEIs
    // ============================================
    function gestionarIMEIs(index) {
        const select        = document.getElementById(`producto_select_${index}`);
        const modeloSelect  = document.getElementById(`modelo_select_${index}`);
        const colorSelect   = document.getElementById(`color_${index}`);
        const varianteSelect = document.getElementById(`variante_select_${index}`);
        const cantidad      = parseInt(document.getElementById(`cantidad_${index}`).value) || 1;

        if (!select.value) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Primero seleccione un producto' });
            return;
        }

        const producto = catalogoProductos.find(p => p.id == select.value);
        let subtitulo = '';

        // ── Producto CON variantes ────────────────────────────────────────
        if (varianteSelect) {
            if (!varianteSelect.value) {
                Swal.fire({ icon: 'warning', title: 'Atención', text: 'Primero seleccione una variante' });
                return;
            }
            const opt = varianteSelect.selectedOptions[0];
            subtitulo = opt.text.split('—')[0].trim();   // "Negro / 128GB" parte del label

        // ── Producto SIN variantes (flujo clásico) ────────────────────────
        } else {
            if (!modeloSelect.value) {
                Swal.fire({ icon: 'warning', title: 'Atención', text: 'Primero seleccione un modelo' });
                return;
            }
            const modeloNombre = modeloSelect.options[modeloSelect.selectedIndex].text;
            const colorNombre  = colorSelect.value ? colorSelect.options[colorSelect.selectedIndex].text : '';
            subtitulo = colorNombre ? `${modeloNombre} · ${colorNombre}` : modeloNombre;
        }

        document.getElementById('imeiModalTitle').innerHTML = `
            <i class="fas fa-microchip mr-3"></i>
            ${producto?.nombre || ''} · ${subtitulo}
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
    function actualizarContadorUnidades() {
        const simplesTotal   = Object.values(cantidadesSeleccionadas).reduce((a, b) => a + b, 0);
        const variantesTotal = Object.values(variantesSeleccionadas).reduce((a, s) => a + (s.cantidad || 1), 0);
        document.getElementById('totalUnidadesCount').innerText = `(${simplesTotal + variantesTotal} unidades)`;
    }

    function actualizarSeleccionVariante(checkbox, prodId, varId) {
        const key     = `${prodId}_${varId}`;
        const producto = productosModal[prodId];
        const variante = variantesModal[key];
        const card = document.getElementById(`vcard_${key}`);
        const cantidadInput = card ? card.querySelector('input[type="number"]') : null;

        if (checkbox.checked) {
            variantesSeleccionadas[key] = {
                productoId: producto.id,
                varianteId: variante.id,
                cantidad:   parseInt(cantidadInput?.value) || 1,
                producto,
                variante,
            };
            if (cantidadInput) cantidadInput.disabled = false;
            if (card) {
                card.classList.replace('border-gray-200', 'border-indigo-400');
                card.classList.replace('bg-gray-50',    'bg-indigo-50');
            }
        } else {
            delete variantesSeleccionadas[key];
            if (cantidadInput) cantidadInput.disabled = true;
            if (card) {
                card.classList.replace('border-indigo-400', 'border-gray-200');
                card.classList.replace('bg-indigo-50',     'bg-gray-50');
            }
        }
        actualizarContadorUnidades();
    }

    function actualizarCantidadVariante(key, cantidad) {
        if (variantesSeleccionadas[key]) {
            variantesSeleccionadas[key].cantidad = parseInt(cantidad) || 1;
        }
        actualizarContadorUnidades();
    }

    /**
     * Agrega `cantidad` filas en la tabla de detalles, pre-seleccionando
     * el producto y la variante indicados.
     */
    function agregarFilaConVariante(producto, varianteId, cantidad) {
        const filas = parseInt(cantidad) || 1;
        for (let i = 0; i < filas; i++) {
            agregarProducto();
            const index = contadorProductos - 1;

            const selectProducto = document.getElementById(`producto_select_${index}`);
            if (!selectProducto) continue;

            selectProducto.value = producto.id;
            // Disparar change → cargarDetallesProducto (síncrono) renderiza el selector de variante
            selectProducto.dispatchEvent(new Event('change', { bubbles: true }));

            // Pre-seleccionar la variante en el dropdown recién renderizado
            const varianteSelect = document.getElementById(`variante_select_${index}`);
            if (varianteSelect) {
                varianteSelect.value = varianteId;
                seleccionarVariante(index);   // actualiza badge de stock y botón IMEI
            }

            const inputCantidad = document.getElementById(`cantidad_${index}`);
            if (inputCantidad) inputCantidad.value = 1;   // cada fila = 1 unidad

            calcularSubtotal(index);
        }
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

        // Validar IMEIs
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

            // Eliminar inputs ocultos anteriores
            document.querySelectorAll(`[data-imei-row="${idx}"]`).forEach(el => el.remove());

            // Guardar en objeto
            imeisPorFila[idx] = imeis;

            // Crear inputs ocultos con estructura correcta
            imeis.forEach((imei, i) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `detalles[${idx}][imeis][${i}][codigo_imei]`;
                input.setAttribute('data-imei-row', idx);
                input.value = imei;
                document.getElementById('compraForm').appendChild(input);
            });

            // Actualizar info en la tabla
            actualizarInfoIMEI(idx);
            
            // CERRAR MODAL
            cerrarModalIMEI();
            
            // MOSTRAR MENSAJE DE ÉXITO
            Swal.fire({
                icon: 'success',
                title: '¡IMEIs guardados!',
                text: `${imeis.length} IMEI(s) registrados correctamente`,
                timer: 2000,
                showConfirmButton: false,
                position: 'center',
                background: '#ffffff',
                iconColor: '#10b981'
            });
        } else {
            cerrarModalIMEI();
        }
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
    function importarIMEIDesdeArchivo(index) {
        // Crear input file oculto
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
                
                const inputs = document.querySelectorAll('.imei-input');
                let importados = 0;
                
                lineas.forEach((linea, idx) => {
                    if (idx < inputs.length) {
                        const partes = linea.split(',');
                        const imei = partes[0].trim().substring(0, 15);
                        
                        if (imei.length === 15 && /^\d+$/.test(imei)) {
                            inputs[idx].value = imei;
                            validarIMEIInput(inputs[idx]);
                            importados++;
                            
                            // Si hay serie, guardarla (opcional)
                            if (partes[1]) {
                                // Aquí podrías guardar la serie si tienes campo para eso
                            }
                        }
                    }
                });
                
                Swal.fire({
                    icon: 'success',
                    title: 'Importación completada',
                    text: `Se importaron ${importados} de ${inputs.length} IMEIs válidos`,
                    timer: 2000,
                    showConfirmButton: false
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

        // Recalcular totales al cambiar checkbox IGV
        document.getElementById('incluir_igv').addEventListener('change', calcularTotales);

        // Recalcular totales al cambiar tipo de operación SUNAT
        document.getElementById('tipo_operacion').addEventListener('change', calcularTotales);

        // Recalcular totales al cambiar toggle "precio incluye IGV"
        document.getElementById('precio_incluye_igv').addEventListener('change', calcularTotales);

        // Ejecutar cálculo inicial para reflejar el tipo de operación por defecto
        calcularTotales();

        // Validación del proveedor al enviar el formulario
        document.getElementById('compraForm').addEventListener('submit', function(e) {
            if (!document.getElementById('proveedor_id').value) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Proveedor requerido',
                    text: 'Selecciona un proveedor antes de continuar.',
                    confirmButtonColor: '#1e3a8a'
                });
                document.getElementById('buscar_proveedor')?.focus();
                return false;
            }
            // Validar que haya al menos un producto
            if (contadorProductos === 0 || !document.querySelector('#detallesBody tr')) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Productos requeridos',
                    text: 'Debes agregar al menos un producto a la compra.',
                    confirmButtonColor: '#1e3a8a'
                });
                return false;
            }
            
            return true; // Todo OK, se envía
        });
    });

// ============================================
// MODAL CREAR PRODUCTO RÁPIDO
// ============================================
function abrirModalCrearProducto(terminoBusqueda) {
    document.getElementById('np_nombre').value = terminoBusqueda || '';
    document.getElementById('np_categoria').value = '';
    document.getElementById('np_tipo').value = 'regular';
    const marcaSelect = document.getElementById('np_marca');
    marcaSelect.innerHTML = '<option value="">Seleccionar categoría primero...</option>';
    marcaSelect.disabled = true;
    const modeloSelect = document.getElementById('np_modelo');
    modeloSelect.innerHTML = '<option value="">Seleccionar marca primero...</option>';
    modeloSelect.disabled = true;
    document.getElementById('np_color').value = '';
    document.getElementById('np_tiene_variantes').checked = false;
    document.getElementById('np_codigo_barras').value = '';
    document.getElementById('np_color_section').classList.remove('hidden');
    toggleModeloLabel();

    const modal = document.getElementById('modalCrearProducto');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => document.getElementById('np_nombre').focus(), 100);
}

function toggleVariantesNuevoProducto() {
    const tieneVariantes = document.getElementById('np_tiene_variantes').checked;
    document.getElementById('np_color_section').classList.toggle('hidden', tieneVariantes);
}

function toggleModeloLabel() {
    const tipo = document.getElementById('np_tipo')?.value;
    const req  = document.getElementById('np_modelo_req_label');
    const opt  = document.getElementById('np_modelo_opt_label');
    if (tipo === 'serie') {
        req?.classList.remove('hidden');
        opt?.classList.add('hidden');
    } else {
        req?.classList.add('hidden');
        opt?.classList.remove('hidden');
    }
}

async function crearMarcaRapida() {
    const categoriaId = document.getElementById('np_categoria').value;
    if (!categoriaId) {
        Swal.fire({ icon: 'warning', title: 'Selecciona categoría', text: 'Primero selecciona una categoría antes de crear una marca.', confirmButtonColor: '#1e3a8a' });
        return;
    }
    const { value: nombre } = await Swal.fire({
        title: 'Nueva Marca',
        input: 'text',
        inputPlaceholder: 'Nombre de la marca...',
        showCancelButton: true,
        confirmButtonText: 'Crear',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#1e3a8a',
        inputValidator: v => !v.trim() ? 'El nombre es obligatorio' : null,
    });
    if (!nombre) return;

    const res = await fetch('{{ route("catalogo.marcas.rapida") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ nombre: nombre.trim(), categoria_id: categoriaId }),
    }).then(r => r.json());

    if (!res.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'No se pudo crear la marca.', confirmButtonColor: '#d33' });
        return;
    }
    const marcaSelect = document.getElementById('np_marca');
    const opt = document.createElement('option');
    opt.value = res.id; opt.text = res.nombre; opt.selected = true;
    marcaSelect.appendChild(opt);
    marcaSelect.disabled = false;
    cargarModelosNuevoProducto();
    Swal.fire({ icon: 'success', title: `Marca "${res.nombre}" creada`, timer: 1500, showConfirmButton: false });
}

async function crearModeloRapido() {
    const marcaId = document.getElementById('np_marca').value;
    if (!marcaId) {
        Swal.fire({ icon: 'warning', title: 'Selecciona marca', text: 'Primero selecciona una marca antes de crear un modelo.', confirmButtonColor: '#1e3a8a' });
        return;
    }
    const { value: nombre } = await Swal.fire({
        title: 'Nuevo Modelo',
        input: 'text',
        inputPlaceholder: 'Nombre del modelo...',
        showCancelButton: true,
        confirmButtonText: 'Crear',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#1e3a8a',
        inputValidator: v => !v.trim() ? 'El nombre es obligatorio' : null,
    });
    if (!nombre) return;

    const res = await fetch('{{ route("catalogo.modelos.rapida") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ nombre: nombre.trim(), marca_id: marcaId }),
    }).then(r => r.json());

    if (!res.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'No se pudo crear el modelo.', confirmButtonColor: '#d33' });
        return;
    }
    const modeloSelect = document.getElementById('np_modelo');
    const opt = document.createElement('option');
    opt.value = res.id; opt.text = res.nombre; opt.selected = true;
    modeloSelect.appendChild(opt);
    modeloSelect.disabled = false;
    Swal.fire({ icon: 'success', title: `Modelo "${res.nombre}" creado`, timer: 1500, showConfirmButton: false });
}

async function crearColorRapido() {
    const { value: nombre } = await Swal.fire({
        title: 'Nuevo Color',
        input: 'text',
        inputPlaceholder: 'Nombre del color...',
        showCancelButton: true,
        confirmButtonText: 'Crear',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#1e3a8a',
        inputValidator: v => !v.trim() ? 'El nombre es obligatorio' : null,
    });
    if (!nombre) return;

    const res = await fetch('{{ route("catalogo.colores.rapida") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ nombre: nombre.trim() }),
    }).then(r => r.json());

    if (!res.success) {
        Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'No se pudo crear el color.', confirmButtonColor: '#d33' });
        return;
    }
    const colorSelect = document.getElementById('np_color');
    const opt = document.createElement('option');
    opt.value = res.id; opt.text = res.nombre; opt.selected = true;
    colorSelect.appendChild(opt);
    Swal.fire({ icon: 'success', title: `Color "${res.nombre}" creado`, timer: 1500, showConfirmButton: false });
}

function cerrarModalCrearProducto() {
    const modal = document.getElementById('modalCrearProducto');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function cargarMarcasNuevoProducto() {
    const categoriaId = document.getElementById('np_categoria').value;
    const marcaSelect = document.getElementById('np_marca');
    const modeloSelect = document.getElementById('np_modelo');

    modeloSelect.innerHTML = '<option value="">Seleccionar marca primero...</option>';
    modeloSelect.disabled = true;

    if (!categoriaId) {
        marcaSelect.innerHTML = '<option value="">Seleccionar categoría primero...</option>';
        marcaSelect.disabled = true;
        return;
    }

    marcaSelect.innerHTML = '<option value="">Cargando...</option>';
    marcaSelect.disabled = true;

    fetch(`/catalogo/marcas-por-categoria/${categoriaId}`)
        .then(r => r.json())
        .then(marcas => {
            marcaSelect.disabled = false;
            marcaSelect.innerHTML = '<option value="">Seleccionar marca...</option>' +
                marcas.map(m => `<option value="${m.id}">${m.nombre}</option>`).join('');
        })
        .catch(() => {
            marcaSelect.disabled = false;
            marcaSelect.innerHTML = '<option value="">Error al cargar</option>';
        });
}

function cargarModelosNuevoProducto() {
    const marcaId = document.getElementById('np_marca').value;
    const modeloSelect = document.getElementById('np_modelo');

    if (!marcaId) {
        modeloSelect.innerHTML = '<option value="">Seleccionar marca primero...</option>';
        modeloSelect.disabled = true;
        return;
    }

    modeloSelect.innerHTML = '<option value="">Cargando...</option>';
    modeloSelect.disabled = true;

    fetch(`/catalogo/modelos-por-marca/${marcaId}`)
        .then(r => r.json())
        .then(modelos => {
            modeloSelect.disabled = false;
            modeloSelect.innerHTML = '<option value="">Seleccionar modelo...</option>' +
                modelos.map(m => `<option value="${m.id}">${m.nombre}</option>`).join('');
        })
        .catch(() => {
            modeloSelect.disabled = false;
            modeloSelect.innerHTML = '<option value="">Error al cargar</option>';
        });
}

function guardarNuevoProducto() {
    const nombre          = document.getElementById('np_nombre').value.trim();
    const categoriaId     = document.getElementById('np_categoria').value;
    const marcaId         = document.getElementById('np_marca').value;
    const modeloId        = document.getElementById('np_modelo').value;
    const colorId         = document.getElementById('np_color').value;
    const tipo            = document.getElementById('np_tipo').value;
    const tieneVariantes  = document.getElementById('np_tiene_variantes').checked;
    const codigoBarras    = document.getElementById('np_codigo_barras').value.trim();

    if (!nombre) {
        Swal.fire({ icon: 'warning', title: 'Falta el nombre', text: 'Ingresa el nombre del producto.', confirmButtonColor: '#1e3a8a' });
        document.getElementById('np_nombre').focus();
        return;
    }
    if (!categoriaId) {
        Swal.fire({ icon: 'warning', title: 'Falta la categoría', text: 'Selecciona una categoría.', confirmButtonColor: '#1e3a8a' });
        return;
    }
    if (!marcaId) {
        Swal.fire({ icon: 'warning', title: 'Falta la marca', text: 'Selecciona una marca.', confirmButtonColor: '#1e3a8a' });
        return;
    }
    if (tipo === 'serie' && !modeloId) {
        Swal.fire({ icon: 'warning', title: 'Falta el modelo', text: 'Para productos con IMEI el modelo es obligatorio.', confirmButtonColor: '#1e3a8a' });
        return;
    }

    const btn = document.getElementById('btn_guardar_nuevo_producto');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';

    fetch('{{ route("compras.crear-producto-rapido") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            nombre,
            categoria_id:    categoriaId,
            marca_id:        marcaId,
            modelo_id:       modeloId,
            color_id:        tieneVariantes ? null : (colorId || null),
            tipo_inventario: tipo,
            tiene_variantes: tieneVariantes,
            codigo_barras:   codigoBarras || null,
        }),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-2"></i>Crear y Agregar';

        if (!data.success) {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo crear el producto.', confirmButtonColor: '#d33' });
            return;
        }

        // Agregar al catálogo local para que agregarProductoConDatos lo encuentre
        catalogoProductos.push({
            id:              data.id,
            nombre:          data.nombre,
            tipo_inventario: data.tipo_inventario,
            categoria:       data.categoria,
            marca_id:        data.marca_id,
            marca:           data.marca,
            modelo_id:       data.modelo_id,
            modelo:          data.modelo,
            requiere_imei:   data.requiere_imei,
            tiene_variantes: data.tiene_variantes || false,
            variantes:       data.variantes || [],
        });

        cerrarModalCrearProducto();
        cerrarModalProductos();
        agregarProductoConDatos(data);

        Swal.fire({
            icon: 'success',
            title: '¡Producto creado!',
            text: `"${data.nombre}" fue creado y agregado a la compra.`,
            timer: 2000,
            showConfirmButton: false,
        });
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-2"></i>Crear y Agregar';
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar al servidor.', confirmButtonColor: '#d33' });
    });
}
</script>

<!-- ============================================================ -->
<!-- MODAL: CREAR PRODUCTO RÁPIDO                                 -->
<!-- ============================================================ -->
<div id="modalCrearProducto"
     class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">

        <!-- Header -->
        <div class="bg-gradient-to-r from-green-700 to-green-600 px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <h3 class="text-lg font-bold text-white flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>
                Crear Producto Rápido
            </h3>
            <button type="button" onclick="cerrarModalCrearProducto()"
                    class="text-white hover:text-green-200 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">

            <!-- Nombre -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nombre del producto <span class="text-red-500">*</span>
                </label>
                <input type="text" id="np_nombre"
                       class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-100 transition"
                       placeholder="Ej: iPhone 15 Pro Max">
            </div>

            <!-- Categoría + Tipo inventario -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Categoría <span class="text-red-500">*</span>
                    </label>
                    <select id="np_categoria" onchange="cargarMarcasNuevoProducto()"
                            class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-100 transition">
                        <option value="">Seleccionar...</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo <span class="text-red-500">*</span>
                    </label>
                    <select id="np_tipo" onchange="toggleModeloLabel()"
                            class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-100 transition">
                        <option value="regular">Regular (stock)</option>
                        <option value="serie">Serie (IMEI)</option>
                    </select>
                </div>
            </div>

            <!-- Marca -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Marca <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <select id="np_marca" onchange="cargarModelosNuevoProducto()"
                            class="flex-1 px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-100 transition">
                        <option value="">Seleccionar categoría primero...</option>
                    </select>
                    <button type="button" onclick="crearMarcaRapida()"
                            title="Nueva marca"
                            class="px-3 py-2 bg-blue-50 text-blue-700 border-2 border-blue-200 rounded-xl hover:bg-blue-100 transition shrink-0">
                        <i class="fas fa-plus text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Modelo -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Modelo
                    <span id="np_modelo_req_label" class="text-red-500">*</span>
                    <span id="np_modelo_opt_label" class="text-gray-400 text-xs font-normal hidden">(opcional)</span>
                </label>
                <div class="flex gap-2">
                    <select id="np_modelo"
                            class="flex-1 px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-100 transition">
                        <option value="">Seleccionar marca primero...</option>
                    </select>
                    <button type="button" onclick="crearModeloRapido()"
                            title="Nuevo modelo"
                            class="px-3 py-2 bg-indigo-50 text-indigo-700 border-2 border-indigo-200 rounded-xl hover:bg-indigo-100 transition shrink-0">
                        <i class="fas fa-plus text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- ¿Tiene variantes? -->
            <div class="flex items-center gap-3 p-3 bg-indigo-50 border border-indigo-200 rounded-xl">
                <input type="checkbox" id="np_tiene_variantes"
                       onchange="toggleVariantesNuevoProducto()"
                       class="w-4 h-4 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-400">
                <div>
                    <label for="np_tiene_variantes" class="text-sm font-medium text-indigo-800 cursor-pointer">
                        <i class="fas fa-layer-group mr-1"></i>Este producto tiene variantes (colores / capacidades)
                    </label>
                    <p class="text-xs text-indigo-500 mt-0.5">Podrás definir las variantes desde Inventario → Variantes después de crearlo.</p>
                </div>
            </div>

            <!-- Color (opcional, ocultar si tiene_variantes) -->
            <div id="np_color_section">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Color
                    <span class="text-gray-400 text-xs font-normal">(opcional)</span>
                </label>
                <div class="flex gap-2">
                    <select id="np_color"
                            class="flex-1 px-3 py-2.5 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-100 transition">
                        <option value="">Sin color</option>
                        @foreach($colores as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="crearColorRapido()"
                            title="Nuevo color"
                            class="px-3 py-2 bg-pink-50 text-pink-700 border-2 border-pink-200 rounded-xl hover:bg-pink-100 transition shrink-0">
                        <i class="fas fa-plus text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Código de barras -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Código de barras
                    <span class="text-gray-400 text-xs font-normal">(dejar vacío para generar automáticamente)</span>
                </label>
                <input type="text" id="np_codigo_barras"
                       class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-100 font-mono transition"
                       placeholder="Ej: 7501234567890">
            </div>

        </div><!-- /body -->

        <!-- Footer -->
        <div class="px-6 pb-6 flex justify-end gap-3">
            <button type="button" onclick="cerrarModalCrearProducto()"
                    class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-medium">
                Cancelar
            </button>
            <button type="button" onclick="guardarNuevoProducto()"
                    id="btn_guardar_nuevo_producto"
                    class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-green-700 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-500 transition shadow-md font-medium">
                <i class="fas fa-save mr-2"></i>
                Crear y Agregar
            </button>
        </div>

    </div>
</div>

</body>
</html>