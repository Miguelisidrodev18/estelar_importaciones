{{-- resources/views/dashboards/admin.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hover-scale {
            transition: transform 0.2s ease-in-out;
        }
        .hover-scale:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        .chart-card {
            min-height: 350px;
            display: flex;
            flex-direction: column;
        }
        .chart-card .chart-wrapper {
            flex: 1;
            min-height: 0;
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .trend-up {
            color: #10b981;
            background: #d1fae5;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .trend-down {
            color: #ef4444;
            background: #fee2e2;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 min-h-screen bg-gray-100">
        {{-- Top Bar con bienvenida personalizada --}}
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="px-6 py-3 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-chart-line text-blue-900 mr-2"></i>
                        ¡Hola, {{ auth()->user()->name }}!
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="far fa-calendar-alt mr-1"></i>{{ now()->format('l, d F Y') }} | 
                        <i class="far fa-clock mr-1"></i>{{ now()->format('h:i A') }}
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button class="p-2 rounded-full hover:bg-gray-100 relative">
                            <i class="fas fa-bell text-gray-600 text-xl"></i>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-900 to-blue-700 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </div>
                        <div class="hidden md:block">
                            <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">Administrador</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            {{-- KPIs Principales del Negocio --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Ventas Totales --}}
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border-l-4 border-blue-900">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Ventas Totales</p>
                            <p class="stat-value text-gray-900">S/ 245,890</p>
                            <div class="flex items-center mt-2">
                                <span class="trend-up mr-2">
                                    <i class="fas fa-arrow-up mr-1"></i>+15.3%
                                </span>
                                <span class="text-xs text-gray-500">vs mes anterior</span>
                            </div>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <i class="fas fa-chart-line text-blue-900 text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Meta mensual: S/ 300,000</span>
                            <span class="font-semibold text-blue-900">82%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-900 h-2 rounded-full" style="width: 82%"></div>
                        </div>
                    </div>
                </div>

                {{-- Stock Total --}}
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border-l-4 border-purple-600">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Stock Total</p>
                            <p class="stat-value text-gray-900">4,567</p>
                            <p class="text-xs text-gray-500 mt-1">unidades en inventario</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <i class="fas fa-boxes text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-xs text-gray-500">Celulares</p>
                            <p class="text-sm font-bold text-gray-900">2,345</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-xs text-gray-500">Cases</p>
                            <p class="text-sm font-bold text-gray-900">1,234</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-xs text-gray-500">Cargadores</p>
                            <p class="text-sm font-bold text-gray-900">988</p>
                        </div>
                    </div>
                </div>

                {{-- IMEIs Registrados --}}
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border-l-4 border-green-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">IMEIs Registrados</p>
                            <p class="stat-value text-gray-900">2,345</p>
                            <div class="flex items-center mt-2 space-x-2">
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                    <i class="fas fa-check-circle mr-1"></i>1,890 disponibles
                                </span>
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">
                                    <i class="fas fa-times-circle mr-1"></i>455 vendidos
                                </span>
                            </div>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <i class="fas fa-microchip text-green-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Últimos 7 días</span>
                            <span class="text-green-600">+45 nuevos</span>
                        </div>
                    </div>
                </div>

                {{-- Productos Bajo Stock --}}
                <div class="bg-white rounded-xl shadow-lg p-6 hover-scale border-l-4 border-orange-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Alertas de Stock</p>
                            <p class="stat-value text-gray-900">8</p>
                            <p class="text-xs text-orange-600 mt-1">productos por reabastecer</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-orange-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-xs">
                            <span>iPhone 13 Cases</span>
                            <span class="font-semibold text-orange-600">5 und</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span>Cargador rápido</span>
                            <span class="font-semibold text-orange-600">3 und</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Segunda fila de KPIs --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-5 flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-store text-blue-900 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tiendas/Locales</p>
                        <p class="text-xl font-bold text-gray-900">3</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-5 flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-warehouse text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Almacenes</p>
                        <p class="text-xl font-bold text-gray-900">2</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-5 flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-truck text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Proveedores</p>
                        <p class="text-xl font-bold text-gray-900">12</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-5 flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-exchange-alt text-yellow-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Traslados Pend.</p>
                        <p class="text-xl font-bold text-gray-900">5</p>
                    </div>
                </div>
            </div>

            {{-- Gráficos y Estadísticas --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                {{-- Ventas por Mes --}}
                <div class="bg-white rounded-xl shadow-lg p-6 chart-card">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900">
                            <i class="fas fa-chart-line text-blue-900 mr-2"></i>
                            Ventas Mensuales
                        </h3>
                        <select class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white">
                            <option>2024</option>
                            <option>2023</option>
                        </select>
                    </div>
                    <div class="chart-wrapper">
                        <div class="chart-container">
                            <canvas id="ventasChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Productos Más Vendidos --}}
                <div class="bg-white rounded-xl shadow-lg p-6 chart-card">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <i class="fas fa-crown text-yellow-500 mr-2"></i>
                        Top Productos Más Vendidos
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">iPhone 14 Pro Max</span>
                                <span class="text-gray-900 font-bold">245 und</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-900 h-2 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">Case Silicona iPhone</span>
                                <span class="text-gray-900 font-bold">189 und</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-900 h-2 rounded-full" style="width: 77%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">Cargador Rápido 20W</span>
                                <span class="text-gray-900 font-bold">156 und</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-900 h-2 rounded-full" style="width: 64%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">Samsung S23 Ultra</span>
                                <span class="text-gray-900 font-bold">98 und</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-900 h-2 rounded-full" style="width: 40%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla de Últimos Movimientos --}}
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        <i class="fas fa-history text-blue-900 mr-2"></i>
                        Últimos Movimientos de Inventario
                    </h3>
                    <a href="#" class="text-sm text-blue-900 hover:text-blue-700 font-semibold">
                        Ver todos <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 text-xs font-semibold text-gray-600 uppercase">Fecha</th>
                                <th class="text-left py-3 text-xs font-semibold text-gray-600 uppercase">Producto</th>
                                <th class="text-left py-3 text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                                <th class="text-left py-3 text-xs font-semibold text-gray-600 uppercase">Cantidad</th>
                                <th class="text-left py-3 text-xs font-semibold text-gray-600 uppercase">Origen/Destino</th>
                                <th class="text-left py-3 text-xs font-semibold text-gray-600 uppercase">Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 text-sm">15/02/2024 10:30</td>
                                <td class="py-3 text-sm font-medium">iPhone 14 Pro Max</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Ingreso</span>
                                </td>
                                <td class="py-3 text-sm font-semibold">+50</td>
                                <td class="py-3 text-sm">Almacén Central</td>
                                <td class="py-3 text-sm">Carlos Mendoza</td>
                            </tr>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 text-sm">15/02/2024 09:15</td>
                                <td class="py-3 text-sm font-medium">Case Silicona</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Salida</span>
                                </td>
                                <td class="py-3 text-sm font-semibold">-12</td>
                                <td class="py-3 text-sm">Tienda Centro</td>
                                <td class="py-3 text-sm">Ana López</td>
                            </tr>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 text-sm">14/02/2024 16:20</td>
                                <td class="py-3 text-sm font-medium">Cargador Rápido</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Traslado</span>
                                </td>
                                <td class="py-3 text-sm font-semibold">30</td>
                                <td class="py-3 text-sm">Central → Tienda</td>
                                <td class="py-3 text-sm">Pedro Ruiz</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Resumen de Usuarios y Accesos Rápidos en dos columnas --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Usuarios por Rol --}}
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <i class="fas fa-users text-blue-900 mr-2"></i>
                        Usuarios por Rol
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $total_usuarios }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500">Activos</p>
                            <p class="text-2xl font-bold text-green-600">{{ $usuarios_activos }}</p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach($usuarios_por_rol as $rol)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ $rol->nombre }}</span>
                            <div class="flex items-center">
                                <span class="text-sm font-semibold text-gray-900 mr-3">{{ $rol->total }}</span>
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    @php
                                        $porcentaje = $total_usuarios > 0 ? round(($rol->total / $total_usuarios) * 100) : 0;
                                    @endphp
                                    <div class="bg-blue-900 h-2 rounded-full" style="width: {{ $porcentaje }}%"></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

               {{-- Accesos Rápidos Mejorados --}}
