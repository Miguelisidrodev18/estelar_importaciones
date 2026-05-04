<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-100">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Código</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Sucursal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Encargado</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wide">Estado</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wide">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
            @foreach($items as $almacen)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-mono font-semibold text-gray-700">{{ $almacen->codigo }}</span>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm font-medium text-gray-900">{{ $almacen->nombre }}</p>
                    @if($almacen->telefono)
                        <p class="text-xs text-gray-400 mt-0.5"><i class="fas fa-phone mr-1"></i>{{ $almacen->telefono }}</p>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($almacen->sucursal)
                        <span class="inline-flex items-center gap-1 text-sm text-blue-700 font-medium">
                            <i class="fas fa-store text-blue-400 text-xs"></i>
                            {{ $almacen->sucursal->nombre }}
                        </span>
                    @else
                        <span class="text-sm text-gray-400 italic">Sin sucursal</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @php
                        $tipoBadge = match($almacen->tipo) {
                            'principal' => ['bg-purple-100 text-purple-800', 'fa-star',      'Principal'],
                            'tienda'    => ['bg-orange-100 text-orange-800', 'fa-store',     'Tienda'],
                            'deposito'  => ['bg-teal-100 text-teal-800',    'fa-boxes',     'Depósito'],
                            'temporal'  => ['bg-gray-100 text-gray-700',    'fa-clock',     'Temporal'],
                            default     => ['bg-gray-100 text-gray-600',    'fa-warehouse', ucfirst($almacen->tipo)],
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tipoBadge[0] }}">
                        <i class="fas {{ $tipoBadge[1] }}"></i>{{ $tipoBadge[2] }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm {{ $almacen->encargado ? 'text-gray-900' : 'text-gray-400 italic' }}">
                        {{ $almacen->encargado ? $almacen->nombre_encargado : 'Sin asignar' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    @if($almacen->estado === 'activo')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactivo</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('inventario.almacenes.show', $almacen) }}"
                            class="text-purple-500 hover:text-purple-800 transition-colors" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($canEdit)
                            <a href="{{ route('inventario.almacenes.edit', $almacen) }}"
                                class="text-blue-500 hover:text-blue-800 transition-colors" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                        @if($canDelete)
                            <form action="{{ route('inventario.almacenes.destroy', $almacen) }}" method="POST"
                                class="inline" onsubmit="return confirm('¿Eliminar {{ $almacen->nombre }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-700 transition-colors" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
