@extends('admin.layouts.app')

@section('title', 'Horarios Médicos')
@section('page-title', 'Horarios Médicos')

@section('content')

{{-- ── Errores de validación ────────────────────────────────────── --}}
@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4">
    <p class="text-sm font-semibold text-red-700 mb-2 flex items-center gap-2">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        Corrige los siguientes errores antes de guardar:
    </p>
    <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('exito'))
<div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3">
    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    {{ session('exito') }}
</div>
@endif

{{-- ── Selector de médico ───────────────────────────────────────── --}}
<form method="GET" action="{{ route('admin.horarios') }}"
      class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <p class="text-sm font-medium text-gray-700 mb-3">Selecciona un médico para configurar su disponibilidad semanal</p>
    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
        <div class="flex flex-col gap-1 flex-1 sm:max-w-sm">
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
        <div class="overflow-x-auto">
            <div class="divide-y divide-gray-50 min-w-[600px]">
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
        </div>
    </div>
</form>

@else
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-400 text-sm">
        Selecciona un médico arriba para ver y configurar su horario semanal.
    </div>
@endif

@endsection
