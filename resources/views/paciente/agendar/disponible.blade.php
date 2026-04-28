@extends('paciente.layouts.app')

@section('title', 'Resultados de Citas')
@section('page-title', 'Resultados de Citas')

@section('content')

{{-- El x-data controla la selección y apertura del Modal --}}
<div class="max-w-5xl mx-auto space-y-4" x-data="{ openModal: false, horaSeleccionada: null, horaDisplay: '' }">

    {{-- Resumen superior --}}
    <div class="px-4 py-2 flex justify-between items-center bg-white rounded-xl border border-gray-100 shadow-sm mx-2">
        <div>
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Resultados</h2>
            <p class="text-sm font-bold text-gray-700">{{ $fecha->locale('es')->isoFormat('dddd D [de] MMMM') }}</p>
        </div>
        <a href="{{ route('paciente.agendar') }}" class="text-xs font-bold text-blue-600 hover:underline">Cambiar</a>
    </div>

    {{-- Grid de 3 Columnas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 px-2">
        @foreach ($slots as $slot)
            <div @click="horaSeleccionada = '{{ $slot['hora'] }}'; horaDisplay = '{{ $slot['hora_display'] }}'; openModal = true"
                 class="bg-white rounded-xl border-2 p-3.5 cursor-pointer transition relative flex flex-col justify-between shadow-sm hover:shadow-md active:scale-95"
                 :class="horaSeleccionada === '{{ $slot['hora'] }}' ? 'border-blue-600 bg-blue-50/20' : 'border-gray-100'">
                
                <div class="absolute top-3.5 right-3.5 text-blue-300">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19,4H18V2H16V4H8V2H6V4H5C3.89,4 3,4.9 3,6V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V6A2,2 0 0,0 19,4M19,20H5V10H19V20M19,8H5V6H19V8M12,13H17V18H12V13Z" />
                    </svg>
                </div>

                <div class="space-y-0.5">
                    <h3 class="text-blue-800 font-extrabold text-sm uppercase leading-tight pr-6">
                        {{ $especialidad }}
                    </h3>
                    <p class="text-gray-500 font-medium text-xs">
                        {{ $fecha->locale('es')->isoFormat('dddd, D MMMM') }}
                    </p>
                    <p class="text-gray-800 font-black text-2xl py-0.5">
                        {{ $slot['hora_display'] }}
                    </p>

                    <div class="border-t border-gray-100 pt-1.5">
                        <p class="text-[11px] font-bold text-blue-600 uppercase">
                            {{ $modalidades->firstWhere('id', $modalidad_id)?->nombre ?? 'MODALIDAD' }}
                        </p>
                    </div>
                </div>

                @if ($slot['cupos'] <= 2)
                    <div class="mt-2.5">
                        <span class="text-[9px] bg-amber-50 text-amber-600 border border-amber-100 px-2 py-0.5 rounded font-bold uppercase inline-flex items-center gap-1">
                            ● Últimos cupos
                        </span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- MODAL DE CONFIRMACIÓN --}}
    <div x-show="openModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-black/50 transition-opacity" @click="openModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white px-6 py-8 text-center shadow-xl transition-all w-full max-w-xs border border-gray-100">
                
                {{-- Cabecera Limpia --}}
                <div class="mb-4">
                    <h2 class="text-blue-600 font-black text-xl tracking-tight">Confirmar Cita</h2>
                    <div class="w-12 h-1 bg-yellow-400 mx-auto mt-1"></div>
                </div>

                <p class="text-gray-500 text-sm mb-6 leading-tight">¿Estás seguro que deseas agendar esta cita médica?</p>

                {{-- Detalles dinámicos --}}
                <div class="text-gray-700 space-y-1 mb-8 text-sm">
                    <p class="font-bold uppercase text-gray-900 border-b border-gray-50 pb-1 mb-2">{{ Auth::user()->name ?? Auth::user()->nombre ?? 'PACIENTE SIN NOMBRE'}}</p>
                    <p class="text-blue-700 font-bold uppercase">{{ $especialidad }}</p>
                    <p class="text-gray-500 font-medium italic">Modalidad: {{ $modalidades->firstWhere('id', $modalidad_id)?->nombre ?? '—' }}</p>
                    <p>{{ $fecha->locale('es')->isoFormat('dddd, D MMMM YYYY') }}</p>
                    <p class="font-bold text-xl text-gray-900" x-text="horaDisplay"></p>
                </div>

                {{-- Botones --}}
                <div class="flex flex-col gap-3">
                    <form method="POST" action="{{ route('paciente.agendar.reservar') }}">
                        @csrf
                        <input type="hidden" name="paciente_nombre" value="{{ Auth::user()->name }}">
                        <input type="hidden" name="especialidad" value="{{ $especialidad }}">
                        <input type="hidden" name="fecha" value="{{ $fecha->toDateString() }}">
                        <input type="hidden" name="modalidad_id" value="{{ $modalidad_id }}">
                        <input type="hidden" name="hora" :value="horaSeleccionada">

                        <button type="submit" class="w-full bg-blue-700 text-white py-3 rounded-full font-bold uppercase text-sm shadow-md hover:bg-blue-800 transition">
                            Agendar Cita
                        </button>
                    </form>

                    <button @click="openModal = false" class="w-full bg-gray-200 text-gray-500 py-3 rounded-full font-bold uppercase text-sm shadow-sm hover:bg-gray-300 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

@endsection