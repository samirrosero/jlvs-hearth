@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

@php
    $empresa = auth()->user()?->empresa;
@endphp

{{-- ── Tarjetas de métricas ─────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    @php
        $cards = [
            ['label' => 'Pacientes',        'value' => $totalPacientes, 'icon' => $empresa?->icono_card_pacientes_url ?? asset('img/icons/pacientes.png'),  'color' => 'blue'],
            ['label' => 'Médicos',           'value' => $totalMedicos,   'icon' => $empresa?->icono_card_medicos_url ?? asset('img/icons/medicos.png'),    'color' => 'emerald'],
            ['label' => 'Citas este mes',    'value' => $citasMes,       'icon' => $empresa?->icono_card_citas_url ?? asset('img/icons/citas-mes.png'), 'color' => 'violet'],
            ['label' => 'Total citas',       'value' => $totalCitas,     'icon' => $empresa?->icono_card_total_url ?? asset('img/icons/citas-total.png'), 'color' => 'amber'],
        ];
        $colorMap = [
            'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'icon' => 'bg-blue-100'],
            'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'icon' => 'bg-emerald-100'],
            'violet'  => ['bg' => 'bg-violet-50',  'text' => 'text-violet-700',  'icon' => 'bg-violet-100'],
            'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'icon' => 'bg-amber-100'],
        ];
    @endphp

    @foreach ($cards as $card)
        @php $c = $colorMap[$card['color']]; @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="{{ $c['icon'] }} rounded-xl p-3">
                <img src="{{ $card['icon'] }}" alt="{{ $card['label'] }}" class="w-8 h-8 flex-shrink-0">
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($card['value']) }}</p>
                <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
            </div>
        </div>
    @endforeach

</div>

{{-- ── Fila: Gráfica citas por mes + Estado de citas ────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Citas por mes --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Citas por mes (últimos 6 meses)</h3>
        <canvas id="chartCitasMes" height="90"></canvas>
    </div>

    {{-- Estado de citas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Citas por estado</h3>
        <canvas id="chartEstados" height="180"></canvas>
        <div class="mt-4 space-y-2">
            @foreach ($citasPorEstado as $e)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $e->color ?? '#94a3b8' }}"></span>
                        <span class="text-gray-600">{{ $e->estado }}</span>
                    </div>
                    <span class="font-semibold text-gray-800">{{ $e->total }}</span>
                </div>
            @endforeach
        </div>
    </div>

</div>

{{-- ── Fila: Médicos top + Métricas extras + Próximas citas ──────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Médicos top --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Top médicos por citas</h3>
        <div class="space-y-3">
            @forelse ($medicoTop as $i => $m)
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center flex-shrink-0">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $m->medico }}</p>
                        <p class="text-xs text-gray-500">{{ $m->especialidad }}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-700">{{ $m->total_citas }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400">Sin datos aún.</p>
            @endforelse
        </div>
    </div>

    {{-- Métricas: duración + valoraciones --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500 mb-1">Duración promedio de consulta</p>
            <p class="text-3xl font-bold text-gray-800">
                {{ number_format($duracionPromedio ?? 0, 1) }}
                <span class="text-lg font-normal text-gray-400">min</span>
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500 mb-1">Valoración promedio</p>
            <div class="flex items-end gap-3">
                <p class="text-3xl font-bold text-gray-800">
                    {{ number_format($promedioValoraciones ?? 0, 1) }}
                    <span class="text-lg font-normal text-gray-400">/ 5</span>
                </p>
                <p class="text-sm text-gray-400 mb-1">{{ $totalValoraciones }} valoraciones</p>
            </div>
            @php $estrellas = round($promedioValoraciones ?? 0); @endphp
            <div class="flex gap-0.5 mt-1">
                @for ($s = 1; $s <= 5; $s++)
                    <span class="text-lg {{ $s <= $estrellas ? 'text-amber-400' : 'text-gray-200' }}">★</span>
                @endfor
            </div>
        </div>
    </div>

    {{-- Próximas citas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Próximas citas</h3>
        <div class="space-y-3 overflow-y-auto max-h-64">
            @forelse ($proximasCitas as $cita)
                <div class="flex items-start gap-3 text-sm border-b border-gray-50 pb-3 last:border-0">
                    <div class="flex-shrink-0 w-12 text-center">
                        <p class="font-bold text-blue-700">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m') }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 truncate">{{ $cita->paciente->nombre_completo }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $cita->medico->usuario->nombre ?? '—' }}</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0"
                          style="background:{{ ($cita->estado->color_hex ?? '#e2e8f0') }}22; color:{{ $cita->estado->color_hex ?? '#64748b' }}">
                        {{ $cita->estado->nombre }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400">No hay citas próximas.</p>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    // ── Gráfica citas por mes ─────────────────────────────────────
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
                backgroundColor: '#3b82f6',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    // ── Gráfica estados de citas ──────────────────────────────────
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
</script>
@endpush
