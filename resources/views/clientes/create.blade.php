<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Cliente - Sistema de Importaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />
    <x-header title="Nuevo Cliente" />

    <div class="ml-64 p-8 pt-24">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center mb-6">
                <a href="{{ route('clientes.index') }}" class="text-blue-600 hover:text-blue-800 mr-4"><i class="fas fa-arrow-left"></i></a>
                <h2 class="text-2xl font-bold text-gray-800">Registrar Cliente</h2>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6" x-data="clienteForm()">
                <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buscar por documento</label>
                    <div class="flex gap-2">
                        <select x-model="tipoBuscar" class="rounded-lg border-gray-300 shadow-sm">
                            <option value="DNI">DNI</option>
                            <option value="RUC">RUC</option>
                        </select>
                        <input type="text" x-model="numeroBuscar" :maxlength="tipoBuscar === 'DNI' ? 8 : 11" placeholder="Número de documento"
                               class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <button type="button" @click="consultarDocumento()" :disabled="cargando"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg disabled:opacity-50">
                            <span x-show="!cargando"><i class="fas fa-search mr-1"></i>Buscar</span>
                            <span x-show="cargando"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </div>
                    <p x-show="mensaje" x-text="mensaje" class="text-sm mt-2" :class="exito ? 'text-green-600' : 'text-red-600'"></p>
                </div>

                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Documento *</label>
                            <select name="tipo_documento" x-model="tipoDocumento" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="CE">CE</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número Documento *</label>
                            <input type="text" name="numero_documento" x-model="numeroDocumento" maxlength="11" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('numero_documento') border-red-500 @enderror">
                            @error('numero_documento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre / Razón Social *</label>
                            <input type="text" name="nombre" x-model="nombre" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('nombre') border-red-500 @enderror">
                            @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <input type="text" name="direccion" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" value="{{ old('direccion') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" maxlength="20" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" value="{{ old('telefono') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" value="{{ old('email') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="estado" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <a href="{{ route('clientes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded-lg">Cancelar</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg"><i class="fas fa-save mr-2"></i>Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function clienteForm() {
        return {
            tipoBuscar: 'DNI', numeroBuscar: '', tipoDocumento: '{{ old("tipo_documento", "DNI") }}',
            numeroDocumento: '{{ old("numero_documento") }}', nombre: '{{ old("nombre") }}',
            cargando: false, mensaje: '', exito: false,
            async consultarDocumento() {
                this.cargando = true; this.mensaje = '';
                try {
                    const res = await fetch('{{ route("clientes.consultar-documento") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ tipo: this.tipoBuscar, numero: this.numeroBuscar })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.tipoDocumento = this.tipoBuscar;
                        this.numeroDocumento = data.data.dni || data.data.ruc;
                        this.nombre = data.data.nombre || data.data.razon_social;
                        this.mensaje = 'Datos encontrados'; this.exito = true;
                    } else { this.mensaje = data.message; this.exito = false; }
                } catch (e) { this.mensaje = 'Error de conexión'; this.exito = false; }
                this.cargando = false;
            }
        }
    }
    </script>
</body>
</html>
