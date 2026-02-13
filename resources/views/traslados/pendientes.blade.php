<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traslados Pendientes - Sistema de Importaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />
    <x-header title="Traslados Pendientes" />

    <div class="ml-64 p-8 pt-24">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-clock text-yellow-500 mr-2"></i>Traslados Pendientes de Confirmación</h2>
            <a href="{{ route('traslados.index') }}" class="text-blue-600 hover:text-blue-800">Ver todos los traslados</a>
        </div>

        @forelse($traslados as $traslado)
        <div class="bg-white rounded-xl shadow-md p-6 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">
                        <span class="font-mono text-blue-600">{{ $traslado->numero_guia ?? 'Sin guía' }}</span>
                        - {{ $traslado->producto->nombre }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-warehouse mr-1"></i>{{ $traslado->almacen->nombre }}
                        <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                        <i class="fas fa-warehouse mr-1"></i>{{ $traslado->almacenDestino->nombre ?? '-' }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        Cantidad: <span class="font-semibold">{{ $traslado->cantidad }}</span> |
                        Enviado por: {{ $traslado->usuario->name }} |
                        Fecha: {{ $traslado->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <form action="{{ route('traslados.confirmar', $traslado) }}" method="POST" onsubmit="return confirm('¿Confirmar la recepción de este traslado?')">
                    @csrf
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-3 rounded-lg">
                        <i class="fas fa-check-double mr-2"></i>Confirmar Recepción
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-md p-12 text-center">
            <i class="fas fa-check-circle text-5xl text-green-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No hay traslados pendientes</p>
        </div>
        @endforelse
    </div>
</body>
</html>
