<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cajero - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <x-sidebar role="Cajero" />

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <x-header 
            title="Dashboard Cajero" 
            subtitle="¡Hola {{ auth()->user()->name }}! Gestiona las ventas de tu tienda" 
        />

        <!-- KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Ventas del Día -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Ventas del Día</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">S/ {{ number_format($ventas_dia, 2) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-cash-register text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Caja Actual -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Caja Actual</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">S/ {{ number_format($caja_actual, 2) }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-wallet text-blue-900 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Transacciones Hoy -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-pink-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Transacciones Hoy</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $transacciones_dia }}</p>
                    </div>
                    <div class="bg-pink-100 rounded-full p-3">
                        <i class="fas fa-receipt text-pink-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Clientes Atendidos -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Clientes Atendidos</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $clientes_atendidos }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-users text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado de Caja -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-cash-register mr-2 text-blue-900"></i>
                    Estado de Caja
                </h2>
                <div class="flex space-x-3">
                    <button class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                        <i class="fas fa-unlock mr-2"></i>
                        Abrir Caja
                    </button>
                    <button class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                        <i class="fas fa-lock mr-2"></i>
                        Cerrar Caja
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Apertura</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1">S/ 0.00</p>
                    <p class="text-xs text-gray-500 mt-1">No hay caja abierta</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Ingresos</p>
                    <p class="text-lg font-semibold text-green-600 mt-1">+ S/ {{ number_format($ventas_dia, 2) }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Egresos</p>
                    <p class="text-lg font-semibold text-red-600 mt-1">- S/ 0.00</p>
                </div>
            </div>
        </div>

        <!-- Acceso Rápido a Cobrar -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-8 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">¿Listo para cobrar?</h2>
                    <p class="text-green-100">Registra una nueva venta en el punto de venta</p>
                </div>
                <a href="#" class="bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-green-50 transition-colors flex items-center">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Punto de Venta
                </a>
            </div>
        </div>

        <!-- Últimas Transacciones -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-list mr-2 text-blue-900"></i>
                Últimas Transacciones
            </h2>
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-receipt text-6xl mb-4"></i>
                <p class="text-lg font-medium">No hay transacciones registradas hoy</p>
                <p class="text-sm mt-2">Las ventas que realices aparecerán aquí</p>
            </div>
        </div>
    </div>
</body>
</html>