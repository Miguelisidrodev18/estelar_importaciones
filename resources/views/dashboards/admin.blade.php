<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar role="Administrador" />

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <x-header 
            title="Dashboard Administrador" 
            subtitle="Bienvenido, {{ auth()->user()->name }}" 
        />

        <!-- KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Usuarios -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Usuarios</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $total_usuarios }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-users text-blue-900 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Usuarios Activos -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Usuarios Activos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $usuarios_activos }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-user-check text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Usuarios Inactivos -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Usuarios Inactivos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $usuarios_inactivos }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-user-times text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Ventas del Día -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-pink-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Ventas Hoy</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">S/ 0.00</p>
                    </div>
                    <div class="bg-pink-100 rounded-full p-3">
                        <i class="fas fa-cash-register text-pink-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usuarios por Rol -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-chart-pie mr-2 text-blue-900"></i>
                Usuarios por Rol
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($usuarios_por_rol as $rol)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <p class="text-sm text-gray-600">{{ $rol->nombre }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $rol->total }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-bolt mr-2 text-blue-900"></i>
                Accesos Rápidos
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-user-plus text-blue-900 text-2xl mr-3"></i>
                    <span class="font-medium text-gray-900">Nuevo Usuario</span>
                </a>
                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-box text-green-600 text-2xl mr-3"></i>
                    <span class="font-medium text-gray-900">Nuevo Producto</span>
                </a>
                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-shopping-cart text-pink-600 text-2xl mr-3"></i>
                    <span class="font-medium text-gray-900">Nueva Compra</span>
                </a>
                <a href="#" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-file-alt text-orange-600 text-2xl mr-3"></i>
                    <span class="font-medium text-gray-900">Reportes</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>