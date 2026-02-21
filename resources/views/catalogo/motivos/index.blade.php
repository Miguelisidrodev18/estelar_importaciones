{{-- resources/views/catalogo/motivos/index.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motivos de Movimiento - Catálogo</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Motivos de Movimiento"
            subtitle="Gestión de motivos para movimientos de inventario"
        />

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @php
            $total     = \App\Models\Catalogo\MotivoMovimiento::count();
            $activos   = \App\Models\Catalogo\MotivoMovimiento::where('estado', 'activo')->count();
            $inactivos = \App\Models\Catalogo\MotivoMovimiento::where('estado', 'inactivo')->count();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-4 flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Total Motivos</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $total }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-4 flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Activos</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $activos }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-red-400 p-4 flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Inactivos</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $inactivos }}</p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-times-circle text-red-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <h2 class="text-lg font-bold text-gray-800">Lista de Motivos</h2>
                <a href="{{ route('catalogo.motivos.create') }}"
                   class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                    <i class="fas fa-plus"></i>Nuevo Motivo
                </a>
            </div>
            <form method="GET" action="{{ route('catalogo.motivos.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           placeholder="Buscar por nombre o código..."
                           class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <select name="tipo" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todos los tipos</option>
                        <option value="ingreso"       {{ request('tipo') == 'ingreso'       ? 'selected' : '' }}>Ingreso</option>
                        <option value="salida"        {{ request('tipo') == 'salida'        ? 'selected' : '' }}>Salida</option>
                        <option value="transferencia" {{ request('tipo') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                        <option value="ajuste"        {{ request('tipo') == 'ajuste'        ? 'selected' : '' }}>Ajuste</option>
                        <option value="otros"         {{ request('tipo') == 'otros'         ? 'selected' : '' }}>Otros</option>
                    </select>
                </div>
                <div>
                    <select name="estado" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todos los estados</option>
                        <option value="activo"   {{ request('estado') == 'activo'   ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-900 hover:bg-blue-800 text-white text-sm px-3 py-2 rounded-lg transition">
                        <i class="fas fa-search mr-1"></i>Filtrar
                    </button>
                    @if(request()->hasAny(['buscar','tipo','estado']))
                        <a href="{{ route('catalogo.motivos.index') }}"
                           class="flex-1 text-center text-sm border border-gray-300 rounded-lg px-3 py-2 text-gray-600 hover:bg-gray-50 transition">
                            <i class="fas fa-times mr-1"></i>Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Afecta Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aprobación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($motivos as $motivo)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-mono text-sm text-gray-500">{{ $motivo->codigo ?? '-' }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $motivo->nombre }}</td>
                        <td class="px-6 py-4">
                            @php
                                $tipoColors = [
                                    'ingreso'       => 'bg-green-100 text-green-700',
                                    'salida'        => 'bg-red-100 text-red-700',
                                    'transferencia' => 'bg-blue-100 text-blue-700',
                                    'ajuste'        => 'bg-amber-100 text-amber-700',
                                    'otros'         => 'bg-gray-100 text-gray-600',
                                ];
                                $tipoCss = $tipoColors[$motivo->tipo] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $tipoCss }}">
                                {{ ucfirst($motivo->tipo) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($motivo->afecta_stock)
                                <span class="inline-flex items-center gap-1 text-green-600 text-sm">
                                    <i class="fas fa-check-circle"></i> Sí
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-gray-400 text-sm">
                                    <i class="fas fa-times-circle"></i> No
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($motivo->requiere_aprobacion)
                                <span class="inline-flex items-center gap-1 text-amber-600 text-sm">
                                    <i class="fas fa-shield-alt"></i> Requerida
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-gray-400 text-sm">
                                    <i class="fas fa-times-circle"></i> No
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $motivo->estado == 'activo' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $motivo->estado == 'activo' ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                {{ ucfirst($motivo->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('catalogo.motivos.edit', $motivo) }}"
                                   class="text-yellow-600 hover:text-yellow-800 transition" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('catalogo.motivos.destroy', $motivo) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 transition" title="Eliminar"
                                            onclick="return confirm('¿Eliminar el motivo «{{ $motivo->nombre }}»?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            <i class="fas fa-exchange-alt text-4xl mb-3 block"></i>
                            <p class="font-medium">No se encontraron motivos de movimiento</p>
                            <a href="{{ route('catalogo.motivos.create') }}" class="text-blue-600 text-sm mt-1 inline-block hover:underline">
                                Crear el primer motivo
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $motivos->withQueryString()->links() }}</div>
    </div>
</body>
</html>
