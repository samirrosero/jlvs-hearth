@extends('medico.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Mi Dashboard')

@section('content')

{{-- ── Tarjetas de métricas ─────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $cards = [
            ['label' => 'Citas hoy',          'value' => $citasHoy,        'color' => 'blue'],
            ['label' => 'Citas este mes',      'value' => $citasMes,        'color' => 'violet'],
            ['label' => 'Mis pacientes',       'value' => $totalPacientes,  'color' => 'emerald'],
            ['label' => 'Citas pendientes',    'value' => $citasPendientes, 'color' => 'amber'],
        ];
        $colorMap = [
            'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'icon' => 'bg-blue-100',    'dot' => 'bg-blue-500'],
            'violet'  => ['bg' => 'bg-violet-50',  'text' => 'text-violet-700',  'icon' => 'bg-violet-100',  'dot' => 'bg-violet-500'],
            'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'icon' => 'bg-emerald-100', 'dot' => 'bg-emerald-500'],
            'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'icon' => 'bg-amber-100',   'dot' => 'bg-amber-500'],
        ];
    @endphp

    @foreach ($cards as $card)
        @php $c = $colorMap[$card['color']]; @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="{{ $c['icon'] }} rounded-xl p-3 flex items-center justify-center">
                <span class="w-4 h-4 rounded-full {{ $c['dot'] }} inline-block"></span>
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
        <h3 class="font-semibold text-gray-700 mb-4">Mis citas por mes (últimos 6 meses)</h3>
        <canvas id="chartCitasMes" height="90"></canvas>
    </div>

    {{-- Estado de citas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Citas por estado</h3>
        @if ($citasPorEstado->isEmpty())
            <p class="text-sm text-gray-400 text-center mt-8">Sin citas registradas aún.</p>
        @else
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
        @endif
    </div>

</div>

{{-- ── Fila: Valoración + Próximas citas ──────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Valoración propia --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500 mb-1">Mi valoración promedio</p>
            @if ($totalValoraciones > 0)
                <div class="flex items-end gap-3">
                    <p class="text-3xl font-bold text-gray-800">
                        {{ number_format($promedioValoraciones, 1) }}
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
            @else
                <p class="text-2xl font-bold text-gray-300">—</p>
                <p class="text-xs text-gray-400 mt-1">Sin valoraciones aún</p>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500 mb-1">Especialidad</p>
            <p class="text-lg font-semibold text-gray-800">{{ $medico->especialidad ?? '—' }}</p>
            <p class="text-xs text-gray-400 mt-1">Reg. {{ $medico->registro_medico ?? '—' }}</p>
        </div>
    </div>

    {{-- Próximas citas --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Próximas citas</h3>
        <div class="space-y-3 overflow-y-auto max-h-72">
            @forelse ($proximasCitas as $cita)
                <div class="flex items-start gap-3 text-sm border-b border-gray-50 pb-3 last:border-0">
                    <div class="flex-shrink-0 w-14 text-center">
                        <p class="font-bold text-blue-700">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m') }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 truncate">{{ $cita->paciente->nombre_completo ?? '—' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $cita->servicio->nombre ?? 'Sin servicio' }}</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0 whitespace-nowrap"
                          style="background:{{ ($cita->estado->color_hex ?? '#e2e8f0') }}22; color:{{ $cita->estado->color_hex ?? '#64748b' }}">
                        {{ $cita->estado->nombre }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400">No tienes citas próximas.</p>
            @endforelse
        </div>
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

