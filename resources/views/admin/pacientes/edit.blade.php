@extends('admin.layouts.app')

@section('title', 'Editar Paciente')
@section('page-title', 'Editar Paciente')

@section('content')
<div class="max-w-2xl">

    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('admin.pacientes.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 transition">
            ← Volver a pacientes
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Editar datos del paciente</h2>
        <p class="text-sm text-gray-500 mb-5">ID: {{ $paciente->id }} — Registrado el {{ $paciente->created_at->format('d/m/Y') }}</p>

        <form method="POST" action="{{ route('admin.pacientes.update', $paciente) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre_completo"
                           value="{{ old('nombre_completo', $paciente->nombre_completo) }}" required
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('nombre_completo') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('nombre_completo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Identificación <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="identificacion"
                           value="{{ old('identificacion', $paciente->identificacion) }}" required maxlength="20"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('identificacion') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('identificacion')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Sexo <span class="text-red-500">*</span>
                    </label>
                    <select name="sexo" required
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('sexo') ? 'border-red-400' : 'border-gray-300' }}">
                        <option value="M" {{ old('sexo', $paciente->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('sexo', $paciente->sexo) === 'F' ? 'selected' : '' }}>Femenino</option>
                        <option value="Otro" {{ old('sexo', $paciente->sexo) === 'Otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de nacimiento <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="fecha_nacimiento"
                           value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento) }}" required
                           max="{{ now()->toDateString() }}"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('fecha_nacimiento') ? 'border-red-400' : 'border-gray-300' }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="telefono"
                           value="{{ old('telefono', $paciente->telefono) }}" maxlength="20"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input type="email" name="correo"
                           value="{{ old('correo', $paciente->correo) }}" maxlength="100"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('correo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" name="direccion"
                           value="{{ old('direccion', $paciente->direccion) }}" maxlength="255"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                    Guardar cambios
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
