@extends('gestor.layouts.app')

@section('title', 'Agenda semanal')
@section('page-title', 'Agenda semanal')

@section('content')
@php
    $hora   = now()->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
    $nombre = auth()->user()->name ?? 'Gestor';

    // Colores por modalidad — normalizados a key
    function modKey(string $nombre): string {
        $n = strtolower($nombre);
        if (str_contains($n, 'tele') || str_contains($n, 'virtual')) return 'virtual';
        if (str_contains($n, 'domicil'))                               return 'domiciliaria';
        return 'presencial';
    }

    $estadoBadge = [
        'Pendiente'  => 'bg-amber-100 text-amber-700',
        'Confirmada' => 'bg-blue-100  text-blue-700',
        'Atendida'   => 'bg-green-100 text-green-700',
        'Cancelada'  => 'bg-red-100   text-red-600',
        'No asistió' => 'bg-gray-100  text-gray-500',
    ];
@endphp

<div class="space-y-5" x-data="{ filtro: 'todos' }">

    {{-- ── Alertas de sesión ── --}}
    @if(session('success') || session('exito'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm font-medium px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') ?? session('exito') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-800 text-sm font-medium px-4 py-3 rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Encabezado ── --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $saludo }}, {{ $nombre }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
        </div>
        <a href="{{ route('gestor.citas.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva cita
        </a>
    </div>

    {{-- ── 4 Stats compactas ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 flex items-center gap-3">
            <div class="bg-blue-50 rounded-lg p-2.5 shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $citasHoy }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Citas hoy</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 flex items-center gap-3">
            <div class="bg-amber-50 rounded-lg p-2.5 shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $citasPendientes }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Pendientes</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 flex items-center gap-3">
            <div class="bg-emerald-50 rounded-lg p-2.5 shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $totalPacientes }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Pacientes</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 flex items-center gap-3">
            <div class="bg-purple-50 rounded-lg p-2.5 shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 leading-none">{{ $totalMedicos }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Médicos</p>
            </div>
        </div>

    </div>

    {{-- ── Agenda semanal ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Cabecera: navegación de semana + filtros de modalidad --}}
        <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">

            {{-- Navegación semana --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('gestor.dashboard', ['semana' => $semanaPrev]) }}"
                   class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="text-sm font-semibold text-gray-800 min-w-[180px] text-center">
                    {{ $semanaLabel }}
                </span>
                <a href="{{ route('gestor.dashboard', ['semana' => $semanaNext]) }}"
                   class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="{{ route('gestor.dashboard') }}"
                   class="ml-1 text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                    Hoy
                </a>
            </div>

            {{-- Leyenda de modalidad + filtro --}}
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button"
                        @click="filtro = 'todos'"
                        :class="filtro === 'todos' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                    Todos
                </button>
                <button type="button"
                        @click="filtro = 'presencial'"
                        :class="filtro === 'presencial' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-600 hover:bg-blue-100'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                    <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                    Presencial
                </button>
                <button type="button"
                        @click="filtro = 'virtual'"
                        :class="filtro === 'virtual' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-600 hover:bg-purple-100'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                    <span class="w-2 h-2 rounded-full bg-purple-500 inline-block"></span>
                    Virtual
                </button>
                <button type="button"
                        @click="filtro = 'domiciliaria'"
                        :class="filtro === 'domiciliaria' ? 'bg-teal-600 text-white' : 'bg-teal-50 text-teal-600 hover:bg-teal-100'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                    <span class="w-2 h-2 rounded-full bg-teal-500 inline-block"></span>
                    Domiciliaria
                </button>
            </div>
        </div>

        {{-- Grid semanal (scroll horizontal en móvil) --}}
        <div class="overflow-x-auto">
            <div style="min-width: 700px">

                {{-- Headers de días --}}
                <div class="grid grid-cols-7 border-b border-gray-100">
                    @foreach($diasSemana as $dia)
                        <div class="px-2 py-3 text-center border-r border-gray-100 last:border-r-0
                                    {{ $dia['es_hoy'] ? 'bg-blue-50' : 'bg-gray-50' }}">
                            <p class="text-xs font-semibold uppercase tracking-wider
                                      {{ $dia['es_hoy'] ? 'text-blue-600' : 'text-gray-400' }}">
                                {{ $dia['nombre'] }}
                            </p>
                            <div class="mt-1 inline-flex items-center justify-center w-8 h-8 rounded-full
                                        {{ $dia['es_hoy'] ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                                <span class="text-sm font-bold">{{ $dia['numero'] }}</span>
                            </div>
                            @if($dia['citas']->isNotEmpty())
                                <p class="text-xs mt-0.5 {{ $dia['es_hoy'] ? 'text-blue-500' : 'text-gray-400' }}">
                                    {{ $dia['citas']->count() }} cita{{ $dia['citas']->count() > 1 ? 's' : '' }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Columnas de citas por día --}}
                <div class="grid grid-cols-7">
                    @foreach($diasSemana as $dia)
                        <div class="border-r border-gray-100 last:border-r-0 p-2 min-h-[220px]
                                    {{ $dia['es_hoy'] ? 'bg-blue-50/30' : '' }}">

                            {{-- Citas del día --}}
                            @if($dia['citas']->isNotEmpty())
                                <div class="flex flex-col gap-1.5">
                                    @foreach($dia['citas'] as $cita)
                                        @php
                                            $modNombre = $cita->modalidad?->nombre ?? 'Presencial';
                                            $mk = modKey($modNombre);
                                            $borderColor = match($mk) {
                                                'virtual'      => 'border-purple-400',
                                                'domiciliaria' => 'border-teal-400',
                                                default        => 'border-blue-400',
                                            };
                                            $bgCard = match($mk) {
                                                'virtual'      => 'bg-purple-50 hover:bg-purple-100',
                                                'domiciliaria' => 'bg-teal-50 hover:bg-teal-100',
                                                default        => 'bg-blue-50 hover:bg-blue-100',
                                            };
                                            $eBadge = $estadoBadge[$cita->estado?->nombre ?? ''] ?? 'bg-gray-100 text-gray-500';
                                        @endphp
                                        <a href="{{ route('gestor.citas.edit', $cita) }}"
                                           class="block border-l-2 {{ $borderColor }} {{ $bgCard }} rounded-r-lg px-2 py-1.5 transition-colors"
                                           x-show="filtro === 'todos' || filtro === '{{ $mk }}'">
                                            <p class="text-xs font-bold text-gray-800 tabular-nums leading-none">
                                                {{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}
                                            </p>
                                            <p class="text-xs text-gray-700 truncate mt-0.5 leading-tight">
                                                {{ $cita->paciente?->nombre_completo ?? '—' }}
                                            </p>
                                            @if($cita->servicio)
                                                <p class="text-xs text-gray-500 truncate leading-tight">
                                                    {{ $cita->servicio->nombre }}
                                                </p>
                                            @endif
                                            <span class="inline-block mt-1 text-xs px-1.5 py-0.5 rounded-full font-semibold leading-none {{ $eBadge }}">
                                                {{ $cita->estado?->nombre ?? '—' }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                {{-- Día vacío — link rápido para agendar --}}
                                <a href="{{ route('gestor.citas.create') }}?fecha={{ $dia['fecha'] }}"
                                   class="flex items-center justify-center w-full h-full min-h-[60px] text-gray-300 hover:text-blue-400 hover:bg-blue-50/50 rounded-lg transition-colors group">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- Leyenda de colores --}}
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex items-center gap-4 flex-wrap">
            <span class="text-xs text-gray-400 font-medium">Modalidad:</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-600">
                <span class="w-3 h-3 rounded-sm bg-blue-400 inline-block"></span> Presencial
            </span>
            <span class="flex items-center gap-1.5 text-xs text-gray-600">
                <span class="w-3 h-3 rounded-sm bg-purple-400 inline-block"></span> Virtual / Telemedicina
            </span>
            <span class="flex items-center gap-1.5 text-xs text-gray-600">
                <span class="w-3 h-3 rounded-sm bg-teal-400 inline-block"></span> Domiciliaria
            </span>
            <span class="ml-auto text-xs text-gray-400">
                Haz clic en una cita para editarla
            </span>
        </div>

    </div>

</div>
@endsection
