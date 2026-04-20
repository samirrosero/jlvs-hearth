@extends('medico.layouts.app')

@section('title', 'Atender Cita')
@section('page-title', 'Atender Cita')

@section('content')

@php
    $ejecucion    = $cita->ejecucion;
    $historia     = $ejecucion?->historiaClinica;
    $signos       = $ejecucion?->signosVitales;
    $receta       = $historia?->recetasMedicas?->first();
    $consultaData = [
        'cita_id'   => $cita->id,
        'ejecucion' => $ejecucion,
        'historia'  => $historia,
        'signos'    => $signos,
        'receta'    => $receta,
    ];
@endphp

<div x-data='consulta(@json($consultaData))'
     @cie10-seleccionado="formHistoria.codigo_cie10 = $event.detail.codigo; formHistoria.descripcion_cie10 = $event.detail.descripcion; formHistoria.diagnostico = $event.detail.codigo + ' - ' + $event.detail.descripcion"
     class="space-y-6">

    {{-- ── Cabecera: info cita + paciente ───────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Datos del paciente --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Paciente</h3>
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-blue-700 font-bold text-lg">{{ strtoupper(substr($cita->paciente->nombre_completo ?? 'P', 0, 1)) }}</span>
                </div>
                <div class="flex-1 grid grid-cols-2 gap-x-6 gap-y-1 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs">Nombre completo</p>
                        <p class="font-semibold text-gray-800">{{ $cita->paciente->nombre_completo ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Identificación</p>
                        <p class="font-medium text-gray-700">{{ $cita->paciente->identificacion ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Fecha de nacimiento</p>
                        <p class="font-medium text-gray-700">
                            {{ $cita->paciente->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                            @if ($cita->paciente->fecha_nacimiento)
                                <span class="text-gray-400">({{ $cita->paciente->fecha_nacimiento->age }} años)</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Sexo</p>
                        <p class="font-medium text-gray-700">{{ $cita->paciente->sexo ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Teléfono</p>
                        <p class="font-medium text-gray-700">{{ $cita->paciente->telefono ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Correo</p>
                        <p class="font-medium text-gray-700 truncate">{{ $cita->paciente->correo ?? '—' }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-50">
                <a href="{{ route('medico.pacientes.show', $cita->paciente) }}"
                   class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                    Ver historial completo del paciente →
                </a>
            </div>
        </div>

        {{-- Datos de la cita + Control de atención --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-4">
            <div>
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Cita</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Fecha</span>
                        <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Hora</span>
                        <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Servicio</span>
                        <span class="font-medium text-gray-800">{{ $cita->servicio->nombre ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Modalidad</span>
                        <span class="font-medium text-gray-800">{{ $cita->modalidad->nombre ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Estado</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                              style="background:{{ ($cita->estado->color_hex ?? '#e2e8f0') }}22; color:{{ $cita->estado->color_hex ?? '#64748b' }}">
                            {{ $cita->estado->nombre ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Control inicio/fin de atención --}}
            <div class="border-t border-gray-100 pt-4 mt-auto">
                <template x-if="!estado.ejecucion">
                    <button @click="iniciarAtencion()"
                            :disabled="cargando"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2.5 rounded-lg transition disabled:opacity-50">
                        Iniciar atención
                    </button>
                </template>
                <template x-if="estado.ejecucion && !estado.ejecucion.fin_atencion">
                    <div class="space-y-2">
                        <p class="text-xs text-emerald-700 font-medium text-center">
                            En atención desde <span x-text="formatHora(estado.ejecucion.inicio_atencion)"></span>
                        </p>
                        <button @click="finalizarAtencion()"
                                :disabled="cargando"
                                class="w-full bg-gray-900 hover:bg-gray-700 text-white text-sm font-medium py-2.5 rounded-lg transition disabled:opacity-50">
                            Finalizar atención
                        </button>
                    </div>
                </template>
                <template x-if="estado.ejecucion && estado.ejecucion.fin_atencion">
                    <div class="text-center text-sm text-gray-500">
                        <p class="font-medium text-gray-700">Atención finalizada</p>
                        <p class="text-xs">Duración: <span x-text="estado.ejecucion.duracion_minutos + ' min'"></span></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ── Tabs: Signos / Historia / Receta ──────────────────────── --}}
    <div x-data="{ tab: 'signos' }" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Tab nav --}}
        <div class="flex border-b border-gray-100">
            @foreach ([['signos', 'Signos Vitales'], ['historia', 'Historia Clínica'], ['receta', 'Receta Médica']] as [$key, $label])
            <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-b-2 border-gray-900 text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="px-5 py-3.5 text-sm transition">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- ── Tab: Signos Vitales ───────────────────────────────── --}}
        <div x-show="tab === 'signos'" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700">Signos Vitales</h3>
                <template x-if="estado.signos">
                    <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Registrado</span>
                </template>
            </div>

            <form @submit.prevent="guardarSignos()" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach ([
                    ['peso_kg',                 'Peso (kg)',             'number', '0.1'],
                    ['talla_cm',                'Talla (cm)',            'number', '1'],
                    ['presion_sistolica',        'Presión sistólica',     'number', '1'],
                    ['presion_diastolica',       'Presión diastólica',    'number', '1'],
                    ['temperatura_c',            'Temperatura (°C)',      'number', '0.1'],
                    ['frecuencia_cardiaca',      'Frec. cardíaca (lpm)',  'number', '1'],
                    ['saturacion_oxigeno',       'Saturación O₂ (%)',     'number', '1'],
                    ['frecuencia_respiratoria',  'Frec. respiratoria',    'number', '1'],
                ] as [$campo, $etiqueta, $tipo, $paso])
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-gray-500 font-medium">{{ $etiqueta }}</label>
                    <input type="{{ $tipo }}" step="{{ $paso }}" name="{{ $campo }}"
                           x-model="formSignos.{{ $campo }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                </div>
                @endforeach

                <div class="col-span-2 md:col-span-3 flex flex-col gap-1">
                    <label class="text-xs text-gray-500 font-medium">Observaciones</label>
                    <textarea name="observaciones" x-model="formSignos.observaciones" rows="2"
                              class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"></textarea>
                </div>

                <div class="col-span-2 md:col-span-3 flex items-center gap-3">
                    <button type="submit" :disabled="cargando || !estado.ejecucion"
                            class="bg-gray-900 hover:bg-gray-700 text-white text-sm px-5 py-2 rounded-lg transition disabled:opacity-40">
                        <span x-text="estado.signos ? 'Actualizar signos' : 'Guardar signos'"></span>
                    </button>
                    <span x-show="!estado.ejecucion" class="text-xs text-amber-600">Inicia la atención primero</span>
                    <span x-show="mensajes.signos" x-text="mensajes.signos" class="text-xs text-emerald-600"></span>
                </div>
            </form>
        </div>

        {{-- ── Tab: Historia Clínica ────────────────────────────── --}}
        <div x-show="tab === 'historia'" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700">Historia Clínica</h3>
                <template x-if="estado.historia">
                    <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Guardada</span>
                </template>
            </div>

            <form @submit.prevent="guardarHistoria()" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500 font-medium">Motivo de consulta <span class="text-red-400">*</span></label>
                        <textarea x-model="formHistoria.motivo_consulta" rows="3" required
                                  class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"></textarea>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500 font-medium">Enfermedad actual</label>
                        <textarea x-model="formHistoria.enfermedad_actual" rows="3"
                                  class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"></textarea>
                    </div>
                </div>

                {{-- CIE-10 --}}
                <div x-data="cie10Search()" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500 font-medium">Diagnóstico CIE-10</label>
                        <div class="relative">
                            <input type="text" x-model="query" @input.debounce.400ms="buscar()"
                                   @focus="abierto = resultados.length > 0"
                                   placeholder="Buscar código o descripción..."
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                            <ul x-show="abierto && resultados.length > 0"
                                @click.outside="abierto = false"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto text-sm">
                                <template x-for="item in resultados" :key="item.codigo">
                                    <li @click="seleccionar(item)"
                                        class="px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-0">
                                        <span class="font-mono text-xs text-blue-600" x-text="item.codigo"></span>
                                        <span class="ml-2 text-gray-700" x-text="item.descripcion"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500 font-medium">Descripción seleccionada</label>
                        <div class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600 bg-gray-50 min-h-[38px]">
                            <template x-if="$root.querySelector && false"></template>
                            <span x-show="formHistoria.codigo_cie10">
                                <span class="font-mono text-blue-600" x-text="formHistoria.codigo_cie10"></span>
                                — <span x-text="formHistoria.descripcion_cie10"></span>
                            </span>
                            <span x-show="!formHistoria.codigo_cie10" class="text-gray-400">Sin diagnóstico seleccionado</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500 font-medium">Plan de tratamiento</label>
                        <textarea x-model="formHistoria.plan_tratamiento" rows="3"
                                  class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"></textarea>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-500 font-medium">Evaluación</label>
                        <textarea x-model="formHistoria.evaluacion" rows="3"
                                  class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"></textarea>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs text-gray-500 font-medium">Observaciones</label>
                    <textarea x-model="formHistoria.observaciones" rows="2"
                              class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"></textarea>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" :disabled="cargando || !estado.ejecucion"
                            class="bg-gray-900 hover:bg-gray-700 text-white text-sm px-5 py-2 rounded-lg transition disabled:opacity-40">
                        <span x-text="estado.historia ? 'Actualizar historia' : 'Guardar historia'"></span>
                    </button>
                    <span x-show="!estado.ejecucion" class="text-xs text-amber-600">Inicia la atención primero</span>
                    <span x-show="mensajes.historia" x-text="mensajes.historia" class="text-xs text-emerald-600"></span>
                </div>
            </form>
        </div>

        {{-- ── Tab: Receta Médica ───────────────────────────────── --}}
        <div x-show="tab === 'receta'" class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-700">Receta Médica</h3>
                <template x-if="estado.receta">
                    <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Emitida</span>
                </template>
            </div>

            <form @submit.prevent="guardarReceta()" class="space-y-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-gray-500 font-medium">
                        Medicamentos <span class="text-red-400">*</span>
                        <span class="text-gray-400 font-normal ml-1">(nombre, dosis, frecuencia y duración)</span>
                    </label>
                    <textarea x-model="formReceta.medicamentos" rows="5" required
                              placeholder="Ej: Ibuprofeno 400mg — 1 tableta cada 8 horas por 5 días"
                              class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"></textarea>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-gray-500 font-medium">Indicaciones adicionales</label>
                    <textarea x-model="formReceta.indicaciones" rows="3"
                              class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"></textarea>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" :disabled="cargando || !estado.historia"
                            class="bg-gray-900 hover:bg-gray-700 text-white text-sm px-5 py-2 rounded-lg transition disabled:opacity-40">
                        <span x-text="estado.receta ? 'Actualizar receta' : 'Emitir receta'"></span>
                    </button>
                    <span x-show="!estado.historia" class="text-xs text-amber-600">Guarda la historia clínica primero</span>
                    <span x-show="mensajes.receta" x-text="mensajes.receta" class="text-xs text-emerald-600"></span>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function jsonHeaders() {
    return { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' };
}

function consulta(inicial) {
    return {
        cargando: false,
        estado: {
            ejecucion: inicial.ejecucion,
            historia:  inicial.historia,
            signos:    inicial.signos,
            receta:    inicial.receta,
        },
        mensajes: { signos: '', historia: '', receta: '' },

        formSignos: {
            peso_kg:                inicial.signos?.peso_kg                ?? '',
            talla_cm:               inicial.signos?.talla_cm               ?? '',
            presion_sistolica:      inicial.signos?.presion_sistolica      ?? '',
            presion_diastolica:     inicial.signos?.presion_diastolica     ?? '',
            temperatura_c:          inicial.signos?.temperatura_c          ?? '',
            frecuencia_cardiaca:    inicial.signos?.frecuencia_cardiaca    ?? '',
            saturacion_oxigeno:     inicial.signos?.saturacion_oxigeno     ?? '',
            frecuencia_respiratoria:inicial.signos?.frecuencia_respiratoria?? '',
            observaciones:          inicial.signos?.observaciones          ?? '',
        },

        formHistoria: {
            motivo_consulta:   inicial.historia?.motivo_consulta   ?? '',
            enfermedad_actual: inicial.historia?.enfermedad_actual ?? '',
            diagnostico:       inicial.historia?.diagnostico       ?? '',
            codigo_cie10:      inicial.historia?.codigo_cie10      ?? '',
            descripcion_cie10: inicial.historia?.descripcion_cie10 ?? '',
            plan_tratamiento:  inicial.historia?.plan_tratamiento  ?? '',
            evaluacion:        inicial.historia?.evaluacion        ?? '',
            observaciones:     inicial.historia?.observaciones     ?? '',
        },

        formReceta: {
            medicamentos: inicial.receta?.medicamentos  ?? '',
            indicaciones: inicial.receta?.indicaciones  ?? '',
        },

        formatHora(dt) {
            if (!dt) return '';
            return new Date(dt).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
        },

        async iniciarAtencion() {
            this.cargando = true;
            try {
                const res = await fetch('/ejecuciones', {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify({ cita_id: inicial.cita_id, inicio_atencion: new Date().toISOString() }),
                });
                const data = await res.json();
                if (res.ok) this.estado.ejecucion = data;
            } finally { this.cargando = false; }
        },

        async finalizarAtencion() {
            if (!this.estado.ejecucion) return;
            this.cargando = true;
            try {
                const res = await fetch(`/ejecuciones/${this.estado.ejecucion.id}`, {
                    method: 'PATCH',
                    headers: jsonHeaders(),
                    body: JSON.stringify({ fin_atencion: new Date().toISOString() }),
                });
                const data = await res.json();
                if (res.ok) this.estado.ejecucion = data;
            } finally { this.cargando = false; }
        },

        async guardarSignos() {
            if (!this.estado.ejecucion) return;
            this.cargando = true;
            this.mensajes.signos = '';
            const payload = { ...this.formSignos, ejecucion_cita_id: this.estado.ejecucion.id, paciente_id: {{ $cita->paciente_id }} };
            try {
                const url    = this.estado.signos ? `/signos-vitales/${this.estado.signos.id}` : '/signos-vitales';
                const method = this.estado.signos ? 'PUT' : 'POST';
                const res    = await fetch(url, { method, headers: jsonHeaders(), body: JSON.stringify(payload) });
                const data   = await res.json();
                if (res.ok) { this.estado.signos = data; this.mensajes.signos = 'Signos guardados correctamente.'; }
            } finally { this.cargando = false; }
        },

        async guardarHistoria() {
            if (!this.estado.ejecucion) return;
            this.cargando = true;
            this.mensajes.historia = '';
            const payload = { ...this.formHistoria, ejecucion_cita_id: this.estado.ejecucion.id, paciente_id: {{ $cita->paciente_id }} };
            try {
                const url    = this.estado.historia ? `/historias-clinicas/${this.estado.historia.id}` : '/historias-clinicas';
                const method = this.estado.historia ? 'PUT' : 'POST';
                const res    = await fetch(url, { method, headers: jsonHeaders(), body: JSON.stringify(payload) });
                const data   = await res.json();
                if (res.ok) { this.estado.historia = data; this.mensajes.historia = 'Historia guardada correctamente.'; }
            } finally { this.cargando = false; }
        },

        async guardarReceta() {
            if (!this.estado.historia) return;
            this.cargando = true;
            this.mensajes.receta = '';
            const payload = { ...this.formReceta, historia_clinica_id: this.estado.historia.id };
            try {
                const url    = this.estado.receta ? `/recetas/${this.estado.receta.id}` : '/recetas';
                const method = this.estado.receta ? 'PUT' : 'POST';
                const res    = await fetch(url, { method, headers: jsonHeaders(), body: JSON.stringify(payload) });
                const data   = await res.json();
                if (res.ok) { this.estado.receta = data; this.mensajes.receta = 'Receta emitida correctamente.'; }
            } finally { this.cargando = false; }
        },
    };
}

function cie10Search() {
    return {
        query: '',
        resultados: [],
        abierto: false,
        async buscar() {
            if (this.query.length < 2) { this.resultados = []; this.abierto = false; return; }
            const res = await fetch(`/cie10?q=${encodeURIComponent(this.query)}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            const data = await res.json();
            this.resultados = Array.isArray(data) ? data.slice(0, 10) : (data.data ?? []).slice(0, 10);
            this.abierto = this.resultados.length > 0;
        },
        seleccionar(item) {
            this.$dispatch('cie10-seleccionado', item);
            this.query   = item.codigo;
            this.abierto = false;
        },
    };
}
</script>
@endpush
