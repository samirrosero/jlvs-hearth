@extends('medico.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Mi Dashboard')

@section('content')

{{-- ── Saludo + fecha ───────────────────────────────────────────────── --}}
@php
    $hora  = now()->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
    $nombreMedico = auth()->user()->nombre ?? 'Doctor';

    // Detectar la cita activa ahora mismo o la siguiente pendiente
    $ahoraStr  = now()->format('H:i:s');
    $citaActual = $agendaHoy->first(fn ($c) =>
        $c->ejecucion && $c->ejecucion->inicio_atencion && !$c->ejecucion->fin_atencion
    );
    $siguienteCita = !$citaActual
        ? $agendaHoy->first(fn ($c) => $c->hora >= $ahoraStr && in_array($c->estado_id, [1, 2]))
        : null;
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-gray-800">{{ $saludo }}, {{ $nombreMedico }}</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
    </div>
    <div class="text-right">
        <p class="text-2xl font-bold text-blue-700">{{ $citasHoy }}</p>
        <p class="text-xs text-gray-500">citas hoy</p>
    </div>
</div>

{{-- ── Buscador de pacientes ────────────────────────────────────────── --}}
<form method="GET" action="{{ route('medico.pacientes') }}" class="mb-6">
    <div class="flex items-center gap-2 bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-3">
        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="buscar" placeholder="Buscar paciente por nombre..."
               class="flex-1 bg-transparent border-none outline-none text-sm text-gray-700 placeholder-gray-400">
        <button type="submit" class="text-sm bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
            Buscar
        </button>
    </div>
</form>

