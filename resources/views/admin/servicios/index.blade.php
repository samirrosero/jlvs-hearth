@extends('admin.layouts.app')
@section('title', 'Servicios médicos')
@section('page-title', 'Servicios médicos')

@section('content')
<div class="space-y-6"
     x-data="{
        editando: {{ isset($servicio) ? $servicio->id : 'null' }},
        editNombre: '{{ isset($servicio) ? addslashes($servicio->nombre) : '' }}',
        editDuracion: '{{ isset($servicio) ? $servicio->duracion_minutos : '' }}',
        editDesc: '{{ isset($servicio) ? addslashes($servicio->descripcion ?? '') : '' }}'
     }">

    {{-- Alertas ────────────────────────────────────────────────────────── --}}
    @if(session('exito'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('exito') }}
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Formulario nuevo servicio ──────────────────────────────── --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Nuevo servicio</h2>

                <form method="POST" action="{{ route('admin.servicios.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del servicio <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}"
                               placeholder="Ej: Consulta general"
                               class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                                      {{ $errors->has('nombre') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                        @error('nombre')
                            <p class="text-red-600 text-xs mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Duración (minutos) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="duracion_minutos" value="{{ old('duracion_minutos') }}"
                               placeholder="Ej: 30" min="5" max="480"
                               class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                                      {{ $errors->has('duracion_minutos') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                        <p class="text-xs text-gray-400 mt-1">Mínimo 5 min · Máximo 480 min</p>
                        @error('duracion_minutos')
                            <p class="text-red-600 text-xs mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="descripcion" rows="2" placeholder="Opcional..."
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('descripcion') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                        Registrar servicio
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Listado de servicios ───────────────────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Catálogo de servicios</h2>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.precios.matriz') }}"
                           class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Ver precios
                        </a>
                        <span class="text-xs text-gray-400">{{ $servicios->count() }} registrado(s)</span>
                    </div>
                </div>

                @if($servicios->isEmpty())
                <div class="text-center py-16 text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm font-medium">Sin servicios registrados</p>
                    <p class="text-xs mt-1">Registra el primer servicio usando el formulario.</p>
                </div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($servicios as $s)
                    <div class="px-6 py-3">
                        {{-- Fila normal ------------------------------------------------ --}}
                        <div class="flex items-center gap-4" x-show="editando !== {{ $s->id }}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $s->nombre }}</p>
                                    @if(!$s->activo)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Inactivo</span>
                                    @endif
                                </div>
                                @if($s->descripcion)
                                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $s->descripcion }}</p>
                                @endif
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <p class="text-sm font-semibold text-gray-700">{{ $s->duracion_minutos }} min</p>
                                <p class="text-xs text-gray-400">duración</p>
                            </div>
                            @if($s->activo)
                            <button type="button"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                    title="Editar"
                                    @click="editando = {{ $s->id }}; editNombre = '{{ addslashes($s->nombre) }}'; editDuracion = '{{ $s->duracion_minutos }}'; editDesc = '{{ addslashes($s->descripcion ?? '') }}'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <a href="{{ route('admin.servicios.precios', $s) }}"
                               class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition"
                               title="Gestionar precios">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('admin.servicios.destroy', $s) }}"
                                  onsubmit="return confirm('¿Desactivar el servicio «{{ $s->nombre }}»?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"
                                        title="Desactivar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>

                        {{-- Formulario de edición inline ------------------------------ --}}
                        <div x-show="editando === {{ $s->id }}" x-cloak class="py-1">
                            <form method="POST" action="{{ route('admin.servicios.update', $s) }}" class="space-y-3">
                                @csrf @method('PUT')

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">
                                            Nombre <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="nombre" x-model="editNombre" required
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        @if(isset($servicio) && $servicio->id === $s->id)
                                            @error('nombre')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">
                                            Duración (min) <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" name="duracion_minutos" x-model="editDuracion"
                                               min="5" max="480" required
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        @if(isset($servicio) && $servicio->id === $s->id)
                                            @error('duracion_minutos')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                                        <input type="text" name="descripcion" x-model="editDesc"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button type="submit"
                                            class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition">
                                        Guardar cambios
                                    </button>
                                    <button type="button" @click="editando = null"
                                            class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs rounded-lg transition">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
