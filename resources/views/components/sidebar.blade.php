@props(['role'])

<aside class="w-64 bg-white shadow-lg min-h-screen fixed left-0 top-0 overflow-y-auto z-10">
    <!-- Logo y Título -->
    <div class="p-6 bg-gradient-to-r from-blue-900 to-pink-600">
        <div class="flex items-center justify-center mb-2">
            <i class="fas fa-home text-white text-4xl"></i>
        </div>
        <h2 class="text-white text-center font-bold text-lg">CORPORACIÓN ADIVON SAC</h2>
        <p class="text-white/80 text-center text-xs mt-1">Sistema de Importaciones</p>
    </div>

    <!-- Información del Usuario -->
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-blue-900"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
        <div class="mt-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if($role == 'Administrador') bg-pink-100 text-pink-800
                @elseif($role == 'Vendedor') bg-green-100 text-green-800
                @elseif($role == 'Almacenero') bg-blue-100 text-blue-800
                @elseif($role == 'Cajero') bg-purple-100 text-purple-800
                @else bg-orange-100 text-orange-800
                @endif">
                <i class="fas fa-user-tag mr-1"></i>
                {{ $role }}
            </span>
        </div>
    </div>

    <!-- Menú de Navegación -->
    <nav class="p-4">
        @if($role == 'Administrador')
            <x-sidebar-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                <i class="fas fa-tachometer-alt mr-3"></i>
                Dashboard
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-users mr-3"></i>
                Usuarios
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-boxes mr-3"></i>
                Inventario
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-shopping-cart mr-3"></i>
                Compras
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-cash-register mr-3"></i>
                Ventas
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-chart-line mr-3"></i>
                Reportes
            </x-sidebar-link>

        @elseif($role == 'Vendedor')
            <x-sidebar-link href="{{ route('vendedor.dashboard') }}" :active="request()->routeIs('vendedor.dashboard')">
                <i class="fas fa-tachometer-alt mr-3"></i>
                Dashboard
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-cash-register mr-3"></i>
                Nueva Venta
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-receipt mr-3"></i>
                Mis Ventas
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-users mr-3"></i>
                Clientes
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-boxes mr-3"></i>
                Ver Inventario
            </x-sidebar-link>

        @elseif($role == 'Almacenero')
            <x-sidebar-link href="{{ route('almacenero.dashboard') }}" :active="request()->routeIs('almacenero.dashboard')">
                <i class="fas fa-tachometer-alt mr-3"></i>
                Dashboard
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-warehouse mr-3"></i>
                Almacenes
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-boxes mr-3"></i>
                Productos
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-exchange-alt mr-3"></i>
                Movimientos
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-tags mr-3"></i>
                Categorías
            </x-sidebar-link>

        @elseif($role == 'Cajero')
            <x-sidebar-link href="{{ route('cajero.dashboard') }}" :active="request()->routeIs('cajero.dashboard')">
                <i class="fas fa-tachometer-alt mr-3"></i>
                Dashboard
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-shopping-cart mr-3"></i>
                Punto de Venta
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-cash-register mr-3"></i>
                Mi Caja
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-receipt mr-3"></i>
                Mis Ventas
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-boxes mr-3"></i>
                Ver Inventario
            </x-sidebar-link>

        @else
            <x-sidebar-link href="{{ route('proveedor.dashboard') }}" :active="request()->routeIs('proveedor.dashboard')">
                <i class="fas fa-tachometer-alt mr-3"></i>
                Dashboard
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-shopping-cart mr-3"></i>
                Mis Órdenes
            </x-sidebar-link>
            <x-sidebar-link href="#" :active="false">
                <i class="fas fa-box-open mr-3"></i>
                Mi Catálogo
            </x-sidebar-link>
        @endif

        <!-- Separador -->
        <div class="my-4 border-t border-gray-200"></div>

        <!-- Opciones Generales -->
        <x-sidebar-link href="#" :active="false">
            <i class="fas fa-user-circle mr-3"></i>
            Mi Perfil
        </x-sidebar-link>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-pink-600 hover:bg-pink-50 rounded-md transition-colors flex items-center font-medium">
                <i class="fas fa-sign-out-alt mr-3"></i>
                Cerrar Sesión
            </button>
        </form>
    </nav>
</aside>