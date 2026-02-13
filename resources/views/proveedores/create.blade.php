<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Proveedor - Sistema de Importaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />
    <x-header title="Nuevo Proveedor" />

    <div class="ml-64 p-8 pt-24">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center mb-6">
                <a href="{{ route('proveedores.index') }}" class="text-blue-600 hover:text-blue-800 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="text-2xl font-bold text-gray-800">Registrar Proveedor</h2>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6" x-data="proveedorForm()">
                <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buscar por RUC (SUNAT)</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="rucBuscar" maxlength="11" placeholder="Ingrese RUC de 11 dígitos"
                               class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <button type="button" @click="consultarSunat()" :disabled="cargando"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg disabled:opacity-50">
                            <span x-show="!cargando"><i class="fas fa-search mr-1"></i>Buscar</span>
                            <span x-show="cargando"><i class="fas fa-spinner fa-spin mr-1"></i>Buscando...</span>
                        </button>
                    </div>
                    <p x-show="mensajeSunat" x-text="mensajeSunat" class="text-sm mt-2" :class="sunatExito ? 'text-green-600' : 'text-red-600'"></p>
                </div>

                <form action="{{ route('proveedores.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">RUC *</label>
                            <input type="text" name="ruc" x-model="ruc" maxlength="11" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('ruc') border-red-500 @enderror"
                                   value="{{ old('ruc') }}">
                            @error('ruc')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Razón Social *</label>
                            <input type="text" name="razon_social" x-model="razonSocial" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('razon_social') border-red-500 @enderror"
                                   value="{{ old('razon_social') }}">
                            @error('razon_social')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Comercial</label>
                            <input type="text" name="nombre_comercial" x-model="nombreComercial"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   value="{{ old('nombre_comercial') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <input type="text" name="direccion" x-model="direccion"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   value="{{ old('direccion') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" maxlength="20"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   value="{{ old('telefono') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   value="{{ old('email') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Contacto</label>
                            <input type="text" name="contacto_nombre"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   value="{{ old('contacto_nombre') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="estado" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                <option value="activo" {{ old('estado', 'activo') === 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ old('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <a href="{{ route('proveedores.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded-lg transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function proveedorForm() {
        return {
            rucBuscar: '',
            ruc: '{{ old('ruc') }}',
            razonSocial: '{{ old('razon_social') }}',
            nombreComercial: '{{ old('nombre_comercial') }}',
            direccion: '{{ old('direccion') }}',
            cargando: false,
            mensajeSunat: '',
            sunatExito: false,

            async consultarSunat() {
                if (this.rucBuscar.length !== 11) {
                    this.mensajeSunat = 'El RUC debe tener 11 dígitos';
                    this.sunatExito = false;
                    return;
                }
                this.cargando = true;
                this.mensajeSunat = '';
                try {
                    const response = await fetch('{{ route("proveedores.consultar-sunat") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ ruc: this.rucBuscar })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.ruc = data.data.ruc;
                        this.razonSocial = data.data.razon_social;
                        this.nombreComercial = data.data.nombre_comercial || '';
                        this.direccion = data.data.direccion || '';
                        this.mensajeSunat = 'Datos encontrados en SUNAT';
                        this.sunatExito = true;
                    } else {
                        this.mensajeSunat = data.message;
                        this.sunatExito = false;
                    }
                } catch (e) {
                    this.mensajeSunat = 'Error de conexión';
                    this.sunatExito = false;
                }
                this.cargando = false;
            }
        }
    }
    </script>
</body>
</html>
