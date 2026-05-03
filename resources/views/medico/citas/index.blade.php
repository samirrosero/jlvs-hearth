@extends('medico.layouts.app')

@section('title', 'Mis Citas')
@section('page-title', 'Mis Citas')

@section('content')

{{-- ── Filtros ──────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('medico.citas') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div class="flex flex-col gap-1">
        <label class="text-xs text-gray-500 font-medium">Fecha</label>
        <input type="date" name="fecha" value="{{ request('fecha') }}"
               class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
    </div>
    <div class="flex flex-col gap-1">
        <label class="text-xs text-gray-500 font-medium">Estado</label>
        <select name="estado_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
            <option value="">Todos los estados</option>
            @foreach ($estados as $estado)
                <option value="{{ $estado->id }}" {{ request('estado_id') == $estado->id ? 'selected' : '' }}>
                    {{ $estado->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit"
            class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-700 transition">
        Filtrar
    </button>
    @if (request('fecha') || request('estado_id'))
        <a href="{{ route('medico.citas') }}"
           class="text-sm text-gray-500 hover:text-gray-800 px-3 py-2 rounded-lg border border-gray-200 transition">
            Limpiar
        </a>
    @endif
</form>

{{-- ── Tabla de citas ───────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[700px]">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Paciente</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fecha y hora</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Servicio</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Modalidad</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($citas as $cita)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800">{{ $cita->paciente->nombre_completo ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $cita->paciente->identificacion ?? '' }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</p>
                    </td>
                    <td class="px-5 py-4 text-gray-600">{{ $cita->servicio->nombre ?? '—' }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $cita->modalidad->nombre ?? '—' }}</td>
                    <td class="px-5 py-4">
                        <span class="text-xs px-2 py-1 rounded-full font-medium"
                              style="background:{{ ($cita->estado->color_hex ?? '#e2e8f0') }}22; color:{{ $cita->estado->color_hex ?? '#64748b' }}">
                            {{ $cita->estado->nombre ?? '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        @if (in_array($cita->estado_id, [3, 4, 5]))
                            <span class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-400 px-3 py-1.5 rounded-lg cursor-not-allowed">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                {{ $cita->estado_id === 3 ? 'Atendida' : ($cita->estado_id === 4 ? 'Cancelada' : 'No asistió') }}
                            </span>
                        @else
                            <a href="{{ route('medico.citas.atender', $cita) }}"
                               class="inline-flex items-center gap-1 text-xs bg-gray-900 text-white px-3 py-1.5 rounded-lg hover:bg-gray-700 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Atender
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-gray-400 text-sm">
                        No hay citas registradas con los filtros seleccionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($citas->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $citas->links('vendor.pagination.simple-tailwind') }}
        </div>
    @endif
</div>

@endsection
