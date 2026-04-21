@extends('paciente.layouts.app')

@section('title', 'Inicio')
@section('page-title', 'Bienvenido, ' . $paciente->nombre_completo)

@section('content')

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

    {{-- Información Personal --}}
    <div>
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
    </div>
</div>

@endsection