<div class="bg-gradient-to-br from-blue-900 to-blue-800 rounded-xl shadow-lg p-6 text-white">
    <h3 class="text-lg font-bold mb-4">
        <i class="fas fa-bolt mr-2"></i>
        Acciones Rápidas
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Nueva Venta --}}
        <a href="{{ route('ventas.create') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 text-center transition-all group">
            <i class="fas fa-shopping-cart text-3xl mb-2 group-hover:scale-110 transition-transform"></i>
            <p class="text-sm font-semibold">Nueva Venta</p>
            <p class="text-xs opacity-75 mt-1">Registrar venta</p>
        </a>

        {{-- Nuevo Usuario --}}
        <a href="{{ route('users.create') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 text-center transition-all group">
            <i class="fas fa-user-plus text-3xl mb-2 group-hover:scale-110 transition-transform"></i>
            <p class="text-sm font-semibold">Nuevo Usuario</p>
            <p class="text-xs opacity-75 mt-1">Crear cuenta</p>
        </a>

        {{-- Nuevo Producto --}}
        <a href="{{ route('inventario.productos.create') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-4 text-center transition-all group">
            <i class="fas fa-box text-3xl mb-2 group-hover:scale-110 transition-transform"></i>
            <p class="text-sm font-semibold">Nuevo Producto</p>
            <p class="text-xs opacity-75 mt-1">Agregar al catálogo</p>
        </a>
    </div>
    
    {{-- Opcional: Fila adicional con más acciones --}}
    <div class="grid grid-cols-2 gap-4 mt-4">
        <a href="{{ route('compras.create') }}" class="bg-white bg-opacity-10 hover:bg-opacity-20 rounded-lg p-3 text-center transition-all text-sm">
            <i class="fas fa-truck mr-2"></i>Nueva Compra
        </a>
        <a href="#" class="bg-white bg-opacity-10 hover:bg-opacity-20 rounded-lg p-3 text-center transition-all text-sm opacity-50 cursor-not-allowed">
            <i class="fas fa-chart-bar mr-2"></i>Reportes (Próximamente)
        </a>
    </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de Ventas
            const ctx = document.getElementById('ventasChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    datasets: [{
                        label: 'Ventas 2024',
                        data: [120000, 190000, 150000, 250000, 220000, 300000, 280000, 350000, 320000, 380000, 420000, 450000],
                        borderColor: '#1e3a8a',
                        backgroundColor: 'rgba(30, 58, 138, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#1e3a8a',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'S/ ' + context.parsed.y.toLocaleString('es-PE');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'S/ ' + value.toLocaleString('es-PE');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>