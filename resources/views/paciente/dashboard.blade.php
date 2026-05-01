@extends('paciente.layouts.app')

@section('title', 'Inicio')
@section('page-title', 'Bienvenido, ' . $paciente->nombre_completo)

@section('content')
<div x-data="{ openModal: {{ $errors->any() ? 'true' : 'false' }} }">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    {{-- Total de Citas --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
        <div class="bg-blue-50 rounded-xl p-3 text-blue-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalCitas }}</p>
            <p class="text-sm text-gray-500 font-medium">Citas totales</p>
        </div>
    </div>

    {{-- Historias Clínicas --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
        <div class="bg-emerald-50 rounded-xl p-3 text-emerald-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalHistorias }}</p>
            <p class="text-sm text-gray-500 font-medium">Historias clínicas</p>
        </div>
    </div>

    {{-- Acceso Rápido --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4">
        <div class="bg-amber-50 rounded-xl p-3 text-amber-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-800 font-bold">¿Necesitas ayuda?</p>
            <p class="text-xs text-gray-500">Consulta con nuestro asistente virtual.</p>
        </div>
    </div>
</div>

{{-- Acción principal --}}
<a href="{{ route('paciente.agendar') }}"
   class="w-full mb-2 active:scale-[.99] transition rounded-2xl shadow-sm px-8 py-6 flex items-center justify-between gap-6"
   style="background-color: var(--color-primario, #475569);">
    <div class="flex items-center gap-5">
        <div class="bg-white/20 rounded-2xl p-4 shrink-0">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-white leading-tight">Agendar una cita</p>
            <p class="text-white/70 text-sm mt-0.5">Toca aquí para solicitar tu próxima consulta médica</p>
        </div>
    </div>
    <div class="shrink-0 bg-white/20 text-white font-bold text-sm px-6 py-3 rounded-xl hidden sm:block border border-white/30">
        + Nueva cita
    </div>
</a>

{{-- ── Órdenes médicas pendientes ─────────────────────────────── --}}
@if ($ordenesPendientes->isNotEmpty())
<div x-data="{
        ordenSeleccionada: null,
        autorizando: false,
        mensaje: '',
        async autorizar(via) {
            if (!this.ordenSeleccionada) return;
            this.autorizando = true;
            this.mensaje = '';
            try {
                const res = await fetch(`/ordenes-medicas/${this.ordenSeleccionada.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ estado: 'autorizada', autorizado_via: via }),
                });
                if (res.ok) {
                    this.mensaje = 'Orden autorizada correctamente.';
                    setTimeout(() => { this.ordenSeleccionada = null; location.reload(); }, 1200);
                }
            } finally { this.autorizando = false; }
        }
     }"
     @keydown.escape.window="ordenSeleccionada = null">

    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-amber-800">Tienes {{ $ordenesPendientes->count() }} orden(es) médica(s) pendiente(s) de autorización</p>
                <p class="text-xs text-amber-600">Revísalas y autorízalas de forma virtual o preséntate en nuestra sede.</p>
            </div>
        </div>

        <div class="space-y-2">
            @foreach ($ordenesPendientes as $orden)
            <button type="button"
                    @click="ordenSeleccionada = {{ $orden->toJson() }}"
                    class="w-full flex items-center justify-between bg-white border border-amber-200 rounded-xl px-4 py-3 hover:bg-amber-50 transition text-left">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800">{{ $orden->tipo }} — {{ $orden->descripcion }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Dr. {{ $orden->historiaClinica?->ejecucionCita?->cita?->medico?->usuario?->nombre ?? '—' }}
                        · {{ $orden->created_at->format('d/m/Y') }}
                    </p>
                </div>
                <span class="ml-4 text-xs font-semibold text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full shrink-0">Autorizar</span>
            </button>
            @endforeach
        </div>
    </div>

    {{-- Modal de autorización --}}
    <template x-if="ordenSeleccionada">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="ordenSeleccionada = null"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md z-10 p-6"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Autorizar orden médica</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="ordenSeleccionada.tipo"></p>
                    </div>
                    <button @click="ordenSeleccionada = null"
                            class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 mb-5 space-y-2">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide">Descripción</p>
                        <p class="text-sm text-gray-800 font-medium" x-text="ordenSeleccionada.descripcion"></p>
                    </div>
                    <template x-if="ordenSeleccionada.instrucciones">
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide">Instrucciones</p>
                            <p class="text-sm text-gray-600" x-text="ordenSeleccionada.instrucciones"></p>
                        </div>
                    </template>
                </div>

                <p class="text-sm text-gray-600 mb-4">
                    Al autorizar esta orden, confirmas que estás de acuerdo con el procedimiento indicado por tu médico.
                </p>

                <p x-show="mensaje" x-text="mensaje" class="text-sm text-emerald-600 font-medium text-center mb-3"></p>

                <div class="grid grid-cols-2 gap-3">
                    <button @click="autorizar('virtual')" :disabled="autorizando"
                            class="flex flex-col items-center gap-2 border-2 border-blue-600 rounded-xl p-4 hover:bg-blue-50 transition disabled:opacity-50">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm font-bold text-blue-700">Autorizar en línea</span>
                        <span class="text-[10px] text-blue-500 text-center">Desde aquí mismo</span>
                    </button>
                    <div class="flex flex-col items-center gap-2 border-2 border-gray-200 rounded-xl p-4 bg-gray-50">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm font-bold text-gray-600">Autorizar presencial</span>
                        <span class="text-[10px] text-gray-400 text-center">Visita nuestra sede</span>
                    </div>
                </div>

                <button @click="ordenSeleccionada = null"
                        class="w-full mt-3 text-sm text-gray-400 hover:text-gray-600 transition text-center">
                    Cerrar
                </button>
            </div>
        </div>
    </template>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Próximas Citas --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                <h3 class="font-bold text-gray-800">Próximas Citas</h3>
                <a href="{{ route('paciente.citas') }}" class="text-blue-600 text-xs font-bold hover:underline">Ver todas</a>
            </div>

            <div class="divide-y divide-gray-50">
                @forelse ($proximasCitas as $cita)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-blue-50 flex flex-col items-center justify-center text-blue-600">
                                <span class="text-[10px] font-bold uppercase leading-none">{{ \Carbon\Carbon::parse($cita->fecha)->format('M') }}</span>
                                <span class="text-sm font-bold leading-none">{{ \Carbon\Carbon::parse($cita->fecha)->format('d') }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ $cita->servicio->nombre ?? 'Consulta Médica' }}</p>
                                <p class="text-xs text-gray-500">Dr. {{ $cita->medico->usuario->nombre }} · {{ \Carbon\Carbon::parse($cita->hora)->format('h:i A') }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold"
                              style="background: {{ $cita->estado->color_hex ?? '#e2e8f0' }}22; color: {{ $cita->estado->color_hex ?? '#64748b' }}">
                            {{ $cita->estado->nombre }}
                        </span>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-gray-400 text-sm italic">No tienes citas próximas programadas.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

{{-- Contenedor de la columna derecha --}}
<div class="space-y-6"> 

    {{-- Bloque 1: Información Personal --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-800 mb-4">Mi Información</h3>
        <div class="space-y-4">
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Identificación</p>
                <p class="text-sm text-gray-700 font-medium">{{ $paciente->identificacion }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Teléfono</p>
                <p class="text-sm text-gray-700 font-medium">{{ $paciente->telefono ?? 'No registrado' }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Correo</p>
                <p class="text-sm text-gray-700 font-medium">{{ $paciente->correo ?? auth()->user()->email }}</p>
            </div>
        </div>
    </div>

    {{-- Bloque 2: Acceso rápido a Certificados --}}
    <a href="{{ route('paciente.certificados') }}"
       class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition group">
        <div class="p-3 rounded-xl bg-slate-100 group-hover:bg-slate-200 transition">
            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <p class="font-bold text-gray-800 text-sm">Mis Certificados</p>
            <p class="text-xs text-gray-400 mt-0.5">Descarga tu certificado de afiliación en PDF</p>
        </div>
        <svg class="w-4 h-4 text-gray-300 ml-auto group-hover:text-gray-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>

</div> 

{{-- Modal para Agendar Cita --}}
<div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" style="display: none;">
    <!-- Fondo oscuro -->
    <div x-show="openModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 backdrop-blur-sm transition-opacity"
         style="background-color: oklch(0.38 0.08 263.78 / 0.38)"
         @click="openModal = false"></div>

    <!-- Contenedor del Modal -->
    <div x-show="openModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 mx-4 my-8 z-10 max-h-[90vh] overflow-y-auto">

        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-bold text-gray-900">Agendar Nueva Cita</h3>
            <button @click="openModal = false" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Mostrar mensaje de error si existe --}}
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-300 rounded-lg flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="text-sm font-bold text-red-700">No se pudo agendar la cita</p>
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-700 mt-1">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('paciente.citas.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Médico</label>
                    <select name="medico_id" required
                            class="w-full bg-gray-50 border {{ $errors->has('medico_id') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} text-gray-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        <option value="">Seleccione un médico...</option>
                        @foreach($medicos as $medico)
                            <option value="{{ $medico->id }}" {{ old('medico_id') == $medico->id ? 'selected' : '' }}>{{ $medico->usuario->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Servicio</label>
                    <select name="servicio_id" required
                            class="w-full bg-gray-50 border {{ $errors->has('servicio_id') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} text-gray-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        <option value="">Seleccione un servicio...</option>
                        @foreach($servicios as $servicio)
                            <option value="{{ $servicio->id }}" {{ old('servicio_id') == $servicio->id ? 'selected' : '' }}>{{ $servicio->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Modalidad</label>
                    <select name="modalidad_id" required
                            class="w-full bg-gray-50 border {{ $errors->has('modalidad_id') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} text-gray-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                        <option value="">Seleccione...</option>
                        @foreach($modalidades as $modalidad)
                            <option value="{{ $modalidad->id }}" {{ old('modalidad_id') == $modalidad->id ? 'selected' : '' }}>{{ $modalidad->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha</label>
                        <input type="date" name="fecha" required min="{{ date('Y-m-d') }}"
                               value="{{ old('fecha') }}"
                               class="w-full bg-gray-50 border {{ $errors->has('fecha') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} text-gray-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Hora</label>
                        <input type="time" name="hora" required
                               value="{{ old('hora') }}"
                               class="w-full bg-gray-50 border {{ $errors->has('hora') ? 'border-red-500 bg-red-50' : 'border-gray-200' }} text-gray-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="openModal = false" class="bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-bold hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 transition">
                    Confirmar Cita
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
