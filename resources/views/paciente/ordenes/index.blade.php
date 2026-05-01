@extends('paciente.layouts.app')

@section('title', 'Mis Órdenes Médicas')
@section('page-title', 'Mis Órdenes Médicas')

@section('content')

{{-- ── Resumen rápido ──────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @php
        $resumen = [
            ['label' => 'Pendientes',  'count' => $pendientes->count(),  'color' => 'amber',  'icon' => '⏳'],
            ['label' => 'Autorizadas', 'count' => $autorizadas->count(), 'color' => 'blue',   'icon' => '✅'],
            ['label' => 'Completadas', 'count' => $completadas->count(), 'color' => 'green',  'icon' => '🏁'],
            ['label' => 'Canceladas',  'count' => $canceladas->count(),  'color' => 'red',    'icon' => '❌'],
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

{{-- ── PENDIENTES ──────────────────────────────────────────────── --}}
@if ($pendientes->isNotEmpty())
<section class="mb-8">
    <h2 class="text-sm font-semibold text-amber-700 uppercase tracking-wider mb-3 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>
        Pendientes de autorización ({{ $pendientes->count() }})
    </h2>

    <div class="space-y-4">
        @foreach ($pendientes as $orden)
        @php
            $medico = $orden->historiaClinica?->ejecucionCita?->cita?->medico?->usuario?->nombre ?? 'Médico';
            $fecha  = $orden->created_at->format('d/m/Y');
        @endphp
        <div class="bg-white rounded-2xl border border-amber-200 shadow-sm overflow-hidden"
             x-data="{ abrirModal: false }">

            {{-- Cabecera --}}
            <div class="flex items-start justify-between px-6 py-4 border-b border-amber-100 bg-amber-50">
                <div>
                    <span class="inline-block text-xs font-semibold text-amber-700 bg-amber-100 rounded-full px-3 py-0.5 mb-1">
                        {{ $orden->tipo }}
                    </span>
                    <p class="font-semibold text-gray-800">{{ $orden->descripcion }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Emitida por Dr. {{ $medico }} · {{ $fecha }}</p>
                </div>
                <span class="flex-shrink-0 text-xs font-bold text-amber-600 bg-amber-100 border border-amber-200 rounded-full px-3 py-1">
                    PENDIENTE
                </span>
            </div>

            {{-- Instrucciones --}}
            @if ($orden->instrucciones)
            <div class="px-6 py-3 bg-white">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Instrucciones del médico</p>
                <p class="text-sm text-gray-700">{{ $orden->instrucciones }}</p>
            </div>
            @endif

            {{-- Acciones --}}
            <div class="px-6 py-4 bg-white flex flex-col sm:flex-row gap-3">
                <button @click="abrirModal = true"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold py-2.5 px-4 rounded-xl transition">
                    Autorizar esta orden
                </button>
            </div>

            {{-- Modal de autorización --}}
            <div x-show="abrirModal" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
                 x-transition:enter="transition duration-200" x-transition:leave="transition duration-150">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
                    <h3 class="text-base font-bold text-gray-800 mb-1">Autorizar orden médica</h3>
                    <p class="text-sm text-gray-500 mb-5">
                        <strong>{{ $orden->tipo }}</strong> · {{ $orden->descripcion }}
                    </p>

                    <form method="POST" action="{{ route('paciente.ordenes.autorizar', $orden) }}">
                        @csrf
                        @method('PATCH')

                        <p class="text-sm font-semibold text-gray-700 mb-3">¿Cómo vas a realizarla?</p>

                        <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-blue-200 bg-blue-50 cursor-pointer mb-3 hover:border-blue-400 transition">
                            <input type="radio" name="autorizado_via" value="virtual" required
                                   class="mt-0.5 accent-blue-600">
                            <div>
                                <p class="text-sm font-semibold text-blue-800">Autorizar en línea</p>
                                <p class="text-xs text-blue-600 mt-0.5">Confirmas que realizarás la orden. Luego coordina directamente con el laboratorio o centro de imágenes.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-gray-200 bg-gray-50 cursor-pointer mb-5 hover:border-gray-400 transition">
                            <input type="radio" name="autorizado_via" value="presencial"
                                   class="mt-0.5 accent-gray-600">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Autorizar presencialmente</p>
                                <p class="text-xs text-gray-500 mt-0.5">Preséntate en las instalaciones de la IPS con este número de orden. El gestor la registrará.</p>
                            </div>
                        </label>

                        <div class="flex gap-3">
                            <button type="button" @click="abrirModal = false"
                                    class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                                Confirmar autorización
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ── AUTORIZADAS ─────────────────────────────────────────────── --}}
@if ($autorizadas->isNotEmpty())
<section class="mb-8">
    <h2 class="text-sm font-semibold text-blue-700 uppercase tracking-wider mb-3 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>
        Autorizadas — en proceso ({{ $autorizadas->count() }})
    </h2>
    <div class="space-y-3">
        @foreach ($autorizadas as $orden)
        @php
            $medico = $orden->historiaClinica?->ejecucionCita?->cita?->medico?->usuario?->nombre ?? 'Médico';
            $via    = $orden->autorizado_via === 'virtual' ? 'En línea' : 'Presencial';
            $viaColor = $orden->autorizado_via === 'virtual' ? 'text-blue-600' : 'text-gray-600';
        @endphp
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm px-6 py-4 flex items-start justify-between gap-4">
            <div>
                <span class="inline-block text-xs font-semibold text-blue-700 bg-blue-50 rounded-full px-3 py-0.5 mb-1">
                    {{ $orden->tipo }}
                </span>
                <p class="font-semibold text-gray-800">{{ $orden->descripcion }}</p>
                <p class="text-xs text-gray-400 mt-1">
                    Dr. {{ $medico }} ·
                    Autorizada el {{ $orden->autorizado_en?->format('d/m/Y') }} ·
                    <span class="{{ $viaColor }} font-medium">{{ $via }}</span>
                </p>
            </div>
            <span class="flex-shrink-0 text-xs font-bold text-blue-600 bg-blue-100 border border-blue-200 rounded-full px-3 py-1">
                AUTORIZADA
            </span>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ── COMPLETADAS ─────────────────────────────────────────────── --}}
