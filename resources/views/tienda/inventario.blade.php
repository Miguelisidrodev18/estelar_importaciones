@extends('layouts.app-layout')

@section('content')
<div class="container mx-auto p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
            <i class="fas fa-store mr-3 text-blue-900"></i>
            Inventario entre Tiendas
        </h1>
        <p class="text-gray-600 mt-1">Consulta el stock disponible en todas las tiendas</p>
    </div>

    <!-- Información de tienda actual -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-store text-blue-600 mr-3 text-xl"></i>
            <div>
                <p class="text-sm text-gray-600">Tu tienda actual:</p>
                <p class="font-semibold text-blue-900">{{ $tiendaActual->nombre }}</p>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar producto</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="Nombre o código">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                <select name="categoria_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Filtrar
                </button>
                <a href="{{ route('tienda.inventario.ver') }}" class="ml-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de productos -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase" colspan="{{ $almacenes->count() }}">
                        Stock por Tienda
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
                <tr class="bg-gray-100">
                    <th colspan="2"></th>
                    @foreach($almacenes as $almacen)
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 border-l">
                            {{ $almacen->nombre }}
                        </th>
                    @endforeach
                    <th></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($productos as $producto)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $producto->nombre }}</div>
                        <div class="text-xs text-gray-500">{{ $producto->codigo }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $producto->categoria->nombre }}</td>
                    
                    @foreach($almacenes as $almacen)
                        @php
                            $stock = $producto->stocks[$almacen->id] ?? null;
                            $cantidad = $stock ? $stock->cantidad : 0;
                            $esMiTienda = $almacen->id == $tiendaActual->id;
                        @endphp
                        <td class="px-3 py-4 text-center">
                            @if($esMiTienda)
                                <span class="font-bold text-blue-600">{{ $cantidad }}</span>
                            @else
                                <span class="{{ $cantidad > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ $cantidad }}
                                </span>
                            @endif
                        </td>
                    @endforeach
                    
                    <td class="px-6 py-4 text-center">
                        @php
                            $otrasTiendas = collect($producto->stocks)
                                ->filter(function($stock, $almacenId) use ($tiendaActual) {
                                    return $almacenId != $tiendaActual->id && $stock->cantidad > 0;
                                });
                        @endphp
                        
                        @if($otrasTiendas->count() > 0)
                            <button onclick="abrirModalTraslado({{ $producto->id }}, '{{ $producto->nombre }}')"
                                    class="text-blue-600 hover:text-blue-800"
                                    title="Solicitar traslado">
                                <i class="fas fa-truck"></i>
                            </button>
                        @else
                            <span class="text-gray-400" title="No hay stock en otras tiendas">
                                <i class="fas fa-truck"></i>
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $productos->links() }}
    </div>
</div>

<!-- Modal de solicitud de traslado -->
<div id="modalTraslado" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="cerrarModalTraslado()"></div>
    
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4 rounded-t-2xl">
            <h3 class="text-lg font-bold text-white flex items-center">
                <i class="fas fa-truck mr-2"></i>
                Solicitar Traslado
            </h3>
        </div>
        
        <form id="formTraslado" class="p-6">
            @csrf
            <input type="hidden" name="producto_id" id="traslado_producto_id">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                    <p id="traslado_producto_nombre" class="text-gray-900 font-medium"></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Almacén de origen</label>
                    <select name="almacen_origen_id" id="traslado_almacen_origen" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            required>
                        <option value="">Seleccionar...</option>
                        @foreach($almacenes as $almacen)
                            @if($almacen->id != $tiendaActual->id)
                                <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                    <input type="number" name="cantidad" id="traslado_cantidad" 
                           min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo (opcional)</label>
                    <textarea name="motivo" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Ej: Venta programada, stock bajo..."></textarea>
                </div>
                
                <div class="bg-blue-50 p-3 rounded-lg">
                    <p class="text-xs text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        El traslado quedará pendiente hasta que el almacén de origen lo confirme.
                    </p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="cerrarModalTraslado()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800">
                    <i class="fas fa-paper-plane mr-2"></i>Solicitar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalTraslado(productoId, productoNombre) {
    document.getElementById('traslado_producto_id').value = productoId;
    document.getElementById('traslado_producto_nombre').textContent = productoNombre;
    document.getElementById('modalTraslado').classList.remove('hidden');
    document.getElementById('modalTraslado').classList.add('flex');
}

function cerrarModalTraslado() {
    document.getElementById('modalTraslado').classList.add('hidden');
    document.getElementById('modalTraslado').classList.remove('flex');
    document.getElementById('formTraslado').reset();
}

document.getElementById('formTraslado').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("tienda.inventario.solicitar-traslado") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Solicitud enviada!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                cerrarModalTraslado();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo conectar al servidor'
        });
    });
});
</script>
@endsection