@extends('paciente.layouts.app')

@section('title', 'Resultados de Citas')
@section('page-title', 'Resultados de Citas')

@section('content')

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
                    {{-- Especialidad: Negro y menos negrilla --}}
                    <h3 class="text-black font-bold text-sm uppercase leading-tight pr-6">
                        {{ $especialidad }}
                    </h3>
                    <p class="text-gray-500 font-medium text-xs">
                        {{ $fecha->locale('es')->isoFormat('dddd, D MMMM') }}
                    </p>
                    {{-- Hora: Resaltada --}}
                    <p class="text-gray-900 font-black text-3xl py-0.5">
                        {{ $slot['hora_display'] }}
                    </p>

                    <div class="border-t border-gray-100 pt-1.5">
                        {{-- Modalidad: Negro y peso normal --}}
                        <p class="text-[11px] font-medium text-black uppercase">
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
            {{-- Fondo del modal con el color OKLCH de fondo si fuera necesario, pero lo mantenemos blanco para que resalten los textos --}}
            <div class="relative transform overflow-hidden rounded-2xl bg-white px-6 py-8 text-center shadow-xl transition-all w-full max-w-xs border border-gray-100">
                
                <div class="mb-4 text-center">
                    <h2 class="font-black text-xl tracking-tight" style="color: oklch(0.25 0.05 267.26)">Confirmar Cita</h2>
                    <div class="w-12 h-1 bg-yellow-400 mx-auto mt-1"></div>
                </div>

                <p class="text-gray-500 text-sm mb-6 leading-tight italic">¿Estás seguro que deseas agendar esta cita médica?</p>

                {{-- Datos del Paciente y Cita --}}
                <div class="text-gray-700 space-y-1 mb-6 text-sm">
                    <p class="font-bold uppercase text-gray-900 border-b border-gray-50 pb-1 mb-2">
                        {{ Auth::user()->name ?? Auth::user()->nombre ?? 'PACIENTE' }}
                    </p>
                    <p class="text-black font-medium uppercase">{{ $especialidad }}</p>
                    <p class="text-black font-normal italic">
                        Modalidad: {{ $modalidades->firstWhere('id', $modalidad_id)?->nombre ?? '—' }}
                    </p>
                    <p class="text-gray-600">{{ $fecha->locale('es')->isoFormat('dddd, D MMMM YYYY') }}</p>
                    {{-- Hora Resaltada en Modal --}}
                    <p class="font-black text-4xl text-gray-900 mt-2" x-text="horaDisplay"></p>
                </div>

                {{-- VALOR A PAGAR: Centrado y con color de fondo más suave --}}
            <div class="rounded-2xl px-4 py-4 mb-8 flex flex-col items-center justify-center text-center shadow-sm" 
                 style="background-color: oklch(0.4656 0.06 270);"> {{-- Color ajustado para no ser tan oscuro --}}
                
                <p class="text-[10px] font-medium text-gray-300 uppercase tracking-widest mb-1">Valor a cancelar en ventanilla</p>
                
                <div class="w-full">
                    @if(isset($precio) && $precio !== null)
                        <p class="text-white font-black text-3xl">${{ number_format($precio, 0, ',', '.') }}</p>
                        @if(isset($portafolio))
                            <p class="text-[10px] mt-0.5" style="color: rgba(255,255,255,0.65)">{{ $portafolio->nombre_convenio }}</p>
                        @endif
                    @elseif(isset($portafolio))
                        <p class="text-white font-bold text-lg">Consultar en ventanilla</p>
                        <p class="text-[10px] mt-0.5" style="color: rgba(255,255,255,0.65)">Tarifa {{ $portafolio->nombre_convenio }} no configurada aún</p>
                    @else
                        <p class="text-white font-bold text-lg leading-tight">Consultar en ventanilla</p>
                        <p class="text-[10px] mt-1" style="color: rgba(255,255,255,0.65)">
                            Actualiza tu cobertura en <a href="{{ route('paciente.perfil') }}" class="underline hover:text-white">Mi Perfil</a>
                        </p>
                    @endif
                </div>
            </div>

                {{-- Botones --}}
                <div class="flex flex-col gap-3">
                    <form method="POST" action="{{ route('paciente.agendar.reservar') }}">
                        @csrf
                        <input type="hidden" name="paciente_nombre" value="{{ Auth::user()->name ?? Auth::user()->nombre }}">
                        <input type="hidden" name="especialidad" value="{{ $especialidad }}">
                        <input type="hidden" name="fecha" value="{{ $fecha->toDateString() }}">
                        <input type="hidden" name="modalidad_id" value="{{ $modalidad_id }}">
                        <input type="hidden" name="hora" :value="horaSeleccionada">

                        {{-- BOTÓN AGENDAR: Con efecto Hover para aclarar el color --}}
                    <button type="submit" 
                            class="btn-confirmar w-full text-white py-4 rounded-full font-extrabold uppercase text-xs shadow-xl transition-all duration-300 active:scale-95">
                        Agendar Cita
                    </button>
                </form>

                <button @click="openModal = false" class="w-full bg-gray-100 text-gray-500 py-3 rounded-full font-bold uppercase text-xs hover:bg-gray-200 transition">
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

<style>
    /* Estilo base del botón usando tu color OKLCH */
    .btn-confirmar {
        background-color: oklch(0.2 0.08 271.72);
    }
    
    /* Efecto al pasar el cursor (Hover): Se aclara la luminosidad del OKLCH */
    .btn-confirmar:hover {
        background-color: oklch(0.3665 0.0464 289.69);
        cursor: pointer;
    }

    [x-cloak] { display: none !important; }
</style>

@endsection