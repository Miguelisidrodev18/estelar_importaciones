@props(['role'])

{{-- Mobile hamburger button --}}
<div x-data="{ sidebarOpen: false }">
    <button @click="sidebarOpen = true"
            class="md:hidden fixed top-4 left-4 z-40 bg-blue-900 text-white p-2 rounded-lg shadow-lg">
        <i class="fas fa-bars text-xl"></i>
    </button>

    {{-- Overlay --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="md:hidden fixed inset-0 bg-black/50 z-40" style="display: none;"></div>

    {{-- Sidebar --}}
    <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="fixed left-0 top-0 h-full w-64 bg-gradient-to-b from-blue-900 to-blue-800 text-white shadow-xl z-50 transition-transform duration-300 ease-in-out"
            x-data="{
                inventarioOpen: {{ request()->routeIs('inventario.*') ? 'true' : 'false' }},
                comprasOpen: {{ request()->routeIs('compras.*') || request()->routeIs('pedidos.*') || request()->routeIs('proveedores.*') || request()->routeIs('cuentas-por-pagar.*') ? 'true' : 'false' }},
                ventasOpen: {{ request()->routeIs('ventas.*') || request()->routeIs('clientes.*') || request()->routeIs('precios.*') ? 'true' : 'false' }},
                trasladosOpen: {{ request()->routeIs('traslados.*') ? 'true' : 'false' }},
                cajaOpen: {{ request()->routeIs('caja.*') ? 'true' : 'false' }},
                catalogoOpen: {{ request()->routeIs('catalogo.*') ? 'true' : 'false' }},
                tiendaOpen: {{ request()->routeIs('tienda.*') ? 'true' : 'false' }},
                adminOpen: {{ request()->routeIs('admin.empresa.*') || request()->routeIs('admin.sucursales.*') || request()->routeIs('admin.cajas.*') ? 'true' : 'false' }}
            }">

        @php $empresa = \App\Models\Empresa::instancia(); @endphp
        <div class="p-4 border-b border-blue-700 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                @if($empresa?->logo_url)
                    <img src="{{ $empresa->logo_url }}" alt="Logo" class="h-10 w-10 object-contain rounded-lg bg-white/10 p-0.5 shrink-0">
                @else
                    <i class="fas fa-home text-2xl text-blue-300 shrink-0"></i>
                @endif
                <div class="min-w-0">
                    <h1 class="text-sm font-bold leading-tight truncate">{{ $empresa?->nombre_display ?? 'CORPORACIÓN' }}</h1>
                    @if($empresa && $empresa->nombre_comercial && $empresa->nombre_comercial !== $empresa->razon_social)
                        <p class="text-[11px] text-blue-300 truncate">{{ $empresa->razon_social }}</p>
                    @elseif(!$empresa)
                        <p class="text-[11px] text-blue-300 truncate">ADIVON SAC</p>
                    @endif
                </div>
            </div>
            <button @click="sidebarOpen = false" class="md:hidden text-blue-300 hover:text-white shrink-0">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-4 bg-blue-800/50">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-600 rounded-full p-2">
                    <i class="fas fa-user text-white"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-blue-300">{{ $role }}</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto p-4" style="max-height: calc(100vh - 220px);">
            <ul class="space-y-2">

                @if($role == 'Administrador')
                    {{-- Dashboard --}}
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                            class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-700' : '' }}">
                            <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                        </a>
                    </li>

                    {{-- Inventario --}}
                    <li>
                        <button @click="inventarioOpen = !inventarioOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-boxes mr-3"></i>Inventario
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': inventarioOpen }"></i>
                        </button>
                        <ul x-show="inventarioOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('inventario.categorias.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.categorias.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-tags mr-3 text-sm"></i>Categorías
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventario.productos.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.productos.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-box mr-3 text-sm"></i>Productos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventario.almacenes.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.almacenes.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-warehouse mr-3 text-sm"></i>Almacenes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventario.imeis.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.imeis.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-mobile-alt mr-3 text-sm"></i>IMEIs
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventario.movimientos.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.movimientos.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-exchange-alt mr-3 text-sm"></i>Movimientos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventario.reportes.stock-valorizado') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.reportes.stock-valorizado') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-coins mr-3 text-sm"></i>Stock Valorizado
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventario.reportes.kardex') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.reportes.kardex') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-book-open mr-3 text-sm"></i>Kardex
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventario.reportes.abc') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.reportes.abc') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-chart-bar mr-3 text-sm"></i>Análisis ABC
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Compras --}}
                    <li>
                        <button @click="comprasOpen = !comprasOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('compras.*') || request()->routeIs('pedidos.*') || request()->routeIs('proveedores.*') || request()->routeIs('cuentas-por-pagar.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-shopping-bag mr-3"></i>Compras
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': comprasOpen }"></i>
                        </button>
                        <ul x-show="comprasOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('proveedores.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('proveedores.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-truck mr-3 text-sm"></i>Proveedores
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('compras.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('compras.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-file-invoice mr-3 text-sm"></i>Registrar Compras
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('cuentas-por-pagar.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('cuentas-por-pagar.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-credit-card mr-3 text-sm"></i>Cuentas por Pagar
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('pedidos.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('pedidos.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-clipboard-list mr-3 text-sm"></i>Pedidos a Proveedor
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Ventas y Precios --}}
                    <li>
                        <button @click="ventasOpen = !ventasOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('ventas.*') || request()->routeIs('clientes.*') || request()->routeIs('precios.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-cash-register mr-3"></i>Ventas
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': ventasOpen }"></i>
                        </button>
                        <ul x-show="ventasOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('clientes.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('clientes.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-users mr-3 text-sm"></i>Clientes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('ventas.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('ventas.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-receipt mr-3 text-sm"></i>Registrar Ventas
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('precios.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('precios.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-tags mr-3 text-sm"></i>Gestión de Precios
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Traslados --}}
                    <li>
                        <button @click="trasladosOpen = !trasladosOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('traslados.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-truck-loading mr-3"></i>Traslados
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': trasladosOpen }"></i>
                        </button>
                        <ul x-show="trasladosOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('traslados.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('traslados.index') || request()->routeIs('traslados.show') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-exchange-alt mr-3 text-sm"></i>Historial
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('traslados.stock') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('traslados.stock') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-boxes mr-3 text-sm"></i>Ver Stock
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('traslados.pendientes') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('traslados.pendientes') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-clock mr-3 text-sm"></i>Pendientes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('traslados.create') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('traslados.create') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-plus mr-3 text-sm"></i>Nuevo Traslado
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Caja --}}
                    <li>
                        <button @click="cajaOpen = !cajaOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('caja.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-cash-register mr-3"></i>Caja
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': cajaOpen }"></i>
                        </button>
                        <ul x-show="cajaOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('caja.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('caja.index') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-history mr-3 text-sm"></i>Historial de Cajas
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('caja.actual') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('caja.actual') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-door-open mr-3 text-sm"></i>Caja Activa
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Catálogo --}}
                    <li>
                        <button @click="catalogoOpen = !catalogoOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('catalogo.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-book mr-3"></i>Catálogo
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': catalogoOpen }"></i>
                        </button>
                        <ul x-show="catalogoOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('catalogo.colores.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('catalogo.colores.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-palette mr-3 text-sm"></i>Colores
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('catalogo.marcas.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('catalogo.marcas.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-trademark mr-3 text-sm"></i>Marcas
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('catalogo.modelos.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('catalogo.modelos.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-mobile-alt mr-3 text-sm"></i>Modelos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('catalogo.unidades.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('catalogo.unidades.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-ruler mr-3 text-sm"></i>Unidades de Medida
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('catalogo.motivos.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('catalogo.motivos.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-exchange-alt mr-3 text-sm"></i>Motivos de Movimiento
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Administración --}}
                    <li>
                        <button @click="adminOpen = !adminOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('admin.empresa.*') || request()->routeIs('admin.sucursales.*') || request()->routeIs('admin.cajas.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-cogs mr-3"></i>Administración
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': adminOpen }"></i>
                        </button>
                        <ul x-show="adminOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('admin.empresa.edit') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('admin.empresa.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-building mr-3 text-sm"></i>Empresa
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.sucursales.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('admin.sucursales.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-store mr-3 text-sm"></i>Sucursales
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.cajas.dashboard') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('admin.cajas.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-cash-register mr-3 text-sm"></i>
                                    <span>Supervisión Cajas</span>
                                    @php $_alertasCaja = app(\App\Http\Controllers\Admin\AdminCajaController::class)->contarAlertas(); @endphp
                                    @if($_alertasCaja > 0)
                                        <span class="ml-auto bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center font-bold shrink-0">
                                            {{ $_alertasCaja > 9 ? '9+' : $_alertasCaja }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Usuarios --}}
                    <li>
                        <a href="{{ route('users.index') }}"
                            class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('users.*') ? 'bg-blue-700' : '' }}">
                            <i class="fas fa-users mr-3"></i>Usuarios
                        </a>
                    </li>

                @elseif($role == 'Almacenero')
                    {{-- Dashboard --}}
                    <li>
                        <a href="{{ route('almacenero.dashboard') }}"
                            class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('almacenero.dashboard') ? 'bg-blue-700' : '' }}">
                            <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                        </a>
                    </li>

                    {{-- Inventario --}}
                    <li>
                        <button @click="inventarioOpen = !inventarioOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-boxes mr-3"></i>Inventario
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': inventarioOpen }"></i>
                        </button>
                        <ul x-show="inventarioOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('inventario.productos.index') }}"
                                     class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.productos.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-box mr-3 text-sm"></i>Productos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventario.almacenes.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.almacenes.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-warehouse mr-3 text-sm"></i>Almacenes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventario.imeis.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.imeis.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-mobile-alt mr-3 text-sm"></i>IMEIs
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('inventario.movimientos.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.movimientos.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-exchange-alt mr-3 text-sm"></i>Movimientos
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Compras --}}
                    <li>
                        <button @click="comprasOpen = !comprasOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('compras.*') || request()->routeIs('pedidos.*') || request()->routeIs('proveedores.*') || request()->routeIs('cuentas-por-pagar.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-shopping-bag mr-3"></i>Compras
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': comprasOpen }"></i>
                        </button>
                        <ul x-show="comprasOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('proveedores.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('proveedores.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-truck mr-3 text-sm"></i>Proveedores
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('compras.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('compras.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-file-invoice mr-3 text-sm"></i>Registrar Compras
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('cuentas-por-pagar.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('cuentas-por-pagar.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-credit-card mr-3 text-sm"></i>Cuentas por Pagar
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('pedidos.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('pedidos.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-clipboard-list mr-3 text-sm"></i>Pedidos a Proveedor
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Traslados --}}
                    <li>
                        <button @click="trasladosOpen = !trasladosOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('traslados.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-truck-loading mr-3"></i>Traslados
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': trasladosOpen }"></i>
                        </button>
                        <ul x-show="trasladosOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('traslados.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('traslados.index') || request()->routeIs('traslados.show') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-exchange-alt mr-3 text-sm"></i>Historial
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('traslados.stock') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('traslados.stock') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-boxes mr-3 text-sm"></i>Ver Stock
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('traslados.pendientes') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('traslados.pendientes') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-clock mr-3 text-sm"></i>Pendientes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('traslados.create') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('traslados.create') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-plus mr-3 text-sm"></i>Nuevo Traslado
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Catálogo --}}
                    <li>
                        <button @click="catalogoOpen = !catalogoOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('catalogo.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-book mr-3"></i>Consultar Catálogo
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': catalogoOpen }"></i>
                        </button>
                        <ul x-show="catalogoOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('catalogo.colores.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-palette mr-3 text-sm"></i>Colores
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('catalogo.marcas.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-trademark mr-3 text-sm"></i>Marcas
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('catalogo.modelos.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-mobile-alt mr-3 text-sm"></i>Modelos
                                </a>
                            </li>
                        </ul>
                    </li>

                @elseif($role == 'Tienda')
                    {{-- Dashboard de Tienda --}}
                    <li>
                        <a href="{{ route('tienda.dashboard') }}"
                            class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('tienda.dashboard') ? 'bg-blue-700' : '' }}">
                            <i class="fas fa-store mr-3"></i>Dashboard Tienda
                        </a>
                    </li>

                    {{-- Ventas --}}
                    <li>
                        <button @click="ventasOpen = !ventasOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('ventas.*') || request()->routeIs('clientes.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-cash-register mr-3"></i>Ventas
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': ventasOpen }"></i>
                        </button>
                        <ul x-show="ventasOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('clientes.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('clientes.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-users mr-3 text-sm"></i>Clientes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('ventas.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('ventas.*') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-receipt mr-3 text-sm"></i>Ventas
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- NUEVO: Inventario entre Tiendas --}}
                    <li>
                        <button @click="tiendaOpen = !tiendaOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('tienda.inventario.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-store-alt mr-3"></i>Inventario Tiendas
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': tiendaOpen }"></i>
                        </button>
                        <ul x-show="tiendaOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('tienda.inventario.ver') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('tienda.inventario.ver') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-boxes mr-3 text-sm"></i>Ver stock
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('tienda.inventario.solicitudes') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('tienda.inventario.solicitudes') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-clipboard-list mr-3 text-sm"></i>Mis solicitudes
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Traslados Pendientes --}}
                    <li>
                        <a href="{{ route('traslados.pendientes') }}"
                            class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('traslados.pendientes') ? 'bg-blue-700' : '' }}">
                            <i class="fas fa-truck-loading mr-3"></i>Traslados Pendientes
                        </a>
                    </li>

                    {{-- Caja --}}
                    <li>
                        <button @click="cajaOpen = !cajaOpen"
                                class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('caja.*') ? 'bg-blue-700' : '' }}">
                            <span class="flex items-center">
                                <i class="fas fa-cash-register mr-3"></i>Mi Caja
                            </span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': cajaOpen }"></i>
                        </button>
                        <ul x-show="cajaOpen" x-transition class="ml-4 mt-2 space-y-1">
                            <li>
                                <a href="{{ route('caja.actual') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('caja.actual') || request()->routeIs('caja.abrir') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-door-open mr-3 text-sm"></i>Caja Actual
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('caja.index') }}"
                                    class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('caja.index') ? 'bg-blue-600' : '' }}">
                                    <i class="fas fa-history mr-3 text-sm"></i>Historial
                                </a>
                            </li>
                        </ul>
                    </li>

                @elseif($role == 'Vendedor')
                    {{-- Dashboard --}}
                    <li>
                        <a href="{{ route('vendedor.dashboard') }}"
                           class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('vendedor.dashboard') ? 'bg-blue-700' : '' }}">
                            <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                        </a>
                    </li>

                    {{-- Ventas --}}
                    <li>
                        <a href="{{ route('ventas.index') }}"
                            class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('ventas.*') ? 'bg-blue-700' : '' }}">
                            <i class="fas fa-shopping-cart mr-3"></i>Mis Ventas
                        </a>
                    </li>

                    {{-- Clientes --}}
                    <li>
                        <a href="{{ route('clientes.index') }}"
                            class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('clientes.*') ? 'bg-blue-700' : '' }}">
                            <i class="fas fa-users mr-3"></i>Clientes
                        </a>
                    </li>

                @elseif($role == 'Proveedor')
                    {{-- Dashboard --}}
                    <li>
                        <a href="{{ route('proveedor.dashboard') }}"
                            class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('proveedor.dashboard') ? 'bg-blue-700' : '' }}">
                            <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                        </a>
                    </li>

                    {{-- Pedidos --}}
                    <li>
                        <a href="{{ route('proveedor.pedidos') }}"
                            class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('proveedor.pedidos') ? 'bg-blue-700' : '' }}">
                            <i class="fas fa-file-invoice mr-3"></i>Mis Pedidos
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        <div class="p-4 border-t border-blue-700">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center px-4 py-3 text-sm rounded-lg hover:bg-red-600 transition-colors">
                    <i class="fas fa-sign-out-alt mr-3"></i>Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</div>