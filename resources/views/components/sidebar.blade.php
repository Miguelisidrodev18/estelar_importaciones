@props(['role'])

<aside class="fixed left-0 top-0 h-screen w-64 bg-gradient-to-b from-blue-900 to-pink-600 text-white shadow-2xl z-50">
    <!-- Header -->
    <div class="p-6 border-b border-white/20">
        <div class="flex items-center space-x-3">
            <i class="fas fa-home text-3xl"></i>
            <div>
                <h1 class="text-xl font-bold">CORPORACIÓN ADIVON SAC</h1>
                <p class="text-xs text-white/80">Sistema de Importaciones</p>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="p-4 border-b border-white/20">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                <i class="fas fa-user text-white"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                <p class="text-xs text-white/80">{{ auth()->user()->email }}</p>
            </div>
        </div>

        <div class="mt-2">
            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-white/20">
                {{ $role }}
            </span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="p-4 overflow-y-auto h-[calc(100vh-280px)]">
        <ul class="space-y-2">

            {{-- DASHBOARD --}}
            @if($role === 'Administrador')
                <li>
                    <x-sidebar-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </x-sidebar-link>
                </li>
            @elseif($role === 'Vendedor')
                <li>
                    <x-sidebar-link href="{{ route('vendedor.dashboard') }}" :active="request()->routeIs('vendedor.dashboard')">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </x-sidebar-link>
                </li>
            @elseif($role === 'Almacenero')
                <li>
                    <x-sidebar-link href="{{ route('almacenero.dashboard') }}" :active="request()->routeIs('almacenero.dashboard')">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </x-sidebar-link>
                </li>
            @elseif($role === 'Cajero')
                <li>
                    <x-sidebar-link href="{{ route('cajero.dashboard') }}" :active="request()->routeIs('cajero.dashboard')">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </x-sidebar-link>
                </li>
            @elseif($role === 'Proveedor')
                <li>
                    <x-sidebar-link href="{{ route('proveedor.dashboard') }}" :active="request()->routeIs('proveedor.dashboard')">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </x-sidebar-link>
                </li>
            @endif

            {{-- INVENTARIO --}}
            @if(in_array($role, ['Administrador', 'Almacenero', 'Vendedor']))
            <li x-data="{ open: {{ request()->routeIs('inventario.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-white/10 transition">
                    <div class="flex items-center">
                        <i class="fas fa-boxes mr-3"></i>Inventario
                    </div>
                    <i class="fas fa-chevron-down transition-transform duration-200"
                        :class="{ 'rotate-180': open }"></i>
                </button>

                <ul x-show="open" x-collapse class="ml-4 mt-2 space-y-1">

                    <li>
                        <x-sidebar-link
                            href="{{ route('inventario.categorias.index') }}"
                            :active="request()->routeIs('inventario.categorias.*')">
                            <i class="fas fa-tags mr-3 text-sm"></i>Categorías
                        </x-sidebar-link>
                    </li>

                    <li>
                        <x-sidebar-link
                            href="{{ route('inventario.productos.index') }}"
                            :active="request()->routeIs('inventario.productos.*')">
                            <i class="fas fa-box mr-3 text-sm"></i>Productos
                        </x-sidebar-link>
                    </li>

                    {{-- MOVIMIENTOS (PROTEGIDO) --}}
                    @if(Route::has('inventario.movimientos.index'))
                        <li>
                            <x-sidebar-link 
                                href="{{ route('inventario.movimientos.index') }}" 
                                :active="request()->routeIs('inventario.movimientos.*')">
                                <i class="fas fa-exchange-alt mr-3 text-sm"></i>Movimientos
                            </x-sidebar-link>
                        </li>
                    @endif

                    <li>
                        <x-sidebar-link 
                            href="{{ route('inventario.almacenes.index') }}" 
                            :active="request()->routeIs('inventario.almacenes.*')">
                            <i class="fas fa-warehouse mr-3 text-sm"></i>Almacenes
                        </x-sidebar-link>
                    </li>

                </ul>
            </li>
            @elseif($role == 'Cajero')
    <li>
        <x-sidebar-link href="{{ route('cajero.dashboard') }}" :active="request()->routeIs('cajero.dashboard')">
            <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
        </x-sidebar-link>
    </li>

    <!-- AGREGAR ESTO: -->
    <li>
        <x-sidebar-link 
        href="{{ route('inventario.consulta-cajero') }}"
        :active="request()->routeIs('inventario.consulta-cajero')">
        <i class="fas fa-boxes mr-3"></i>Consultar Inventario
        </x-sidebar-link>
    </li>

    <li>
        <x-sidebar-link href="#" :active="false">
            <i class="fas fa-cash-register mr-3"></i>Punto de Venta
        </x-sidebar-link>
    </li>
            @endif

            {{-- LOGOUT --}}
            <li class="pt-4 border-t border-white/20">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center px-4 py-3 rounded-lg bg-white/10 hover:bg-white/20 transition">
                        <i class="fas fa-sign-out-alt mr-3"></i>Cerrar Sesión
                    </button>
                </form>
            </li>

        </ul>
    </nav>
</aside>
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script src="//unpkg.com/alpinejs" defer></script>
