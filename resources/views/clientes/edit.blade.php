<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Sistema de Importaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />
    <x-header title="Editar Cliente" />

    <div class="ml-64 p-8 pt-24">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center mb-6">
                <a href="{{ route('clientes.index') }}" class="text-blue-600 hover:text-blue-800 mr-4"><i class="fas fa-arrow-left"></i></a>
                <h2 class="text-2xl font-bold text-gray-800">Editar Cliente</h2>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <form action="{{ route('clientes.update', $cliente) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Documento *</label>
                            <select name="tipo_documento" class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="DNI" {{ old('tipo_documento', $cliente->tipo_documento) === 'DNI' ? 'selected' : '' }}>DNI</option>
                                <option value="RUC" {{ old('tipo_documento', $cliente->tipo_documento) === 'RUC' ? 'selected' : '' }}>RUC</option>
                                <option value="CE" {{ old('tipo_documento', $cliente->tipo_documento) === 'CE' ? 'selected' : '' }}>CE</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número Documento *</label>
                            <input type="text" name="numero_documento" maxlength="11" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm @error('numero_documento') border-red-500 @enderror"
                                   value="{{ old('numero_documento', $cliente->numero_documento) }}">
                            @error('numero_documento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                            <input type="text" name="nombre" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm @error('nombre') border-red-500 @enderror"
                                   value="{{ old('nombre', $cliente->nombre) }}">
                            @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <input type="text" name="direccion" class="w-full rounded-lg border-gray-300 shadow-sm" value="{{ old('direccion', $cliente->direccion) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" maxlength="20" class="w-full rounded-lg border-gray-300 shadow-sm" value="{{ old('telefono', $cliente->telefono) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" class="w-full rounded-lg border-gray-300 shadow-sm" value="{{ old('email', $cliente->email) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="estado" class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="activo" {{ old('estado', $cliente->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ old('estado', $cliente->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <a href="{{ route('clientes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded-lg">Cancelar</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg"><i class="fas fa-save mr-2"></i>Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
