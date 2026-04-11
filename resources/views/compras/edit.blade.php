<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar Compra - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                <a href="{{ route('compras.show', $compra) }}" class="hover:text-blue-900">Compra #{{ $compra->numero_factura }}</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <span class="text-gray-700 font-medium">Editar</span>
            </div>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-edit mr-3 text-blue-900"></i>
                    Editar Compra #{{ $compra->numero_factura }}
                </h1>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                    <i class="fas fa-info-circle mr-1"></i>
                    Los productos no pueden modificarse; anula y crea de nuevo si es necesario
                </span>
            </div>
        </div>

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
            <div class="bg-gradient-to-r from-yellow-600 to-yellow-500 px-8 py-5">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-edit mr-3"></i>
                    Editar Datos de la Compra
                </h2>
            </div>

            <form action="{{ route('compras.update', $compra) }}" method="POST" id="compraForm" class="p-8">
                @csrf
                @method('PUT')

                <!-- SECCIÓN 1: INFORMACIÓN PRINCIPAL (SOLO EDITABLE EN CIERTOS CASOS) -->
                <div class="mb-10">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-2">
                            <i class="fas fa-file-invoice text-blue-900 text-sm"></i>
                        </span>
                        Información General
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Proveedor (editable) -->
                        <div class="relative">
                            <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Proveedor <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="proveedor_id" id="proveedor_id" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 appearance-none bg-white">
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id }}" {{ old('proveedor_id', $compra->proveedor_id) == $prov->id ? 'selected' : '' }}>
                                            {{ $prov->razon_social }} ({{ $prov->ruc }})
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Número de Factura (editable) -->
                        <div>
                            <label for="numero_factura" class="block text-sm font-medium text-gray-700 mb-1.5">
                                N° Factura/Boleta <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="numero_factura" id="numero_factura" required
                                   value="{{ old('numero_factura', $compra->numero_factura) }}"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200"
                                   placeholder="Ej: F001-00001234">
                        </div>

                        <!-- Sucursal (cascada) -->
                        <div class="relative">
                            <label for="edit_sucursal_sel" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Sucursal <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="edit_sucursal_sel" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 appearance-none bg-white"
                                        onchange="onEditSucursalChange(this.value)">
                                    <option value="">— Seleccione una sucursal —</option>
                                    @foreach($sucursales as $suc)
                                        <option value="{{ $suc->id }}"
                                            {{ old('sucursal_edit', $compra->almacen?->sucursal_id) == $suc->id ? 'selected' : '' }}>
                                            {{ $suc->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Almacén (aparece si hay 2+) -->
                        <div id="edit_almacen_wrap" class="relative hidden">
                            <label for="edit_almacen_sel" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Almacén Destino <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                {{-- Sin name: el valor se copia al input hidden de abajo --}}
                                <select id="edit_almacen_sel"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 appearance-none bg-white"
                                        onchange="document.getElementById('almacen_id_hidden').value = this.value">
                                    <option value="">Seleccione un almacén</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                        <!-- Almacén único (info) -->
                        <div id="edit_almacen_unico_info" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Almacén Destino</label>
                            <div class="flex items-center gap-2 px-4 py-3 bg-yellow-50 border-2 border-yellow-200 rounded-xl">
                                <i class="fas fa-warehouse text-yellow-600"></i>
                                <span id="edit_almacen_unico_nombre" class="text-sm font-semibold text-yellow-900"></span>
                                <span class="text-xs text-yellow-500 ml-auto">Auto-seleccionado</span>
                            </div>
                        </div>
                        <!-- Campo hidden: siempre enviado al servidor -->
                        <input type="hidden" name="almacen_id" id="almacen_id_hidden" value="{{ old('almacen_id', $compra->almacen_id) }}">

                        <!-- Fecha (editable) -->
                        <div>
                            <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Fecha de Compra <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="fecha" id="fecha" required
                                   value="{{ old('fecha', $compra->fecha->format('Y-m-d')) }}"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200">
                        </div>

                        <!-- Tipo Comprobante (solo lectura) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Tipo Comprobante
                            </label>
                            <input type="text" 
                                   value="{{ ucfirst($compra->tipo_comprobante ?? 'Factura') }}" 
                                   class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl text-gray-700"
                                   readonly>
                        </div>

                        <!-- Forma de Pago (editable) -->
                        <div>
                            <label for="forma_pago" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Forma de Pago <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="forma_pago" id="forma_pago" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 appearance-none bg-white"
                                        onchange="toggleCondicionPago(this.value)">
                                    <option value="contado" {{ old('forma_pago', $compra->forma_pago) === 'contado' ? 'selected' : '' }}>Contado</option>
                                    <option value="credito" {{ old('forma_pago', $compra->forma_pago) === 'credito' ? 'selected' : '' }}>Crédito</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Condición de Pago (editable, visible solo si es crédito) -->
                        <div id="condicion_pago_wrap" class="{{ old('forma_pago', $compra->forma_pago) === 'credito' ? '' : 'hidden' }}">
                            <label for="condicion_pago" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Días de Crédito
                            </label>
                            <input type="number" name="condicion_pago" id="condicion_pago" min="1" max="365"
                                   value="{{ old('condicion_pago', $compra->condicion_pago) }}"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200"
                                   placeholder="Ej: 30">
                        </div>

                        <!-- Moneda (solo lectura) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Moneda
                            </label>
                            <input type="text" 
                                   value="{{ $compra->tipo_moneda }}" 
                                   class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl text-gray-700"
                                   readonly>
                        </div>

                        <!-- Tipo de Operación SUNAT (solo lectura) -->
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Tipo de Operación SUNAT
                            </label>
                            <input type="text" 
                                   value="{{ $compra->tipo_operacion == '01' ? 'Gravado (IGV 18%)' : ($compra->tipo_operacion == '02' ? 'Exonerado' : ($compra->tipo_operacion == '03' ? 'Inafecto' : 'Exportación')) }}"
                                   class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl text-gray-700"
                                   readonly>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: PRODUCTOS (EDITABLES) -->
                <div class="mb-10">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1 flex items-center">
                        <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-2">
                            <i class="fas fa-boxes text-green-700 text-sm"></i>
                        </span>
                        Productos de la Compra
                    </h3>
                    <p class="text-xs text-gray-500 mb-4 ml-10">Puedes corregir cantidades y precios unitarios. El stock se ajustará automáticamente con la diferencia.</p>

                    <div class="rounded-xl border-2 border-green-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200" id="tablaProductos">
                                <thead class="bg-green-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Producto</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Variante</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-28">Cantidad</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-36">Precio Unit.</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase w-32">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($compra->detalles as $i => $detalle)
                                    <tr data-index="{{ $i }}">
                                        <td class="px-4 py-3">
                                            <input type="hidden" name="detalles[{{ $i }}][id]" value="{{ $detalle->id }}">
                                            <p class="font-medium text-gray-900 text-sm">{{ $detalle->producto->nombre }}</p>
                                            <p class="text-xs text-gray-500">{{ $detalle->producto->marca->nombre ?? '' }}</p>
                                            @if($detalle->producto->tipo_inventario === 'serie')
                                                <span class="inline-flex items-center gap-1 text-xs text-purple-600 mt-0.5">
                                                    <i class="fas fa-microchip text-xs"></i> Con IMEI
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $detalle->variante?->color?->nombre ?? ($detalle->producto->modelo->nombre ?? '-') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number"
                                                   name="detalles[{{ $i }}][cantidad]"
                                                   value="{{ old("detalles.$i.cantidad", $detalle->cantidad) }}"
                                                   min="1"
                                                   class="w-full text-center px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-1 focus:ring-green-200 text-sm font-semibold detalle-cantidad"
                                                   data-index="{{ $i }}"
                                                   required>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number"
                                                   name="detalles[{{ $i }}][precio_unitario]"
                                                   value="{{ old("detalles.$i.precio_unitario", number_format($detalle->precio_unitario, 2, '.', '')) }}"
                                                   min="0.01"
                                                   step="0.01"
                                                   class="w-full text-center px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-1 focus:ring-green-200 text-sm detalle-precio"
                                                   data-index="{{ $i }}"
                                                   required>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="font-semibold text-gray-900 text-sm subtotal-linea" data-index="{{ $i }}">
                                                {{ number_format($detalle->subtotal, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    @php $tipoOp = $compra->tipo_operacion ?? '01'; @endphp
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Subtotal:</td>
                                        <td class="px-4 py-2 text-right font-bold text-blue-900" id="resumen-subtotal">{{ number_format($compra->subtotal, 2) }}</td>
                                    </tr>
                                    @if($tipoOp === '01')
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-right text-sm font-semibold text-gray-700">IGV (18%):</td>
                                        <td class="px-4 py-2 text-right font-bold text-blue-900" id="resumen-igv">{{ number_format($compra->igv, 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr class="border-t-2 border-gray-300">
                                        <td colspan="4" class="px-4 py-3 text-right font-bold text-gray-900">Total:</td>
                                        <td class="px-4 py-3 text-right font-bold text-blue-900 text-base" id="resumen-total">{{ number_format($compra->total, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: OBSERVACIONES (EDITABLE) -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-2">
                            <i class="fas fa-comment text-yellow-600 text-sm"></i>
                        </span>
                        Observaciones
                    </h3>
                    <textarea name="observaciones" rows="3"
                              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200"
                              placeholder="Notas adicionales sobre la compra...">{{ old('observaciones', $compra->observaciones) }}</textarea>
                </div>

                <!-- Botones de acción -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t-2 border-gray-100">
                    <a href="{{ route('compras.show', $compra) }}" 
                       class="px-6 py-3 border-2 border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 transition font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-yellow-600 to-yellow-500 text-white rounded-xl hover:from-yellow-500 hover:to-yellow-400 transition shadow-lg font-medium">
                        <i class="fas fa-save mr-2"></i>
                        Actualizar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('compraForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
        });

        function toggleCondicionPago(value) {
            const wrap = document.getElementById('condicion_pago_wrap');
            const input = document.getElementById('condicion_pago');
            if (value === 'credito') {
                wrap.classList.remove('hidden');
                input.required = true;
            } else {
                wrap.classList.add('hidden');
                input.required = false;
                input.value = '';
            }
        }

        // ── Sucursal → Almacén (edit) ──────────────────────────────────
        const editSucursalAlmacenesMap = @json(
            $sucursales->mapWithKeys(fn($s) => [
                $s->id => $s->almacenes->map(fn($a) => ['id' => $a->id, 'nombre' => $a->nombre])->values()
            ])
        );

        function onEditSucursalChange(sucursalId) {
            const wrap        = document.getElementById('edit_almacen_wrap');
            const selAlmacen  = document.getElementById('edit_almacen_sel');
            const hiddenAlm   = document.getElementById('almacen_id_hidden');
            const infoUnico   = document.getElementById('edit_almacen_unico_info');
            const nombreUnico = document.getElementById('edit_almacen_unico_nombre');

            // Limpiar estado
            wrap.classList.add('hidden');
            infoUnico.classList.add('hidden');
            selAlmacen.innerHTML = '<option value="">Seleccione un almacén</option>';

            if (!sucursalId) {
                hiddenAlm.value = '';
                return;
            }

            const almacenes = editSucursalAlmacenesMap[sucursalId] || [];
            const prevAlmacen = hiddenAlm.value;

            if (almacenes.length === 1) {
                // Un único almacén → auto-seleccionar
                hiddenAlm.value = almacenes[0].id;
                nombreUnico.textContent = almacenes[0].nombre;
                infoUnico.classList.remove('hidden');

            } else if (almacenes.length > 1) {
                // Múltiples almacenes → mostrar dropdown
                almacenes.forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a.id;
                    opt.textContent = a.nombre;
                    selAlmacen.appendChild(opt);
                });
                // Restaurar almacén previo si pertenece a esta sucursal
                const ids = almacenes.map(a => String(a.id));
                if (prevAlmacen && ids.includes(String(prevAlmacen))) {
                    selAlmacen.value = prevAlmacen;
                } else {
                    hiddenAlm.value = '';
                }
                wrap.classList.remove('hidden');
            } else {
                hiddenAlm.value = '';
            }
        }

        // Disparar al cargar para pre-poblar el estado actual
        (function() {
            const sel = document.getElementById('edit_sucursal_sel');
            if (sel && sel.value) {
                onEditSucursalChange(sel.value);
            }
        })();

        // ── Recálculo en tiempo real de subtotales ───────────────────────
        const tipoOp = '{{ $compra->tipo_operacion ?? "01" }}';

        function recalcularTotales() {
            let subtotal = 0;

            document.querySelectorAll('#tablaProductos tbody tr').forEach(function(row) {
                const idx      = row.dataset.index;
                const cantidad = parseFloat(document.querySelector(`[name="detalles[${idx}][cantidad]"]`)?.value) || 0;
                const precio   = parseFloat(document.querySelector(`[name="detalles[${idx}][precio_unitario]"]`)?.value) || 0;
                const linea    = cantidad * precio;

                const spanSubtotal = row.querySelector('.subtotal-linea');
                if (spanSubtotal) spanSubtotal.textContent = linea.toFixed(2);

                subtotal += linea;
            });

            const igv   = tipoOp === '01' ? subtotal * 0.18 : 0;
            const total = subtotal + igv;

            const elSub = document.getElementById('resumen-subtotal');
            const elIgv = document.getElementById('resumen-igv');
            const elTot = document.getElementById('resumen-total');

            if (elSub) elSub.textContent = subtotal.toFixed(2);
            if (elIgv) elIgv.textContent = igv.toFixed(2);
            if (elTot) elTot.textContent  = total.toFixed(2);
        }

        document.querySelectorAll('.detalle-cantidad, .detalle-precio').forEach(function(input) {
            input.addEventListener('input', recalcularTotales);
        });
    </script>
</body>
</html>