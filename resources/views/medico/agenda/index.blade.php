@extends('medico.layouts.app')

@section('title', 'Mi Agenda')
@section('page-title', 'Mi Agenda Semanal')

@push('styles')
<style>
    .estado-pendiente  { background:#fef3c7; color:#92400e; border-color:#fde68a; }
    .estado-atendida   { background:#dcfce7; color:#166534; border-color:#bbf7d0; }
    .estado-cancelada  { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
    .estado-default    { background:#f1f5f9; color:#475569; border-color:#e2e8f0; }
    .col-hoy           { background:#f0f9ff; border-color:#bae6fd !important; }
    .col-hoy .day-name { color:#0369a1; }
    .col-hoy .day-num  { background:#0369a1; color:#fff; }
</style>
@endpush

@section('content')

{{-- ── Navegación de semana ───────────────────────────────────── --}}
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex items-center gap-3">
        <a href="{{ route('medico.agenda', ['semana' => $semanaAnt]) }}"
           class="p-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition text-gray-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <p class="font-bold text-gray-800 text-sm">
                {{ $inicio->translatedFormat('d M') }} — {{ $fin->translatedFormat('d M Y') }}
            </p>
            <p class="text-xs text-gray-400">Semana {{ $inicio->weekOfYear }}</p>
        </div>
        <a href="{{ route('medico.agenda', ['semana' => $semanaSig]) }}"
           class="p-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition text-gray-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    <a href="{{ route('medico.agenda') }}"
       class="text-xs font-semibold text-blue-600 hover:underline bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100">
        Ir a esta semana
    </a>
</div>

{{-- ── Grid semanal ───────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3">
    @foreach ($dias as $dia)
    @php
        $key      = $dia->toDateString();
        $esHoy    = $key === $hoy;
        $citasDia = $citas->get($key, collect());
        $diasNom  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        $nomDia   = $diasNom[$dia->dayOfWeek];
    @endphp

    <div class="rounded-2xl border {{ $esHoy ? 'col-hoy border-sky-200' : 'border-gray-100 bg-white' }} shadow-sm flex flex-col overflow-hidden min-h-[160px]">

        {{-- Cabecera del día --}}
        <div class="px-3 py-3 border-b {{ $esHoy ? 'border-sky-200' : 'border-gray-100' }} flex items-center gap-2">
            <div class="day-num w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                        {{ $esHoy ? 'bg-sky-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                {{ $dia->day }}
            </div>
            <div>
                <p class="day-name text-xs font-bold uppercase tracking-wide {{ $esHoy ? 'text-sky-700' : 'text-gray-500' }}">
                    {{ $nomDia }}
                </p>
                @if ($esHoy)
                <p class="text-[10px] text-sky-500 font-medium leading-none">Hoy</p>
                @endif
            </div>
            @if ($citasDia->isNotEmpty())
            <span class="ml-auto text-[10px] font-bold bg-gray-100 text-gray-500 rounded-full px-2 py-0.5">
                {{ $citasDia->count() }}
            </span>
            @endif
        </div>

        {{-- Citas del día --}}
        <div class="flex-1 p-2 space-y-1.5 overflow-y-auto max-h-72">
            @forelse ($citasDia as $cita)
            @php
                $estadoNom = strtolower($cita->estado?->nombre ?? '');
                $claseEstado = match(true) {
                    str_contains($estadoNom, 'pendiente') => 'estado-pendiente',
                    str_contains($estadoNom, 'atendida') || str_contains($estadoNom, 'completada') => 'estado-atendida',
                    str_contains($estadoNom, 'cancelada') => 'estado-cancelada',
                    default => 'estado-default',
                };
                $esVirtual = str_contains(strtolower($cita->modalidad?->nombre ?? ''), 'tele');
            @endphp
            <a href="{{ route('medico.citas.atender', $cita) }}"
               class="block rounded-lg border px-2.5 py-2 {{ $claseEstado }} hover:opacity-80 transition text-left">
                <div class="flex items-center gap-1 mb-0.5">
                    <span class="text-[10px] font-bold">{{ \Carbon\Carbon::createFromFormat('H:i:s', $cita->hora)->format('g:i A') }}</span>
                    @if ($esVirtual)
                    <span class="ml-auto">
                        <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                        </svg>
                    </span>
                    @endif
                </div>
                <p class="text-[11px] font-semibold leading-tight truncate">
                    {{ $cita->paciente?->nombre_completo ?? '—' }}
                </p>
                @if ($cita->servicio?->nombre)
                <p class="text-[10px] opacity-70 truncate">{{ $cita->servicio->nombre }}</p>
                @endif
            </a>
            @empty
            <div class="flex items-center justify-center h-full py-6">
                <p class="text-[11px] text-gray-300 text-center">Sin citas</p>
            </div>
            @endforelse
        </div>

    </div>
    @endforeach
</div>

{{-- ── Leyenda ────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-4 mt-5 text-xs text-gray-500">
    <span class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-sm estado-pendiente border inline-block"></span> Pendiente
    </span>
    <span class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-sm estado-atendida border inline-block"></span> Atendida
    </span>
    <span class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-sm estado-cancelada border inline-block"></span> Cancelada
    </span>
    <span class="flex items-center gap-1.5">
        <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
        </svg> Telemedicina
    </span>
</div>

@endsection
