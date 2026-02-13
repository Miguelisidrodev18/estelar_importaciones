<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Compra - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="ml-64 p-8">
        <x-header 
            title="Registrar Compra" 
            subtitle="Ingreso de mercadería al inventario" 
        />

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <p class="font-medium"><i class="fas fa-exclamation-circle mr-2"></i>Se encontraron errores:</p>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="max-w-5xl" x-data="compraForm()">
            {{-- Botón volver --}}
            <div class="mb-6">
                <a href="{{ route('compras.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Volver a compras
                </a>
            </div>

            <form @submit.prevent="submitForm">
                @csrf

                {{-- Datos Generales --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="bg-blue-900 px-6 py-4">
                        <h2 class="text-lg font-bold text-white">
                            <i class="fas fa-info-circle mr-2"></i>Datos Generales
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor <span class="text-red-500">*</span></label>
                                <select x-model="proveedor_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Seleccione proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                            {{ $proveedor->ruc }} - {{ $proveedor->razon_social }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Almacén Destino <span class="text-red-500">*</span></label>
                                <select x-model="almacen_id" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Seleccione almacén</option>
                                    @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}" {{ old('almacen_id') == $almacen->id ? 'selected' : '' }}>
                                            {{ $almacen->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">N° Factura <span class="text-red-500">*</span></label>
                                <input type="text" x-model="numero_factura" required placeholder="Ej: F001-00012345"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       value="{{ old('numero_factura') }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha <span class="text-red-500">*</span></label>
                                <input type="date" x-model="fecha" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       value="{{ old('fecha', date('Y-m-d')) }}">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                <textarea x-model="observaciones" rows="2" placeholder="Notas adicionales..."
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('observaciones') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detalle de Productos --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="bg-blue-900 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-white">
                            <i class="fas fa-boxes mr-2"></i>Detalle de Productos
                        </h2>
                        <button type="button" @click="agregarDetalle()"
                                class="bg-green-500 hover:bg-green-600 text-white text-sm font-semibold py-2 px-4 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-1"></i>Agregar Producto
                        </button>
                    </div>
                    <div class="p-6">
                        <template x-if="detalles.length === 0">
                            <div class="text-center py-10 text-gray-400">
                                <i class="fas fa-box-open text-5xl mb-3"></i>
                                <p>No hay productos agregados.</p>
                                <p class="text-sm mt-1">Haga clic en "Agregar Producto" para comenzar.</p>
                            </div>
                        </template>

                        <template x-for="(detalle, index) in detalles" :key="index">
                            <div class="border border-gray-200 rounded-lg p-5 mb-4 hover:border-blue-300 transition-colors">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-sm font-bold text-blue-900">
                                        <i class="fas fa-cube mr-1"></i>Producto #<span x-text="index + 1"></span>
                                    </span>
                                    <button type="button" @click="eliminarDetalle(index)"
                                            class="text-red-500 hover:text-red-700 text-sm font-medium">
                                        <i class="fas fa-trash mr-1"></i>Eliminar
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Producto <span class="text-red-500">*</span></label>
                                        <select x-model="detalle.producto_id" @change="onProductoChange(index)" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Seleccione producto</option>
                                            @foreach($productos as $producto)
                                                <option value="{{ $producto->id }}">
                                                    {{ $producto->codigo }} - {{ $producto->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Cantidad <span class="text-red-500">*</span></label>
                                        <input type="number" x-model.number="detalle.cantidad" min="1" required
                                               @input="calcularSubtotalDetalle(index)"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Precio Unit. <span class="text-red-500">*</span></label>
                                        <input type="number" x-model.number="detalle.precio_unitario" min="0.01" step="0.01" required
                                               @input="calcularSubtotalDetalle(index)"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>

                                {{-- Subtotal --}}
                                <div class="mt-3 text-right">
                                    <span class="text-sm text-gray-500">Subtotal: </span>
                                    <span class="text-sm font-bold text-gray-800" x-text="'S/ ' + (detalle.cantidad * detalle.precio_unitario).toFixed(2)"></span>
                                </div>

                                {{-- IMEIs para celulares --}}
                                <template x-if="detalle.tipo_producto === 'celular'">
                                    <div class="mt-4 border-t border-dashed border-purple-300 pt-4 bg-purple-50 rounded-lg p-4 -mx-1">
                                        <div class="flex justify-between items-center mb-3">
                                            <label class="text-xs font-bold text-purple-800">
                                                <i class="fas fa-mobile-alt mr-1"></i>IMEIs (debe ingresar <span x-text="detalle.cantidad"></span>)
                                            </label>
                                            <span class="text-xs px-2 py-1 rounded-full font-semibold"
                                                  :class="detalle.imeis.length === detalle.cantidad ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                                <span x-text="detalle.imeis.length"></span> / <span x-text="detalle.cantidad"></span>
                                            </span>
                                        </div>

                                        <template x-for="(imei, iIndex) in detalle.imeis" :key="iIndex">
                                            <div class="grid grid-cols-12 gap-2 mb-2">
                                                <div class="col-span-5">
                                                    <input type="text" x-model="imei.codigo_imei" maxlength="20" required
                                                           placeholder="IMEI (15 dígitos)"
                                                           class="w-full px-2 py-1.5 rounded border border-gray-300 text-xs focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                                </div>
                                                <div class="col-span-3">
                                                    <input type="text" x-model="imei.serie" placeholder="Serie (opcional)"
                                                           class="w-full px-2 py-1.5 rounded border border-gray-300 text-xs focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                                </div>
                                                <div class="col-span-3">
                                                    <input type="text" x-model="imei.color" placeholder="Color"
                                                           class="w-full px-2 py-1.5 rounded border border-gray-300 text-xs focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                                </div>
                                                <div class="col-span-1 flex items-center justify-center">
                                                    <button type="button" @click="detalle.imeis.splice(iIndex, 1)"
                                                            class="text-red-400 hover:text-red-600">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>

                                        <button type="button" @click="agregarImei(index)"
                                                x-show="detalle.imeis.length < detalle.cantidad"
                                                class="text-purple-600 hover:text-purple-800 text-xs font-semibold mt-2">
                                            <i class="fas fa-plus mr-1"></i>Agregar IMEI
                                        </button>

                                        <template x-if="detalle.imeis.length < detalle.cantidad">
                                            <p class="text-xs text-red-500 mt-2">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Faltan <span x-text="detalle.cantidad - detalle.imeis.length"></span> IMEI(s)
                                            </p>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Resumen --}}
                <template x-if="detalles.length > 0">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                        <div class="bg-blue-900 px-6 py-4">
                            <h2 class="text-lg font-bold text-white">
                                <i class="fas fa-calculator mr-2"></i>Resumen de Totales
                            </h2>
                        </div>
                        <div class="p-6 flex justify-end">
                            <div class="w-72 space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Subtotal:</span>
                                    <span class="font-semibold" x-text="'S/ ' + subtotal.toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">IGV (18%):</span>
                                    <span class="font-semibold" x-text="'S/ ' + igv.toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between text-xl border-t-2 border-gray-200 pt-3">
                                    <span class="font-bold text-gray-700">Total:</span>
                                    <span class="font-bold text-blue-900" x-text="'S/ ' + total.toFixed(2)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Botones --}}
                <div class="flex items-center justify-end space-x-3 pt-4">
                    <a href="{{ route('compras.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </a>
                    <button type="submit" :disabled="guardando || detalles.length === 0"
                            class="px-6 py-2.5 bg-blue-900 text-white rounded-lg hover:bg-blue-800 font-medium disabled:opacity-50 transition-colors">
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