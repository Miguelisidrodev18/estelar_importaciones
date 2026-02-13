<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja - Sistema de Importaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />
    <x-header title="Gestión de Caja" />

    <div class="ml-64 p-8 pt-24">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Historial de Cajas</h2>
            <a href="{{ route('caja.abrir') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                <i class="fas fa-cash-register mr-2"></i>Abrir Caja
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Almacén</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto Inicial</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto Final</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($cajas as $caja)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $caja->fecha->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm">{{ $caja->usuario->name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $caja->almacen->nombre }}</td>
                        <td class="px-6 py-4 text-sm">S/ {{ number_format($caja->monto_inicial, 2) }}</td>
                        <td class="px-6 py-4 text-sm font-semibold">S/ {{ number_format($caja->monto_final, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $caja->estado === 'abierta' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($caja->estado) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-cash-register text-4xl mb-3 text-gray-300"></i>
                            <p>No hay registros de caja</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
