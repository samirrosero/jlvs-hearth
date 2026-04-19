@extends('admin.layouts.app')

@section('title', 'Horarios Médicos')
@section('page-title', 'Horarios Médicos')

@section('content')

{{-- ── Selector de médico ───────────────────────────────────────── --}}
<form method="GET" action="{{ route('admin.horarios') }}"
      class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <p class="text-sm font-medium text-gray-700 mb-3">Selecciona un médico para configurar su disponibilidad semanal</p>
    <div class="flex gap-3 items-end">
        <div class="flex flex-col gap-1 flex-1 max-w-sm">
            <label class="text-xs text-gray-500 font-medium">Médico</label>
            <select name="medico_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                <option value="">— Selecciona un médico —</option>
                @foreach ($medicos as $m)
                    <option value="{{ $m->id }}"
                        {{ $medicoSeleccionado?->id == $m->id ? 'selected' : '' }}>
                        {{ $m->usuario->nombre }}
                        @if ($m->especialidad)
                            — {{ $m->especialidad }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-700 transition">
            Ver horario
        </button>
    </div>
</form>

{{-- ── Formulario de horario ────────────────────────────────────── --}}
@if ($medicoSeleccionado)
<form method="POST" action="{{ route('admin.horarios.guardar') }}">
    @csrf
    <input type="hidden" name="medico_id" value="{{ $medicoSeleccionado->id }}">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Cabecera --}}
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800">{{ $medicoSeleccionado->usuario->nombre }}</h3>
                <p class="text-xs text-gray-400">{{ $medicoSeleccionado->especialidad ?? 'Sin especialidad' }}
                    · Reg. {{ $medicoSeleccionado->registro_medico ?? '—' }}</p>
            </div>
            <button type="submit"
                    class="bg-gray-900 text-white text-sm px-5 py-2 rounded-lg hover:bg-gray-700 transition">
                Guardar horario
            </button>
        </div>

        {{-- Días de la semana --}}
        <div class="divide-y divide-gray-50">
            @foreach ($horarios as $num => $dia)
            <div class="flex items-center gap-5 px-5 py-4
                        {{ $dia['activo'] ? '' : 'opacity-60' }}"
                 x-data="{ activo: {{ $dia['activo'] ? 'true' : 'false' }} }">

                {{-- Toggle día --}}
                <div class="w-28 flex items-center gap-2 flex-shrink-0">
                    <input type="checkbox"
                           name="dias[{{ $num }}][activo]"
                           id="dia_{{ $num }}"
                           @if ($dia['activo']) checked @endif
                           @change="activo = $event.target.checked"
                           class="w-4 h-4 rounded accent-gray-900 cursor-pointer">
                    <label for="dia_{{ $num }}"
                           class="text-sm font-medium text-gray-700 cursor-pointer select-none">
                        {{ $dia['nombre'] }}
                    </label>
                </div>

                {{-- Horas --}}
                <div class="flex items-center gap-3 flex-1" :class="activo ? '' : 'pointer-events-none'">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-400">Hora inicio</label>
                        <input type="time"
                               name="dias[{{ $num }}][hora_inicio]"
                               value="{{ $dia['hora_inicio'] }}"
                               :disabled="!activo"
                               class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 disabled:bg-gray-50 disabled:text-gray-400">
                    </div>
                    <span class="text-gray-300 mt-4">—</span>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-gray-400">Hora fin</label>
                        <input type="time"
                               name="dias[{{ $num }}][hora_fin]"
                               value="{{ $dia['hora_fin'] }}"
                               :disabled="!activo"
                               class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 disabled:bg-gray-50 disabled:text-gray-400">
                    </div>

                    {{-- Badge estado --}}
                    <div class="ml-2">
                        <template x-if="activo">
                            <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Disponible</span>
                        </template>
                        <template x-if="!activo">
                            <span class="text-xs bg-gray-100 text-gray-400 px-2 py-0.5 rounded-full">No trabaja</span>
                        </template>
                    </div>
                </div>

            </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
            <button type="submit"
                    class="bg-gray-900 text-white text-sm px-5 py-2 rounded-lg hover:bg-gray-700 transition">
                Guardar horario
            </button>
        </div>
    </div>
</form>

@else
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-400 text-sm">
        Selecciona un médico arriba para ver y configurar su horario semanal.
    </div>
@endif

@endsection
