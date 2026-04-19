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
    <div x-show="seccion === 'antecedentes'" class="space-y-3">
        @forelse ($antecedentes as $ant)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-sm">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-semibold text-gray-800">{{ $ant->tipo ?? 'Antecedente' }}</span>
                    <span class="text-xs text-gray-400">{{ $ant->created_at->format('d/m/Y') }}</span>
                </div>
                <p class="text-gray-600">{{ $ant->descripcion ?? '—' }}</p>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-400 text-sm">
                No hay antecedentes registrados para este paciente.
            </div>
        @endforelse
    </div>

</div>
@endsection
