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

                {{-- Tipo de documento + Número --}}
                <div class="grid grid-cols-5 gap-3">
                    <div class="col-span-2">
                        <label for="tipo_documento" class="block text-sm font-medium text-gray-700 mb-1">
                            Tipo <span class="text-red-500">*</span>
                        </label>
                        <select id="tipo_documento"
                                name="tipo_documento"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                       @error('tipo_documento') border-red-400 @enderror">
                            <option value="">Seleccionar</option>
                            <option value="CC"  {{ old('tipo_documento') === 'CC'  ? 'selected' : '' }}>CC — Cédula ciudadanía</option>
                            <option value="TI"  {{ old('tipo_documento') === 'TI'  ? 'selected' : '' }}>TI — Tarjeta identidad</option>
                            <option value="CE"  {{ old('tipo_documento') === 'CE'  ? 'selected' : '' }}>CE — Cédula extranjería</option>
                            <option value="PA"  {{ old('tipo_documento') === 'PA'  ? 'selected' : '' }}>PA — Pasaporte</option>
                            <option value="RC"  {{ old('tipo_documento') === 'RC'  ? 'selected' : '' }}>RC — Registro civil</option>
                            <option value="NIT" {{ old('tipo_documento') === 'NIT' ? 'selected' : '' }}>NIT</option>
                        </select>
                        @error('tipo_documento')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-span-3">
                        <label for="identificacion" class="block text-sm font-medium text-gray-700 mb-1">
                            Número de documento <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="identificacion"
                               name="identificacion"
                               value="{{ old('identificacion') }}"
                               placeholder="Ej. 1004567890"
                               required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                      @error('identificacion') border-red-400 @enderror">
                        @error('identificacion')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Nombres + Apellidos --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="nombres" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombres <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="nombres"
                               name="nombres"
                               value="{{ old('nombres') }}"
                               placeholder="Ej. María Camila"
                               required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                      @error('nombres') border-red-400 @enderror">
                        @error('nombres')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="apellidos" class="block text-sm font-medium text-gray-700 mb-1">
                            Apellidos <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="apellidos"
                               name="apellidos"
                               value="{{ old('apellidos') }}"
                               placeholder="Ej. García López"
                               required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                      @error('apellidos') border-red-400 @enderror">
                        @error('apellidos')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Fecha de nacimiento + Sexo --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha de nacimiento <span class="text-red-500">*</span>
                        </label>
                        <input type="date"
                               id="fecha_nacimiento"
                               name="fecha_nacimiento"
                               value="{{ old('fecha_nacimiento') }}"
                               required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                      @error('fecha_nacimiento') border-red-400 @enderror">
                        @error('fecha_nacimiento')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sexo" class="block text-sm font-medium text-gray-700 mb-1">
                            Sexo <span class="text-red-500">*</span>
                        </label>
                        <select id="sexo"
                                name="sexo"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                       @error('sexo') border-red-400 @enderror">
                            <option value="">Seleccionar</option>
                            <option value="M"    {{ old('sexo') === 'M'    ? 'selected' : '' }}>Masculino</option>
                            <option value="F"    {{ old('sexo') === 'F'    ? 'selected' : '' }}>Femenino</option>
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
                        Teléfono <span class="text-red-500">*</span>
                    </label>
                    <input type="tel"
                           id="telefono"
                           name="telefono"
                           value="{{ old('telefono') }}"
                           placeholder="Ej. 3001234567"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                  @error('telefono') border-red-400 @enderror">
                    @error('telefono')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Correo electrónico --}}
                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo electrónico <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           id="correo"
                           name="correo"
                           value="{{ old('correo') }}"
                           placeholder="paciente@ejemplo.com"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                  @error('correo') border-red-400 @enderror">
                    @error('correo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña temporal + Confirmar --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Contraseña temporal <span class="text-red-500">*</span>
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Mínimo 6 caracteres"
                               required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                      @error('password') border-red-400 @enderror">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmar contraseña <span class="text-red-500">*</span>
                        </label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               placeholder="Repetir contraseña"
                               required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
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
