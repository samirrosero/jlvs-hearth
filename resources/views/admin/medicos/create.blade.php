@extends('admin.layouts.app')

@section('title', 'Nuevo Médico')
@section('page-title', 'Nuevo Médico')

@section('content')
<div class="max-w-2xl">

    <a href="{{ route('admin.medicos.index') }}"
       class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-5 w-fit transition">
        ← Volver a médicos
    </a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Registrar nuevo médico</h2>
        <p class="text-sm text-gray-500 mb-5">Se creará un usuario con rol <em>médico</em> y su perfil profesional.</p>

        <form method="POST" action="{{ route('admin.medicos.store') }}" class="space-y-4">
            @csrf

            {{-- Separador Cuenta --}}
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider pt-2">Cuenta de acceso</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('nombre') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Correo electrónico <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Identificación <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="identificacion" value="{{ old('identificacion') }}" required maxlength="20"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('identificacion') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('identificacion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar contraseña <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

            </div>

            {{-- Separador Perfil médico --}}
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider pt-4">Perfil profesional</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Especialidad <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="especialidad" value="{{ old('especialidad') }}" required maxlength="100"
                           placeholder="Ej: Medicina General"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('especialidad') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('especialidad') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Registro médico <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="registro_medico" value="{{ old('registro_medico') }}" required maxlength="50"
                           placeholder="Ej: RM-12345"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('registro_medico') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('registro_medico') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition">
                    Registrar médico
                </button>
                <a href="{{ route('admin.medicos.index') }}"
                   class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
