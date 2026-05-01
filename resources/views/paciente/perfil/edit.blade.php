@extends('paciente.layouts.app')

@section('title', 'Mi Perfil')
@section('page-title', 'Mi Perfil')

@section('content')

<div class="max-w-2xl mx-auto space-y-6">

    {{-- Datos de contacto --}}
    <form method="POST" action="{{ route('paciente.perfil.update') }}" x-data>
        @csrf
        @method('PATCH')

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Datos personales</h3>
                    <p class="text-xs text-gray-400">Nombre e identificación no se pueden modificar aquí.</p>
                </div>
            </div>

            <div class="p-6 space-y-5">

                {{-- Nombre (solo lectura) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                        Nombre completo
                    </label>
                    <input type="text" value="{{ $paciente->nombre_completo }}" disabled
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-400 text-sm cursor-not-allowed">
                </div>

                {{-- Identificación (solo lectura) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                        Identificación
                    </label>
                    <input type="text" value="{{ $paciente->identificacion }}" disabled
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-400 text-sm cursor-not-allowed">
                </div>

                {{-- Teléfono --}}
                <div>
                    <label for="telefono" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Teléfono
                    </label>
                    <input type="tel" id="telefono" name="telefono"
                           value="{{ old('telefono', $paciente->telefono) }}"
                           placeholder="Ej: 3001234567"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm transition
                                  {{ $errors->has('telefono') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100' }}
                                  outline-none">
                    @error('telefono')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Correo de contacto --}}
                <div>
                    <label for="correo" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Correo de contacto
                    </label>
                    <input type="email" id="correo" name="correo"
                           value="{{ old('correo', $paciente->correo) }}"
                           placeholder="tucorreo@ejemplo.com"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm transition
                                  {{ $errors->has('correo') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100' }}
                                  outline-none">
                    <p class="mt-1 text-[11px] text-gray-400">A este correo se enviarán las historias clínicas y notificaciones.</p>
                    @error('correo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- Cobertura --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Cobertura médica</h3>
                    <p class="text-xs text-gray-400">Tipo de convenio o aseguradora con la que asistes.</p>
                </div>
            </div>

            <div class="p-6 space-y-5">
                {{-- Portafolio --}}
                <div>
                    <label for="portafolio_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Tipo de seguro / cobertura médica
                    </label>
                    <select id="portafolio_id" name="portafolio_id"
                            class="w-full px-4 py-2.5 rounded-xl border text-sm transition
                                   {{ $errors->has('portafolio_id') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100' }}
                                   outline-none bg-gray-50">
                        <option value="">— Sin cobertura registrada —</option>
                        @foreach($portafolios as $p)
                            <option value="{{ $p->id }}"
                                {{ old('portafolio_id', $paciente->portafolio_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->nombre_convenio }}
                            </option>
                        @endforeach
                    </select>
                    @error('portafolio_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Aseguradora --}}
                <div>
                    <label for="nombre_aseguradora" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nombre de la aseguradora <span class="text-gray-400 font-normal normal-case">(opcional)</span>
                    </label>
                    <input type="text" id="nombre_aseguradora" name="nombre_aseguradora"
                           value="{{ old('nombre_aseguradora', $paciente->nombre_aseguradora) }}"
                           placeholder="Ej: Colsanitas, AXA Colpatria"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm transition
                                  {{ $errors->has('nombre_aseguradora') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100' }}
                                  outline-none">
                </div>

                {{-- Número de póliza --}}
                <div>
                    <label for="numero_poliza" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Número de póliza / afiliación <span class="text-gray-400 font-normal normal-case">(opcional)</span>
                    </label>
                    <input type="text" id="numero_poliza" name="numero_poliza"
                           value="{{ old('numero_poliza', $paciente->numero_poliza) }}"
                           placeholder="Si no lo sabes, puedes dejarlo vacío"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm transition
                                  {{ $errors->has('numero_poliza') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100' }}
                                  outline-none">
                </div>
            </div>
        </div>

        {{-- Cambio de contraseña --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6"
             x-data="{ cambiar: {{ $errors->hasAny(['password_actual', 'password']) ? 'true' : 'false' }} }">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center text-white shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Cambiar contraseña</h3>
                        <p class="text-xs text-gray-400">Opcional — solo si deseas actualizarla.</p>
                    </div>
                </div>
                <button type="button" @click="cambiar = !cambiar"
                        class="text-xs font-bold text-blue-600 hover:text-blue-800 transition">
                    <span x-text="cambiar ? 'Cancelar' : 'Cambiar'"></span>
                </button>
            </div>

            <div x-show="cambiar" x-transition class="p-6 space-y-5">

                <div>
                    <label for="password_actual" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Contraseña actual
                    </label>
                    <input type="password" id="password_actual" name="password_actual"
                           autocomplete="current-password"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm transition
                                  {{ $errors->has('password_actual') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100' }}
                                  outline-none">
                    @error('password_actual')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nueva contraseña
                    </label>
                    <input type="password" id="password" name="password"
                           autocomplete="new-password"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm transition
                                  {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100' }}
                                  outline-none">
                    <p class="mt-1 text-[11px] text-gray-400">Mínimo 8 caracteres.</p>
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Confirmar nueva contraseña
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           autocomplete="new-password"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm transition
                                  border-gray-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 outline-none">
                </div>

            </div>

        </div>

        <div class="flex justify-end mt-4">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 active:scale-95 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar cambios
            </button>
        </div>

    </form>

</div>

@endsection
