@extends('paciente.layouts.app')

@section('title', 'Detalle de Historia')
@section('page-title', 'Detalle de Atención Médica')

@section('content')

    @php
        $ejecucion = $historia->ejecucionCita;
        $cita = $ejecucion?->cita;
        $medico = $cita?->medico;
        $sv = $ejecucion?->signosVitales;
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">
        <a href="{{ route('paciente.historial') }}"
            class="text-xs font-bold text-gray-400 hover:text-gray-800 flex items-center gap-1 transition px-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>

        {{-- Mensajes de éxito --}}
        @if (session('success'))
            <div
                class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Cabecera --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-6 bg-gray-50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Historia Clínica
                            #{{ str_pad($historia->id, 8, '0', STR_PAD_LEFT) }}</h2>
                        <p class="text-sm text-gray-500">
                            Fecha:
                            {{ $ejecucion?->inicio_atencion
                                ? \Carbon\Carbon::parse($ejecucion->inicio_atencion)->format('d/m/Y H:i')
                                : \Carbon\Carbon::parse($historia->created_at)->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('paciente.historial.pdf', $historia) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Descargar PDF
                    </a>
                    <form method="POST" action="{{ route('paciente.historial.correo', $historia) }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Enviar al correo
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Médico Tratante</p>
                    <p class="text-sm text-gray-800 font-bold">Dr. {{ $medico?->usuario?->nombre ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500">{{ $medico?->especialidad ?? 'Médico IPS' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Motivo de consulta</p>
                    <p class="text-sm text-gray-700">{{ $historia->motivo_consulta ?? 'No especificado' }}</p>
                </div>
            </div>
        </div>

        {{-- Signos Vitales --}}
        @if ($sv)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                        Signos Vitales
                    </h3>
                </div>
                <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach ([['Peso', $sv->peso_kg, 'kg'], ['Talla', $sv->talla_cm, 'cm'], ['P. Sistólica', $sv->presion_sistolica, 'mmHg'], ['P. Diastólica', $sv->presion_diastolica, 'mmHg'], ['Temperatura', $sv->temperatura_c, '°C'], ['Frec. Cardiaca', $sv->frecuencia_cardiaca, 'lpm'], ['SpO₂', $sv->saturacion_oxigeno, '%'], ['Frec. Respiratoria', $sv->frecuencia_respiratoria, 'rpm']] as [$label, $valor, $unidad])
                        @if ($valor !== null)
                            <div class="text-center p-3 bg-blue-50 rounded-xl border border-blue-100">
                                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">{{ $label }}
                                </p>
                                <p class="text-lg font-bold text-blue-700 mt-1">{{ $valor }}</p>
                                <p class="text-[10px] text-gray-400">{{ $unidad }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Contenido Clínico --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">

            @if ($historia->diagnostico)
                <div>
                    <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                        Diagnóstico Principal
                        @if ($historia->codigo_cie10)
                            <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-md">
                                CIE-10: {{ $historia->codigo_cie10 }}
                            </span>
                        @endif
                    </h3>
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 text-gray-700 text-sm leading-relaxed">
                        {{ $historia->diagnostico }}
                        @if ($historia->descripcion_cie10)
                            <p class="mt-1 text-xs text-gray-400 italic">{{ $historia->descripcion_cie10 }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if ($historia->enfermedad_actual)
                <div>
                    <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                        Enfermedad Actual
                    </h3>
                    <div class="text-gray-600 text-sm leading-relaxed">
                        {!! nl2br(e($historia->enfermedad_actual)) !!}
                    </div>
                </div>
            @endif

            @if ($historia->plan_tratamiento)
                <div>
                    <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                        Plan de Tratamiento
                    </h3>
                    <div class="text-gray-600 text-sm leading-relaxed">
                        {!! nl2br(e($historia->plan_tratamiento)) !!}
                    </div>
                </div>
            @endif

            @if ($historia->observaciones)
                <div>
                    <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                        Observaciones
                    </h3>
                    <div class="text-gray-600 text-sm leading-relaxed">
                        {!! nl2br(e($historia->observaciones)) !!}
                    </div>
                </div>
            @endif

        </div>

        {{-- Recetas Médicas --}}
        @if ($historia->recetasMedicas && $historia->recetasMedicas->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 bg-amber-50 border-b border-amber-100">
                    <h3 class="font-bold text-amber-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        Fórmula Médica
                    </h3>
                </div>
                <div class="p-6">
                    <ul class="space-y-4">
                        @foreach ($historia->recetasMedicas as $r)
                            <li class="p-4 rounded-xl border border-gray-100 hover:border-amber-200 transition">
                                <p class="text-sm font-bold text-gray-800">{{ $r->medicamentos }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $r->indicaciones }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

    </div>

@endsection
