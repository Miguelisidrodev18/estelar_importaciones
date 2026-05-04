<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Traslado - Sistema de Importaciones</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <x-sidebar :role="auth()->user()->role->nombre" />

    <div class="md:ml-64 p-4 md:p-8">
        <x-header
            title="Nuevo Traslado"
            subtitle="Registra un traslado de stock entre almacenes — puedes incluir múltiples productos"
        />

        {{-- Navegación --}}
        <div class="flex flex-wrap gap-3 mb-6">
            <a href="{{ route('traslados.index') }}" class="text-sm text-gray-600 hover:text-blue-700 flex items-center gap-1">
                <i class="fas fa-exchange-alt"></i> Historial
            </a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('traslados.pendientes') }}" class="text-sm text-gray-600 hover:text-yellow-600 flex items-center gap-1">
                <i class="fas fa-clock"></i> Pendientes
            </a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('traslados.stock') }}" class="text-sm text-gray-600 hover:text-blue-700 flex items-center gap-1">
                <i class="fas fa-boxes"></i> Stock por Almacén
            </a>
            <span class="text-gray-300">|</span>
            <span class="text-sm font-semibold text-blue-700 flex items-center gap-1">
                <i class="fas fa-plus-circle"></i> Nuevo Traslado
            </span>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div x-data="trasladoForm()" x-init="init()">

            <form action="{{ route('traslados.store') }}" method="POST">
                @csrf

                {{-- ══════════════════════════════════════════════════
                     CABECERA DEL TRASLADO
                ═══════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-5">

                    <div class="bg-linear-to-r from-blue-900 to-blue-700 px-6 py-4 flex items-center gap-3">
                        <div class="bg-white/20 rounded-xl p-2.5">
                            <i class="fas fa-exchange-alt text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white">Datos del Traslado</h2>
                            <p class="text-blue-200 text-xs">Origen, destino y guía de remisión</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">

                        {{-- Origen / Destino --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <i class="fas fa-warehouse mr-1 text-orange-500"></i>Almacén Origen *
                                </label>
                                <select name="almacen_id" required
                                        x-model="almacenId"
                                        @change="onAlmacenOrigenChange()"
                                        class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">— Seleccione origen —</option>
                                    @foreach($almacenes as $alm)
                                        <option value="{{ $alm->id }}" {{ old('almacen_id') == $alm->id ? 'selected' : '' }}>
                                            {{ $alm->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('almacen_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <i class="fas fa-store mr-1 text-green-500"></i>Almacén Destino *
                                </label>
                                <select name="almacen_destino_id" required
                                        x-model="almacenDestinoId"
                                        class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">— Seleccione destino —</option>
                                    @foreach($almacenes as $alm)
                                        <option value="{{ $alm->id }}" {{ old('almacen_destino_id') == $alm->id ? 'selected' : '' }}>
                                            {{ $alm->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('almacen_destino_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p x-show="almacenId && almacenDestinoId && almacenId === almacenDestinoId" x-cloak
                                   class="text-xs text-red-500 mt-1">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Origen y destino no pueden ser iguales
                                </p>
                            </div>
                        </div>

                        {{-- N° Guía --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                <i class="fas fa-file-alt mr-1 text-blue-400"></i>N° Guía
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="numero_guia" x-model="numeroGuia"
                                       class="w-full sm:w-64 px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono uppercase"
                                       placeholder="Seleccione almacén origen"
                                       @input="numeroGuia = numeroGuia.toUpperCase()">
                                <span x-show="guiaSerieId" x-cloak
                                      class="text-xs text-emerald-600 font-medium flex items-center gap-1 whitespace-nowrap">
                                    <i class="fas fa-check-circle"></i> Serie de sucursal
                                </span>
                                <span x-show="almacenId && !guiaSerieId" x-cloak
                                      class="text-xs text-amber-500 font-medium flex items-center gap-1 whitespace-nowrap">
                                    <i class="fas fa-exclamation-triangle"></i> Sin serie configurada
                                </span>
                            </div>
                            <input type="hidden" name="guia_serie_id" x-model="guiaSerieId">
                            @error('numero_guia')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                <i class="fas fa-comment-alt mr-1 text-gray-400"></i>Observaciones
                                <span class="text-gray-400 font-normal normal-case">(opcional)</span>
                            </label>
                            <textarea name="observaciones" rows="2"
                                      class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 resize-none"
                                      placeholder="Motivo del traslado, instrucciones...">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════
                     GUÍA DE REMISIÓN
                ═══════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-5">

                    <div class="bg-linear-to-r from-emerald-900 to-emerald-700 px-6 py-4 flex items-center gap-3">
                        <div class="bg-white/20 rounded-xl p-2.5">
                            <i class="fas fa-file-invoice text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white">Guía de Remisión</h2>
                            <p class="text-emerald-200 text-xs">Datos para el comprobante de traslado (SUNAT)</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">

                        {{-- Motivo / Modalidad / Fecha --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <i class="fas fa-tag mr-1 text-emerald-500"></i>Motivo de Traslado *
                                </label>
                                <select name="guia[motivo_traslado]" required
                                        class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white">
                                    <option value="TRASLADO_ENTRE_ALMACENES" {{ old('guia.motivo_traslado','TRASLADO_ENTRE_ALMACENES')==='TRASLADO_ENTRE_ALMACENES' ? 'selected':'' }}>Traslado entre almacenes</option>
                                    <option value="VENTA" {{ old('guia.motivo_traslado')==='VENTA' ? 'selected':'' }}>Venta</option>
                                    <option value="COMPRA" {{ old('guia.motivo_traslado')==='COMPRA' ? 'selected':'' }}>Compra</option>
                                    <option value="DEVOLUCION" {{ old('guia.motivo_traslado')==='DEVOLUCION' ? 'selected':'' }}>Devolución</option>
                                    <option value="IMPORTACION" {{ old('guia.motivo_traslado')==='IMPORTACION' ? 'selected':'' }}>Importación</option>
                                    <option value="EXPORTACION" {{ old('guia.motivo_traslado')==='EXPORTACION' ? 'selected':'' }}>Exportación</option>
                                    <option value="OTRO" {{ old('guia.motivo_traslado')==='OTRO' ? 'selected':'' }}>Otro</option>
                                </select>
                                @error('guia.motivo_traslado')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <i class="fas fa-truck mr-1 text-emerald-500"></i>Modalidad de Transporte *
                                </label>
                                <select name="guia[modalidad]" required x-model="modalidad"
                                        class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white">
                                    <option value="privado">Transporte Privado (propio)</option>
                                    <option value="publico">Transporte Público (tercero)</option>
                                </select>
                                @error('guia.modalidad')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <i class="fas fa-calendar mr-1 text-emerald-500"></i>Fecha de Traslado *
                                </label>
                                <input type="date" name="guia[fecha_traslado]" required
                                       value="{{ old('guia.fecha_traslado', now()->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                @error('guia.fecha_traslado')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Dirección Partida / Llegada --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <i class="fas fa-map-marker-alt mr-1 text-orange-400"></i>Dirección de Partida (Origen)
                                </label>
                                <input type="text" name="guia[direccion_partida]"
                                       value="{{ old('guia.direccion_partida') }}"
                                       placeholder="Dirección del almacén origen"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <i class="fas fa-map-marker-alt mr-1 text-green-500"></i>Dirección de Llegada (Destino)
                                </label>
                                <input type="text" name="guia[direccion_llegada]"
                                       value="{{ old('guia.direccion_llegada') }}"
                                       placeholder="Dirección del almacén destino"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        {{-- Ubigeo Partida / Llegada --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Ubigeo Partida
                                </label>
                                <input type="text" name="guia[ubigeo_partida]" maxlength="6"
                                       value="{{ old('guia.ubigeo_partida') }}"
                                       placeholder="Ej: 150101"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Ubigeo Llegada
                                </label>
                                <input type="text" name="guia[ubigeo_llegada]" maxlength="6"
                                       value="{{ old('guia.ubigeo_llegada') }}"
                                       placeholder="Ej: 150101"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 font-mono">
                            </div>
                        </div>

                        {{-- Peso / Bultos --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <i class="fas fa-weight-hanging mr-1 text-gray-400"></i>Peso Total (kg)
                                </label>
                                <input type="number" step="0.01" min="0" name="guia[peso_total]"
                                       value="{{ old('guia.peso_total') }}"
                                       placeholder="0.00"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <i class="fas fa-boxes mr-1 text-gray-400"></i>N° de Bultos
                                </label>
                                <input type="number" min="1" name="guia[bultos]"
                                       value="{{ old('guia.bultos') }}"
                                       placeholder="1"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>

                        {{-- Transportista (solo transporte público) --}}
                        <div x-show="modalidad === 'publico'" x-cloak class="border-t border-gray-100 pt-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                                <i class="fas fa-id-card mr-1 text-blue-400"></i>Datos del Transportista
                                <span class="ml-2 text-gray-400 font-normal normal-case text-[11px]">Empresa o persona que realiza el traslado</span>
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Tipo Documento</label>
                                    <select name="guia[transportista_tipo_doc]" x-model="transpTipoDoc"
                                            class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white">
                                        <option value="">— Tipo —</option>
                                        <option value="RUC">RUC (empresa)</option>
                                        <option value="DNI">DNI (persona natural)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">N° Documento</label>
                                    <div class="flex gap-2">
                                        <input type="text" name="guia[transportista_doc]" x-model="transpDoc"
                                               maxlength="11" placeholder="RUC o DNI"
                                               class="flex-1 px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 font-mono"
                                               @keydown.enter.prevent="buscarTransportista()">
                                        <button type="button" @click="buscarTransportista()"
                                                :disabled="transpBuscando || !transpDoc"
                                                class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs rounded-lg transition disabled:opacity-50"
                                                title="Buscar en SUNAT/RENIEC">
                                            <i class="fas" :class="transpBuscando ? 'fa-spinner fa-spin' : 'fa-search'"></i>
                                        </button>
                                    </div>
                                    <p x-show="transpError" x-text="transpError" x-cloak class="text-xs text-amber-600 mt-1"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Nombre / Razón Social</label>
                                    <input type="text" name="guia[transportista_nombre]" x-model="transpNombre"
                                           maxlength="200" placeholder="Se llena automático o escribe manualmente"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>
                        </div>

                        {{-- Conductor --}}
                        <div class="border-t border-gray-100 pt-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    <i class="fas fa-user-tie mr-1 text-purple-400"></i>Datos del Conductor
                                    <span class="ml-2 text-gray-400 font-normal normal-case text-[11px]">
                                        (privado: conductor propio · público: conductor del transportista)
                                    </span>
                                </p>
                                @if($ultimoConductor)
                                    <button type="button" @click="restaurarUltimoConductor()"
                                            class="text-xs text-purple-600 hover:text-purple-800 flex items-center gap-1">
                                        <i class="fas fa-history"></i> Usar último conductor
                                    </button>
                                @endif
                            </div>

                            {{-- Banner último conductor --}}
                            @if($ultimoConductor)
                            <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-purple-50 border border-purple-100 rounded-xl text-sm">
                                <i class="fas fa-user-check text-purple-400 shrink-0"></i>
                                <div class="flex-1 text-purple-800">
                                    Último conductor registrado:
                                    <strong>{{ $ultimoConductor->conductor_nombre }}</strong>
                                    &middot; DNI <span class="font-mono">{{ $ultimoConductor->conductor_dni }}</span>
                                    @if($ultimoConductor->placa_vehiculo)
                                        &middot; Placa <span class="font-mono font-semibold">{{ $ultimoConductor->placa_vehiculo }}</span>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">DNI Conductor</label>
                                    <div class="flex gap-2">
                                        <input type="text" name="guia[conductor_dni]" x-model="condDni"
                                               maxlength="8" placeholder="DNI"
                                               class="flex-1 px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 font-mono"
                                               @keydown.enter.prevent="buscarConductor()">
                                        <button type="button" @click="buscarConductor()"
                                                :disabled="condBuscando || condDni.length !== 8"
                                                class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded-lg transition disabled:opacity-50"
                                                title="Buscar en RENIEC">
                                            <i class="fas" :class="condBuscando ? 'fa-spinner fa-spin' : 'fa-search'"></i>
                                        </button>
                                    </div>
                                    <p x-show="condError" x-text="condError" x-cloak class="text-xs text-amber-600 mt-1"></p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Nombre del Conductor</label>
                                    <input type="text" name="guia[conductor_nombre]" x-model="condNombre"
                                           maxlength="200" placeholder="Apellidos y nombres (se llena desde RENIEC)"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">N° Licencia</label>
                                    <input type="text" name="guia[conductor_licencia]" x-model="condLicencia"
                                           maxlength="20" placeholder="Licencia de conducir"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 font-mono">
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <i class="fas fa-car mr-1 text-gray-400"></i>Placa del Vehículo
                                </label>
                                <input type="text" name="guia[placa_vehiculo]" x-model="condPlaca"
                                       maxlength="20" placeholder="Ej: ABC-123"
                                       @input="condPlaca = condPlaca.toUpperCase()"
                                       class="w-full sm:w-48 px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 font-mono uppercase">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════
                     PRODUCTOS (REPEATER)
                ═══════════════════════════════════════════════════ --}}
                <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-5">

                    <div class="bg-linear-to-r from-purple-900 to-purple-700 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 rounded-xl p-2.5">
                                <i class="fas fa-boxes text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-white">Productos a Trasladar</h2>
                                <p class="text-purple-200 text-xs">Agrega uno o varios productos</p>
                            </div>
                        </div>
                        <span class="bg-white/20 text-white text-sm font-bold px-3 py-1 rounded-full"
                              x-text="productos.length + ' producto(s)'"></span>
                    </div>

                    <div class="p-6 space-y-4">

                        {{-- Aviso sin almacén origen --}}
                        <div x-show="!almacenId"
                             class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 px-4 py-3 rounded-lg">
                            <i class="fas fa-arrow-up shrink-0"></i>
                            Selecciona el almacén origen primero para ver el stock disponible.
                        </div>

                        {{-- ── Filas de producto ── --}}
                        <template x-for="(producto, idx) in productos" :key="producto._id">
                            <div class="border border-gray-200 rounded-xl overflow-hidden"
                                 :class="esDuplicado(idx) ? 'border-red-300 bg-red-50' : 'bg-gray-50/40'">

                                {{-- Cabecera de la fila --}}
                                <div class="flex items-center justify-between px-4 py-2.5 bg-white border-b border-gray-100">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">
                                        Producto <span x-text="idx + 1"></span>
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span x-show="producto.esSerie" x-cloak
                                              class="inline-flex items-center gap-1 text-[10px] font-semibold bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                                            <i class="fas fa-barcode"></i> IMEI
                                        </span>
                                        <button type="button"
                                                @click="eliminarProducto(idx)"
                                                x-show="productos.length > 1"
                                                class="text-gray-400 hover:text-red-500 transition p-1 rounded-lg hover:bg-red-50">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="p-4 space-y-3">

                                    {{-- Select producto --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                            <i class="fas fa-box mr-1 text-blue-400"></i>Producto *
                                        </label>
                                        <select :name="`productos[${idx}][producto_id]`"
                                                x-model="producto.productoId"
                                                @change="onProductoChange(idx)"
                                                required
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                                            <option value="">— Seleccione un producto —</option>
                                            @foreach($productos as $prod)
                                                <option value="{{ $prod->id }}"
                                                        :disabled="estaUsado({{ $prod->id }}, idx)"
                                                        {{ old("productos.{$loop->index}.producto_id") == $prod->id ? 'selected' : '' }}>
                                                    {{ $prod->nombre }}
                                                    @if($prod->tipo_inventario === 'serie') (IMEI) @endif
                                                    — {{ $prod->codigo }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p x-show="esDuplicado(idx)" x-cloak
                                           class="text-xs text-red-500 mt-1 flex items-center gap-1">
                                            <i class="fas fa-exclamation-triangle"></i> Este producto ya está en otra fila.
                                        </p>
                                    </div>

                                    {{-- Stock badge --}}
                                    <div x-show="producto.productoId && producto.stockOrigen !== null && almacenId" x-cloak>
                                        <span class="text-xs px-2.5 py-1 rounded-full font-medium"
                                              :class="producto.stockOrigen > 0
                                                  ? 'bg-green-100 text-green-700'
                                                  : 'bg-red-100 text-red-600'">
                                            <i class="fas fa-cubes mr-1"></i>
                                            Disponible:
                                            <strong x-text="producto.stockOrigen"></strong>
                                            <span x-show="producto.esSerie"> IMEIs</span>
                                            <span x-show="!producto.esSerie"> unid.</span>
                                        </span>
                                    </div>

                                    {{-- ── CANTIDAD (accesorio) ── --}}
                                    <div x-show="producto.productoId && !producto.esSerie">
                                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                            <i class="fas fa-hashtag mr-1 text-blue-400"></i>Cantidad *
                                        </label>
                                        <input type="number"
                                               :name="`productos[${idx}][cantidad]`"
                                               x-model.number="producto.cantidad"
                                               min="1"
                                               :max="producto.stockOrigen ?? undefined"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <p x-show="producto.stockOrigen !== null && producto.cantidad > producto.stockOrigen" x-cloak
                                           class="text-xs text-red-500 mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Supera el stock disponible.
                                        </p>
                                    </div>

                                    {{-- ── IMEI PICKER ── --}}
                                    <div x-show="producto.productoId && producto.esSerie" x-cloak>
                                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                            <i class="fas fa-barcode mr-1 text-purple-500"></i>IMEIs a trasladar *
                                        </label>

                                        {{-- Prompt si no hay almacén --}}
                                        <div x-show="!almacenId"
                                             class="text-xs text-gray-400 bg-gray-100 rounded-lg px-3 py-2">
                                            <i class="fas fa-info-circle mr-1"></i>Seleccione almacén origen para ver los IMEIs.
                                        </div>

                                        <div x-show="almacenId" x-cloak>

                                            {{-- Buscador + contador --}}
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="relative flex-1">
                                                    <i class="fas fa-search absolute left-2.5 top-2.5 text-[10px] text-gray-400 pointer-events-none"></i>
                                                    <input type="text"
                                                           x-model="producto.imeiBusqueda"
                                                           placeholder="Buscar IMEI o S/N..."
                                                           class="w-full pl-7 pr-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-400">
                                                </div>
                                                <div class="shrink-0 text-xs bg-purple-50 border border-purple-200 text-purple-700 font-mono rounded-lg px-2.5 py-1.5">
                                                    <span class="font-bold" x-text="producto.imeisSeleccionados.length"></span>
                                                    /
                                                    <span x-text="producto.imeisDisponibles.length"></span>
                                                </div>
                                            </div>

                                            {{-- Loading --}}
                                            <div x-show="producto.imeisLoading"
                                                 class="py-4 text-center text-xs text-gray-400">
                                                <i class="fas fa-spinner fa-spin mr-1"></i>Cargando IMEIs...
                                            </div>

                                            {{-- Sin IMEIs --}}
                                            <div x-show="!producto.imeisLoading && producto.imeisDisponibles.length === 0" x-cloak
                                                 class="py-4 text-center text-xs text-gray-400 bg-gray-50 border border-dashed border-gray-200 rounded-lg">
                                                <i class="fas fa-box-open text-gray-300 text-xl block mb-1"></i>
                                                Sin IMEIs disponibles en este almacén
                                            </div>

                                            {{-- Lista de IMEIs --}}
                                            <div x-show="!producto.imeisLoading && producto.imeisDisponibles.length > 0" x-cloak
                                                 class="border border-gray-200 rounded-lg overflow-hidden">

                                                {{-- Toolbar: seleccionar todos --}}
                                                <div class="flex items-center justify-between px-3 py-1.5 bg-gray-50 border-b border-gray-100 sticky top-0">
                                                    <button type="button"
                                                            @click="seleccionarTodos(idx)"
                                                            class="text-xs font-semibold text-purple-600 hover:text-purple-800 flex items-center gap-1">
                                                        <i class="fas fa-check-double text-[10px]"></i>
                                                        <span x-text="imeisFiltradosDe(idx).length > 0 && imeisFiltradosDe(idx).every(i => producto.imeisSeleccionados.includes(i.id))
                                                            ? 'Deseleccionar todos' : 'Seleccionar todos'"></span>
                                                    </button>
                                                    <span class="text-[10px] text-gray-400"
                                                          x-show="producto.imeiBusqueda" x-cloak
                                                          x-text="imeisFiltradosDe(idx).length + ' resultado(s)'"></span>
                                                </div>

                                                <div class="max-h-44 overflow-y-auto divide-y divide-gray-100">
                                                    <template x-for="imei in imeisFiltradosDe(idx)" :key="imei.id">
                                                        <label :for="`imei-${producto._id}-${imei.id}`"
                                                               class="flex items-center gap-2.5 px-3 py-2 cursor-pointer transition-colors"
                                                               :class="isSelected(idx, imei.id) ? 'bg-purple-50 hover:bg-purple-100' : 'hover:bg-gray-50'">
                                                            <input type="checkbox"
                                                                   :id="`imei-${producto._id}-${imei.id}`"
                                                                   :checked="isSelected(idx, imei.id)"
                                                                   @change="toggleImei(idx, imei.id)"
                                                                   class="w-3.5 h-3.5 accent-purple-600 cursor-pointer shrink-0">
                                                            <div class="flex-1 min-w-0">
                                                                <span class="font-mono text-xs font-semibold"
                                                                      :class="isSelected(idx, imei.id) ? 'text-purple-700' : 'text-gray-800'"
                                                                      x-text="imei.codigo_imei"></span>
                                                                <span x-show="imei.serie"
                                                                      class="text-[10px] text-gray-400 ml-1.5 font-mono"
                                                                      x-text="'S/N: ' + imei.serie"></span>
                                                            </div>
                                                            <span x-show="isSelected(idx, imei.id)"
                                                                  class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded font-bold shrink-0">✓</span>
                                                        </label>
                                                    </template>
                                                    <div x-show="producto.imeiBusqueda && imeisFiltradosDe(idx).length === 0" x-cloak
                                                         class="px-3 py-3 text-center text-xs text-gray-400">
                                                        Sin resultados para "<span x-text="producto.imeiBusqueda"></span>"
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Chips de seleccionados --}}
                                            <div x-show="producto.imeisSeleccionados.length > 0" x-cloak
                                                 class="mt-2 flex flex-wrap gap-1">
                                                <template x-for="imeiId in producto.imeisSeleccionados" :key="imeiId">
                                                    <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-800 text-[10px] font-mono px-2 py-1 rounded-lg border border-purple-200">
                                                        <span x-text="getImeiCodigo(idx, imeiId)"></span>
                                                        <button type="button" @click="toggleImei(idx, imeiId)"
                                                                class="text-purple-400 hover:text-red-500 ml-0.5 leading-none">
                                                            <i class="fas fa-times text-[8px]"></i>
                                                        </button>
                                                    </span>
                                                </template>
                                            </div>

                                        </div>{{-- /x-show almacenId --}}

                                        {{-- Hidden inputs para IMEIs seleccionados --}}
                                        <template x-for="imeiId in producto.imeisSeleccionados" :key="imeiId">
                                            <input type="hidden"
                                                   :name="`productos[${idx}][imei_ids][]`"
                                                   :value="imeiId">
                                        </template>

                                    </div>{{-- /IMEI PICKER --}}

                                </div>{{-- /p-4 --}}
                            </div>{{-- /border row --}}
                        </template>

                        {{-- Botón agregar producto --}}
                        <button type="button"
                                @click="agregarProducto()"
                                class="w-full py-2.5 border-2 border-dashed border-blue-300 hover:border-blue-500 text-blue-500 hover:text-blue-700 text-sm font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-plus-circle"></i> Agregar otro producto
                        </button>

                    </div>{{-- /p-6 --}}
                </div>{{-- /card productos --}}

                {{-- ── Info box + Acciones ── --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex gap-2 text-sm text-blue-800 mb-5">
                    <i class="fas fa-info-circle mt-0.5 text-blue-500 shrink-0"></i>
                    <span>
                        El stock de <strong>accesorios</strong> se descuenta del origen al registrar.
                        Los <strong>IMEIs</strong> se marcan «En Tránsito» hasta que el destino confirme la recepción.
                    </span>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('traslados.index') }}"
                       class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            :disabled="!puedeEnviar()"
                            class="px-6 py-2.5 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-paper-plane"></i>
                        <span x-text="'Enviar traslado (' + productos.length + ' prod.)'"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>

<script>
function trasladoForm() {
    const stocks = @json($stocksData->toArray());   // {producto_id: {almacen_id: cantidad}}
    const imeis  = @json($imeisData->toArray());    // {producto_id: {almacen_id: total}}
    const tipos  = @json($tiposInventario->toArray()); // {producto_id: 'serie'|'accesorio'}
    const imeisUrl = '{{ route('traslados.imeis-disponibles') }}';

    const guiaSeriesMap = @json($guiaSeriesMap);   // { almacen_id: { serie_id, numero } }
    const dniUrl  = '{{ route('ventas.dni.buscar', ['dni' => '__DNI__']) }}';
    const rucUrl  = '{{ route('traslados.api.ruc', ['ruc' => '__RUC__']) }}';
    @php
        $conductorData = [
            'dni'      => $ultimoConductor?->conductor_dni      ?? '',
            'nombre'   => $ultimoConductor?->conductor_nombre   ?? '',
            'licencia' => $ultimoConductor?->conductor_licencia ?? '',
            'placa'    => $ultimoConductor?->placa_vehiculo     ?? '',
        ];
    @endphp
    const ultimoConductor = @json($conductorData);

    return {
        almacenId:        '',
        almacenDestinoId: '',
        productos:        [],
        nextId:           1,

        // N° Guía
        numeroGuia:  '{{ old('numero_guia', '') }}',
        guiaSerieId: '{{ old('guia_serie_id', '') }}',

        // Modalidad de transporte
        modalidad: '{{ old('guia.modalidad', 'privado') }}',

        // Transportista (transporte público)
        transpTipoDoc:  '{{ old('guia.transportista_tipo_doc', 'RUC') }}',
        transpDoc:      '{{ old('guia.transportista_doc', '') }}',
        transpNombre:   '{{ old('guia.transportista_nombre', '') }}',
        transpBuscando: false,
        transpError:    '',

        // Conductor
        condDni:      '{{ old('guia.conductor_dni', $ultimoConductor?->conductor_dni ?? '') }}',
        condNombre:   '{{ old('guia.conductor_nombre', $ultimoConductor?->conductor_nombre ?? '') }}',
        condLicencia: '{{ old('guia.conductor_licencia', $ultimoConductor?->conductor_licencia ?? '') }}',
        condPlaca:    '{{ old('guia.placa_vehiculo', $ultimoConductor?->placa_vehiculo ?? '') }}',
        condBuscando: false,
        condError:    '',

        init() {
            this.agregarProducto();
        },

        // ── Gestión del repeater ──────────────────────────────────────

        agregarProducto() {
            this.productos.push({
                _id:               this.nextId++,
                productoId:        '',
                esSerie:           false,
                stockOrigen:       null,
                cantidad:          1,
                imeisDisponibles:  [],
                imeisSeleccionados: [],
                imeiBusqueda:      '',
                imeisLoading:      false,
            });
        },

        eliminarProducto(idx) {
            if (this.productos.length > 1) {
                this.productos.splice(idx, 1);
            }
        },

        estaUsado(productoId, idx) {
            return this.productos.some((p, i) => i !== idx && String(p.productoId) === String(productoId));
        },

        esDuplicado(idx) {
            const pid = String(this.productos[idx]?.productoId ?? '');
            if (!pid) return false;
            return this.productos.filter((p, i) => i !== idx && String(p.productoId) === pid).length > 0;
        },

        // ── Eventos de selección ─────────────────────────────────────

        onAlmacenOrigenChange() {
            // Auto-fill N° Guía desde la serie de la sucursal del almacén origen
            const serieData = guiaSeriesMap[this.almacenId];
            if (serieData) {
                this.numeroGuia  = serieData.numero;
                this.guiaSerieId = String(serieData.serie_id);
            } else {
                this.numeroGuia  = '';
                this.guiaSerieId = '';
            }

            this.productos.forEach((p, idx) => {
                p.imeisSeleccionados = [];
                p.imeisDisponibles   = [];
                p.imeiBusqueda       = '';
                this.calcularStock(idx);
                if (p.esSerie && p.productoId && this.almacenId) {
                    this.cargarImeis(idx);
                }
            });
        },

        onProductoChange(idx) {
            const p  = this.productos[idx];
            const pid = String(p.productoId);
            p.esSerie           = !!(pid && tipos[pid] === 'serie');
            p.imeisSeleccionados = [];
            p.imeisDisponibles   = [];
            p.imeiBusqueda       = '';
            p.cantidad           = 1;
            this.calcularStock(idx);
            if (p.esSerie && this.almacenId) {
                this.cargarImeis(idx);
            }
        },

        calcularStock(idx) {
            const p   = this.productos[idx];
            const pid = String(p.productoId);
            const aid = String(this.almacenId);

            if (!pid || !aid) { p.stockOrigen = null; return; }

            if (p.esSerie) {
                p.stockOrigen = (imeis[pid] && imeis[pid][aid] !== undefined)
                    ? parseInt(imeis[pid][aid]) : 0;
            } else {
                p.stockOrigen = (stocks[pid] && stocks[pid][aid] !== undefined)
                    ? parseInt(stocks[pid][aid]) : 0;
            }
        },

        // ── IMEI carga y gestión ─────────────────────────────────────

        async cargarImeis(idx) {
            const p = this.productos[idx];
            p.imeisLoading = true;
            try {
                const url  = `${imeisUrl}?producto_id=${p.productoId}&almacen_id=${this.almacenId}`;
                const resp = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });
                if (!resp.ok) {
                    const err = await resp.json().catch(() => ({}));
                    throw new Error(err.error || `HTTP ${resp.status}`);
                }
                p.imeisDisponibles = await resp.json();
            } catch (e) {
                p.imeisDisponibles = [];
                console.error('Error cargando IMEIs:', e.message);
            } finally {
                p.imeisLoading = false;
            }
        },

        imeisFiltradosDe(idx) {
            const p = this.productos[idx];
            if (!p || !p.imeiBusqueda.trim()) return p?.imeisDisponibles ?? [];
            const q = p.imeiBusqueda.toLowerCase();
            return p.imeisDisponibles.filter(i =>
                i.codigo_imei.toLowerCase().includes(q) ||
                (i.serie && i.serie.toLowerCase().includes(q))
            );
        },

        toggleImei(idx, id) {
            const p   = this.productos[idx];
            const pos = p.imeisSeleccionados.indexOf(id);
            pos === -1 ? p.imeisSeleccionados.push(id) : p.imeisSeleccionados.splice(pos, 1);
        },

        isSelected(idx, id) {
            return this.productos[idx]?.imeisSeleccionados.includes(id) ?? false;
        },

        seleccionarTodos(idx) {
            const p        = this.productos[idx];
            const filtrados = this.imeisFiltradosDe(idx);
            const todos    = filtrados.every(i => p.imeisSeleccionados.includes(i.id));
            if (todos) {
                const fids = filtrados.map(i => i.id);
                p.imeisSeleccionados = p.imeisSeleccionados.filter(id => !fids.includes(id));
            } else {
                filtrados.forEach(i => {
                    if (!p.imeisSeleccionados.includes(i.id)) p.imeisSeleccionados.push(i.id);
                });
            }
        },

        getImeiCodigo(idx, id) {
            const imei = this.productos[idx]?.imeisDisponibles.find(i => i.id === id);
            return imei ? imei.codigo_imei : String(id);
        },

        // ── Lookups RENIEC / SUNAT ───────────────────────────────────

        async buscarConductor() {
            if (this.condDni.length !== 8) return;
            this.condBuscando = true;
            this.condError    = '';
            try {
                const url  = dniUrl.replace('__DNI__', this.condDni);
                const resp = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await resp.json();
                if (resp.ok && data.nombre) {
                    this.condNombre = data.nombre;
                } else {
                    this.condError = data.error ?? 'No encontrado. Ingrese el nombre manualmente.';
                }
            } catch {
                this.condError = 'Sin conexión al servicio RENIEC.';
            } finally {
                this.condBuscando = false;
            }
        },

        async buscarTransportista() {
            if (!this.transpDoc) return;
            this.transpBuscando = true;
            this.transpError    = '';
            try {
                let url;
                if (this.transpTipoDoc === 'RUC') {
                    url = rucUrl.replace('__RUC__', this.transpDoc);
                } else {
                    url = dniUrl.replace('__DNI__', this.transpDoc);
                }
                const resp = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await resp.json();
                if (resp.ok && data.nombre) {
                    this.transpNombre = data.nombre;
                } else {
                    this.transpError = data.error ?? 'No encontrado. Ingrese el nombre manualmente.';
                }
            } catch {
                this.transpError = 'Sin conexión al servicio SUNAT/RENIEC.';
            } finally {
                this.transpBuscando = false;
            }
        },

        restaurarUltimoConductor() {
            this.condDni      = ultimoConductor.dni;
            this.condNombre   = ultimoConductor.nombre;
            this.condLicencia = ultimoConductor.licencia;
            this.condPlaca    = ultimoConductor.placa;
            this.condError    = '';
        },

        // ── Validación global ────────────────────────────────────────

        puedeEnviar() {
            if (!this.almacenId || !this.almacenDestinoId) return false;
            if (this.almacenId === this.almacenDestinoId)   return false;
            if (this.productos.length === 0)                 return false;

            return this.productos.every((p, idx) => {
                if (!p.productoId)       return false;
                if (this.esDuplicado(idx)) return false;
                if (p.esSerie)           return p.imeisSeleccionados.length > 0;
                return p.cantidad >= 1 && (p.stockOrigen === null || p.stockOrigen >= p.cantidad);
            });
        },
    };
}
</script>
</body>
</html>
