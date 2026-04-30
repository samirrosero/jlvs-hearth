@extends('paciente.layouts.app')

@section('title', 'Videollamada — ' . ($cita->servicio->nombre ?? 'Telemedicina'))
@section('page-title', 'Videollamada')

@section('content')

    <div class="max-w-lg mx-auto mt-8 space-y-6">


        <div class="text-center">
            <a href="{{ route('paciente.citas') }}"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a mis citas
            </a>
        </div>

        {{-- Tarjeta de información de la cita --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-11 h-11 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Cita por Telemedicina</p>
                    <p class="text-base font-bold text-gray-800">{{ $cita->servicio->nombre ?? 'Consulta' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm mb-5">
                <div>
                    <p class="text-xs text-gray-400">Médico</p>
                    <p class="font-semibold text-gray-800">{{ $cita->medico->usuario->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Estado</p>
                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold"
                        style="background: {{ $cita->estado->color_hex ?? '#e2e8f0' }}22; color: {{ $cita->estado->color_hex ?? '#64748b' }}">
                        {{ $cita->estado->nombre ?? '—' }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Fecha</p>
                    <p class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Hora</p>
                    <p class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($cita->hora)->format('h:i A') }}</p>
                </div>
            </div>

            {{-- Botón o aviso según si hay link --}}
            @if ($cita->link_videollamada)
                <a href="{{ $cita->link_videollamada }}" target="_blank" rel="noopener"
                    class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Unirse a la videollamada
                </a>
                <p class="text-center text-[11px] text-gray-400 mt-3">
                    Se abrirá en una nueva pestaña. Permite el acceso a cámara y micrófono cuando se solicite.
                </p>
            @else
                <div
                    class="w-full text-center text-sm text-amber-700 bg-amber-50 border border-amber-100 rounded-xl py-3 px-4">
                    El link de la videollamada aún no ha sido configurado.<br>
                    <span class="text-xs text-amber-600">Comunícate con la IPS para recibirlo.</span>
                </div>
            @endif
        </div>

        {{-- Instrucciones --}}
        @if ($cita->link_videollamada)
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-800 space-y-1.5">
                <p class="font-semibold text-blue-900 mb-2">Antes de unirte:</p>
                <p>· Asegúrate de tener buena conexión a internet.</p>
                <p>· Usa Google Chrome o Mozilla Firefox para mejor experiencia.</p>
                <p>· Permite el acceso a tu cámara y micrófono cuando el navegador lo solicite.</p>
                <p>· El médico te admitirá a la sala una vez inicie la consulta.</p>
            </div>
        @endif

    </div>

@endsection
