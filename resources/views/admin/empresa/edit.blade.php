<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configuración de Empresa</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
<x-sidebar :role="auth()->user()->role->nombre" />

<div class="md:ml-64 p-4 md:p-8" x-data="{ tab: 'datos' }">
    <x-header title="Configuración de Empresa" subtitle="Datos generales, logos y configuración SUNAT" />

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded flex items-center">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button @click="tab = 'datos'"
                    :class="tab === 'datos' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition-colors">
                    <i class="fas fa-building"></i> Datos Iniciales
                </button>
                <button @click="tab = 'graficos'"
                    :class="tab === 'graficos' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition-colors">
                    <i class="fas fa-image"></i> Gráficos / Logos
                </button>
                <button @click="tab = 'redes'"
                    :class="tab === 'redes' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition-colors">
                    <i class="fas fa-share-alt"></i> Redes Sociales
                </button>
                <button @click="tab = 'api'"
                    :class="tab === 'api' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition-colors">
                    <i class="fas fa-plug"></i> Integración API / SUNAT
                </button>
            </nav>
        </div>

        <form action="{{ route('admin.empresa.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- TAB: DATOS INICIALES --}}
            <div x-show="tab === 'datos'" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RUC *</label>
                        <input type="text" name="ruc" value="{{ old('ruc', $empresa->ruc) }}" maxlength="11"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('ruc') border-red-500 @enderror"
                            placeholder="20XXXXXXXXX">
                        @error('ruc')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Razón Social *</label>
                        <input type="text" name="razon_social" value="{{ old('razon_social', $empresa->razon_social) }}" maxlength="200"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('razon_social') border-red-500 @enderror">
                        @error('razon_social')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Comercial</label>
                        <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial', $empresa->nombre_comercial) }}" maxlength="200"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Régimen Tributario</label>
                        <select name="regimen" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            @foreach(['RER' => 'Régimen Especial de Renta', 'RG' => 'Régimen General', 'RMT' => 'Régimen MYPE Tributario', 'RUS' => 'Nuevo RUS'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('regimen', $empresa->regimen) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección Fiscal</label>
                        <input type="text" name="direccion" value="{{ old('direccion', $empresa->direccion) }}" maxlength="300"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                        <input type="text" name="departamento" value="{{ old('departamento', $empresa->departamento) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                        <input type="text" name="provincia" value="{{ old('provincia', $empresa->provincia) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Distrito</label>
                        <input type="text" name="distrito" value="{{ old('distrito', $empresa->distrito) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ubigeo</label>
                        <input type="text" name="ubigeo" value="{{ old('ubigeo', $empresa->ubigeo) }}" maxlength="6"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}" maxlength="20"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $empresa->email) }}" maxlength="150"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sitio Web</label>
                        <input type="url" name="web" value="{{ old('web', $empresa->web) }}" maxlength="200"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                            placeholder="https://www.ejemplo.com">
                    </div>
                </div>
            </div>

            {{-- TAB: GRÁFICOS --}}
            <div x-show="tab === 'graficos'" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Logo Principal</label>
                        @if($empresa->logo_path)
                            <div class="mb-3 p-3 bg-gray-50 rounded-lg border inline-flex">
                                <img src="{{ $empresa->logo_url }}" alt="Logo" class="h-20 object-contain">
                            </div>
                        @endif
                        <input type="file" name="logo" accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">PNG, JPG — máx. 2MB. Aparece en el encabezado del sistema.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Logo para PDF / Comprobantes</label>
                        @if($empresa->logo_pdf_path)
                            <div class="mb-3 p-3 bg-gray-50 rounded-lg border inline-flex">
                                <img src="{{ $empresa->logo_pdf_url }}" alt="Logo PDF" class="h-20 object-contain">
                            </div>
                        @endif
                        <input type="file" name="logo_pdf" accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-gray-400 mt-1">PNG, JPG — máx. 2MB. Aparece en boletas, facturas y guías.</p>
                    </div>
                </div>
            </div>

            {{-- TAB: REDES SOCIALES --}}
            <div x-show="tab === 'redes'" class="p-6">
                <div class="space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                            <i class="fab fa-facebook-f text-white"></i>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                            <input type="text" name="facebook" value="{{ old('facebook', $empresa->facebook) }}" maxlength="200"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                placeholder="https://facebook.com/mi-empresa">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center flex-shrink-0">
                            <i class="fab fa-instagram text-white"></i>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                            <input type="text" name="instagram" value="{{ old('instagram', $empresa->instagram) }}" maxlength="200"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                placeholder="https://instagram.com/mi-empresa">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-black flex items-center justify-center flex-shrink-0">
                            <i class="fab fa-tiktok text-white"></i>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">TikTok</label>
                            <input type="text" name="tiktok" value="{{ old('tiktok', $empresa->tiktok) }}" maxlength="200"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                                placeholder="https://tiktok.com/@mi-empresa">
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: INTEGRACIÓN API --}}
            <div x-show="tab === 'api'" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-file-invoice text-blue-600"></i> Credenciales SUNAT (Clave SOL)
                        </h3>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usuario SOL</label>
                        <input type="text" name="sunat_usuario_sol" value="{{ old('sunat_usuario_sol', $empresa->sunat_usuario_sol) }}" maxlength="100"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                            placeholder="MODDATOS">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Clave SOL</label>
                        <input type="password" name="sunat_clave_sol" maxlength="100"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                            placeholder="Dejar vacío para no cambiar">
                        <p class="text-xs text-gray-400 mt-1">Se almacenará de forma segura. Dejar vacío para mantener la clave actual.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modo SUNAT</label>
                        <select name="sunat_modo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="beta" {{ old('sunat_modo', $empresa->sunat_modo) === 'beta' ? 'selected' : '' }}>Beta / Pruebas</option>
                            <option value="produccion" {{ old('sunat_modo', $empresa->sunat_modo) === 'produccion' ? 'selected' : '' }}>Producción</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 border-t pt-5 mt-2">
                        <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-server text-green-600"></i> Servidor de Facturación (API REST)
                        </h3>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL del API</label>
                        <input type="url" name="api_url" value="{{ old('api_url', $empresa->api_url) }}" maxlength="300"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                            placeholder="https://api.mifacturador.pe">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">API Key / Token</label>
                        <input type="password" name="api_key" maxlength="300"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                            placeholder="Dejar vacío para no cambiar">
                    </div>
                </div>
            </div>

            {{-- Botón guardar --}}
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors flex items-center gap-2">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
