<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Almacenes y Tiendas - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Almacenes y Tiendas"
            subtitle="Puntos de venta y almacenes de inventario"
        />

        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-xl"></i>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        {{-- Estadísticas --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-blue-900">
                <p class="text-xs text-gray-500 font-medium">Total</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-400 mt-1"><i class="fas fa-warehouse mr-1"></i>Ubicaciones</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-green-500">
                <p class="text-xs text-gray-500 font-medium">Activos</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['activos'] }}</p>
                <p class="text-xs text-gray-400 mt-1"><i class="fas fa-check-circle mr-1"></i>En operación</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-purple-500">
                <p class="text-xs text-gray-500 font-medium">Principal</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['principal'] }}</p>
                <p class="text-xs text-gray-400 mt-1"><i class="fas fa-star mr-1"></i>Almacén central</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-orange-500">
                <p class="text-xs text-gray-500 font-medium">Tiendas</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['tiendas'] }}</p>
                <p class="text-xs text-gray-400 mt-1"><i class="fas fa-store mr-1"></i>Puntos de venta</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-teal-500">
                <p class="text-xs text-gray-500 font-medium">Depósitos</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['depositos'] }}</p>
                <p class="text-xs text-gray-400 mt-1"><i class="fas fa-boxes mr-1"></i>Almacenes</p>
            </div>
        </div>

        {{-- Filtro estado --}}
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6 flex items-center gap-4">
            <form action="{{ route('inventario.almacenes.index') }}" method="GET" class="flex items-center gap-3 flex-1">
                <label class="text-sm font-medium text-gray-600 shrink-0">Filtrar por estado:</label>
                <select name="estado" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    <option value="activo"   {{ request('estado') === 'activo'   ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
                @if(request('estado'))
                    <a href="{{ route('inventario.almacenes.index') }}" class="text-xs text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times mr-1"></i>Limpiar
                    </a>
                @endif
            </form>
            @if($canCreate)
                <a href="{{ route('inventario.almacenes.create') }}"
                    class="bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold px-4 py-2 rounded-lg flex items-center gap-2 transition-colors shrink-0">
                    <i class="fas fa-plus"></i> Nuevo
                </a>
            @endif
        </div>

        {{-- ── SECCIÓN: PUNTOS DE VENTA ──────────────────────────────────────── --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                    <i class="fas fa-store text-orange-600"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">Puntos de Venta</h2>
                    <p class="text-xs text-gray-400">Tiendas habilitadas para emitir comprobantes</p>
                </div>
                <span class="ml-auto text-xs font-semibold px-2.5 py-0.5 rounded-full bg-orange-100 text-orange-700">
                    {{ $tiendas->count() }} registro(s)
                </span>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                @if($tiendas->isEmpty())
                    <div class="py-14 text-center text-gray-400">
                        <i class="fas fa-store text-4xl mb-3 block opacity-30"></i>
                        <p class="text-sm">No hay puntos de venta registrados.</p>
                        @if($canCreate)
                            <a href="{{ route('inventario.almacenes.create') }}"
                                class="inline-flex items-center gap-2 mt-4 text-sm text-blue-600 hover:underline">
                                <i class="fas fa-plus"></i> Crear punto de venta
                            </a>
                        @endif
                    </div>
                @else
                    @include('inventario.almacenes._tabla', ['items' => $tiendas, 'canEdit' => $canEdit, 'canDelete' => $canDelete])
                @endif
            </div>
        </div>

        {{-- ── SECCIÓN: ALMACENES / DEPÓSITOS ───────────────────────────────── --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center">
                    <i class="fas fa-warehouse text-teal-600"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">Almacenes</h2>
                    <p class="text-xs text-gray-400">Depósitos y almacenes de inventario</p>
                </div>
                <span class="ml-auto text-xs font-semibold px-2.5 py-0.5 rounded-full bg-teal-100 text-teal-700">
                    {{ $depositos->count() }} registro(s)
                </span>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                @if($depositos->isEmpty())
                    <div class="py-14 text-center text-gray-400">
                        <i class="fas fa-warehouse text-4xl mb-3 block opacity-30"></i>
                        <p class="text-sm">No hay almacenes registrados.</p>
                        @if($canCreate)
                            <a href="{{ route('inventario.almacenes.create') }}"
                                class="inline-flex items-center gap-2 mt-4 text-sm text-blue-600 hover:underline">
                                <i class="fas fa-plus"></i> Crear almacén
                            </a>
                        @endif
                    </div>
                @else
                    @include('inventario.almacenes._tabla', ['items' => $depositos, 'canEdit' => $canEdit, 'canDelete' => $canDelete])
                @endif
            </div>
        </div>

    </div>
</body>
</html>
