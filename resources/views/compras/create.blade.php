<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Compra - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="ml-64 p-8">
        <x-header
            title="Registrar Compra"
            subtitle="Ingreso de mercadería al inventario"
        />

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="max-w-5xl mx-auto">
            <form @submit.prevent="submitForm" x-data="compraForm()">
                @csrf

                {{-- Datos Generales --}}
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-blue-900 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-info-circle mr-2"></i>Datos Generales
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Proveedor <span class="text-red-500">*</span>
                                </label>
                                <select x-model="proveedor_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                            {{ $proveedor->ruc }} - {{ $proveedor->razon_social }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Almacén Destino <span class="text-red-500">*</span>
                                </label>
                                <select x-model="almacen_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="">Seleccione un almacén</option>
                                    @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}" {{ old('almacen_id') == $almacen->id ? 'selected' : '' }}>
                                            {{ $almacen->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    N° Factura <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="numero_factura" required placeholder="Ej: F001-00012345"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       value="{{ old('numero_factura') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha de Compra <span class="text-red-500">*</span>
                                </label>
                                <input type="date" x-model="fecha" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       value="{{ old('fecha', date('Y-m-d')) }}">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                <textarea x-model="observaciones" rows="3" placeholder="Notas adicionales sobre la compra..."
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('observaciones') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detalle de Productos --}}
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-blue-900 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-boxes mr-2"></i>Detalle de Productos
                        </h2>
                        <button type="button" @click="agregarDetalle()"
                                class="bg-green-500 hover:bg-green-600 text-white text-sm font-semibold py-2 px-4 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-1"></i>Agregar Producto
                        </button>
                    </div>
                    <div class="p-6">
                        <template x-if="detalles.length === 0">
                            <div class="text-center py-12 text-gray-400">
                                <i class="fas fa-box-open text-5xl mb-4"></i>
                                <p class="text-lg font-medium">No hay productos agregados</p>
                                <p class="text-sm mt-2">Haga clic en "Agregar Producto" para comenzar a registrar la compra</p>
                            </div>
                        </template>

                        <template x-for="(detalle, index) in detalles" :key="index">
                            <div class="border-2 border-gray-200 rounded-lg p-5 mb-4 hover:border-blue-300 transition-all bg-gray-50">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-sm font-bold text-blue-900">
                                        <i class="fas fa-cube mr-2"></i>Producto #<span x-text="index + 1"></span>
                                    </span>
                                    <button type="button" @click="eliminarDetalle(index)"
                                            class="text-red-500 hover:text-red-700 font-medium" x-show="detalles.length > 1">
                                        <i class="fas fa-trash mr-1"></i>Eliminar
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                    <div class="md:col-span-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Producto <span class="text-red-500">*</span>
                                        </label>
                                        <select x-model="detalle.producto_id" @change="onProductoChange(index)" required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="">Seleccione un producto</option>
                                            @foreach($productos as $producto)
                                                <option value="{{ $producto->id }}">
                                                    {{ $producto->codigo }} - {{ $producto->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Cantidad <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" x-model.number="detalle.cantidad" min="1" required
                                               @input="calcularSubtotalDetalle(index)"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Precio Unit. <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" x-model.number="detalle.precio_unitario" min="0.01" step="0.01" required
                                               @input="calcularSubtotalDetalle(index)"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Subtotal</label>
                                        <div class="px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p class="text-lg font-bold text-blue-900" x-text="'S/ ' + (detalle.cantidad * detalle.precio_unitario).toFixed(2)"></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- IMEIs para celulares --}}
                                <template x-if="detalle.tipo_producto === 'celular'">
                                    <div class="mt-4 border-t-2 border-dashed border-purple-300 pt-4 bg-purple-50 rounded-lg p-4 -mx-1">
                                        <div class="flex justify-between items-center mb-3">
                                            <label class="text-sm font-bold text-purple-800">
                                                <i class="fas fa-mobile-alt mr-2"></i>Registro de IMEIs
                                                <span class="text-xs font-normal text-purple-600">(debe ingresar <span x-text="detalle.cantidad"></span> unidades)</span>
                                            </label>
                                            <span class="text-xs px-3 py-1 rounded-full font-bold"
                                                  :class="detalle.imeis.length === detalle.cantidad ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                                                <span x-text="detalle.imeis.length"></span> / <span x-text="detalle.cantidad"></span>
                                            </span>
                                        </div>

                                        <template x-for="(imei, iIndex) in detalle.imeis" :key="iIndex">
                                            <div class="grid grid-cols-12 gap-3 mb-3 bg-white p-3 rounded-lg border border-purple-200">
                                                <div class="col-span-5">
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">IMEI <span class="text-red-500">*</span></label>
                                                    <input type="text" x-model="imei.codigo_imei" maxlength="20" required
                                                           placeholder="15 dígitos"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                                                </div>
                                                <div class="col-span-3">
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Serie</label>
                                                    <input type="text" x-model="imei.serie" placeholder="Opcional"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                                                </div>
                                                <div class="col-span-3">
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                                    <input type="text" x-model="imei.color" placeholder="Ej: Negro"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                                                </div>
                                                <div class="col-span-1 flex items-end justify-center pb-2">
                                                    <button type="button" @click="detalle.imeis.splice(iIndex, 1)"
                                                            class="text-red-500 hover:text-red-700 p-2">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>

                                        <button type="button" @click="agregarImei(index)"
                                                x-show="detalle.imeis.length < detalle.cantidad"
                                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg mt-2 transition-colors">
                                            <i class="fas fa-plus mr-2"></i>Agregar IMEI
                                        </button>

                                        <template x-if="detalle.imeis.length < detalle.cantidad">
                                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mt-3">
                                                <p class="text-sm text-red-700">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                                    <strong>Atención:</strong> Faltan <span x-text="detalle.cantidad - detalle.imeis.length"></span> IMEI(s) por registrar
                                                </p>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Resumen --}}
                <template x-if="detalles.length > 0">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                        <div class="bg-blue-900 px-6 py-4">
                            <h2 class="text-xl font-bold text-white">
                                <i class="fas fa-calculator mr-2"></i>Resumen de Totales
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="max-w-md ml-auto">
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                        <span class="text-gray-700 font-medium">Subtotal:</span>
                                        <span class="text-lg font-semibold text-gray-900" x-text="'S/ ' + subtotal.toFixed(2)"></span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                        <span class="text-gray-700 font-medium">IGV (18%):</span>
                                        <span class="text-lg font-semibold text-gray-900" x-text="'S/ ' + igv.toFixed(2)"></span>
                                    </div>
                                    <div class="flex justify-between items-center py-3 bg-blue-50 px-4 rounded-lg border-2 border-blue-200">
                                        <span class="text-xl font-bold text-blue-900">TOTAL:</span>
                                        <span class="text-2xl font-bold text-blue-900" x-text="'S/ ' + total.toFixed(2)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Botones --}}
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('compras.index') }}"
                       class="px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="submit" :disabled="guardando || detalles.length === 0"
                            class="px-6 py-3 bg-blue-900 text-white rounded-lg font-semibold hover:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!guardando"><i class="fas fa-save mr-2"></i>Registrar Compra</span>
                        <span x-show="guardando"><i class="fas fa-spinner fa-spin mr-2"></i>Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function compraForm() {
        const productosMap = {
            @foreach($productos as $producto)
                '{{ $producto->id }}': '{{ $producto->tipo_producto }}',
            @endforeach
        };

        return {
            proveedor_id: '{{ old("proveedor_id") }}',
            almacen_id: '{{ old("almacen_id") }}',
            numero_factura: '{{ old("numero_factura") }}',
            fecha: '{{ old("fecha", date("Y-m-d")) }}',
            observaciones: '{{ old("observaciones") }}',
            detalles: [],
            guardando: false,

            get subtotal() { return this.detalles.reduce((s, d) => s + (d.cantidad * d.precio_unitario), 0); },
            get igv() { return this.subtotal * 0.18; },
            get total() { return this.subtotal + this.igv; },

            agregarDetalle() {
                this.detalles.push({ producto_id: '', cantidad: 1, precio_unitario: 0, tipo_producto: '', imeis: [] });
            },
            eliminarDetalle(i) { this.detalles.splice(i, 1); },

            onProductoChange(i) {
                const tipo = productosMap[this.detalles[i].producto_id] || '';
                this.detalles[i].tipo_producto = tipo;
                this.detalles[i].imeis = [];
                if (tipo === 'celular') this.sincronizarImeis(i);
            },

            calcularSubtotalDetalle(i) {
                if (this.detalles[i].tipo_producto === 'celular') this.sincronizarImeis(i);
            },

            sincronizarImeis(i) {
                const d = this.detalles[i];
                const diff = d.cantidad - d.imeis.length;
                if (diff > 0) for (let j = 0; j < diff; j++) d.imeis.push({ codigo_imei: '', serie: '', color: '' });
                else if (diff < 0) d.imeis.splice(d.cantidad);
            },

            agregarImei(i) { this.detalles[i].imeis.push({ codigo_imei: '', serie: '', color: '' }); },

            validarFormulario() {
                if (!this.proveedor_id) { alert('Seleccione un proveedor'); return false; }
                if (!this.almacen_id) { alert('Seleccione un almacén'); return false; }
                if (!this.numero_factura) { alert('Ingrese el número de factura'); return false; }
                if (!this.fecha) { alert('Ingrese la fecha'); return false; }
                if (this.detalles.length === 0) { alert('Agregue al menos un producto'); return false; }
                for (let i = 0; i < this.detalles.length; i++) {
                    const d = this.detalles[i];
                    if (!d.producto_id) { alert(`Producto #${i+1}: Seleccione un producto`); return false; }
                    if (d.cantidad < 1) { alert(`Producto #${i+1}: Cantidad mayor a 0`); return false; }
                    if (d.precio_unitario <= 0) { alert(`Producto #${i+1}: Precio mayor a 0`); return false; }
                    if (d.tipo_producto === 'celular') {
                        if (d.imeis.length !== d.cantidad) { alert(`Producto #${i+1}: Registre ${d.cantidad} IMEI(s). Tiene ${d.imeis.length}`); return false; }
                        for (let j = 0; j < d.imeis.length; j++) {
                            if (!d.imeis[j].codigo_imei.trim()) { alert(`Producto #${i+1}, IMEI #${j+1}: Código obligatorio`); return false; }
                        }
                    }
                }
                return true;
            },

            submitForm() {
                if (!this.validarFormulario()) return;
                this.guardando = true;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("compras.store") }}';
                const add = (n, v) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v ?? ''; form.appendChild(i); };
                add('_token', '{{ csrf_token() }}');
                add('proveedor_id', this.proveedor_id);
                add('almacen_id', this.almacen_id);
                add('numero_factura', this.numero_factura);
                add('fecha', this.fecha);
                add('observaciones', this.observaciones);
                this.detalles.forEach((d, i) => {
                    add(`detalles[${i}][producto_id]`, d.producto_id);
                    add(`detalles[${i}][cantidad]`, d.cantidad);
                    add(`detalles[${i}][precio_unitario]`, d.precio_unitario);
                    if (d.tipo_producto === 'celular') {
                        d.imeis.forEach((im, j) => {
                            add(`detalles[${i}][imeis][${j}][codigo_imei]`, im.codigo_imei);
                            add(`detalles[${i}][imeis][${j}][serie]`, im.serie);
                            add(`detalles[${i}][imeis][${j}][color]`, im.color);
                        });
                    }
                });
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
    </script>
</body>
</html>