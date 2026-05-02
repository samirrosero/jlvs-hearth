@extends('gestor.layouts.app')

@section('title', 'Registrar paciente')
@section('page-title', 'Registrar paciente')

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    {{-- ── Encabezado ── --}}
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">Nuevo paciente</h2>
        <a href="{{ route('gestor.pacientes') }}"
           class="text-sm text-gray-600 hover:text-gray-800 font-medium inline-flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver a pacientes
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

    {{-- ── Formulario ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('gestor.pacientes.store') }}">
            @csrf

            <div class="space-y-5">

                {{-- Nombre completo --}}
                <div>
                    <label for="nombre_completo" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="nombre_completo"
                           name="nombre_completo"
                           value="{{ old('nombre_completo') }}"
                           placeholder="Ej. María García López"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                  @error('nombre_completo') border-red-400 @enderror">
                    @error('nombre_completo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Identificación --}}
                <div>
                    <label for="identificacion" class="block text-sm font-medium text-gray-700 mb-1">
                        Número de identificación <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="identificacion"
                           name="identificacion"
                           value="{{ old('identificacion') }}"
                           placeholder="Cédula, pasaporte u otro documento"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                  @error('identificacion') border-red-400 @enderror">
                    @error('identificacion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fecha de nacimiento + Sexo --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha de nacimiento
                        </label>
                        <input type="date"
                               id="fecha_nacimiento"
                               name="fecha_nacimiento"
                               value="{{ old('fecha_nacimiento') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                      @error('fecha_nacimiento') border-red-400 @enderror">
                        @error('fecha_nacimiento')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sexo" class="block text-sm font-medium text-gray-700 mb-1">
                            Sexo
                        </label>
                        <select id="sexo"
                                name="sexo"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                       @error('sexo') border-red-400 @enderror">
                            <option value="">Seleccionar</option>
                            <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                            <option value="Otro" {{ old('sexo') === 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('sexo')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Teléfono --}}
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">
                        Teléfono
                    </label>
                    <input type="tel"
                           id="telefono"
                           name="telefono"
                           value="{{ old('telefono') }}"
                           placeholder="Ej. 3001234567"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                  @error('telefono') border-red-400 @enderror">
                    @error('telefono')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Correo --}}
                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo electrónico
                    </label>
                    <input type="email"
                           id="correo"
                           name="correo"
                           value="{{ old('correo') }}"
                           placeholder="paciente@ejemplo.com"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                  @error('correo') border-red-400 @enderror">
                    @error('correo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dirección --}}
                <div>
                    <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">
                        Dirección
                    </label>
                    <textarea id="direccion"
                              name="direccion"
                              rows="2"
                              placeholder="Calle, barrio, ciudad…"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none
                                     @error('direccion') border-red-400 @enderror">{{ old('direccion') }}</textarea>
                    @error('direccion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- Acciones --}}
            <div class="flex items-center justify-between pt-6 mt-2 border-t border-gray-100">
                <a href="{{ route('gestor.pacientes') }}"
                   class="text-sm text-gray-600 hover:text-gray-800 font-medium transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
                    Registrar paciente
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
