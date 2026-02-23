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
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Solo edita datos generales, no productos
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
                        <!-- Proveedor (solo lectura) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Proveedor
                            </label>
                            <input type="text" 
                                   value="{{ $compra->proveedor->razon_social }} (RUC: {{ $compra->proveedor->ruc }})" 
                                   class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl text-gray-700"
                                   readonly>
                            <input type="hidden" name="proveedor_id" value="{{ $compra->proveedor_id }}">
                        </div>

                        <!-- Número de Factura (solo lectura) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                N° Factura/Boleta
                            </label>
                            <input type="text" 
                                   value="{{ $compra->numero_factura }}" 
                                   class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl text-gray-700"
                                   readonly>
                        </div>

                        <!-- Almacén (editable) -->
                        <div class="relative">
                            <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Almacén Destino <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="almacen_id" id="almacen_id" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 appearance-none bg-white">
                                    <option value="">Seleccione un almacén</option>
                                    @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}" {{ old('almacen_id', $compra->almacen_id) == $almacen->id ? 'selected' : '' }}>
                                            {{ $almacen->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>

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

                        <!-- Forma de Pago (solo lectura) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Forma de Pago
                            </label>
                            <input type="text" 
                                   value="{{ ucfirst($compra->forma_pago) }}" 
                                   class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl text-gray-700"
                                   readonly>
                        </div>

                        <!-- Condición de Pago (solo lectura, mostrar solo si es crédito) -->
                        @if($compra->forma_pago === 'credito' && $compra->condicion_pago)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Días de Crédito
                            </label>
                            <input type="text" 
                                value="{{ $compra->condicion_pago }} días" 
                                class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl text-gray-700"
                                readonly>
                        </div>
                        @endif

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

                <!-- SECCIÓN 2: PRODUCTOS (SOLO LECTURA) -->
                <div class="mb-10">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-2">
                            <i class="fas fa-boxes text-green-700 text-sm"></i>
                        </span>
                        Productos de la Compra (No editables)
                    </h3>

                    <div class="bg-gray-50 rounded-xl border-2 border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Producto</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Marca</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Modelo</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Color</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Cant.</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Precio Unit.</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($compra->detalles as $detalle)
                                    <tr>
                                        <td class="px-6 py-4">{{ $detalle->producto->nombre }}</td>
                                        <td class="px-6 py-4">{{ $detalle->producto->marca->nombre ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $detalle->producto->modelo->nombre ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $detalle->producto->color->nombre ?? '-' }}</td>
                                        <td class="px-6 py-4 text-right">{{ $detalle->cantidad }}</td>
                                        <td class="px-6 py-4 text-right">{{ number_format($detalle->precio_unitario, 2) }}</td>
                                        <td class="px-6 py-4 text-right font-semibold">{{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="6" class="px-6 py-3 text-right font-bold text-gray-700">Subtotal:</td>
                                        <td class="px-6 py-3 text-right font-bold text-blue-900">{{ number_format($compra->subtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="px-6 py-3 text-right font-bold text-gray-700">IGV (18%):</td>
                                        <td class="px-6 py-3 text-right font-bold text-blue-900">{{ number_format($compra->igv, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="px-6 py-3 text-right font-bold text-gray-900 text-lg">Total:</td>
                                        <td class="px-6 py-3 text-right font-bold text-blue-900 text-lg">{{ number_format($compra->total, 2) }}</td>
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
    </script>
</body>
</html>