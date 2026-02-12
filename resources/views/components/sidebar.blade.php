@props(['role'])

<div class="fixed left-0 top-0 h-full w-64 bg-gradient-to-b from-blue-900 to-blue-800 text-white shadow-xl z-50">
    <!-- Logo -->
    <div class="p-6 border-b border-blue-700">
        <div class="flex items-center space-x-3">
            <i class="fas fa-home text-3xl text-blue-300"></i>
            <div>
                <h1 class="text-xl font-bold">CORPORACIÓN</h1>
                <p class="text-sm text-blue-300">ADIVON SAC</p>
            </div>
        </div>
        <p class="text-xs text-blue-400 mt-2">Sistema de Importaciones</p>
    </div>

    <!-- User Info -->
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

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto p-4">
        <ul class="space-y-2">
            
            @if($role == 'Administrador')
                <!-- Dashboard -->
                <li>
                    <x-sidebar-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </x-sidebar-link>
                </li>

                <!-- Inventario -->
                <li x-data="{ open: {{ request()->routeIs('inventario.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors">
                        <span class="flex items-center">
                            <i class="fas fa-boxes mr-3"></i>Inventario
                        </span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <ul x-show="open" x-collapse class="ml-4 mt-2 space-y-1">
                        <li>
                            <x-sidebar-link href="{{ route('inventario.categorias.index') }}" :active="request()->routeIs('inventario.categorias.*')">
                                <i class="fas fa-tags mr-3 text-sm"></i>Categorías
                            </x-sidebar-link>
                        </li>
                        <li>
                            <x-sidebar-link href="{{ route('inventario.productos.index') }}" :active="request()->routeIs('inventario.productos.*')">
                                <i class="fas fa-box mr-3 text-sm"></i>Productos
                            </x-sidebar-link>
                        </li>
                        <li>
                            <x-sidebar-link href="{{ route('inventario.almacenes.index') }}" :active="request()->routeIs('inventario.almacenes.*')">
                                <i class="fas fa-warehouse mr-3 text-sm"></i>Almacenes
                            </x-sidebar-link>
                        </li>
                        <li>
                            <x-sidebar-link href="{{ route('inventario.imeis.index') }}" :active="request()->routeIs('inventario.imeis.*')">
                                <i class="fas fa-mobile-alt mr-3 text-sm"></i>IMEIs
                            </x-sidebar-link>
                        </li>
                        <li>
                            <x-sidebar-link href="{{ route('inventario.movimientos.index') }}" :active="request()->routeIs('inventario.movimientos.*')">
                                <i class="fas fa-exchange-alt mr-3 text-sm"></i>Movimientos
                            </x-sidebar-link>
                        </li>
                    </ul>
                </li>

            @elseif($role == 'Almacenero')
                <li>
                    <x-sidebar-link href="{{ route('almacenero.dashboard') }}" :active="request()->routeIs('almacenero.dashboard')">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </x-sidebar-link>
                </li>

                <li x-data="{ open: {{ request()->routeIs('inventario.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors">
                        <span class="flex items-center">
                            <i class="fas fa-boxes mr-3"></i>Inventario
                        </span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <ul x-show="open" x-collapse class="ml-4 mt-2 space-y-1">
                        <li>
                            <x-sidebar-link href="{{ route('inventario.productos.index') }}" :active="request()->routeIs('inventario.productos.*')">
                                <i class="fas fa-box mr-3 text-sm"></i>Productos
                            </x-sidebar-link>
                        </li>
                        <li>
                            <x-sidebar-link href="{{ route('inventario.almacenes.index') }}" :active="request()->routeIs('inventario.almacenes.*')">
                                <i class="fas fa-warehouse mr-3 text-sm"></i>Almacenes
                            </x-sidebar-link>
                        </li>
                        <li>
                            <x-sidebar-link href="{{ route('inventario.imeis.index') }}" :active="request()->routeIs('inventario.imeis.*')">
                                <i class="fas fa-mobile-alt mr-3 text-sm"></i>IMEIs
                            </x-sidebar-link>
                        </li>
                        <li>
                            <x-sidebar-link href="{{ route('inventario.movimientos.index') }}" :active="request()->routeIs('inventario.movimientos.*')">
                                <i class="fas fa-exchange-alt mr-3 text-sm"></i>Movimientos
                            </x-sidebar-link>
                        </li>
                    </ul>
                </li>

            @elseif($role == 'Tienda')
                <li>
                    <x-sidebar-link href="{{ route('tienda.dashboard') }}" :active="request()->routeIs('tienda.dashboard')">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </x-sidebar-link>
                </li>

                <li>
                    <x-sidebar-link href="{{ route('inventario.consulta-tienda') }}" :active="request()->routeIs('inventario.consulta-tienda')">
                        <i class="fas fa-boxes mr-3"></i>Consultar Inventario
                    </x-sidebar-link>
                </li>

                <li>
                    <x-sidebar-link href="#" :active="false">
                        <i class="fas fa-cash-register mr-3"></i>Punto de Venta
                    </x-sidebar-link>
                </li>

            @elseif($role == 'Vendedor')
                <li>
                    <x-sidebar-link href="{{ route('vendedor.dashboard') }}" :active="request()->routeIs('vendedor.dashboard')">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </x-sidebar-link>
                </li>

                <li>
                    <x-sidebar-link href="#" :active="false">
                        <i class="fas fa-shopping-cart mr-3"></i>Mis Ventas
                    </x-sidebar-link>
                </li>

            @elseif($role == 'Proveedor')
                <li>
                    <x-sidebar-link href="{{ route('proveedor.dashboard') }}" :active="request()->routeIs('proveedor.dashboard')">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </x-sidebar-link>
                </li>

                <li>
                    <x-sidebar-link href="#" :active="false">
                        <i class="fas fa-file-invoice mr-3"></i>Pedidos
                    </x-sidebar-link>
                </li>
            @endif
        </ul>
    </nav>

    <!-- Logout -->
    <div class="p-4 border-t border-blue-700">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center px-4 py-3 text-sm rounded-lg hover:bg-red-600 transition-colors">
                <i class="fas fa-sign-out-alt mr-3"></i>Cerrar Sesión
            </button>
        </form>
    </div>
</div>