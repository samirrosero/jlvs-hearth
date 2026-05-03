@extends('gestor.layouts.app')

@section('title', 'Agenda semanal')
@section('page-title', 'Agenda semanal')

@section('content')
@php
    $hora   = now()->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
    $nombre = auth()->user()->nombre;

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

    {{-- ── Buscar pacientes ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4"
         x-data="checkIn()">

        <div class="flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-sm font-semibold text-gray-800">Buscar pacientes</h3>
            <span class="text-xs text-gray-400 ml-auto">Hoy &mdash; {{ now()->format('d/m/Y') }}</span>
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" x-model="cedula" @keydown.enter="buscar()"
                   placeholder="Número de cédula / documento"
                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <button type="button" @click="buscar()" :disabled="buscando || !cedula.trim()"
                    class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold text-sm px-4 py-2 rounded-lg transition-colors">
                <svg x-show="buscando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <svg x-show="!buscando" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span x-text="buscando ? 'Buscando...' : 'Buscar'"></span>
            </button>
        </div>

        {{-- Sin resultados --}}
        <div x-show="buscado && citas.length === 0" style="display:none"
             class="mt-3 text-sm text-gray-500 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            No se encontraron citas para hoy con ese documento.
        </div>

        {{-- Resultados --}}
        <div x-show="citas.length > 0" style="display:none" class="mt-3 space-y-2">
            <template x-for="cita in citas" :key="cita.id">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm leading-snug" x-text="cita.paciente?.nombre_completo ?? '\u2014'"></p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <span x-text="cita.servicio?.nombre ?? 'Sin servicio'"></span>
                            &bull;
                            <span class="tabular-nums font-medium" x-text="(cita.hora ?? '').slice(0,5)"></span>
                            <template x-if="cita.precio_sugerido">
                                <span class="ml-2 text-emerald-700 font-semibold">
                                    &bull; $<span x-text="formatearPrecio(cita.precio_sugerido)"></span>
                                </span>
                            </template>
                        </p>
                        <template x-if="cita.modalidad?.nombre === 'Telemedicina'">
                            <p class="text-xs text-purple-700 font-medium mt-0.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Telemedicina — Pago procesado desde la app del paciente
                            </p>
                        </template>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <span :class="{
                                'bg-amber-100 text-amber-700': cita.estado?.nombre === 'Pendiente',
                                'bg-blue-100 text-blue-700':   cita.estado?.nombre === 'Confirmada',
                                'bg-green-100 text-green-700': cita.estado?.nombre === 'Atendida',
                                'bg-red-100 text-red-600':     cita.estado?.nombre === 'Cancelada',
                                'bg-gray-100 text-gray-500':   cita.estado?.nombre === 'No asistió',
                              }"
                              class="text-xs font-semibold px-2.5 py-1 rounded-full"
                              x-text="cita.estado?.nombre ?? '\u2014'"></span>

                        {{-- Badge pago --}}
                        <template x-if="cita.pago_estado === 'pagado'">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/></svg>
                                Pagado
                            </span>
                        </template>

                        {{-- Paso 1: Confirmar llegada (solo citas presenciales pendientes) --}}
                        <button type="button"
                                x-show="cita.estado?.nombre === 'Pendiente' && cita.modalidad?.nombre !== 'Telemedicina'"
                                @click="confirmarLlegada(cita.id)"
                                :disabled="confirmando === cita.id"
                                class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold text-xs px-3 py-1.5 rounded-lg transition-colors">
                            <svg x-show="confirmando === cita.id" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <svg x-show="confirmando !== cita.id" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            1. Confirmar llegada
                        </button>

                        {{-- Paso 2: Cobrar (si está Confirmada y no está pagada) --}}
                        <a x-show="cita.estado?.nombre === 'Confirmada' && cita.pago_estado !== 'pagado' && cita.modalidad?.nombre !== 'Telemedicina'"
                           :href="'/gestor/recepcion/citas/' + cita.id + '/pago'"
                           class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3 py-1.5 rounded-lg transition-colors"
                           style="display:none">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            2. Cobrar
                        </a>

                        {{-- Ver detalle --}}
                        <a :href="'/gestor/citas/' + cita.id + '/editar'"
                           class="inline-flex items-center gap-1 text-xs text-gray-600 hover:text-blue-600 font-medium px-2 py-1.5 transition-colors">
                            Detalle →
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ── 6 Stats compactas ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-5 flex items-start gap-4">
            <div class="bg-blue-50 rounded-2xl p-3 shrink-0">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-3xl font-bold text-gray-900 leading-none">{{ $citasHoy }}</p>
                <p class="text-xs text-gray-500 mt-1">Citas hoy</p>
                <p class="text-xs font-semibold mt-2 {{ $citasHoyDiff >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $citasHoyDiff >= 0 ? '+' : '' }}{{ $citasHoyDiff }} vs ayer
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-5 flex items-start gap-4">
            <div class="bg-amber-50 rounded-2xl p-3 shrink-0">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-3xl font-bold text-gray-900 leading-none">{{ $citasPendientes }}</p>
                <p class="text-xs text-gray-500 mt-1">Pendientes</p>
                <p class="text-xs text-gray-500 mt-2 truncate">Revisa confirmaciones y llegadas</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-5 flex items-start gap-4">
            <div class="bg-emerald-50 rounded-2xl p-3 shrink-0">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900 leading-none">{{ $citasConfirmadasHoy }}</p>
                <p class="text-xs text-gray-500 mt-1">Confirmadas hoy</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-5 flex items-start gap-4">
            <div class="bg-sky-50 rounded-2xl p-3 shrink-0">
                <svg class="w-8 h-8 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7h18M7 7v13M17 7v13M5 20h14"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900 leading-none">{{ $totalPacientes }}</p>
                <p class="text-xs text-gray-500 mt-1">Pacientes</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-5 flex items-start gap-4">
            <div class="bg-purple-50 rounded-2xl p-3 shrink-0">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-bold text-gray-900 leading-none">{{ $totalMedicos }}</p>
                <p class="text-xs text-gray-500 mt-1">Médicos</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-4 flex flex-col justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="bg-cyan-50 rounded-2xl p-3 shrink-0">
                    <svg class="w-8 h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3v18h18M7 17V7m6 10V5m6 12V11"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-3xl font-bold text-gray-900 leading-none">{{ $citasSemanaTotal }}</p>
                    <p class="text-xs text-gray-500 mt-1">Citas esta semana</p>
                    <p class="text-xs font-semibold mt-2 {{ $citasSemanaDiff >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $citasSemanaDiff >= 0 ? '+' : '' }}{{ $citasSemanaDiff }} vs anterior
                    </p>
                </div>
            </div>
            <div class="space-y-2">
                @foreach ($citasSemanaDias as $dia)
                    <div class="flex items-center gap-2 text-[11px]">
                        <span class="w-10 text-gray-500">{{ $dia['label'] }}</span>
                        <div class="h-2 rounded-full bg-slate-100 flex-1 overflow-hidden">
                            <div class="h-full rounded-full bg-cyan-500" style="width: {{ $dia['percent'] }}%"></div>
                        </div>
                        <span class="w-6 text-right text-gray-700">{{ $dia['count'] }}</span>
                    </div>
                @endforeach
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
@push('scripts')
<script>
function checkIn() {
    return {
        cedula:      '',
        buscando:    false,
        citas:       [],
        buscado:     false,
        confirmando: null,
        confirmado:  null,

        async buscar() {
            if (!this.cedula.trim()) return;
            this.buscando = true;
            this.citas    = [];
            this.buscado  = false;
            this.confirmado = null;
            try {
                const res = await fetch('/gestor/citas/buscar-hoy?identificacion=' + encodeURIComponent(this.cedula), {
                    headers: { 'Accept': 'application/json' },
                });
                this.citas   = await res.json();
                this.buscado = true;
            } catch (e) {
                this.citas   = [];
                this.buscado = true;
            } finally {
                this.buscando = false;
            }
        },

        formatearPrecio(valor) {
            if (!valor) return '0';
            return new Intl.NumberFormat('es-CO').format(valor);
        },

        async confirmarLlegada(id) {
            this.confirmando = id;
            try {
                const res = await fetch('/gestor/citas/' + id + '/estado', {
                    method:  'PATCH',
                    headers: {
                        'Accept':       'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ estado_id: 2 }),
                });
                if (res.ok) {
                    this.confirmado = id;
                    this.citas = this.citas.map(c =>
                        c.id === id
                            ? { ...c, estado: { ...c.estado, nombre: 'Confirmada' } }
                            : c
                    );
                }
            } catch (e) {}
            finally {
                this.confirmando = null;
            }
        },
    };
}
</script>
@endpush

@endsection
