@extends('admin.layouts.app')
@section('title', 'Importar Datos')
@section('content')
<div class="max-w-7xl mx-auto" x-data="{ modalTipo: null, formTitulo: '' }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Importar Datos Masivos</h1>
            <p class="text-gray-600 mt-1">Migre datos de pacientes, médicos y personal desde archivos Excel o CSV.</p>
        </div>
        <a href="{{ route('admin.importar.historial') }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Historial
        </a>
    </div>

    {{-- Alertas --}}
    @if(session('exito'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3 mb-6">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('exito') }}
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-6">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Grid de tipos de importación --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        @foreach($tiposImportacion as $tipo => $info)
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-900 text-white flex items-center justify-center">
                        @switch($tipo)
                            @case('pacientes')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                @break
                            @case('medicos')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                @break
                            @case('gestores')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                @break
                            @default
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                        @endswitch
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $info['titulo'] }}</h3>
                </div>

                <p class="text-sm text-gray-600 mb-4">{{ $info['descripcion'] }}</p>

                {{-- Campos requeridos --}}
                <div class="mb-4">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Campos obligatorios:</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($info['campos_requeridos'] as $campo)
                            <span class="px-2 py-1 bg-red-50 text-red-700 text-xs rounded">{{ $campo }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Campos opcionales --}}
                <div class="mb-4">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Campos opcionales:</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($info['campos_opcionales'] as $campo)
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">{{ $campo }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex gap-3">
                <a href="{{ route('admin.importar.plantilla', $tipo) }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar plantilla
                </a>
                <button type="button"
                        @click="modalTipo = '{{ $tipo }}'; formTitulo = '{{ $info['titulo'] }}'"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Importar
                </button>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Modal único (fuera del foreach) --}}
    <div x-show="modalTipo !== null"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition.opacity>
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="modalTipo = null"></div>

        {{-- Modal content --}}
        <div class="flex items-center justify-center min-h-screen px-4 py-8 relative">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full relative z-10"
                 @click.stop>
                <div class="px-6 pt-6 pb-4">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Importar <span x-text="formTitulo"></span>
                        </h3>
                        <button @click="modalTipo = null" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('admin.importar.procesar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="tipo" :value="modalTipo">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Seleccionar archivo (Excel o CSV)
                            </label>
                            <input type="file"
                                   name="archivo"
                                   accept=".xlsx,.xls,.csv"
                                   required
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-900 file:text-white hover:file:bg-gray-800">
                            <p class="text-xs text-gray-500 mt-1">Máximo 10MB. Formatos: .xlsx, .xls, .csv</p>
                        </div>

                        <div class="flex items-center gap-2 mb-4">
                            <input type="checkbox"
                                   name="enviar_correos"
                                   id="enviar_correos_modal"
                                   value="1"
                                   checked
                                   class="h-4 w-4 text-gray-900 border-gray-300 rounded focus:ring-gray-900">
                            <label for="enviar_correos_modal" class="text-sm text-gray-700">
                                Enviar correos con credenciales a los usuarios importados
                            </label>
                        </div>

                        <div class="flex gap-3 pt-4 border-t border-gray-200">
                            <button type="button"
                                    @click="modalTipo = null"
                                    class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800">
                                🚀 Iniciar Importación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Instrucciones generales --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <h3 class="font-semibold text-blue-900 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Instrucciones importantes
        </h3>
        <ul class="space-y-2 text-sm text-blue-800">
            <li class="flex items-start gap-2">
                <span class="font-bold">1.</span>
                <span>Descarga la plantilla correspondiente y completa los datos.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">2.</span>
                <span>Los campos <strong>identificacion</strong>, <strong>nombre_completo</strong> y <strong>correo</strong> son obligatorios.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">3.</span>
                <span>Se generará una contraseña temporal automáticamente para cada usuario.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">4.</span>
                <span>Los usuarios recibirán un correo con sus credenciales y link de acceso.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">5.</span>
                <span>En el primer inicio de sesión, se pedirá cambiar la contraseña temporal.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="font-bold">6.</span>
                <span>Se omitirán registros con correos o identificaciones duplicadas.</span>
            </li>
        </ul>
    </div>

</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection
