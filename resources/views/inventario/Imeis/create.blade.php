<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar IMEI - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="ml-64 p-8">
        <x-header 
            title="Registrar IMEI" 
            subtitle="Ingreso individual de celular con código IMEI" 
        />

        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-900 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-mobile-alt mr-2"></i>
                        Datos del Celular
                    </h2>
                </div>

                <form action="{{ route('inventario.imeis.store') }}" method="POST" class="p-6">
                    @csrf

                    <div class="space-y-6">
                        <!-- Código IMEI -->
                        <div>
                            <label for="codigo_imei" class="block text-sm font-medium text-gray-700 mb-2">
                                Código IMEI <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="codigo_imei" id="codigo_imei" value="{{ old('codigo_imei') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono text-lg"
                                   placeholder="Ej: 123456789012345" maxlength="20" required>
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Ingresa el código IMEI de 15 dígitos (único por dispositivo)
                            </p>
                            @error('codigo_imei')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Producto -->
                        <div>
                            <label for="producto_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Producto (Celular) <span class="text-red-500">*</span>
                            </label>
                            <select name="producto_id" id="producto_id" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    required>
                                <option value="">Seleccione un producto</option>
                                @foreach($productos as $producto)
                                    <option value="{{ $producto->id }}" {{ old('producto_id') == $producto->id ? 'selected' : '' }}>
                                        {{ $producto->nombre }} - {{ $producto->marca }} {{ $producto->modelo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('producto_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Almacén -->
                        <div>
                            <label for="almacen_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Almacén <span class="text-red-500">*</span>
                            </label>
                            <select name="almacen_id" id="almacen_id" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    required>
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

                        <!-- Número de Serie -->
                        <div>
                            <label for="serie" class="block text-sm font-medium text-gray-700 mb-2">
                                Número de Serie
                            </label>
                            <input type="text" name="serie" id="serie" value="{{ old('serie') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="Número de serie del dispositivo">
                            <p class="mt-1 text-xs text-gray-500">Opcional - Número de serie adicional</p>
                        </div>

                        <!-- Color -->
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                                Color
                            </label>
                            <input type="text" name="color" id="color" value="{{ old('color') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="Ej: Negro, Blanco, Azul...">
                            <p class="mt-1 text-xs text-gray-500">Opcional - Color del dispositivo</p>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                Estado <span class="text-red-500">*</span>
                            </label>
                            <select name="estado" id="estado" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    required>
                                <option value="disponible" selected>Disponible</option>
                                <option value="reservado">Reservado</option>
                                <option value="dañado">Dañado</option>
                            </select>
                        </div>
                    </div>

                    <!-- Nota Informativa -->
                    <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                        <div class="flex">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                            <div class="text-sm text-blue-900">
                                <p class="font-medium">Información importante:</p>
                                <ul class="mt-2 list-disc list-inside space-y-1 text-blue-800">
                                    <li>El código IMEI debe ser único (no se puede duplicar)</li>
                                    <li>El dispositivo se registrará en el almacén seleccionado</li>
                                    <li>El stock del producto se incrementará automáticamente</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                        <a href="{{ route('inventario.imeis.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                            <i class="fas fa-save mr-2"></i>Registrar IMEI
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>