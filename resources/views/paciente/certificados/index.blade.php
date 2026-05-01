@extends('paciente.layouts.app')

@section('title', 'Mis Certificados')
@section('page-title', 'Mis Certificados')

@section('content')

<div class="max-w-2xl mx-auto space-y-6">

    {{-- Certificado de afiliación --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Encabezado --}}
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-gray-800">Certificado de Afiliación</h2>
                <p class="text-xs text-gray-400 mt-0.5">Documento que certifica tu vinculación activa con la IPS</p>
            </div>
            <span class="text-xs font-bold text-green-600 bg-green-100 px-3 py-1 rounded-full">ACTIVO</span>
        </div>

        {{-- Datos del paciente --}}
        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Nombre completo</p>
                <p class="text-sm text-gray-800 font-medium">{{ $paciente->nombre_completo }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Documento</p>
                <p class="text-sm text-gray-800 font-medium">{{ $paciente->tipo_documento ?? 'CC' }} {{ $paciente->identificacion }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Institución</p>
                <p class="text-sm text-gray-800 font-medium">{{ $paciente->empresa?->nombre ?? '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">Fecha de expedición</p>
                <p class="text-sm text-gray-800 font-medium">{{ now()->format('d/m/Y') }}</p>
            </div>
        </div>

        {{-- Acción de descarga --}}
        <div class="px-6 py-5 bg-gray-50 border-t border-gray-100">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-2.5 bg-slate-700 rounded-xl shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Certificado_Afiliacion.pdf</p>
                    <p class="text-xs text-gray-400">Se genera en tiempo real con tus datos actuales</p>
                </div>
            </div>
            <a href="{{ route('paciente.certificado.descargar') }}"
               class="w-full inline-flex items-center justify-center gap-2 bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold px-5 py-3 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Descargar certificado PDF
            </a>
        </div>
    </div>

    {{-- Nota informativa --}}
    <div class="flex gap-3 bg-blue-50 border border-blue-100 rounded-xl px-5 py-4 text-sm text-blue-700">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>
            Este certificado es generado automáticamente con la información registrada en el sistema.
            Si algún dato no es correcto, actualízalo desde
            <a href="{{ route('paciente.perfil') }}" class="font-semibold underline underline-offset-2">Mi Perfil</a>
            o comunícate con la IPS.
        </p>
    </div>

</div>

@endsection