{{-- ── AGENDA DE HOY (hero) ─────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
            Agenda de hoy
        </h3>
        @if ($citaActual)
            <span class="text-xs bg-green-100 text-green-700 font-medium px-3 py-1 rounded-full animate-pulse">
                En consulta ahora
            </span>
        @elseif ($siguienteCita)
            <span class="text-xs bg-blue-50 text-blue-700 font-medium px-3 py-1 rounded-full">
                Próxima: {{ \Carbon\Carbon::parse($siguienteCita->hora)->format('H:i') }}
            </span>
        @endif
    </div>

    @if ($agendaHoy->isEmpty())
        <div class="py-16 text-center">
            <p class="text-4xl mb-3">📅</p>
            <p class="text-gray-500 font-medium">No tienes citas programadas para hoy</p>
            <p class="text-sm text-gray-400 mt-1">Disfruta tu día libre</p>
        </div>
    @else
        <div class="divide-y divide-gray-50">
            @foreach ($agendaHoy as $cita)
                @php
                    $esActual    = $citaActual && $citaActual->id === $cita->id;
                    $esSiguiente = $siguienteCita && $siguienteCita->id === $cita->id;
                    $yaAtendida  = in_array($cita->estado_id, [3, 4, 5]); // Atendida, Cancelada, No asistió
                    $colorEstado = $cita->estado->color_hex ?? '#94a3b8';
                @endphp
                <div class="flex items-center gap-4 px-5 py-4
                    {{ $esActual    ? 'bg-green-50 border-l-4 border-green-400' : '' }}
                    {{ $esSiguiente ? 'bg-blue-50  border-l-4 border-blue-400'  : '' }}
                    {{ $yaAtendida  ? 'opacity-50' : '' }}">

                    {{-- Hora --}}
                    <div class="w-14 flex-shrink-0 text-center">
                        <p class="text-lg font-bold {{ $esActual ? 'text-green-700' : 'text-blue-700' }}">
                            {{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}
                        </p>
                    </div>

                    {{-- Paciente + servicio --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 truncate">
                            {{ $cita->paciente->nombre_completo ?? '—' }}
                        </p>
                        <p class="text-sm text-gray-500 truncate">
                            {{ $cita->servicio->nombre ?? 'Sin servicio' }}
                            @if ($cita->modalidad)
                                · <span class="text-gray-400">{{ $cita->modalidad->nombre }}</span>
                            @endif
                        </p>
                    </div>

                    {{-- Estado --}}
                    <span class="hidden sm:inline-flex text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0"
                          style="background:{{ $colorEstado }}22; color:{{ $colorEstado }}">
                        {{ $cita->estado->nombre ?? '—' }}
                    </span>

                    {{-- Acción --}}
                    <div class="flex-shrink-0">
                        @if ($yaAtendida)
                            <span class="text-xs text-gray-400">Finalizada</span>
                        @else
                            <a href="{{ route('medico.citas.atender', $cita->id) }}"
                               class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg transition
                                   {{ $esActual
                                       ? 'bg-green-600 text-white hover:bg-green-700'
                                       : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                                {{ $esActual ? 'Continuar' : 'Atender' }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ── Métricas rápidas ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $cards = [
            ['label' => 'Citas este mes',   'value' => $citasMes,        'color' => 'violet'],
            ['label' => 'Pendientes',        'value' => $citasPendientes, 'color' => 'amber'],
            ['label' => 'Mis pacientes',     'value' => $totalPacientes,  'color' => 'emerald'],
            ['label' => 'Mi valoración',     'value' => $totalValoraciones > 0 ? number_format($promedioValoraciones, 1).' ★' : '—', 'color' => 'blue'],
        ];
        $colorMap = [
            'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'dot' => 'bg-blue-500'],
            'violet'  => ['bg' => 'bg-violet-50',  'text' => 'text-violet-700',  'dot' => 'bg-violet-500'],
            'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
            'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'dot' => 'bg-amber-500'],
        ];
    @endphp

    @foreach ($cards as $card)
        @php $c = $colorMap[$card['color']]; @endphp
        <div class="{{ $c['bg'] }} rounded-xl border border-white p-4">
            <p class="text-2xl font-bold {{ $c['text'] }}">{{ $card['value'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</p>
        </div>
    @endforeach
</div>

{{-- ── Próximas citas (días futuros) + Gráficas ───────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Próximas citas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4 text-sm">Próximos días</h3>
        <div class="space-y-3">
            @forelse ($proximasCitas as $cita)
                <div class="flex items-center gap-3 text-sm">
                    <div class="flex-shrink-0 w-12 text-center">
                        <p class="font-bold text-blue-700 leading-none">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m') }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 truncate">{{ $cita->paciente->nombre_completo ?? '—' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $cita->servicio->nombre ?? 'Sin servicio' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Sin citas próximas</p>
            @endforelse
        </div>
    </div>

    {{-- Gráfica citas por mes --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4 text-sm">Citas por mes (últimos 6 meses)</h3>
        <canvas id="chartCitasMes" height="110"></canvas>
    </div>

</div>

{{-- ── Estado de citas + Especialidad ────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Especialidad / registro --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col justify-center">
        <p class="text-xs text-gray-400 mb-1">Especialidad</p>
        <p class="text-lg font-semibold text-gray-800">{{ $medico->especialidad ?? '—' }}</p>
        <p class="text-xs text-gray-400 mt-2">Reg. médico</p>
        <p class="text-sm font-medium text-gray-600">{{ $medico->registro_medico ?? '—' }}</p>
    </div>

    {{-- Citas por estado --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4 text-sm">Citas por estado (histórico)</h3>
        @if ($citasPorEstado->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Sin citas registradas aún.</p>
        @else
            <div class="flex gap-6 items-center">
                <canvas id="chartEstados" class="w-32 h-32 flex-shrink-0" style="max-width:128px;max-height:128px"></canvas>
                <div class="flex-1 space-y-2">
                    @foreach ($citasPorEstado as $e)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $e->color ?? '#94a3b8' }}"></span>
                                <span class="text-gray-600">{{ $e->estado }}</span>
                            </div>
                            <span class="font-semibold text-gray-800">{{ $e->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
    const datosMes  = @json($citasPorMes);
    const labelsMes = datosMes.map(d => d.mes);
    const valsMes   = datosMes.map(d => d.total);

    new Chart(document.getElementById('chartCitasMes'), {
        type: 'bar',
        data: {
            labels: labelsMes,
            datasets: [{
                label: 'Citas',
                data: valsMes,
                backgroundColor: '#0369a1',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    @if ($citasPorEstado->isNotEmpty())
    const datosEstado = @json($citasPorEstado);
    new Chart(document.getElementById('chartEstados'), {
        type: 'doughnut',
        data: {
            labels: datosEstado.map(e => e.estado),
            datasets: [{
                data: datosEstado.map(e => e.total),
                backgroundColor: datosEstado.map(e => e.color ?? '#94a3b8'),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } }
            },
            cutout: '65%'
        }
    });
    @endif
</script>
@endpush
