@extends('paciente.layouts.app')

@section('title', 'Pago de Telemedicina')
@section('page-title', 'Confirmar Pago')

@section('content')
<div class="max-w-2xl mx-auto" x-data="{ metodoPago: 'tarjeta' }">

    {{-- Alerta info --}}
    @if(session('info'))
    <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 text-blue-800 text-sm rounded-xl px-4 py-3 mb-6">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"/>
        </svg>
        <p>{{ session('info') }}</p>
    </div>
    @endif

    {{-- Resumen de la cita --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-4">
            <div class="flex items-center gap-3 text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider opacity-80">Cita de Telemedicina</p>
                    <p class="font-semibold">{{ $cita->servicio?->nombre ?? 'Consulta virtual' }}</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-3">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Médico:</span>
                <span class="font-medium text-gray-900">{{ $cita->medico?->usuario?->nombre ?? 'Por asignar' }}</span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Fecha:</span>
                <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($cita->fecha)->locale('es')->isoFormat('dddd D [de] MMMM YYYY') }}</span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Hora:</span>
                <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Modalidad:</span>
                <span class="px-2 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">
                    {{ $cita->modalidad?->nombre ?? 'Telemedicina' }}
                </span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Convenio:</span>
                <span class="font-medium text-gray-900">{{ $cita->paciente?->portafolio?->nombre_convenio ?? 'Particular' }}</span>
            </div>

            <hr class="my-4">

            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-700">Total a pagar:</span>
                @if($precio)
                    <span class="text-3xl font-bold text-emerald-600">${{ number_format($precio, 0, ',', '.') }}</span>
                @else
                    <span class="text-sm text-amber-600 italic">Precio no configurado</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Formulario de pago --}}
    <form action="{{ route('paciente.citas.pago.store', $cita->id) }}" method="POST" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        @csrf
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Método de pago</h3>

        {{-- Selector de método --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <label class="relative cursor-pointer">
                <input type="radio" name="metodo_pago" value="tarjeta" x-model="metodoPago" class="sr-only" checked>
                <div class="px-4 py-3 rounded-xl border-2 text-center text-sm font-medium transition"
                     :class="metodoPago === 'tarjeta' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Tarjeta
                </div>
            </label>
            <label class="relative cursor-pointer">
                <input type="radio" name="metodo_pago" value="transferencia" x-model="metodoPago" class="sr-only">
                <div class="px-4 py-3 rounded-xl border-2 text-center text-sm font-medium transition"
                     :class="metodoPago === 'transferencia' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Transferencia
                </div>
            </label>
            <label class="relative cursor-pointer">
                <input type="radio" name="metodo_pago" value="prepagada" x-model="metodoPago" class="sr-only">
                <div class="px-4 py-3 rounded-xl border-2 text-center text-sm font-medium transition"
                     :class="metodoPago === 'prepagada' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    Prepagada
                </div>
            </label>
        </div>

        {{-- Monto (oculto, se toma del precio) --}}
        <input type="hidden" name="monto" value="{{ $precio ?? 0 }}">

        {{-- Referencia (opcional) --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Referencia de pago <span class="text-gray-400 font-normal">(opcional)</span>
            </label>
            <input type="text" name="referencia"
                   placeholder="Número de transacción, comprobante, etc."
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
        </div>

        {{-- Aviso de simulación --}}
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 mb-6 flex items-start gap-2">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Este es un entorno de demostración. El pago será registrado como exitoso sin procesar una transacción real.</span>
        </div>

        {{-- Botones --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('paciente.citas') }}"
               class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-semibold rounded-lg shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Pagar @if($precio) ${{ number_format($precio, 0, ',', '.') }}@endif
            </button>
        </div>
    </form>

</div>
@endsection
