@extends('admin.layouts.app')
@section('title', 'Convenios / Portafolios')
@section('page-title', 'Convenios / Portafolios')

@section('content')
<div class="space-y-6"
     x-data="{
        editando: {{ isset($portafolio) ? $portafolio->id : 'null' }},
        editNombre: '{{ isset($portafolio) ? addslashes($portafolio->nombre_convenio) : '' }}',
        editDesc: '{{ isset($portafolio) ? addslashes($portafolio->descripcion ?? '') : '' }}'
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

        {{-- ── Formulario nuevo convenio ──────────────────────────────── --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-1">Nuevo convenio</h2>
                <p class="text-xs text-gray-400 mb-4">EPS, ARL, medicina prepagada u otros convenios institucionales.</p>

                <form method="POST" action="{{ route('admin.portafolios.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del convenio <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre_convenio" value="{{ old('nombre_convenio') }}"
                               placeholder="Ej: EPS Sura, Colmédica..."
                               class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                                      {{ $errors->has('nombre_convenio') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                        @error('nombre_convenio')
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
                        Registrar convenio
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Listado de convenios ───────────────────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Convenios disponibles</h2>
                    <span class="text-xs text-gray-400">{{ $portafolios->count() }} registrado(s)</span>
                </div>

                @if($portafolios->isEmpty())
                <div class="text-center py-16 text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <p class="text-sm font-medium">Sin convenios registrados</p>
                    <p class="text-xs mt-1">Registra el primer convenio usando el formulario.</p>
                </div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($portafolios as $p)
                    <div class="px-6 py-3">

                        {{-- Fila normal ------------------------------------------- --}}
                        <div class="flex items-center gap-4" x-show="editando !== {{ $p->id }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $p->nombre_convenio }}</p>
                                @if($p->descripcion)
                                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $p->descripcion }}</p>
                                @endif
                            </div>
                            <div class="flex-shrink-0 text-right">
                                @if($p->citas_count > 0)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 font-medium">
                                    {{ $p->citas_count }} cita(s)
                                </span>
                                @else
                                <span class="text-xs text-gray-300">Sin citas</span>
                                @endif
                            </div>

                            {{-- Editar --}}
                            <button type="button"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                    title="Editar"
                                    @click="editando = {{ $p->id }}; editNombre = '{{ addslashes($p->nombre_convenio) }}'; editDesc = '{{ addslashes($p->descripcion ?? '') }}'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>

                            {{-- Eliminar (solo si no tiene citas) --}}
                            @if($p->citas_count === 0)
                            <form method="POST" action="{{ route('admin.portafolios.destroy', $p) }}"
                                  onsubmit="return confirm('¿Eliminar el convenio «{{ $p->nombre_convenio }}»? Esta acción no se puede deshacer.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"
                                        title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @else
                            <div class="p-1.5 text-gray-200" title="No se puede eliminar: tiene citas asociadas">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </div>
                            @endif
                        </div>

                        {{-- Formulario edición inline ----------------------------- --}}
                        <div x-show="editando === {{ $p->id }}" x-cloak class="py-1">
                            <form method="POST" action="{{ route('admin.portafolios.update', $p) }}" class="space-y-3">
                                @csrf @method('PUT')

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">
                                            Nombre del convenio <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="nombre_convenio" x-model="editNombre" required
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        @if(isset($portafolio) && $portafolio->id === $p->id)
                                            @error('nombre_convenio')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                                        @endif
                                    </div>
                                    <div class="sm:col-span-2">
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
