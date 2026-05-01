@extends('medico.layouts.app')

@section('title', 'Mi Horario')
@section('page-title', 'Mi Horario')

@section('content')

@php
    $orden = [1, 2, 3, 4, 5, 6, 0]; // Lunes a Domingo
@endphp

@if ($horarios->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <div class="text-5xl mb-4">📅</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-1">Sin horario asignado</h3>
        <p class="text-sm text-gray-400 max-w-xs">
            El administrador aún no ha configurado tus horarios de atención. Comunícate con el área administrativa.
        </p>
    </div>
@else
    <div class="max-w-2xl mx-auto space-y-4">

        <p class="text-sm text-gray-500 mb-2">Estos son tus horarios de atención activos. Son configurados por el administrador.</p>

        @foreach ($orden as $dia)
            @if ($horarios->has($dia))
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>
                    <h3 class="font-bold text-gray-800 text-sm">{{ $dias[$dia] }}</h3>
                </div>
                <div class="px-6 py-4 space-y-2">
                    @foreach ($horarios[$dia] as $h)
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-gray-700 font-medium">
                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $h->hora_inicio)->format('g:i A') }}
                            —
                            {{ \Carbon\Carbon::createFromFormat('H:i:s', $h->hora_fin)->format('g:i A') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach

    </div>
@endif

@endsection
