@extends('gestor.layouts.app')

@section('title', 'Nueva cita')
@section('page-title', 'Nueva cita')

@section('content')
<div class="max-w-2xl mx-auto space-y-4"
     x-data="agendarGestor()"
     x-init="init()">

{{-- Encabezado --}}
<div class="relative flex items-center justify-center py-2">
    <a href="{{ route('gestor.citas') }}"
       class="absolute left-0 text-sm text-gray-600 hover:text-gray-800 font-medium inline-flex items-center gap-1 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <span class="hidden sm:inline">Volver a citas</span>
    </a>

    <h2 class="text-xl font-bold text-gray-900 text-center">
        Agendar nueva cita
    </h2>
</div>

    {{-- Errores de validación --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm space-y-1">
            <p class="font-semibold">Por favor corrige los siguientes errores:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ══ PASO 1 — Identificar paciente (siempre visible) ══ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">

        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold text-white"
                  :class="pacienteId ? 'bg-green-500' : 'bg-blue-600'">
                <template x-if="pacienteId">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="!pacienteId"><span>1</span></template>
            </span>
            Identificar paciente
        </h3>

        {{-- Barra de búsqueda --}}
        <div x-show="estadoPac === 'inicial' || estadoPac === 'no_encontrado'" class="flex flex-col sm:flex-row gap-2">
            <select x-model="tipoDoco"
                    class="w-28 border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                <option value="CC">CC</option>
                <option value="CE">CE</option>
                <option value="TI">TI</option>
                <option value="RC">RC</option>
                <option value="PA">Pasaporte</option>
                <option value="NIT">NIT</option>
            </select>
            <input type="text"
                   x-model="numDoco"
                   @keydown.enter.prevent="buscarPaciente()"
                   placeholder="Número de documento…"
                   autocomplete="off"
                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <button type="button"
                    @click="buscarPaciente()"
                    :disabled="!numDoco.trim() || buscandoPac"
                    class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold text-sm px-4 py-2 rounded-xl transition-colors">
                <svg x-show="!buscandoPac" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <svg x-show="buscandoPac" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span x-text="buscandoPac ? '' : 'Buscar'"></span>
            </button>
        </div>

        {{-- Paciente encontrado --}}
        <div x-show="estadoPac === 'encontrado'" style="display:none"
             class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-green-200 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm" x-text="pacienteInfo?.nombre_completo"></p>
                    <p class="text-xs text-gray-500">
                        <span x-text="tipoDoco"></span> <span x-text="pacienteInfo?.identificacion"></span>
                        <template x-if="pacienteInfo?.correo">
                            <span> · <span x-text="pacienteInfo.correo"></span></span>
                        </template>
                    </p>
                </div>
            </div>
            <button type="button" @click="limpiarPaciente()"
                    class="text-xs text-gray-400 hover:text-red-600 font-medium transition-colors ml-3 shrink-0">
                Cambiar
            </button>
        </div>

        {{-- No encontrado → registro rápido --}}
        <div x-show="estadoPac === 'no_encontrado'" style="display:none" class="space-y-3">
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm">
                <p class="font-semibold text-amber-800">No se encontró ningún paciente con ese documento.</p>
                <p class="text-amber-700 text-xs mt-0.5">Completa el registro rápido para continuar.</p>
            </div>
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 space-y-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Registro rápido</p>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Nombre completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="regNombre" placeholder="Nombres y apellidos completos"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Fecha de nacimiento <span class="text-red-500">*</span>
                        </label>
                        <input type="date" x-model="regFechaNac" :max="hoy"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Sexo <span class="text-red-500">*</span>
                        </label>
                        <select x-model="regSexo"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Seleccionar</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Teléfono <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" x-model="regTelefono" placeholder="Ej. 3001234567"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        Correo electrónico <span class="text-red-500">*</span>
                    </label>
                    <input type="email" x-model="regCorreo" placeholder="paciente@correo.com"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <p class="text-xs text-gray-400">
                    Se generará una contraseña temporal. El paciente la cambiará en su primer ingreso.
                </p>
                <div x-show="errorReg" x-text="errorReg"
                     class="text-red-600 text-xs bg-red-50 border border-red-200 rounded-lg px-3 py-2"
                     style="display:none"></div>
                <button type="button"
                        @click="registrarRapido()"
                        :disabled="!regNombre.trim() || !regFechaNac || !regSexo || !regTelefono.trim() || !regCorreo.trim() || registrando"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold text-sm px-4 py-2 rounded-xl transition-colors">
                    <svg x-show="registrando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="registrando ? 'Registrando…' : 'Registrar y continuar'"></span>
                </button>
            </div>
        </div>

        {{-- Registrado exitosamente --}}
        <div x-show="estadoPac === 'registrado'" style="display:none"
             class="space-y-2">
            <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-green-800 text-sm">
                            Paciente registrado — <span x-text="pacienteInfo?.nombre_completo"></span>
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <span x-text="tipoDoco"></span> <span x-text="pacienteInfo?.identificacion"></span>
                        </p>
                    </div>
                    <button type="button" @click="limpiarPaciente()"
                            class="text-xs text-gray-400 hover:text-red-600 font-medium ml-3 shrink-0">
                        Cambiar
                    </button>
                </div>
                <div class="mt-3 flex items-center gap-2 bg-white border border-green-200 rounded-lg px-3 py-2 w-fit">
                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <span class="text-xs text-gray-500">Contraseña temporal:</span>
                    <code class="font-mono font-bold text-green-700 text-sm" x-text="passwordTemporal"></code>
                </div>
                <p class="text-xs text-gray-400 mt-1">Entrega esta contraseña al paciente.</p>
            </div>
        </div>

    </div>

    {{-- ══ PASO 2 — Especialidad y servicio (aparece al identificar paciente) ══ --}}
    <div x-show="pacienteId"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         style="display:none"
         class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">

        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold text-white"
                  :class="especialidad ? 'bg-green-500' : 'bg-blue-600'">
                <template x-if="especialidad">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="!especialidad"><span>2</span></template>
            </span>
            Especialidad y servicio
        </h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Especialidad <span class="text-red-500">*</span>
            </label>
            <select x-model="especialidad" @change="resetBusqueda()"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                <option value="">Selecciona una especialidad</option>
                @foreach($especialidades as $esp)
                    <option value="{{ $esp }}" {{ old('especialidad') === $esp ? 'selected' : '' }}>
                        {{ $esp }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Servicio <span class="text-red-500">*</span> <span class="text-blue-600 font-normal">(auto-seleccionado según especialidad)</span>
            </label>
            <select x-model="servicioId" @change="resetHora()"
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    :class="!servicioId && especialidad ? 'border-red-300 bg-red-50' : 'border-gray-300'">
                <option value="">— Selecciona un servicio —</option>
                @foreach($servicios as $servicio)
                    <option value="{{ $servicio->id }}" {{ old('servicio_id') == $servicio->id ? 'selected' : '' }}>
                        {{ $servicio->nombre }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-red-600 mt-1" x-show="!servicioId && especialidad">
                <span class="font-semibold">⚠</span> El servicio es obligatorio para facturación. Selecciona uno.
            </p>
            <p class="text-xs text-blue-600 mt-1" x-show="servicioId && especialidad">
                <span>✓</span> Servicio seleccionado para facturación. Puedes cambiarlo si el paciente requiere un procedimiento diferente.
            </p>
        </div>
    </div>

    {{-- ══ PASO 3 — Fecha y horario (aparece al elegir especialidad) ══ --}}
    <div x-show="pacienteId && especialidad"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         style="display:none"
         class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">

        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold text-white"
                  :class="hora ? 'bg-green-500' : 'bg-blue-600'">
                <template x-if="hora">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="!hora"><span>3</span></template>
            </span>
            Fecha y horario
        </h3>

        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha <span class="text-red-500">*</span>
                </label>
                <input type="date" x-model="fecha" @change="resetHora()" :min="hoy"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <button type="button"
                    @click="buscarDisponibilidad()"
                    :disabled="!fecha || buscando"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
                <svg x-show="!buscando" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <svg x-show="buscando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span x-text="buscando ? 'Buscando…' : 'Buscar disponibilidad'"></span>
            </button>
        </div>

        {{-- Sin disponibilidad --}}
        <div x-show="mensajeError" style="display:none"
             class="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-800">
            <svg class="w-4 h-4 mt-0.5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <span x-text="mensajeError"></span>
        </div>

        {{-- Lista de espera (cuando no hay disponibilidad y ya hay paciente seleccionado) --}}
        <div x-show="mensajeError && pacienteId && !listaEsperaOk" style="display:none"
             class="bg-white border border-amber-200 rounded-xl px-4 py-3 space-y-2">
            <p class="text-sm font-semibold text-gray-700">No hay cupos disponibles — ¿registrar en lista de espera?</p>
            <p class="text-xs text-gray-500">
                El paciente quedará en espera para
                <strong x-text="especialidad"></strong> el <strong x-text="fecha"></strong>.
                El gestor lo contactará cuando se libere un cupo.
            </p>
            <div x-show="errorEspera" x-text="errorEspera"
                 class="text-red-600 text-xs bg-red-50 border border-red-200 rounded-lg px-3 py-2"
                 style="display:none"></div>
            <button type="button"
                    @click="registrarEnEspera()"
                    :disabled="registrandoEspera"
                    class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold text-sm px-4 py-2 rounded-xl transition-colors">
                <svg x-show="registrandoEspera" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span x-text="registrandoEspera ? 'Registrando...' : 'Registrar en lista de espera'"></span>
            </button>
        </div>

        {{-- Confirmacion lista de espera --}}
        <div x-show="listaEsperaOk" style="display:none"
             class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
            <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-green-800">Paciente registrado en lista de espera.</p>
                <p class="text-xs text-green-700 mt-0.5">Se le notificara cuando se libere un cupo.</p>
            </div>
        </div>

        {{-- Slots --}}
        <div x-show="slots.length > 0" style="display:none">
            <p class="text-xs font-medium text-gray-500 mb-2">Horarios disponibles — selecciona uno:</p>
            <div class="flex flex-wrap gap-2">
                <template x-for="slot in slots" :key="slot">
                    <button type="button"
                            @click="hora = slot"
                            :class="hora === slot
                                ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                                : 'bg-white text-gray-700 border-gray-300 hover:border-blue-400 hover:text-blue-600'"
                            class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-all tabular-nums">
                        <span x-text="slot"></span>
                    </button>
                </template>
            </div>
            <p class="text-xs text-green-700 mt-2" x-show="hora">
                Horario seleccionado: <strong x-text="hora"></strong>
                — el médico se asignará automáticamente.
            </p>
        </div>
    </div>

    {{-- ══ PASO 4 — Confirmar (aparece al seleccionar horario) ══ --}}
    <div x-show="hora"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         style="display:none">

        <form method="POST" action="{{ route('gestor.citas.agendar') }}">
            @csrf
            <input type="hidden" name="especialidad" x-model="especialidad">
            <input type="hidden" name="fecha"        x-model="fecha">
            <input type="hidden" name="hora"         x-model="hora">
            <input type="hidden" name="servicio_id"  x-model="servicioId">
            <input type="hidden" name="paciente_id"  x-model="pacienteId">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">

                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-xs font-bold text-white">4</span>
                    Confirmar cita
                </h3>

                {{-- Resumen --}}
                <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-0.5">Paciente</p>
                        <p class="font-semibold text-gray-800" x-text="pacienteInfo?.nombre_completo ?? '—'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-0.5">Especialidad</p>
                        <p class="font-semibold text-gray-800" x-text="especialidad || '—'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-0.5">Fecha</p>
                        <p class="font-semibold text-gray-800" x-text="fecha || '—'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-0.5">Hora</p>
                        <p class="font-semibold text-gray-800" x-text="hora || '—'"></p>
                    </div>
                </div>

                {{-- Precio estimado --}}
                <div class="bg-green-50 border border-green-200 rounded-xl p-4" x-show="pacienteId && servicioId && precioEstimado">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-green-700 font-medium uppercase">Valor a cobrar</p>
                            <p class="text-2xl font-bold text-green-800">
                                $<span x-text="formatearPrecio(precioEstimado)"></span>
                            </p>
                            <p class="text-xs text-green-600 mt-1" x-show="portafolioPaciente">
                                Convenio: <span x-text="portafolioPaciente"></span>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-green-600" x-show="modalidadId == 2 || modalidadId == 3">
                                ⚠️ Telemedicina: cobrar ANTES de la cita
                            </p>
                            <p class="text-xs text-green-600" x-show="modalidadId == 1">
                                💰 Pago en recepción
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Modalidad --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Modalidad <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        @foreach($modalidades as $modalidad)
                            @php
                                $icons = [
                                    'Presencial'   => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                                    'Virtual'      => 'M15 10l4.553-2.276A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
                                    'Domiciliaria' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                                ];
                                $iconPath = $icons[$modalidad->nombre] ?? 'M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0-18 0';
                            @endphp
                            <button type="button"
                                    @click="modalidadId = '{{ $modalidad->id }}'"
                                    :class="modalidadId == '{{ $modalidad->id }}'
                                        ? 'border-blue-500 bg-blue-50 shadow-sm'
                                        : 'border-gray-200 bg-white hover:border-blue-300 hover:bg-blue-50/40'"
                                    class="flex flex-col items-center gap-1.5 px-3 py-3 rounded-xl border-2 transition-all text-center w-full cursor-pointer">
                                <svg :class="modalidadId == '{{ $modalidad->id }}' ? 'text-blue-600' : 'text-gray-400'"
                                     class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/>
                                </svg>
                                <span :class="modalidadId == '{{ $modalidad->id }}' ? 'text-blue-700' : 'text-gray-600'"
                                      class="text-xs font-semibold transition-colors">
                                    {{ $modalidad->nombre }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="modalidad_id" x-model="modalidadId">
                    @error('modalidad_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Acciones --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2 border-t border-gray-100">
                    <a href="{{ route('gestor.citas') }}"
                       class="text-sm text-gray-500 hover:text-gray-800 font-medium transition-colors text-center sm:text-left py-2">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Agendar cita
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function agendarGestor() {
    return {
        // Paciente
        pacienteId:       '',
        tipoDoco:         'CC',
        numDoco:          '',
        buscandoPac:      false,
        estadoPac:        'inicial',
        pacienteInfo:     null,
        regNombre:        '',
        regFechaNac:      '',
        regSexo:          '',
        regTelefono:      '',
        regCorreo:        '',
        registrando:      false,
        errorReg:         '',
        passwordTemporal: '',
        listaEsperaOk:    false,
        registrandoEspera: false,
        errorEspera:      '',

        // Especialidad y servicio
        especialidad:  '{{ old('especialidad', '') }}',
        servicioId:    '{{ old('servicio_id', '') }}',
        modalidadId:   '{{ old('modalidad_id', '') }}',

        // Precios por servicio y portafolio (precargado desde el servidor)
        preciosServicios: @json(
            $servicios->mapWithKeys(fn($s) => [$s->id =>
                $preciosPorPortafolio->get($s->id, collect())
                    ->mapWithKeys(fn($p) => [$p->portafolio_id => $p->precio])
                    ->toArray()
            ])->toArray()
        ),
        portafolioPaciente: null,
        portafolioId: null,
        precioEstimado: 0,

        // Mapeo especialidad → servicio (auto-selección para facturación)
        mapaEspecialidadServicio: {
            'Medicina General': {{ $servicios->firstWhere('nombre', 'Consulta Medicina General')?->id ?? 'null' }},
            'Pediatría': {{ $servicios->firstWhere('nombre', 'Consulta Pediatría')?->id ?? 'null' }},
            'Cardiología': {{ $servicios->firstWhere('nombre', 'Electrocardiograma')?->id ?? 'null' }},
        },

        // Fecha y hora
        fecha:        '{{ old('fecha', '') }}',
        hora:         '',
        buscando:     false,
        slots:        [],
        mensajeError: '',
        hoy:          new Date().toISOString().split('T')[0],

        init() {
            const savedHora = '{{ old('hora', '') }}';
            if (this.especialidad && this.fecha) {
                this.buscarDisponibilidad().then(() => {
                    if (savedHora && this.slots.includes(savedHora)) {
                        this.hora = savedHora;
                    }
                });
            }
        },

        // ── Paciente ──────────────────────────────────────────
        async buscarPaciente() {
            if (!this.numDoco.trim()) return;
            this.buscandoPac  = true;
            this.estadoPac    = 'inicial';
            this.pacienteInfo = null;
            this.pacienteId   = '';
            this.errorReg     = '';
            try {
                const res  = await fetch(
                    '{{ route('gestor.pacientes.buscar') }}?identificacion=' + encodeURIComponent(this.numDoco.trim()),
                    { headers: { 'Accept': 'application/json' } }
                );
                const data = await res.json();
                if (data.encontrado) {
                    this.pacienteInfo = data.paciente;
                    this.pacienteId   = data.paciente.id;
                    this.portafolioPaciente = data.paciente.portafolio?.nombre_convenio || 'Particular';
                    this.portafolioId = data.paciente.portafolio_id;
                    this.estadoPac    = 'encontrado';
                    this.calcularPrecio();
                } else {
                    this.estadoPac   = 'no_encontrado';
                    this.regNombre   = '';
                    this.regFechaNac = '';
                    this.regSexo     = '';
                    this.regTelefono = '';
                    this.regCorreo   = '';
                }
            } catch (e) {
                this.estadoPac = 'no_encontrado';
            } finally {
                this.buscandoPac = false;
            }
        },

        async registrarRapido() {
            this.registrando = true;
            this.errorReg    = '';
            try {
                const res = await fetch('{{ route('gestor.pacientes.registro-rapido') }}', {
                    method:  'POST',
                    headers: {
                        'Accept':       'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        nombre_completo:  this.regNombre.trim(),
                        identificacion:   this.numDoco.trim(),
                        fecha_nacimiento: this.regFechaNac,
                        sexo:             this.regSexo,
                        telefono:         this.regTelefono.trim(),
                        email_cuenta:     this.regCorreo.trim(),
                    }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.errorReg = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : (data.message || 'Error al registrar.');
                    return;
                }
                this.pacienteInfo     = data.paciente;
                this.pacienteId       = data.paciente.id;
                this.passwordTemporal = data.password_temporal;
                this.estadoPac        = 'registrado';
            } catch (e) {
                this.errorReg = 'Error al registrar. Intenta de nuevo.';
            } finally {
                this.registrando = false;
            }
        },

        limpiarPaciente() {
            this.estadoPac        = 'inicial';
            this.pacienteId       = '';
            this.pacienteInfo     = null;
            this.numDoco          = '';
            this.regNombre        = '';
            this.regFechaNac      = '';
            this.regSexo          = '';
            this.regTelefono      = '';
            this.regCorreo        = '';
            this.errorReg         = '';
            this.passwordTemporal = '';
        },

        // ── Disponibilidad ────────────────────────────────────
        resetBusqueda() {
            this.fecha             = '';
            this.hora              = '';
            this.slots             = [];
            this.mensajeError      = '';
            this.listaEsperaOk     = false;
            this.registrandoEspera = false;
            this.errorEspera       = '';
            // Auto-seleccionar servicio según especialidad para facturación
            this.autoSeleccionarServicio();
        },

        // Auto-seleccionar servicio basado en especialidad
        autoSeleccionarServicio() {
            if (this.especialidad && this.mapaEspecialidadServicio[this.especialidad]) {
                const servicioId = this.mapaEspecialidadServicio[this.especialidad];
                // Solo auto-seleccionar si no hay un servicio ya seleccionado manualmente
                if (!this.servicioId) {
                    this.servicioId = servicioId;
                }
            }
            this.calcularPrecio();
        },

        // Calcular precio según servicio y portafolio del paciente
        calcularPrecio() {
            if (this.servicioId && this.portafolioId && this.preciosServicios[this.servicioId]) {
                const preciosPortafolio = this.preciosServicios[this.servicioId];
                this.precioEstimado = preciosPortafolio[this.portafolioId] || 0;
            } else {
                this.precioEstimado = 0;
            }
        },

        // Formatear precio con separadores de miles
        formatearPrecio(valor) {
            return new Intl.NumberFormat('es-CO').format(valor);
        },

        resetHora() {
            this.hora              = '';
            this.slots             = [];
            this.mensajeError      = '';
            this.listaEsperaOk     = false;
            this.registrandoEspera = false;
            this.errorEspera       = '';
        },

        async registrarEnEspera() {
            this.registrandoEspera = true;
            this.errorEspera       = '';
            try {
                const res = await fetch('/lista-espera', {
                    method:  'POST',
                    headers: {
                        'Accept':       'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        paciente_id:      this.pacienteId,
                        fecha_solicitada: this.fecha,
                        servicio_id:      this.servicioId || null,
                        notas:            'Sin disponibilidad para ' + this.especialidad,
                    }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.errorEspera = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : (data.message || 'Error al registrar.');
                    return;
                }
                this.listaEsperaOk = true;
            } catch (e) {
                this.errorEspera = 'Error al registrar. Intenta de nuevo.';
            } finally {
                this.registrandoEspera = false;
            }
        },

        async buscarDisponibilidad() {
            if (!this.especialidad || !this.fecha) return;
            this.buscando     = true;
            this.slots        = [];
            this.hora         = '';
            this.mensajeError = '';
            try {
                let url = '{{ route('citas.disponibilidad-especialidad') }}'
                    + '?especialidad=' + encodeURIComponent(this.especialidad)
                    + '&fecha='        + this.fecha;
                if (this.servicioId) url += '&servicio_id=' + this.servicioId;
                const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.disponible && data.slots?.length > 0) {
                    this.slots = data.slots;
                } else {
                    this.mensajeError = data.mensaje || 'No hay citas disponibles para esa fecha. Selecciona otra.';
                }
            } catch (e) {
                this.mensajeError = 'Error al consultar disponibilidad. Intenta de nuevo.';
            } finally {
                this.buscando = false;
            }
        },
    };
}
</script>
@endpush
