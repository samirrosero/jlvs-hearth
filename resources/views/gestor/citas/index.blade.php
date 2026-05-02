@extends('gestor.layouts.app')

@section('title', 'Citas')
@section('page-title', 'Citas')

@section('content')
@php
    $estadoBadge = [
        'Pendiente'  => 'bg-amber-100 text-amber-700',
        'Confirmada' => 'bg-blue-100 text-blue-700',
        'Atendida'   => 'bg-green-100 text-green-700',
        'Cancelada'  => 'bg-red-100 text-red-600',
        'No asistió' => 'bg-gray-100 text-gray-500',
    ];
@endphp

<div class="space-y-5">

    {{-- ── Encabezado ── --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h2 class="text-xl font-bold text-gray-900">Listado de citas</h2>
        <a href="{{ route('gestor.citas.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva cita
        </a>
    </div>

    {{-- ── Filtros ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
        <form method="GET" action="{{ route('gestor.citas') }}" class="flex flex-wrap items-end gap-3">

            <div class="flex flex-col gap-1 min-w-[140px]">
                <label for="filtro_fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                <input type="date" id="filtro_fecha" name="fecha"
                       value="{{ request('fecha') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div class="flex flex-col gap-1 min-w-[180px]">
                <label for="filtro_medico" class="block text-sm font-medium text-gray-700 mb-1">Médico</label>
                <select id="filtro_medico" name="medico_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Todos los médicos</option>
                    @foreach($medicos as $medico)
                        <option value="{{ $medico->id }}"
                            {{ request('medico_id') == $medico->id ? 'selected' : '' }}>
                            {{ $medico->usuario->name ?? $medico->usuario->nombre ?? '—' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1 min-w-[160px]">
                <label for="filtro_estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select id="filtro_estado" name="estado_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado->id }}"
                            {{ request('estado_id') == $estado->id ? 'selected' : '' }}>
                            {{ $estado->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2 pb-0">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('gestor.citas') }}"
                   class="text-sm text-gray-600 hover:text-gray-800 font-medium px-3 py-2.5">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- ── Tabla ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        @if($citas->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm font-medium text-gray-500">No se encontraron citas</p>
                <p class="text-xs text-gray-400 mt-1">Intenta cambiar los filtros o
                    <a href="{{ route('gestor.citas.create') }}" class="text-blue-600 hover:underline">crea una nueva cita</a>.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Hora</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Paciente</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Médico</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Servicio</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Modalidad</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($citas as $cita)
                            @php
                                $nombreEstado = $cita->estado?->nombre ?? '';
                                $badge = $estadoBadge[$nombreEstado] ?? 'bg-gray-100 text-gray-500';
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 text-gray-800 font-medium whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-700 tabular-nums whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="font-medium text-gray-800 leading-snug">
                                        {{ $cita->paciente?->nombre_completo ?? '—' }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $cita->paciente?->identificacion ?? '' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3.5 text-gray-700 whitespace-nowrap">
                                    {{ $cita->medico?->usuario->name ?? $cita->medico?->usuario->nombre ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-700">
                                    {{ $cita->servicio?->nombre ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-700">
                                    {{ $cita->modalidad?->nombre ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ $nombreEstado ?: '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <a href="{{ route('gestor.citas.edit', $cita) }}"
                                       class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($citas->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $citas->withQueryString()->links() }}
                </div>
            @endif
        @endif

    </div>

</div>
@endsection
