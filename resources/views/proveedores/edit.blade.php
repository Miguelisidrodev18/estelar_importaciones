<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor - Sistema de Importaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />
    <x-header title="Editar Proveedor" />

    <div class="ml-64 p-8 pt-24">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center mb-6">
                <a href="{{ route('proveedores.index') }}" class="text-blue-600 hover:text-blue-800 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="text-2xl font-bold text-gray-800">Editar Proveedor</h2>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <form action="{{ route('proveedores.update', $proveedor) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">RUC *</label>
                            <input type="text" name="ruc" maxlength="11" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('ruc') border-red-500 @enderror"
                                   value="{{ old('ruc', $proveedor->ruc) }}">
                            @error('ruc') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Razón Social *</label>
                            <input type="text" name="razon_social" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('razon_social') border-red-500 @enderror"
                                   value="{{ old('razon_social', $proveedor->razon_social) }}">
                            @error('razon_social') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Comercial</label>
                            <input type="text" name="nombre_comercial"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   value="{{ old('nombre_comercial', $proveedor->nombre_comercial) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <input type="text" name="direccion"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   value="{{ old('direccion', $proveedor->direccion) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" maxlength="20"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   value="{{ old('telefono', $proveedor->telefono) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   value="{{ old('email', $proveedor->email) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Contacto</label>
                            <input type="text" name="contacto_nombre"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   value="{{ old('contacto_nombre', $proveedor->contacto_nombre) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="estado" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                <option value="activo" {{ old('estado', $proveedor->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ old('estado', $proveedor->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <a href="{{ route('proveedores.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded-lg transition-colors">Cancelar</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
