<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Traslado - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8 ">
        <x-header 
            title="Registrar Nuevo Traslado" 
            subtitle="Complete el formulario para agregar un nuevo traslado al sistema"
        />
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
        @endif

        <div class="flex items-center mb-6">
            <a href="{{ route('traslados.index') }}" class="text-blue-600 hover:text-blue-800 mr-4"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-2xl font-bold text-gray-800">Registrar Traslado</h2>
        </div>

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-md p-6">
                <form action="{{ route('traslados.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Producto *</label>
                            <select name="producto_id" required class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">Seleccione...</option>
                                @foreach($productos as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->nombre }} ({{ $prod->tipo_producto }})</option>
                                @endforeach
                            </select>
                            @error('producto_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Almacén Origen *</label>
                                <select name="almacen_id" required class="w-full rounded-lg border-gray-300 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    @foreach($almacenes as $alm)
                                        <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('almacen_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Almacén Destino *</label>
                                <select name="almacen_destino_id" required class="w-full rounded-lg border-gray-300 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    @foreach($almacenes as $alm)
                                        <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('almacen_destino_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
                            <input type="number" name="cantidad" min="1" required class="w-full rounded-lg border-gray-300 shadow-sm" value="{{ old('cantidad', 1) }}">
                            @error('cantidad') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transportista</label>
                            <input type="text" name="transportista" class="w-full rounded-lg border-gray-300 shadow-sm" value="{{ old('transportista') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea name="observaciones" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm">{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <p class="text-sm text-yellow-700"><i class="fas fa-info-circle mr-1"></i>El stock se descontará del origen inmediatamente. El destino recibirá el stock cuando confirme la recepción.</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <a href="{{ route('traslados.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-6 rounded-lg">Cancelar</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg"><i class="fas fa-paper-plane mr-2"></i>Enviar Traslado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
