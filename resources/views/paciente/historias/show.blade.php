@extends('paciente.layouts.app')

@section('title', 'Detalle de Historia')
@section('page-title', 'Detalle de Atención Médica')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">
    
    {{-- Cabecera Detalle --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-6 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Historia Clínica #{{ $historia->id }}</h2>
                    <p class="text-sm text-gray-500">Fecha de atención: {{ \Carbon\Carbon::parse($h->created_at)->format('d/m/Y h:i A') }}</p>
                </div>
            </div>
            <a href="{{ route('paciente.historial') }}" class="text-xs font-bold text-gray-400 hover:text-gray-800 flex items-center gap-1 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver al listado
            </a>
        </div>

        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Médico Tratante</p>
                <p class="text-sm text-gray-800 font-bold">Dr. {{ $historia->ejecucionCita?->cita?->medico?->usuario?->nombre ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500">{{ $historia->ejecucionCita?->cita?->medico?->especialidad ?? 'Médico IPS' }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Motivo de consulta</p>
                <p class="text-sm text-gray-700">{{ $historia->ejecucionCita?->cita?->motivo ?? 'No especificado' }}</p>
            </div>
        </div>
    </div>

    {{-- Diagnóstico y Evolución --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
        <div>
            <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                Diagnóstico Principal
            </h3>
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 text-gray-700 text-sm leading-relaxed">
                {{ $historia->diagnostico ?? 'Sin diagnóstico registrado.' }}
            </div>
        </div>

        <div>
            <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                Evolución / Observaciones
            </h3>
            <div class="text-gray-600 text-sm leading-relaxed">
                {!! nl2br(e($historia->evolucion ?? 'No hay observaciones adicionales.')) !!}
            </div>
        </div>
    </div>

    {{-- Recetas Médicas --}}
    @if ($historia->recetasMedicas && $historia->recetasMedicas->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-amber-50 border-b border-amber-100">
                <h3 class="font-bold text-amber-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    Fórmula Médica
                </h3>
            </div>
            <div class="p-6">
                <ul class="space-y-4">
                    @foreach ($historia->recetasMedicas as $r)
                        <li class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 rounded-xl border border-gray-100 hover:border-amber-200 transition">
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ $r->medicamento }}</p>
                                <p class="text-xs text-gray-500">{{ $r->indicaciones }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-400 font-medium">Cantidad: {{ $r->cantidad }}</p>
                                <p class="text-xs text-gray-400 font-medium">Frecuencia: {{ $r->frecuencia }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

</div>

@endsection