@if ($completadas->isNotEmpty())
<section class="mb-8">
    <h2 class="text-sm font-semibold text-green-700 uppercase tracking-wider mb-3 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>
        Completadas ({{ $completadas->count() }})
    </h2>
    <div class="space-y-3">
        @foreach ($completadas as $orden)
        @php $medico = $orden->historiaClinica?->ejecucionCita?->cita?->medico?->usuario?->nombre ?? 'Médico'; @endphp
        <div class="bg-white rounded-2xl border border-green-100 shadow-sm px-6 py-4 flex items-start justify-between gap-4 opacity-80">
            <div>
                <span class="inline-block text-xs font-semibold text-green-700 bg-green-50 rounded-full px-3 py-0.5 mb-1">
                    {{ $orden->tipo }}
                </span>
                <p class="font-semibold text-gray-800">{{ $orden->descripcion }}</p>
                <p class="text-xs text-gray-400 mt-1">Dr. {{ $medico }} · {{ $orden->created_at->format('d/m/Y') }}</p>
            </div>
            <span class="flex-shrink-0 text-xs font-bold text-green-600 bg-green-100 border border-green-200 rounded-full px-3 py-1">
                COMPLETADA
            </span>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ── CANCELADAS ──────────────────────────────────────────────── --}}
@if ($canceladas->isNotEmpty())
<section class="mb-8">
    <h2 class="text-sm font-semibold text-red-700 uppercase tracking-wider mb-3 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>
        Canceladas ({{ $canceladas->count() }})
    </h2>
    <div class="space-y-3">
        @foreach ($canceladas as $orden)
        @php $medico = $orden->historiaClinica?->ejecucionCita?->cita?->medico?->usuario?->nombre ?? 'Médico'; @endphp
        <div class="bg-white rounded-2xl border border-red-100 shadow-sm px-6 py-4 flex items-start justify-between gap-4 opacity-60">
            <div>
                <span class="inline-block text-xs font-semibold text-red-700 bg-red-50 rounded-full px-3 py-0.5 mb-1">
                    {{ $orden->tipo }}
                </span>
                <p class="font-semibold text-gray-800">{{ $orden->descripcion }}</p>
                <p class="text-xs text-gray-400 mt-1">Dr. {{ $medico }} · {{ $orden->created_at->format('d/m/Y') }}</p>
            </div>
            <span class="flex-shrink-0 text-xs font-bold text-red-500 bg-red-50 border border-red-200 rounded-full px-3 py-1">
                CANCELADA
            </span>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- Estado vacío --}}
@if ($pendientes->isEmpty() && $autorizadas->isEmpty() && $completadas->isEmpty() && $canceladas->isEmpty())
<div class="flex flex-col items-center justify-center py-20 text-center">
    <div class="text-5xl mb-4">📋</div>
    <h3 class="text-lg font-semibold text-gray-700 mb-1">Sin órdenes médicas</h3>
    <p class="text-sm text-gray-400 max-w-xs">
        Cuando un médico emita una orden médica durante tu consulta, aparecerá aquí para que puedas autorizarla.
    </p>
</div>
@endif

@endsection
