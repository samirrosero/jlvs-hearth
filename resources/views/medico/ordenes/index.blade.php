@extends('medico.layouts.app')

@section('title', 'Órdenes Emitidas')
@section('page-title', 'Órdenes Emitidas')

@section('content')

{{-- Resumen --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @php
        $resumen = [
            ['label' => 'Pendientes',  'count' => $pendientes->count(),  'icon' => '⏳'],
            ['label' => 'Autorizadas', 'count' => $autorizadas->count(), 'icon' => '✅'],
            ['label' => 'Completadas', 'count' => $completadas->count(), 'icon' => '🏁'],
            ['label' => 'Canceladas',  'count' => $canceladas->count(),  'icon' => '❌'],
        ];
    @endphp
    @foreach ($resumen as $r)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-3">
        <span class="text-2xl">{{ $r['icon'] }}</span>
        <div>
            <p class="text-xl font-bold text-gray-800">{{ $r['count'] }}</p>
            <p class="text-xs text-gray-500">{{ $r['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

@php
    $secciones = [
        ['titulo' => 'Pendientes de autorización', 'ordenes' => $pendientes,  'color' => 'amber', 'badge' => 'PENDIENTE'],
        ['titulo' => 'Autorizadas',                'ordenes' => $autorizadas, 'color' => 'blue',  'badge' => 'AUTORIZADA'],
        ['titulo' => 'Completadas',                'ordenes' => $completadas, 'color' => 'green', 'badge' => 'COMPLETADA'],
        ['titulo' => 'Canceladas',                 'ordenes' => $canceladas,  'color' => 'red',   'badge' => 'CANCELADA'],
    ];
    $dotColors  = ['amber' => 'bg-amber-400', 'blue' => 'bg-blue-400', 'green' => 'bg-green-400', 'red' => 'bg-red-400'];
    $badgeColors = [
        'amber' => 'text-amber-600 bg-amber-100 border-amber-200',
        'blue'  => 'text-blue-600 bg-blue-100 border-blue-200',
        'green' => 'text-green-600 bg-green-100 border-green-200',
        'red'   => 'text-red-500 bg-red-50 border-red-200',
    ];
    $borderColors = ['amber' => 'border-amber-200', 'blue' => 'border-blue-100', 'green' => 'border-green-100', 'red' => 'border-red-100'];
@endphp

@foreach ($secciones as $sec)
    @if ($sec['ordenes']->isNotEmpty())
    <section class="mb-8">
        <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-3 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full {{ $dotColors[$sec['color']] }} inline-block"></span>
            {{ $sec['titulo'] }} ({{ $sec['ordenes']->count() }})
        </h2>
        <div class="space-y-3">
            @foreach ($sec['ordenes'] as $orden)
            @php
                $pacienteNombre = $orden->paciente?->nombre_completo ?? '—';
                $servicio = $orden->historiaClinica?->ejecucionCita?->cita?->servicio?->nombre ?? '—';
            @endphp
            <div class="bg-white rounded-2xl border {{ $borderColors[$sec['color']] }} shadow-sm px-6 py-4 flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block text-xs font-semibold text-gray-600 bg-gray-100 rounded-full px-3 py-0.5">
                            {{ $orden->tipo }}
                        </span>
                    </div>
                    <p class="font-semibold text-gray-800 truncate">{{ $orden->descripcion }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        Paciente: <span class="font-medium text-gray-600">{{ $pacienteNombre }}</span>
                        · {{ $orden->created_at->format('d/m/Y') }}
                    </p>
                    @if ($orden->instrucciones)
                    <p class="text-xs text-gray-500 mt-1 italic">"{{ Str::limit($orden->instrucciones, 80) }}"</p>
                    @endif
                </div>
                <span class="flex-shrink-0 text-xs font-bold border rounded-full px-3 py-1 {{ $badgeColors[$sec['color']] }}">
                    {{ $sec['badge'] }}
                </span>
            </div>
            @endforeach
        </div>
    </section>
    @endif
@endforeach

{{-- Estado vacío --}}
@if ($pendientes->isEmpty() && $autorizadas->isEmpty() && $completadas->isEmpty() && $canceladas->isEmpty())
<div class="flex flex-col items-center justify-center py-20 text-center">
    <div class="text-5xl mb-4">📋</div>
    <h3 class="text-lg font-semibold text-gray-700 mb-1">Sin órdenes emitidas</h3>
    <p class="text-sm text-gray-400 max-w-xs">
        Las órdenes médicas que generes durante una consulta aparecerán aquí.
    </p>
</div>
@endif

@endsection
