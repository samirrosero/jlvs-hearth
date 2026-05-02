@extends('gestor.layouts.app')

@section('title', 'Editar cita')
@section('page-title', 'Editar cita')

@section('content')
@php
    $estadoBadge = [
        'Pendiente'  => 'bg-amber-100 text-amber-700',
        'Confirmada' => 'bg-blue-100 text-blue-700',
        'Atendida'   => 'bg-green-100 text-green-700',
        'Cancelada'  => 'bg-red-100 text-red-600',
        'No asistió' => 'bg-gray-100 text-gray-500',
    ];
    $nombreEstadoActual = $cita->estado?->nombre ?? '';
    $badgeActual = $estadoBadge[$nombreEstadoActual] ?? 'bg-gray-100 text-gray-500';
@endphp

<div class="max-w-2xl mx-auto space-y-5">

    {{-- ── Encabezado ── --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-bold text-gray-900">Editar cita</h2>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeActual }}">
                {{ $nombreEstadoActual ?: '—' }}
            </span>
        </div>
        <a href="{{ route('gestor.citas') }}"
           class="text-sm text-gray-600 hover:text-gray-800 font-medium inline-flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver a citas
        </a>
    </div>

    {{-- ── Errores de validación ── --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm space-y-1">
            <p class="font-semibold">Por favor corrige los siguientes errores:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Formulario principal ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('gestor.citas.update', $cita) }}">
            @csrf
            @method('PUT')

            <div class="space-y-5">

                {{-- Paciente --}}
                <div>
                    <label for="paciente_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Paciente <span class="text-red-500">*</span>
                    </label>
                    <select id="paciente_id" name="paciente_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Selecciona un paciente</option>
                        @foreach($pacientes as $pac)
                            <option value="{{ $pac->id }}"
                                {{ old('paciente_id', $cita->paciente_id) == $pac->id ? 'selected' : '' }}>
                                {{ $pac->nombre_completo }} — {{ $pac->identificacion }}
                            </option>
                        @endforeach
                    </select>
                    @error('paciente_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Médico --}}
                <div>
                    <label for="medico_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Médico <span class="text-red-500">*</span>
                    </label>
                    <select id="medico_id" name="medico_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Selecciona un médico</option>
                        @foreach($medicos as $medico)
                            <option value="{{ $medico->id }}"
                                {{ old('medico_id', $cita->medico_id) == $medico->id ? 'selected' : '' }}>
                                {{ $medico->usuario->name ?? $medico->usuario->nombre ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                    @error('medico_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Servicio --}}
                <div>
                    <label for="servicio_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Servicio <span class="text-red-500">*</span>
                    </label>
                    <select id="servicio_id" name="servicio_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Selecciona un servicio</option>
                        @foreach($servicios as $servicio)
                            <option value="{{ $servicio->id }}"
                                {{ old('servicio_id', $cita->servicio_id) == $servicio->id ? 'selected' : '' }}>
                                {{ $servicio->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('servicio_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Modalidad --}}
                <div>
                    <label for="modalidad_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Modalidad <span class="text-red-500">*</span>
                    </label>
                    <select id="modalidad_id" name="modalidad_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Selecciona una modalidad</option>
                        @foreach($modalidades as $modalidad)
                            <option value="{{ $modalidad->id }}"
                                {{ old('modalidad_id', $cita->modalidad_id) == $modalidad->id ? 'selected' : '' }}>
                                {{ $modalidad->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('modalidad_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fecha y Hora --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="fecha"
                               name="fecha"
                               value="{{ old('fecha', $cita->fecha) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        @error('fecha')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="hora" class="block text-sm font-medium text-gray-700 mb-1">
                            Hora <span class="text-red-500">*</span>
                        </label>
                        <input type="time"
                               id="hora"
                               name="hora"
                               value="{{ old('hora', \Carbon\Carbon::parse($cita->hora)->format('H:i')) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        @error('hora')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Estado --}}
                <div>
                    <label for="estado_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Estado
                    </label>
                    <select id="estado_id" name="estado_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        @foreach($estados as $estado)
                            <option value="{{ $estado->id }}"
                                {{ old('estado_id', $cita->estado_id) == $estado->id ? 'selected' : '' }}>
                                {{ $estado->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('estado_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- Acciones formulario principal --}}
            <div class="flex items-center justify-between pt-6 mt-2 border-t border-gray-100">
                <a href="{{ route('gestor.citas') }}"
                   class="text-sm text-gray-600 hover:text-gray-800 font-medium transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
                    Guardar cambios
                </button>
            </div>

        </form>
    </div>

    {{-- ── Cambio rápido de estado ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Cambiar estado rápido</h3>
        <div class="flex flex-wrap gap-2">

            @php
                $estadosRapidos = [
                    1 => ['label' => 'Pendiente',   'ring' => 'ring-amber-400',  'bg' => 'bg-amber-50  hover:bg-amber-100  text-amber-700'],
                    2 => ['label' => 'Confirmada',  'ring' => 'ring-blue-400',   'bg' => 'bg-blue-50   hover:bg-blue-100   text-blue-700'],
                    3 => ['label' => 'Atendida',    'ring' => 'ring-green-400',  'bg' => 'bg-green-50  hover:bg-green-100  text-green-700'],
                    4 => ['label' => 'Cancelada',   'ring' => 'ring-red-400',    'bg' => 'bg-red-50    hover:bg-red-100    text-red-600'],
                    5 => ['label' => 'No asistió',  'ring' => 'ring-gray-300',   'bg' => 'bg-gray-50   hover:bg-gray-100   text-gray-500'],
                ];
            @endphp

            @foreach($estadosRapidos as $eid => $cfg)
                <div x-data="{ confirmar: false }">
                    {{-- Botón inicial --}}
                    <button type="button"
                            x-show="!confirmar"
                            @click="confirmar = true"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all
                                   {{ $cfg['bg'] }}
                                   {{ $cita->estado_id == $eid ? 'ring-2 ' . $cfg['ring'] . ' border-transparent' : 'border-gray-200' }}">
                        {{ $cfg['label'] }}
                        @if($cita->estado_id == $eid)
                            <span class="ml-1 opacity-60">(actual)</span>
                        @endif
                    </button>

                    {{-- Confirmación inline --}}
                    <div x-show="confirmar"
                         class="flex items-center gap-1.5"
                         style="display:none">
                        <form method="POST" action="{{ route('gestor.citas.estado', $cita) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="estado_id" value="{{ $eid }}">
                            <span class="text-xs text-gray-600 mr-1">¿Confirmar?</span>
                            <button type="submit"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-800 text-white hover:bg-gray-700 transition-colors">
                                Sí
                            </button>
                        </form>
                        <button type="button"
                                @click="confirmar = false"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                            No
                        </button>
                    </div>
                </div>
            @endforeach

        </div>
    </div>

    {{-- ── Zona de cancelación ── --}}
    <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-6"
         x-data="{ confirmarCancelar: false }">
        <h3 class="text-sm font-semibold text-red-700 mb-1">Cancelar esta cita</h3>
        <p class="text-xs text-gray-500 mb-3">
            Esto cambiará el estado a "Cancelada". La cita no se eliminará del sistema.
        </p>

        <div x-show="!confirmarCancelar">
            <button type="button"
                    @click="confirmarCancelar = true"
                    class="text-sm font-semibold text-red-600 hover:text-red-800 border border-red-200 hover:border-red-400 px-4 py-2 rounded-xl transition-colors">
                Cancelar cita
            </button>
        </div>

        <div x-show="confirmarCancelar" class="flex items-center gap-3" style="display:none">
            <p class="text-sm text-gray-700">¿Seguro que deseas cancelar esta cita?</p>
            <form method="POST" action="{{ route('gestor.citas.estado', $cita) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="estado_id" value="4">
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold text-sm px-4 py-2 rounded-xl transition-colors">
                    Sí, cancelar
                </button>
            </form>
            <button type="button"
                    @click="confirmarCancelar = false"
                    class="text-sm text-gray-600 hover:text-gray-800 font-medium px-4 py-2 rounded-xl border border-gray-200 hover:border-gray-300 transition-colors">
                No, volver
            </button>
        </div>
    </div>

</div>
@endsection
