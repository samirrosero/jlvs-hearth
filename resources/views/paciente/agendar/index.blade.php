@extends('paciente.layouts.app')

@section('title', 'Agendar Cita')
@section('page-title', 'Agendar una Cita')

@push('styles')
<style>
    .agendar-focus:focus {
        border-color: var(--color-primario) !important;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primario) 15%, transparent) !important;
    }
    .modalidad-radio:checked + .modalidad-card {
        border-color: var(--color-primario);
        background-color: color-mix(in srgb, var(--color-primario) 8%, white);
        color: var(--color-primario);
    }
    .btn-primario {
        background-color: var(--color-primario);
    }
    .btn-primario:hover {
        background-color: var(--color-secundario);
    }
</style>
@endpush

@section('content')

<div class="max-w-xl mx-auto">
    <div class="relative flex items-center justify-center py-8">
        <a href="{{ route('paciente.dashboard') }}"
           class="absolute left-0 text-sm text-gray-600 hover:text-gray-800 font-medium inline-flex items-center gap-1 transition-colors">
            ← Volver al inicio
        </a>
    </div>
    @if (session('error'))
        <div class="mb-6 flex items-start gap-3 px-5 py-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-sm font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Encabezado --}}
        <div class="px-8 py-6" style="background-color: var(--color-primario)">
            <div class="flex items-center gap-4">
                <div class="bg-white/20 rounded-2xl p-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">Solicitar cita médica</h2>
                    <p class="text-sm mt-0.5" style="color: rgba(255,255,255,0.75)">Elige la especialidad y la fecha que prefieras</p>
                </div>
            </div>
        </div>

        {{-- Formulario --}}
        <form method="GET" action="{{ route('paciente.agendar.disponible') }}" class="p-8 space-y-6">

            {{-- Especialidad --}}
            <div>
                <label for="especialidad" class="block text-base font-bold text-gray-700 mb-2">
                    ¿Qué tipo de médico necesitas?
                </label>
                <select id="especialidad" name="especialidad" required
                        class="agendar-focus w-full px-4 py-3.5 rounded-xl border border-gray-200 text-gray-800 text-base outline-none bg-gray-50 transition
                               {{ $errors->has('especialidad') ? 'border-red-400 bg-red-50' : '' }}">
                    <option value="">— Selecciona una especialidad —</option>
                    @foreach ($especialidades as $esp)
                        <option value="{{ $esp }}" {{ old('especialidad') === $esp ? 'selected' : '' }}>
                            {{ $esp }}
                        </option>
                    @endforeach
                </select>
                @error('especialidad')
                    <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Fecha --}}
            <div>
                <label for="fecha" class="block text-base font-bold text-gray-700 mb-2">
                    ¿Qué día quieres la cita?
                </label>
                <input type="date" id="fecha" name="fecha" required
                       min="{{ $fechaMinima->format('Y-m-d') }}"
                       max="{{ now()->addMonths(3)->format('Y-m-d') }}"
                       value="{{ old('fecha') }}"
                       class="agendar-focus w-full px-4 py-3.5 rounded-xl border border-gray-200 text-gray-800 text-base outline-none bg-gray-50 transition
                              {{ $errors->has('fecha') ? 'border-red-400 bg-red-50' : '' }}">
                <p class="mt-1.5 text-sm text-gray-400">Las citas en línea se agendan con mínimo 2 días hábiles de anticipación.</p>
                @error('fecha')
                    <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Aviso presencial --}}
            <div class="flex items-start gap-3 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-amber-700">
                    <span class="font-bold">¿Necesitas cita urgente o para hoy?</span>
                    Dirígete directamente a la clínica. En recepción te asignan el turno disponible más próximo.
                </p>
            </div>

            {{-- Modalidad --}}
            <div>
                <label class="block text-base font-bold text-gray-700 mb-2">
                    ¿Cómo prefieres la atención?
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach ($modalidades as $m)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="modalidad_id" value="{{ $m->id }}"
                                   class="modalidad-radio sr-only"
                                   {{ old('modalidad_id', 1) == $m->id ? 'checked' : '' }} required>
                            <div class="modalidad-card px-4 py-3 rounded-xl border-2 border-gray-200 text-center text-sm font-bold text-gray-600 hover:border-gray-300 transition">
                                {{ $m->nombre }}
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('modalidad_id')
                    <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Botón --}}
            <button type="submit"
                    class="btn-primario w-full py-4 active:scale-[.98] text-white text-lg font-bold
                           rounded-xl transition flex items-center justify-center gap-3 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Ver horarios disponibles
            </button>

        </form>
    </div>

</div>

@endsection
