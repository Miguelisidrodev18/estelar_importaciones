{{-- resources/views/compras/create.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Compra - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Nueva Compra"
            subtitle="Registrar una nueva compra a proveedor"
        />

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <form action="{{ route('compras.store') }}" method="POST" id="compraForm">
                @csrf

                {{-- SECCIÓN 1: INFORMACIÓN DE LA COMPRA --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                        <i class="fas fa-file-invoice mr-2 text-blue-900"></i>
                        Información de la Compra
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Proveedor --}}
                        <div>
                            <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Proveedor <span class="text-red-500">*</span>
                            </label>
                            <select name="proveedor_id" id="proveedor_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Seleccione un proveedor</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                        {{ $proveedor->nombre_comercial }} - {{ $proveedor->razon_social }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proveedor_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Número de Factura --}}
                        <div>
                            <label for="numero_factura" class="block text-sm font-medium text-gray-700 mb-2">
                                N° Factura/Boleta <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="numero_factura" id="numero_factura"
                                   value="{{ old('numero_factura') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="Ej: F001-000001">
                            @error('numero_factura')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Almacén Destino --}}
                        <div>
                            <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Almacén Destino <span class="text-red-500">*</span>
                            </label>
                            <select name="almacen_id" id="almacen_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Seleccione un almacén</option>
                                @foreach($almacenes as $almacen)
                                    <option value="{{ $almacen->id }}" {{ old('almacen_id') == $almacen->id ? 'selected' : '' }}>
                                        {{ $almacen->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('almacen_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fecha de Compra --}}
                        <div>
                            <label for="fecha" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Compra <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha" id="fecha" required
                                   value="{{ old('fecha', date('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        {{-- Tipo de Comprobante --}}
                        <div>
                            <label for="tipo_comprobante" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo Comprobante
                            </label>
                            <select name="tipo_comprobante" id="tipo_comprobante"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="factura">Factura</option>
                                <option value="boleta">Boleta</option>
                                <option value="nota_credito">Nota de Crédito</option>
                            </select>
                        </div>

                        {{-- Forma de Pago --}}
                        <div>
                            <label for="forma_pago" class="block text-sm font-medium text-gray-700 mb-2">
                                Forma de Pago <span class="text-red-500">*</span>
                            </label>
                            <select name="forma_pago" id="forma_pago" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    onchange="toggleCondicionPago(this.value)">
                                <option value="contado" {{ old('forma_pago') == 'contado' ? 'selected' : '' }}>Contado</option>
                                <option value="credito" {{ old('forma_pago') == 'credito' ? 'selected' : '' }}>Crédito</option>
                            </select>
                        </div>

                        {{-- Condición de pago (días) - solo crédito --}}
                        <div id="condicion_pago_div" style="display:none">
                            <label for="condicion_pago" class="block text-sm font-medium text-gray-700 mb-2">
                                Días de Crédito
                            </label>
                            <input type="number" name="condicion_pago" id="condicion_pago"
                                   value="{{ old('condicion_pago', 30) }}" min="1" max="90"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="Ej: 30">
                        </div>

                        {{-- Moneda --}}
                        <div>
                            <label for="tipo_moneda" class="block text-sm font-medium text-gray-700 mb-2">
                                Moneda
                            </label>
                            <select name="tipo_moneda" id="tipo_moneda"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    onchange="toggleTipoCambio(this.value)">
                                <option value="PEN" {{ old('tipo_moneda', 'PEN') == 'PEN' ? 'selected' : '' }}>PEN (S/)</option>
                                <option value="USD" {{ old('tipo_moneda') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                            </select>
                        </div>

                        {{-- Tipo de cambio - solo USD --}}
                        <div id="tipo_cambio_div" style="display:none">
                            <label for="tipo_cambio" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Cambio (S/ por $)
                            </label>
                            <input type="number" name="tipo_cambio" id="tipo_cambio"
                                   value="{{ old('tipo_cambio', '3.80') }}" min="0.001" step="0.001"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="Ej: 3.80">
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 2: DETALLE DE PRODUCTOS --}}
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-boxes mr-2 text-blue-900"></i>
                            Productos de la Compra
                        </h3>
                        <button type="button" onclick="agregarProducto()"
                                class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 text-sm">
                            <i class="fas fa-plus mr-2"></i>Agregar Producto
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="tablaProductos">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marca</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modelo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IMEIs</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="detallesBody">
                                {{-- Los productos se agregarán dinámicamente aquí --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- Totales --}}
                    <div class="mt-6 flex justify-end">
                        <div class="w-72 space-y-2">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal:</span>
                                <span id="subtotal">S/ 0.00</span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" id="incluir_igv" name="incluye_igv" checked
                                           class="rounded border-gray-300 text-blue-900">
                                    <span>Incluir IGV (18%):</span>
                                </label>
                                <span id="igv">S/ 0.00</span>
                            </div>
                            <div class="flex justify-between font-bold text-gray-900 text-base border-t pt-2">
                                <span>Total:</span>
                                <span id="total">S/ 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 3: OBSERVACIONES --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                        <i class="fas fa-comment mr-2 text-blue-900"></i>
                        Observaciones
                    </h3>
                    <textarea name="observaciones" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('observaciones') }}</textarea>
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('compras.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                        <i class="fas fa-save mr-2"></i>Registrar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal para IMEIs --}}
    <div id="imeiModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-4xl w-full max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold" id="imeiModalTitle">Registrar IMEIs</h3>
                <button type="button" onclick="cerrarModalIMEI()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div id="imeiContainer" class="space-y-4">
                {{-- Los inputs de IMEI se generarán aquí --}}
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button type="button" onclick="cerrarModalIMEI()"
                        class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="button" onclick="guardarIMEIs()"
                        class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                    Guardar IMEIs
                </button>
            </div>
        </div>
    </div>

    <script>
    let contadorProductos = 0;
    let productoEnEdicion = null;
    let imeisPorFila = {}; // { rowIndex: ['imei1', 'imei2', ...] }

    const catalogoProductos = @json($productos);
    const coloresCatalogo   = @json($colores);
    const marcasCatalogo    = @json($marcas);

    document.addEventListener('DOMContentLoaded', function() {
        agregarProducto();

        document.getElementById('incluir_igv').addEventListener('change', calcularTotales);
    });

    function toggleCondicionPago(valor) {
        document.getElementById('condicion_pago_div').style.display = valor === 'credito' ? 'block' : 'none';
    }

    function toggleTipoCambio(valor) {
        document.getElementById('tipo_cambio_div').style.display = valor === 'USD' ? 'block' : 'none';
    }

    function agregarProducto() {
        const tbody = document.getElementById('detallesBody');
        const idx   = contadorProductos;
        const rowId = `producto_${idx}`;

        const opcionesProductos = catalogoProductos.map(p =>
            `<option value="${p.id}" data-tipo="${p.tipo_producto}">${p.nombre} (${p.categoria})</option>`
        ).join('');

        const opcionesMarcas = marcasCatalogo.map(m =>
            `<option value="${m.id}">${m.nombre}</option>`
        ).join('');

        const row = document.createElement('tr');
        row.id = rowId;
        row.className = 'border-b border-gray-100 hover:bg-gray-50';
        row.innerHTML = `
            <td class="px-4 py-3" style="min-width:200px">
                <select name="detalles[${idx}][producto_id]"
                        id="producto_select_${idx}"
                        onchange="cargarDetallesProducto(this, ${idx})"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        required>
                    <option value="">Seleccione producto</option>
                    ${opcionesProductos}
                </select>
            </td>
            <td class="px-4 py-3" style="min-width:140px">
                <select id="marca_select_${idx}"
                        onchange="cambiarMarca(${idx})"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        disabled>
                    <option value="">— Marca —</option>
                    ${opcionesMarcas}
                </select>
            </td>
            <td class="px-4 py-3" style="min-width:160px">
                <select name="detalles[${idx}][modelo_id]"
                        id="modelo_select_${idx}"
                        onchange="actualizarTrasCambioModelo(${idx})"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        disabled>
                    <option value="">— Modelo —</option>
                </select>
            </td>
            <td class="px-4 py-3" style="min-width:150px">
                <select name="detalles[${idx}][color_id]"
                        id="color_${idx}"
                        onchange="actualizarVistaIMEI(${idx})"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        disabled>
                    <option value="">No aplica</option>
                </select>
            </td>
            <td class="px-4 py-3" style="min-width:90px">
                <input type="number" name="detalles[${idx}][cantidad]"
                       id="cantidad_${idx}"
                       value="1" min="1" step="1"
                       onchange="actualizarCantidad(${idx})"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                       required>
            </td>
            <td class="px-4 py-3" style="min-width:120px">
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
    }

    function cargarDetallesProducto(select, index) {
        const productoId  = select.value;
        const producto    = catalogoProductos.find(p => p.id == productoId);
        const marcaSelect = document.getElementById(`marca_select_${index}`);
        const modeloSelect = document.getElementById(`modelo_select_${index}`);
        const colorSelect  = document.getElementById(`color_${index}`);
        const btnIMEI      = document.getElementById(`btn_imei_${index}`);

        // Reset all dependientes
        marcaSelect.value    = '';
        marcaSelect.disabled = true;
        modeloSelect.innerHTML = '<option value="">— Modelo —</option>';
        modeloSelect.disabled  = true;
        modeloSelect.required  = false;
        colorSelect.innerHTML  = '<option value="">No aplica</option>';
        colorSelect.disabled   = true;
        colorSelect.required   = false;
        btnIMEI.disabled = true;

        if (!producto) { calcularSubtotal(index); return; }

        // Habilitar marca
        marcaSelect.disabled = false;

        // Si el producto ya tiene marca asignada, pre-seleccionarla y cargar modelos
        if (producto.marca_id) {
            marcaSelect.value = producto.marca_id;
            cargarModelosPorMarca(producto.marca_id, index, producto.modelo_id, producto.tipo_producto);
        }

        calcularSubtotal(index);
    }

    function cambiarMarca(index) {
        const marcaSelect  = document.getElementById(`marca_select_${index}`);
        const modeloSelect = document.getElementById(`modelo_select_${index}`);
        const colorSelect  = document.getElementById(`color_${index}`);
        const btnIMEI      = document.getElementById(`btn_imei_${index}`);

        // Reset modelo y color
        modeloSelect.innerHTML = '<option value="">— Modelo —</option>';
        modeloSelect.disabled  = true;
        modeloSelect.required  = false;
        colorSelect.innerHTML  = '<option value="">No aplica</option>';
        colorSelect.disabled   = true;
        colorSelect.required   = false;
        btnIMEI.disabled = true;

        const marcaId = marcaSelect.value;
        if (!marcaId) return;

        const productoId = document.getElementById(`producto_select_${index}`).value;
        const producto   = catalogoProductos.find(p => p.id == productoId);
        const tipo       = producto ? producto.tipo_producto : null;

        cargarModelosPorMarca(marcaId, index, null, tipo);
    }

    function cargarModelosPorMarca(marcaId, index, modeloSeleccionado, tipo) {
        const modeloSelect = document.getElementById(`modelo_select_${index}`);
        modeloSelect.innerHTML = '<option value="">Cargando…</option>';
        modeloSelect.disabled  = true;

        fetch(`/catalogo/modelos-por-marca/${marcaId}`)
            .then(r => r.json())
            .then(modelos => {
                if (!modelos.length) {
                    modeloSelect.innerHTML = '<option value="">Sin modelos</option>';
                    return;
                }
                modeloSelect.innerHTML = '<option value="">— Seleccione modelo —</option>';
                modelos.forEach(m => {
                    const sel = modeloSeleccionado && m.id == modeloSeleccionado ? 'selected' : '';
                    modeloSelect.innerHTML += `<option value="${m.id}" ${sel}>${m.nombre}</option>`;
                });
                modeloSelect.disabled = false;
                modeloSelect.required = (tipo === 'celular');

                // Si había modelo preseleccionado y quedó marcado, activar color
                if (modeloSeleccionado && modeloSelect.value) {
                    actualizarTrasCambioModelo(index);
                }
            })
            .catch(() => {
                modeloSelect.innerHTML = '<option value="">Error al cargar</option>';
            });
    }

    function actualizarTrasCambioModelo(index) {
        const modeloSelect = document.getElementById(`modelo_select_${index}`);
        const colorSelect  = document.getElementById(`color_${index}`);
        const btnIMEI      = document.getElementById(`btn_imei_${index}`);
        const productoId   = document.getElementById(`producto_select_${index}`).value;
        const producto     = catalogoProductos.find(p => p.id == productoId);

        if (!producto || producto.tipo_producto !== 'celular') return;

        if (modeloSelect.value) {
            colorSelect.innerHTML = '<option value="">Seleccione color</option>';
            coloresCatalogo.forEach(c => {
                colorSelect.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
            });
            colorSelect.disabled = false;
            colorSelect.required = true;
        } else {
            colorSelect.innerHTML = '<option value="">No aplica</option>';
            colorSelect.disabled  = true;
            colorSelect.required  = false;
            btnIMEI.disabled = true;
        }
    }

    function actualizarVistaIMEI(index) {
        const colorSelect = document.getElementById(`color_${index}`);
        const btnIMEI     = document.getElementById(`btn_imei_${index}`);
        btnIMEI.disabled  = !colorSelect.value;
    }

    function actualizarCantidad(index) {
        calcularSubtotal(index);
        actualizarInfoIMEI(index);
    }

    function gestionarIMEIs(index) {
        const select       = document.getElementById(`producto_select_${index}`);
        const modeloSelect = document.getElementById(`modelo_select_${index}`);
        const colorSelect  = document.getElementById(`color_${index}`);
        const cantidad     = parseInt(document.getElementById(`cantidad_${index}`).value) || 1;

        if (!select.value) {
            alert('Primero seleccione un producto');
            return;
        }
        if (!modeloSelect.value) {
            alert('Primero seleccione un modelo');
            return;
        }
        if (!colorSelect.value) {
            alert('Primero seleccione un color');
            return;
        }

        const producto     = catalogoProductos.find(p => p.id == select.value);
        const modeloNombre = modeloSelect.options[modeloSelect.selectedIndex].text;
        const colorNombre  = colorSelect.options[colorSelect.selectedIndex].text;

        productoEnEdicion = index;
        document.getElementById('imeiModalTitle').innerHTML =
            `Registrar IMEIs — ${producto.nombre} · ${modeloNombre} · ${colorNombre}`;

        const imeisGuardados = imeisPorFila[index] || [];
        let html = '';
        for (let i = 0; i < cantidad; i++) {
            const imeiExistente = imeisGuardados[i] || '';
            html += `
                <div class="grid grid-cols-12 gap-3 items-center p-3 bg-gray-50 rounded-lg">
                    <div class="col-span-1 text-sm font-medium text-gray-600">${i + 1}</div>
                    <div class="col-span-11">
                        <input type="text"
                               class="imei-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="IMEI de 15 dígitos"
                               value="${imeiExistente}"
                               maxlength="15"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                </div>
            `;
        }

        document.getElementById('imeiContainer').innerHTML = html;
        document.getElementById('imeiModal').classList.remove('hidden');
        document.getElementById('imeiModal').classList.add('flex');
    }

    function guardarIMEIs() {
        const inputs = document.querySelectorAll('.imei-input');
        const imeis  = [];
        let valido   = true;

        inputs.forEach(input => {
            const valor = input.value.trim();
            if (valor.length !== 15 || !/^\d+$/.test(valor)) {
                input.classList.add('border-red-500');
                valido = false;
            } else {
                input.classList.remove('border-red-500');
                imeis.push(valor);
            }
        });

        if (!valido) {
            alert('Todos los IMEI deben tener exactamente 15 dígitos numéricos');
            return;
        }

        if (productoEnEdicion !== null) {
            const idx = productoEnEdicion;

            // Eliminar inputs ocultos anteriores de esta fila
            document.querySelectorAll(`[data-imei-row="${idx}"]`).forEach(el => el.remove());

            // Guardar en objeto JS
            imeisPorFila[idx] = imeis;

            // Crear inputs ocultos con estructura correcta para Laravel
            imeis.forEach((imei, i) => {
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = `detalles[${idx}][imeis][${i}][codigo_imei]`;
                input.setAttribute('data-imei-row', idx);
                input.value = imei;
                document.getElementById('compraForm').appendChild(input);
            });

            actualizarInfoIMEI(idx);
        }

        cerrarModalIMEI();
    }

    function actualizarInfoIMEI(index) {
        const infoDiv    = document.getElementById(`imei_info_${index}`);
        const countSpan  = document.getElementById(`imei_count_${index}`);
        const guardados  = imeisPorFila[index] || [];

        if (guardados.length > 0) {
            countSpan.innerText = guardados.length;
            infoDiv.classList.remove('hidden');
        } else {
            infoDiv.classList.add('hidden');
        }
    }

    function cerrarModalIMEI() {
        document.getElementById('imeiModal').classList.add('hidden');
        document.getElementById('imeiModal').classList.remove('flex');
        productoEnEdicion = null;
    }

    function calcularSubtotal(index) {
        const cantidad = parseFloat(document.getElementById(`cantidad_${index}`).value) || 0;
        const precio   = parseFloat(document.getElementById(`precio_${index}`).value)   || 0;
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

        const incluyeIGV = document.getElementById('incluir_igv').checked;
        const igv   = incluyeIGV ? subtotalGeneral * 0.18 : 0;
        const total = subtotalGeneral + igv;

        document.getElementById('subtotal').innerText = `S/ ${subtotalGeneral.toFixed(2)}`;
        document.getElementById('igv').innerText      = `S/ ${igv.toFixed(2)}`;
        document.getElementById('total').innerText    = `S/ ${total.toFixed(2)}`;
    }

    function eliminarProducto(rowId) {
        if (confirm('¿Eliminar este producto?')) {
            document.getElementById(rowId).remove();
            calcularTotales();
        }
    }

    document.getElementById('compraForm').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('#detallesBody tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un producto');
            return;
        }

        for (let i = 0; i < contadorProductos; i++) {
            const select = document.getElementById(`producto_select_${i}`);
            if (!select || !select.value) continue;

            const producto = catalogoProductos.find(p => p.id == select.value);
            if (producto && producto.tipo_producto === 'celular') {
                const modeloSelect = document.getElementById(`modelo_select_${i}`);
                if (!modeloSelect || !modeloSelect.value) {
                    e.preventDefault();
                    alert(`"${producto.nombre}" requiere seleccionar un modelo`);
                    modeloSelect && modeloSelect.focus();
                    return;
                }
                const colorSelect = document.getElementById(`color_${i}`);
                if (!colorSelect || !colorSelect.value) {
                    e.preventDefault();
                    alert(`"${producto.nombre}" requiere seleccionar un color`);
                    return;
                }
                const cantidad      = parseInt(document.getElementById(`cantidad_${i}`).value) || 1;
                const guardados     = imeisPorFila[i] || [];
                if (guardados.length !== cantidad) {
                    e.preventDefault();
                    alert(`"${producto.nombre}" requiere ${cantidad} IMEI(s). Registrados: ${guardados.length}`);
                    return;
                }
            }
        }
    });
    </script>
</body>
</html>
