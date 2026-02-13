<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir Caja - Sistema de Importaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <x-sidebar :role="auth()->user()->role->nombre" />
    <x-header title="Abrir Caja" />

    <div class="ml-64 p-8 pt-24">
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
        @endif

        <div class="max-w-lg mx-auto">
            @if($cajaAbierta)
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-3"></i>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Ya tienes una caja abierta</h3>
                    <p class="text-gray-600 mb-4">Abierta el {{ $cajaAbierta->fecha->format('d/m/Y') }} en {{ $cajaAbierta->almacen->nombre }}</p>
                    <a href="{{ route('caja.actual') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                        <i class="fas fa-eye mr-2"></i>Ver Caja Actual
                    </a>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6"><i class="fas fa-cash-register mr-2 text-blue-500"></i>Abrir Caja</h2>

                    <form action="{{ route('caja.store') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Almacén / Sucursal *</label>
                                <select name="almacen_id" required class="w-full rounded-lg border-gray-300 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    @foreach($almacenes as $alm)
                                        <option value="{{ $alm->id }}">{{ $alm->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Monto Inicial (S/) *</label>
                                <input type="number" name="monto_inicial" min="0" step="0.01" required
                                       class="w-full rounded-lg border-gray-300 shadow-sm" placeholder="0.00">
                            </div>
                        </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded-lg">
                                <i class="fas fa-unlock mr-2"></i>Abrir Caja
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
