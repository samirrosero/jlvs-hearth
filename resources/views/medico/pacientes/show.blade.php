@extends('medico.layouts.app')

@section('title', $paciente->nombre_completo)
@section('page-title', 'Historial del Paciente')

@section('content')

{{-- ── Info del paciente ────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <div class="flex items-start gap-5">
        <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
            <span class="text-blue-700 font-bold text-xl">
                {{ strtoupper(substr($paciente->nombre_completo ?? 'P', 0, 1)) }}
            </span>
        </div>
        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-2 text-sm">
            <div>
                <p class="text-gray-400 text-xs">Nombre completo</p>
                <p class="font-semibold text-gray-800">{{ $paciente->nombre_completo }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Identificación</p>
                <p class="font-medium text-gray-700">{{ $paciente->identificacion ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Fecha de nacimiento</p>
                <p class="font-medium text-gray-700">
                    {{ $paciente->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                    @if ($paciente->fecha_nacimiento)
                        <span class="text-gray-400">({{ $paciente->fecha_nacimiento->age }} años)</span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Sexo</p>
                <p class="font-medium text-gray-700">{{ $paciente->sexo ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Teléfono</p>
                <p class="font-medium text-gray-700">{{ $paciente->telefono ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Correo</p>
                <p class="font-medium text-gray-700">{{ $paciente->correo ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs">Dirección</p>
                <p class="font-medium text-gray-700">{{ $paciente->direccion ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>

<div x-data="{ seccion: 'historias' }" class="space-y-4">

    {{-- ── Tabs de secciones ───────────────────────────────────── --}}
    <div class="flex gap-2 border-b border-gray-200">
        @foreach ([
            ['historias', 'Historias Clínicas', count($historias)],
            ['signos',    'Signos Vitales',      count($signosVitales)],
            ['antecedentes', 'Antecedentes',     count($antecedentes)],
        ] as [$clave, $etiqueta, $cantidad])
        <button @click="seccion = '{{ $clave }}'"
                :class="seccion === '{{ $clave }}' ? 'border-b-2 border-gray-900 text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-3 text-sm transition flex items-center gap-2">
            {{ $etiqueta }}
            <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full">{{ $cantidad }}</span>
        </button>
        @endforeach
    </div>

    {{-- ── Historias Clínicas ──────────────────────────────────── --}}
    <div x-show="seccion === 'historias'" class="space-y-4">
        @forelse ($historias as $historia)
            <div x-data="{ abierta: false }" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <button @click="abierta = !abierta"
                        class="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition text-left">
                    <div class="flex items-center gap-4">
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ $historia->ejecucionCita?->cita?->fecha
                                    ? \Carbon\Carbon::parse($historia->ejecucionCita->cita->fecha)->format('d/m/Y')
                                    : $historia->created_at->format('d/m/Y') }}
                            </p>
                            @if ($historia->codigo_cie10)
                                <p class="text-xs text-blue-600 font-mono">
                                    {{ $historia->codigo_cie10 }} — {{ $historia->descripcion_cie10 }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <svg :class="abierta ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="abierta" class="px-5 pb-5 border-t border-gray-50 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm pt-4">
                    @foreach ([
                        ['Motivo de consulta',  $historia->motivo_consulta],
                        ['Enfermedad actual',    $historia->enfermedad_actual],
                        ['Plan de tratamiento', $historia->plan_tratamiento],
                        ['Evaluación',          $historia->evaluacion],
                        ['Observaciones',       $historia->observaciones],
                    ] as [$label, $valor])
                        @if ($valor)
                        <div>
                            <p class="text-xs text-gray-400 font-medium mb-1">{{ $label }}</p>
                            <p class="text-gray-700">{{ $valor }}</p>
                        </div>
                        @endif
                    @endforeach

                    {{-- Recetas asociadas --}}
                    @if ($historia->recetasMedicas->isNotEmpty())
                        <div class="md:col-span-2 border-t border-gray-100 pt-3">
                            <p class="text-xs text-gray-400 font-medium mb-2">Receta médica</p>
                            @foreach ($historia->recetasMedicas as $receta)
                                <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700 space-y-1">
                                    <p><span class="font-medium text-gray-600">Medicamentos:</span> {{ $receta->medicamentos }}</p>
                                    @if ($receta->indicaciones)
                                        <p><span class="font-medium text-gray-600">Indicaciones:</span> {{ $receta->indicaciones }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-400 text-sm">
                No hay historias clínicas registradas para este paciente.
            </div>
        @endforelse
    </div>

    {{-- ── Signos Vitales ──────────────────────────────────────── --}}
    <div x-show="seccion === 'signos'">
        @if ($signosVitales->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        @foreach (['Fecha', 'Peso', 'Talla', 'P. Arterial', 'Temp.', 'FC', 'SatO₂', 'FR'] as $col)
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($signosVitales as $sv)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-700">
                            {{ $sv->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $sv->peso_kg ? $sv->peso_kg . ' kg' : '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $sv->talla_cm ? $sv->talla_cm . ' cm' : '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $sv->presion_sistolica && $sv->presion_diastolica
                                ? $sv->presion_sistolica . '/' . $sv->presion_diastolica . ' mmHg'
                                : '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $sv->temperatura_c ? $sv->temperatura_c . '°C' : '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $sv->frecuencia_cardiaca ? $sv->frecuencia_cardiaca . ' lpm' : '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $sv->saturacion_oxigeno ? $sv->saturacion_oxigeno . '%' : '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $sv->frecuencia_respiratoria ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-400 text-sm">
                No hay signos vitales registrados para este paciente.
            </div>
        @endif
    </div>

    {{-- ── Antecedentes ────────────────────────────────────────── --}}
    <div x-show="seccion === 'antecedentes'">
        <div x-data="antecedentesPanel(@json($antecedentes), {{ $paciente->id }})" class="space-y-5">

            {{-- Formulario para agregar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Registrar nuevo antecedente</h4>
                <form @submit.prevent="guardar()" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-gray-500 font-medium">Tipo <span class="text-red-400">*</span></label>
                            <select x-model="form.tipo"
                                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 bg-white">
                                <option value="">Seleccionar tipo...</option>
                                <option value="personal">Personal</option>
                                <option value="familiar">Familiar</option>
                                <option value="quirurgico">Quirúrgico</option>
                                <option value="alergico">Alérgico</option>
                                <option value="farmacologico">Farmacológico</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs text-gray-500 font-medium">Descripción <span class="text-red-400">*</span></label>
                            <input type="text" x-model="form.descripcion"
                                   placeholder="Ej: Hipertensión arterial desde 2018"
                                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" :disabled="cargando"
                                class="bg-gray-900 hover:bg-gray-700 text-white text-sm px-5 py-2 rounded-lg transition disabled:opacity-40">
                            <span x-text="cargando ? 'Guardando...' : 'Registrar antecedente'"></span>
                        </button>
                        <span x-show="mensaje" x-text="mensaje"
                              :class="error ? 'text-red-600' : 'text-emerald-600'"
                              class="text-xs"></span>
                    </div>
                </form>
            </div>

            {{-- Listado agrupado por tipo --}}
            <template x-if="lista.length === 0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-400 text-sm">
                    No hay antecedentes registrados para este paciente.
                </div>
            </template>

            <template x-if="lista.length > 0">
                <div class="space-y-4">
                    <template x-for="grupo in tipos" :key="grupo.valor">
                        <div x-show="lista.filter(a => a.tipo === grupo.valor).length > 0"
                             class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wide" x-text="grupo.etiqueta"></h4>
                            </div>
                            <div class="divide-y divide-gray-50">
                                <template x-for="ant in lista.filter(a => a.tipo === grupo.valor)" :key="ant.id">
                                    <div class="px-5 py-3 flex items-start justify-between gap-4 text-sm">
                                        <p class="text-gray-700" x-text="ant.descripcion"></p>
                                        <span class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0"
                                              x-text="ant.created_at ? new Date(ant.created_at).toLocaleDateString('es-CO') : ''"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function antecedentesPanel(inicial, pacienteId) {
    return {
        lista: inicial,
        form: { tipo: '', descripcion: '' },
        cargando: false,
        mensaje: '',
        error: false,
        tipos: [
            { valor: 'personal',      etiqueta: 'Personal' },
            { valor: 'familiar',      etiqueta: 'Familiar' },
            { valor: 'quirurgico',    etiqueta: 'Quirúrgico' },
            { valor: 'alergico',      etiqueta: 'Alérgico' },
            { valor: 'farmacologico', etiqueta: 'Farmacológico' },
            { valor: 'otros',         etiqueta: 'Otros' },
        ],
        async guardar() {
            this.error = false;
            if (!this.form.tipo || !this.form.descripcion.trim()) {
                this.mensaje = 'Debes seleccionar el tipo y escribir una descripción.';
                this.error = true;
                return;
            }
            this.cargando = true;
            this.mensaje = '';
            try {
                const res = await fetch('/antecedentes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        paciente_id: pacienteId,
                        tipo:        this.form.tipo,
                        descripcion: this.form.descripcion,
                    }),
                });
                const data = await res.json();
                if (res.ok) {
                    this.lista.unshift(data);
                    this.form = { tipo: '', descripcion: '' };
                    this.mensaje = 'Antecedente registrado correctamente.';
                    setTimeout(() => this.mensaje = '', 3000);
                } else {
                    this.mensaje = data.message ?? 'Error al guardar el antecedente.';
                    this.error = true;
                }
            } finally { this.cargando = false; }
        },
    };
}
</script>
@endpush
