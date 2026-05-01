@extends('medico.layouts.app')

@section('title', 'Mi Perfil')
@section('page-title', 'Mi Perfil')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <form method="POST" action="{{ route('medico.perfil.update') }}">
        @csrf
        @method('PATCH')

        {{-- Datos personales --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="font-bold text-gray-800">Datos personales</h2>
                <p class="text-xs text-gray-400 mt-0.5">Información básica de tu cuenta</p>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Nombre completo</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $user->nombre) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                                  {{ $errors->has('nombre') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100' }}">
                    @error('nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                                  {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100' }}">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Datos profesionales --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="font-bold text-gray-800">Datos profesionales</h2>
                <p class="text-xs text-gray-400 mt-0.5">Información médica y de registro</p>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Especialidad</label>
                    <input type="text" name="especialidad" value="{{ old('especialidad', $medico->especialidad) }}"
                           placeholder="Ej: Medicina general, Pediatría"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                                  border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    @error('especialidad') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Registro médico</label>
                    <input type="text" name="registro_medico" value="{{ old('registro_medico', $medico->registro_medico) }}"
                           placeholder="Número de tarjeta profesional"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none transition
                                  border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    @error('registro_medico') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Institución</label>
                    <input type="text" value="{{ $medico->empresa?->nombre ?? '—' }}" disabled
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-100 text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
                    <p class="text-[10px] text-gray-400 mt-1">Asignada por el administrador</p>
                </div>

            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar cambios
            </button>
        </div>
    </form>

</div>
@endsection
