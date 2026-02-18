{{-- resources/views/catalogo/motivos/edit.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Motivo - Catálogo</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header 
            title="Editar Motivo de Movimiento" 
            subtitle="Modificar información del motivo"
        />

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="{{ route('catalogo.motivos.update', $motivo) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $motivo->nombre) }}" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                            <input type="text" name="codigo" value="{{ old('codigo', $motivo->codigo) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                            <select name="tipo" required class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="ingreso" {{ $motivo->tipo == 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                                <option value="salida" {{ $motivo->tipo == 'salida' ? 'selected' : '' }}>Salida</option>
                                <option value="transferencia" {{ $motivo->tipo == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                <option value="ajuste" {{ $motivo->tipo == 'ajuste' ? 'selected' : '' }}>Ajuste</option>
                                <option value="otros" {{ $motivo->tipo == 'otros' ? 'selected' : '' }}>Otros</option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea name="descripcion" rows="3"
                                      class="w-full rounded-lg border-gray-300 shadow-sm">{{ old('descripcion', $motivo->descripcion) }}</textarea>
                        </div>

                        <div class="col-span-2 flex space-x-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="requiere_aprobacion" value="1" {{ $motivo->requiere_aprobacion ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-900">
                                <span class="ml-2 text-sm">Requiere aprobación</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="afecta_stock" value="1" {{ $motivo->afecta_stock ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-900">
                                <span class="ml-2 text-sm">Afecta stock</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="estado" class="w-full rounded-lg border-gray-300">
                                <option value="activo" {{ $motivo->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ $motivo->estado == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                        <a href="{{ route('catalogo.motivos.index') }}" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                            <i class="fas fa-save mr-2"></i>Actualizar Motivo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>