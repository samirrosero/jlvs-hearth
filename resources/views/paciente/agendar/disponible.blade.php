@extends('paciente.layouts.app')

@section('title', 'Horarios Disponibles')
@section('page-title', 'Horarios Disponibles')

@section('content')

<div class="max-w-3xl mx-auto space-y-6" x-data="{ horaSeleccionada: null, horaDisplay: '' }">

    {{-- Resumen de la búsqueda --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Mostrando cupos para</p>
            <p class="text-lg font-bold text-gray-800">{{ $especialidad }}</p>
            <p class="text-sm text-gray-500">
                {{ $fecha->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
            </p>
        </div>
        <a href="{{ route('paciente.agendar') }}"
           class="inline-flex items-center gap-2 text-sm font-bold transition"
           style="color: var(--color-primario)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Cambiar búsqueda
        </a>
    </div>

    {{-- Grid de franjas horarias --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
            <h3 class="font-bold text-gray-800">Selecciona un horario</h3>
            <p class="text-sm text-gray-400 mt-0.5">Toca el horario que prefieras — el médico será asignado automáticamente</p>
        </div>

        <div class="p-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach ($slots as $slot)
                <button type="button"
                        @click="horaSeleccionada = '{{ $slot['hora'] }}'; horaDisplay = '{{ $slot['hora_display'] }}'"
                        :style="horaSeleccionada === '{{ $slot['hora'] }}'
                            ? 'background-color: var(--color-primario); border-color: var(--color-primario); color: white;'
                            : ''"
                        class="relative py-4 px-3 rounded-xl border-2 border-gray-200 bg-gray-50 text-gray-700
                               text-center transition font-bold text-base active:scale-95
                               hover:border-gray-300">
                    {{ $slot['hora_display'] }}
                    @if ($slot['cupos'] <= 2)
                        <span class="absolute top-1 right-1.5 text-[9px] font-bold"
                              :class="horaSeleccionada === '{{ $slot['hora'] }}' ? 'text-white/70' : 'text-amber-500'">
                            ● Últimos cupos
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Panel de confirmación (aparece al seleccionar una franja) --}}
    <div x-show="horaSeleccionada !== null"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="rounded-2xl shadow-lg p-6"
         style="background-color: var(--color-primario)">

        <h3 class="text-white font-bold text-lg mb-1">Confirmar cita</h3>
        <p class="text-sm mb-5" style="color: rgba(255,255,255,0.75)">Revisa los datos y confirma tu cita.</p>

        <div class="bg-white/15 rounded-xl p-4 mb-5 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="font-medium" style="color: rgba(255,255,255,0.75)">Especialidad</span>
                <span class="text-white font-bold">{{ $especialidad }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="font-medium" style="color: rgba(255,255,255,0.75)">Fecha</span>
                <span class="text-white font-bold">{{ $fecha->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="font-medium" style="color: rgba(255,255,255,0.75)">Hora</span>
                <span class="text-white font-bold" x-text="horaDisplay"></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="font-medium" style="color: rgba(255,255,255,0.75)">Modalidad</span>
                <span class="text-white font-bold">
                    {{ $modalidades->firstWhere('id', $modalidad_id)?->nombre ?? '—' }}
                </span>
            </div>
            <div class="flex justify-between text-sm pt-1 border-t border-white/20">
                <span class="font-medium" style="color: rgba(255,255,255,0.75)">Médico asignado</span>
                <span class="text-white font-bold italic">Se asigna al confirmar</span>
            </div>
        </div>

        {{-- Valor a pagar --}}
        <div class="bg-white/10 rounded-xl px-4 py-3 mb-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium" style="color: rgba(255,255,255,0.75)">Valor a cancelar en ventanilla</p>
                @if($precio !== null)
                    <p class="text-white font-bold text-xl">${{ number_format($precio, 0, ',', '.') }}</p>
                    @if($portafolio)
                        <p class="text-[10px] mt-0.5" style="color: rgba(255,255,255,0.65)">{{ $portafolio->nombre_convenio }}</p>
                    @endif
                @elseif($portafolio)
                    <p class="text-white font-bold">Consultar en ventanilla</p>
                    <p class="text-[10px] mt-0.5" style="color: rgba(255,255,255,0.65)">Tarifa {{ $portafolio->nombre_convenio }} no configurada aún</p>
                @else
                    <p class="text-white font-bold">Consultar en ventanilla</p>
                    <p class="text-[10px] mt-0.5" style="color: rgba(255,255,255,0.65)">
                        Actualiza tu cobertura en
                        <a href="{{ route('paciente.perfil') }}" class="underline">Mi Perfil</a>
                        para ver el precio.
                    </p>
                @endif
            </div>
            <svg class="w-8 h-8 text-white/30 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>

        <form method="POST" action="{{ route('paciente.agendar.reservar') }}">
            @csrf
            <input type="hidden" name="especialidad"  value="{{ $especialidad }}">
            <input type="hidden" name="fecha"         value="{{ $fecha->toDateString() }}">
            <input type="hidden" name="modalidad_id"  value="{{ $modalidad_id }}">
            <input type="hidden" name="hora"          :value="horaSeleccionada">

            <button type="submit"
                    class="w-full py-4 bg-white font-bold text-lg rounded-xl
                           hover:bg-white/90 active:scale-[.98] transition flex items-center justify-center gap-2"
                    style="color: var(--color-primario)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Confirmar mi cita
            </button>
        </form>
    </div>

    @if ($slots->isEmpty())
        <div class="text-center py-12 text-gray-400 italic">
            No hay cupos disponibles para esta fecha.
        </div>
    @endif

</div>

@endsection
