@extends('admin.layouts.app')

@section('title', 'Nuevo Paciente')
@section('page-title', 'Nuevo Paciente')

@section('content')
<div class="max-w-2xl">

    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('admin.pacientes.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 transition">
            ← Volver a pacientes
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-5">Datos del paciente</h2>

        <form method="POST" action="{{ route('admin.pacientes.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Nombre completo --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre_completo" value="{{ old('nombre_completo') }}" required
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('nombre_completo') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('nombre_completo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Identificación --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Número de identificación <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="identificacion" value="{{ old('identificacion') }}" required maxlength="20"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('identificacion') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('identificacion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Sexo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Sexo <span class="text-red-500">*</span>
                    </label>
                    <select name="sexo" required
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('sexo') ? 'border-red-400' : 'border-gray-300' }}">
                        <option value="">Seleccionar...</option>
                        <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                        <option value="Otro" {{ old('sexo') === 'Otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('sexo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fecha de nacimiento --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de nacimiento <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required
                           max="{{ now()->toDateString() }}"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('fecha_nacimiento') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('fecha_nacimiento')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Teléfono --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}" maxlength="20"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Correo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input type="email" name="correo" value="{{ old('correo') }}" maxlength="100"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('correo') ? 'border-red-400' : '' }}">
                    @error('correo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dirección --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}" maxlength="255"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition">
                    Guardar paciente
                </button>
                <a href="{{ route('admin.pacientes.index') }}"
                   class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
