@extends('gestor.layouts.app')

@section('title', 'Nueva cita')
@section('page-title', 'Nueva cita')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- ── Encabezado ── --}}
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">Agendar nueva cita</h2>
        <a href="{{ route('gestor.citas') }}"
           class="text-sm text-gray-600 hover:text-gray-800 font-medium inline-flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver a citas
        </a>
    </div>

    {{-- ── Errores de validación ── --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm space-y-1">
            <p class="font-semibold">Por favor corrige los siguientes errores:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Formulario ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
         x-data="formularioCita()">

        <form method="POST" action="{{ route('gestor.citas.store') }}" @submit.prevent="submitForm($event)">
            @csrf

            <div class="space-y-5">

                {{-- Paciente (searchable) --}}
                <div x-data="{ busqueda: '{{ old('paciente_search', '') }}', abierto: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Paciente <span class="text-red-500">*</span>
                    </label>
                    <input type="hidden" name="paciente_id" x-model="pacienteId">
                    <div class="relative">
                        <input type="text"
                               x-model="busqueda"
                               @focus="abierto = true"
                               @click.outside="abierto = false"
                               placeholder="Buscar por nombre o identificación…"
                               autocomplete="off"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <ul x-show="abierto && busqueda.length >= 1"
                            class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-52 overflow-y-auto"
                            style="display:none">
                            @foreach($pacientes as $pac)
                                <li>
                                    <button type="button"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-blue-50 transition-colors"
                                            x-show="busqueda.length < 1 || '{{ strtolower($pac->nombre_completo . ' ' . $pac->identificacion) }}'.includes(busqueda.toLowerCase())"
                                            @click="pacienteId = {{ $pac->id }}; busqueda = '{{ addslashes($pac->nombre_completo) }}'; abierto = false">
                                        <span class="font-medium text-gray-800">{{ $pac->nombre_completo }}</span>
                                        <span class="text-gray-400 text-xs ml-2">{{ $pac->identificacion }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        ¿Paciente nuevo?
                        <a href="{{ route('gestor.pacientes.create') }}" class="text-blue-600 hover:underline">Registrarlo aquí</a>.
                    </p>
                    @error('paciente_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Médico --}}
                <div>
                    <label for="medico_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Médico <span class="text-red-500">*</span>
                    </label>
                    <select id="medico_id" name="medico_id"
                            x-model="medicoId"
                            @change="alCambiarMedico()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Selecciona un médico</option>
                        @foreach($medicos as $medico)
                            <option value="{{ $medico->id }}"
                                {{ old('medico_id') == $medico->id ? 'selected' : '' }}>
                                {{ $medico->usuario->name ?? $medico->usuario->nombre ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                    @error('medico_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Servicio --}}
                <div>
                    <label for="servicio_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Servicio <span class="text-red-500">*</span>
                    </label>
                    <select id="servicio_id" name="servicio_id"
                            x-model="servicioId"
                            @change="alCambiarServicio()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Selecciona un servicio</option>
                        @foreach($servicios as $servicio)
                            <option value="{{ $servicio->id }}"
                                {{ old('servicio_id') == $servicio->id ? 'selected' : '' }}>
                                {{ $servicio->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('servicio_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Modalidad --}}
                <div>
                    <label for="modalidad_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Modalidad <span class="text-red-500">*</span>
                    </label>
                    <select id="modalidad_id" name="modalidad_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Selecciona una modalidad</option>
                        @foreach($modalidades as $modalidad)
                            <option value="{{ $modalidad->id }}"
                                {{ old('modalidad_id') == $modalidad->id ? 'selected' : '' }}>
                                {{ $modalidad->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('modalidad_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fecha --}}
                <div>
                    <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           id="fecha"
                           name="fecha"
                           x-model="fecha"
                           @change="alCambiarFecha()"
                           :min="hoy"
                           value="{{ old('fecha') }}"
                           :disabled="!medicoId"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed">
                    <p class="text-xs text-gray-400 mt-1" x-show="!medicoId">
                        Selecciona un médico primero para habilitar la fecha.
                    </p>
                    <p class="text-xs text-blue-600 mt-1"
                       x-show="medicoId && diasDisponibles.length > 0 && !fecha">
                        El médico tiene <span x-text="diasDisponibles.length"></span> días disponibles este mes.
                    </p>
                    @error('fecha')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Hora (slot pills) --}}
                <div x-show="fecha && medicoId">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Hora <span class="text-red-500">*</span>
                    </label>

                    {{-- Cargando --}}
                    <div x-show="cargandoSlots" class="flex items-center gap-2 text-sm text-gray-500 py-2">
                        <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Consultando disponibilidad…
                    </div>

                    {{-- Sin disponibilidad --}}
                    <div x-show="!cargandoSlots && sinDisponibilidad"
                         class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-800">
                        No hay disponibilidad para la fecha seleccionada. Elige otro día o médico.
                    </div>

                    {{-- Slots pill --}}
                    <div x-show="!cargandoSlots && slots.length > 0"
                         class="flex flex-wrap gap-2 pt-1">
                        <input type="hidden" name="hora" x-model="hora">
                        <template x-for="slot in slots" :key="slot">
                            <button type="button"
                                    @click="hora = slot"
                                    :class="hora === slot
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'bg-white text-gray-700 border-gray-300 hover:border-blue-400 hover:text-blue-600'"
                                    class="px-3 py-1.5 rounded-lg border text-sm font-medium transition-colors tabular-nums"
                                    x-text="slot">
                            </button>
                        </template>
                    </div>
                    @error('hora')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Estado --}}
                <div>
                    <label for="estado_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Estado
                    </label>
                    <select id="estado_id" name="estado_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        @foreach($estados as $estado)
                            <option value="{{ $estado->id }}"
                                {{ (old('estado_id', 1) == $estado->id) ? 'selected' : '' }}>
                                {{ $estado->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('estado_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- Acciones --}}
            <div class="flex items-center justify-between pt-6 mt-2 border-t border-gray-100">
                <a href="{{ route('gestor.citas') }}"
                   class="text-sm text-gray-600 hover:text-gray-800 font-medium transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
                    Guardar cita
                </button>
            </div>

        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function formularioCita() {
    return {
        medicoId:         '{{ old('medico_id', '') }}',
        servicioId:       '{{ old('servicio_id', '') }}',
        pacienteId:       '{{ old('paciente_id', '') }}',
        fecha:            '{{ old('fecha', '') }}',
        hora:             '{{ old('hora', '') }}',
        slots:            [],
        diasDisponibles:  [],
        cargandoSlots:    false,
        sinDisponibilidad: false,
        hoy:              new Date().toISOString().split('T')[0],
        mesActual:        new Date().toISOString().slice(0, 7),

        async alCambiarMedico() {
            this.fecha = '';
            this.hora = '';
            this.slots = [];
            this.sinDisponibilidad = false;
            if (!this.medicoId) return;
            await this.cargarDiasDisponibles(this.mesActual);
        },

        async alCambiarServicio() {
            if (this.fecha && this.medicoId) {
                await this.cargarSlots();
            }
        },

        async cargarDiasDisponibles(mes) {
            try {
                const res = await fetch(`/medicos/${this.medicoId}/dias-disponibles?mes=${mes}`, {
                    headers: this.headers(),
                });
                const data = await res.json();
                this.diasDisponibles = data.dias_disponibles ?? [];
            } catch (e) {
                this.diasDisponibles = [];
            }
        },

        async alCambiarFecha() {
            this.hora = '';
            this.slots = [];
            this.sinDisponibilidad = false;
            if (!this.fecha || !this.medicoId) return;
            await this.cargarSlots();
        },

        async cargarSlots() {
            this.cargandoSlots = true;
            this.slots = [];
            this.sinDisponibilidad = false;
            try {
                let url = `/citas/disponibilidad?medico_id=${this.medicoId}&fecha=${this.fecha}`;
                if (this.servicioId) url += `&servicio_id=${this.servicioId}`;
                const res  = await fetch(url, { headers: this.headers() });
                const data = await res.json();
                this.slots             = data.slots ?? [];
                this.sinDisponibilidad = !data.disponible;
            } catch (e) {
                this.sinDisponibilidad = true;
            } finally {
                this.cargandoSlots = false;
            }
        },

        submitForm(event) {
            event.target.submit();
        },

        headers() {
            return {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            };
        },
    };
}
</script>
@endpush
