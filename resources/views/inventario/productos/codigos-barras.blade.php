<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Códigos de Barras - {{ $producto->nombre }} - CORPORACIÓN ADIVON SAC</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Códigos de Barras</h1>
                    <p class="text-sm text-gray-600 mt-1">Gestiona los códigos de barras de {{ $producto->nombre }}</p>
                </div>
                <a href="{{ route('inventario.productos.show', $producto) }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al producto
                </a>
            </div>
        </div>

        <!-- Mensajes -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-xl mr-3"></i>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Mostrar código principal actual del producto -->
        @if($producto->codigo_barras)
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Código principal del producto:</strong>
                    </p>
                    <p class="text-xl font-mono font-bold text-blue-900 mt-1">
                        {{ $producto->codigo_barras }}
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                        Este código se usa en facturas y búsquedas rápidas
                    </p>
                </div>
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>
                        Principal en producto
                    </span>
                </div>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Columna izquierda: Información del producto -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-blue-900 px-4 py-3">
                        <h3 class="font-semibold text-white">
                            <i class="fas fa-box mr-2"></i>
                            Información del Producto
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-center mb-4">
                            @if($producto->imagen)
                                <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="h-32 w-32 object-cover rounded-lg border-2 border-gray-200">
                            @else
                                <div class="h-32 w-32 rounded-lg bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-box text-4xl text-gray-400"></i>
                                </div>
                            @endif
                        </div>
                        
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Código:</span>
                                <span class="text-gray-900 ml-2">{{ $producto->codigo }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Nombre:</span>
                                <span class="text-gray-900 ml-2">{{ $producto->nombre }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Categoría:</span>
                                <span class="text-gray-900 ml-2">{{ $producto->categoria->nombre ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Marca:</span>
                                <span class="text-gray-900 ml-2">{{ $producto->marca->nombre ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Modelo:</span>
                                <span class="text-gray-900 ml-2">{{ $producto->modelo->nombre ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Color:</span>
                                <span class="text-gray-900 ml-2">{{ $producto->color->nombre ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="bg-yellow-50 p-3 rounded-lg">
                                <p class="text-xs text-yellow-700">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Código principal:</strong> Se usará en facturas y búsquedas rápidas.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Lista de códigos de barras -->
            <div class="md:col-span-2">
                <!-- Formulario para agregar nuevo código -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-green-600 px-4 py-3">
                        <h3 class="font-semibold text-white">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Agregar Nuevo Código de Barras
                        </h3>
                    </div>
                    <div class="p-4">
                        <form action="{{ route('inventario.productos.codigos-barras.store', $producto) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative">
                                    <label for="codigo_barras" class="block text-sm font-medium text-gray-700 mb-2">
                                        Código de Barras <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex space-x-2">
                                        <div class="flex-1">
                                            <input type="text"
                                                   name="codigo_barras"
                                                   id="codigo_barras"
                                                   value="{{ old('codigo_barras') }}"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                                   placeholder="Ej: 1234567890123"
                                                   required>
                                        </div>
                                        <button type="button"
                                                id="btnGenerarCodigo"
                                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                            <i class="fas fa-sync-alt mr-2"></i>
                                            Generar
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                                        Descripción
                                    </label>
                                    <input type="text" 
                                           name="descripcion" 
                                           id="descripcion" 
                                           value="{{ old('descripcion') }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                           placeholder="Ej: Unidad, Caja x6, Pack">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="es_principal" 
                                               value="1"
                                               class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                               {{ old('es_principal') ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">
                                            Establecer como código principal
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                    <i class="fas fa-save mr-2"></i>
                                    Guardar Código
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de códigos existentes -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-blue-900 px-4 py-3">
                        <h3 class="font-semibold text-white">
                            <i class="fas fa-list mr-2"></i>
                            Códigos de Barras Registrados
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Principal</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($codigosBarras as $codigo)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-mono">{{ $codigo->codigo_barras }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-900">{{ $codigo->descripcion ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($codigo->es_principal)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i> Principal
                                            </span>
                                        @else
                                            <form action="{{ route('inventario.productos.codigos-barras.principal', $codigo) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-gray-400 hover:text-green-600" title="Establecer como principal">
                                                    <i class="far fa-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('inventario.productos.codigos-barras.destroy', $codigo) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('¿Estás seguro de eliminar este código de barras?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <i class="fas fa-barcode text-6xl mb-4"></i>
                                            <p class="text-lg font-medium">No hay códigos de barras registrados</p>
                                            <p class="text-sm text-gray-400 mt-1">Agrega el primer código usando el formulario</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Código principal actual -->
                    @php $principal = $codigosBarras->firstWhere('es_principal', true); @endphp
                    @if($principal)
                    <div class="px-6 py-4 bg-blue-50 border-t border-blue-200">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Código principal actual:</strong> {{ $principal->codigo_barras }}
                            @if($principal->descripcion)
                                ({{ $principal->descripcion }})
                            @endif
                        </p>
                    </div>
                    @endif
                </div>

                <!-- Botón de volver -->
                <div class="mt-6 flex justify-end">
                    <a href="{{ route('inventario.productos.show', $producto) }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver al Producto
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Botón Generar
        document.getElementById('btnGenerarCodigo')?.addEventListener('click', function() {
            const btn = this;
            const codigoInput = document.getElementById('codigo_barras');

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generando...';

            const tipoProducto = '{{ $producto->tipo_inventario === "serie" ? "celular" : "accesorio" }}';

            fetch('{{ route("inventario.productos.generar-codigo-barras") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tipo: tipoProducto })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    codigoInput.value = data.codigo;
                    codigoInput.classList.add('border-green-500', 'bg-green-50');
                    setTimeout(() => codigoInput.classList.remove('border-green-500', 'bg-green-50'), 1000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(() => alert('Error al generar código'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Generar';
            });
        });

        // Solo números en el input
        document.getElementById('codigo_barras')?.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>