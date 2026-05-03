@extends('gestor.layouts.app')
@section('title', 'Recepción - Cobro de Citas')
@section('page-title', 'Recepción')
@section('page-subtitle', 'Verificación y cobro de citas')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Título y descripción --}}
    <div class="text-center py-4">
        <h1 class="text-2xl font-bold text-gray-900">Recepción de Pacientes</h1>
        <p class="text-gray-600 mt-2">Busca al paciente por cédula para verificar su cita y registrar el pago</p>
    </div>

    {{-- Buscador --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('gestor.recepcion.buscar') }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="flex-shrink-0">
                <select name="tipo_doc" class="w-24 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="CC" selected>CC</option>
                    <option value="CE">CE</option>
                    <option value="TI">TI</option>
                    <option value="PA">PA</option>
                </select>
            </div>
            <div class="flex-1">
                <input type="text"
                       name="identificacion"
                       placeholder="Número de identificación..."
                       required
                       autofocus
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       value="{{ old('identificacion') }}">
            </div>
            <button type="submit"
                    class="bg-gray-900 hover:bg-gray-800 text-white font-semibold text-sm px-6 py-2.5 rounded-lg transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Buscar
            </button>
        </form>
    </div>

    {{-- Alertas --}}
    @if(session('exito'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('exito') }}
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Resultados de búsqueda --}}
    @if(isset($paciente))
    <div class="space-y-4">

        {{-- Info del paciente --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $paciente->nombre_completo }}</h3>
                    <p class="text-sm text-gray-600">
                        {{ $paciente->identificacion }} ·
                        {{ $paciente->telefono }} ·
                        Convenio: <span class="font-medium">{{ $paciente->portafolio?->nombre_convenio ?? 'No especificado' }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Citas encontradas --}}
        @if($citas->count() > 0)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">Citas para hoy ({{ now()->format('d/m/Y') }})</h2>
            </div>

            <div class="divide-y divide-gray-50">
                @foreach($citas as $cita)
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

                        {{-- Info cita --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-lg font-bold text-gray-900">{{ $cita->hora }}</span>
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    {{ $cita->estado?->nombre === 'Confirmada' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $cita->estado?->nombre }}
                                </span>
                                <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ $cita->modalidad?->nombre }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-700 font-medium">{{ $cita->servicio?->nombre }}</p>
                            <p class="text-sm text-gray-600">Dr(a). {{ $cita->medico?->usuario?->nombre ?? 'No asignado' }}</p>
                            <p class="text-sm text-gray-500">{{ $cita->medico?->especialidad }}</p>
                        </div>

                        {{-- Precio y acciones --}}
                        <div class="flex flex-col items-end gap-3">
                            @if($cita->pago)
                                <div class="text-right">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        PAGADO
                                    </span>
                                    <p class="text-lg font-bold text-gray-900 mt-1">${{ number_format($cita->pago->monto, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500">{{ $cita->pago->metodo_pago }} · {{ $cita->pago->fecha_pago?->format('H:i') }}</p>
                                </div>
                                <a href="{{ route('gestor.recepcion.llegada', $cita) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Confirmar Llegada
                                </a>
                            @else
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Valor a cobrar:</p>
                                    <p class="text-2xl font-bold text-gray-900">${{ number_format($cita->precio_sugerido ?? 0, 0, ',', '.') }}</p>
                                    @if($paciente->portafolio?->nombre_convenio !== 'Particular')
                                        <p class="text-xs text-gray-500">Convenio: {{ $paciente->portafolio?->nombre_convenio }}</p>
                                    @endif
                                </div>
                                <div class="flex gap-2">
                                    @if($paciente->portafolio?->nombre_convenio !== 'Particular')
                                        <a href="{{ route('gestor.recepcion.llegada', $cita) }}"
                                           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition"
                                           onclick="return confirm('¿Confirmar llegada con convenio {{ $paciente->portafolio?->nombre_convenio }}?')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                                            </svg>
                                            Confirmar (Convenio)
                                        </a>
                                    @endif
                                    <a href="{{ route('gestor.recepcion.pago', $cita) }}"
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Registrar Pago
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <h3 class="font-medium text-amber-900">No hay citas para hoy</h3>
            <p class="text-sm text-amber-700 mt-1">El paciente no tiene citas agendadas para el día de hoy.</p>
            <a href="{{ route('gestor.citas.create') }}" class="inline-block mt-3 text-sm text-blue-600 hover:text-blue-800 underline">
                Agendar nueva cita →
            </a>
        </div>
        @endif
    </div>
    @endif

    {{-- Instrucciones --}}
    @if(!isset($paciente))
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
        <h3 class="font-medium text-gray-800 mb-4">Proceso de recepción:</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex items-start gap-3">
                <span class="w-8 h-8 bg-gray-900 text-white rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0">1</span>
                <div>
                    <p class="font-medium text-gray-700">Buscar paciente</p>
                    <p class="text-sm text-gray-500">Ingresa la cédula del paciente</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-8 h-8 bg-gray-900 text-white rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0">2</span>
                <div>
                    <p class="font-medium text-gray-700">Verificar cita</p>
                    <p class="text-sm text-gray-500">Confirma la hora y el médico</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-8 h-8 bg-gray-900 text-white rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0">3</span>
                <div>
                    <p class="font-medium text-gray-700">Cobrar / Confirmar</p>
                    <p class="text-sm text-gray-500">Registra el pago o confirma llegada</p>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
