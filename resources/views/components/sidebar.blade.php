@props(['role'])

<div class="fixed left-0 top-0 h-full w-64 bg-gradient-to-b from-blue-900 to-blue-800 text-white shadow-xl z-50" x-data="{ inventarioOpen: {{ request()->routeIs('inventario.*') ? 'true' : 'false' }} }">
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
                    <a href="{{ route('admin.dashboard') }}" 
                        class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                </li>

                <!-- Inventario (Desplegable) -->
                <li>
                    <button @click="inventarioOpen = !inventarioOpen" 
                            class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.*') ? 'bg-blue-700' : '' }}">
                        <span class="flex items-center">
                            <i class="fas fa-boxes mr-3"></i>Inventario
                        </span>
                        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': inventarioOpen }"></i>
                    </button>
                    
                    <ul x-show="inventarioOpen" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="ml-4 mt-2 space-y-1">
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
                    </ul>
                </li>

            @elseif($role == 'Almacenero')
                <li>
                    <a href="{{ route('almacenero.dashboard') }}" 
                        class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('almacenero.dashboard') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                </li>

                <li>
                    <button @click="inventarioOpen = !inventarioOpen" 
                            class="w-full flex items-center justify-between px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.*') ? 'bg-blue-700' : '' }}">
                        <span class="flex items-center">
                            <i class="fas fa-boxes mr-3"></i>Inventario
                        </span>
                        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': inventarioOpen }"></i>
                    </button>
                    
                    <ul x-show="inventarioOpen" x-collapse class="ml-4 mt-2 space-y-1">
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

            @elseif($role == 'Tienda')
                <li>
                    <a href="{{ route('tienda.dashboard') }}" 
                        class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('tienda.dashboard') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                </li>

                <li>
                    <a href="{{ route('inventario.consulta-tienda') }}" 
                        class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('inventario.consulta-tienda') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-boxes mr-3"></i>Consultar Inventario
                    </a>
                </li>

                <li>
                    <a href="#" class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-cash-register mr-3"></i>Punto de Venta
                    </a>
                </li>

            @elseif($role == 'Vendedor')
                <li>
                    <a href="{{ route('vendedor.dashboard') }}" 
                       class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('vendedor.dashboard') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                </li>

                <li>
                    <a href="#" class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-shopping-cart mr-3"></i>Mis Ventas
                    </a>
                </li>

            @elseif($role == 'Proveedor')
                <li>
                    <a href="{{ route('proveedor.dashboard') }}" 
                        class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors {{ request()->routeIs('proveedor.dashboard') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                </li>

                <li>
                    <a href="#" class="flex items-center px-4 py-3 text-sm rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-file-invoice mr-3"></i>Pedidos
                    </a>
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
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script src="//unpkg.com/alpinejs" defer></script>
