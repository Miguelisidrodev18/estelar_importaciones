<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Vendedor - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar :role="auth()->user()->role->nombre" />

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <x-header 
            title="Dashboard Vendedor" 
            subtitle="¡Hola {{ auth()->user()->name }}! Aquí está tu resumen de ventas." 
        />

        <!-- KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Ventas Hoy -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Ventas Hoy</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">S/ {{ number_format($ventas_hoy, 2) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-calendar-day text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Ventas del Mes -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Ventas del Mes</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">S/ {{ number_format($ventas_mes, 2) }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-calendar-alt text-blue-900 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Clientes Atendidos -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-pink-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Clientes Atendidos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $clientes_atendidos }}</p>
                    </div>
                    <div class="bg-pink-100 rounded-full p-3">
                        <i class="fas fa-users text-pink-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Productos Vendidos -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Productos Vendidos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $productos_vendidos }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-box text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acceso Rápido a Nueva Venta -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-8 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">¿Listo para vender?</h2>
                    <p class="text-green-100">Comienza una nueva venta ahora</p>
                </div>
                <a href="#" class="bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-green-50 transition-colors flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Nueva Venta
                </a>
            </div>
        </div>

        <!-- Últimas Ventas -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-history mr-2 text-blue-900"></i>
                Últimas Ventas
            </h2>
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-receipt text-6xl mb-4"></i>
                <p class="text-lg font-medium">No hay ventas registradas aún</p>
                <p class="text-sm mt-2">Las ventas que realices aparecerán aquí</p>
            </div>
        </div>
    </div>
</body>
</html>