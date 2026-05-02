@extends('gestor.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Mi Dashboard')

@section('content')

{{-- ── Saludo + fecha ───────────────────────────────────────────────── --}}
@php
    $hora  = now()->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
    $nombreGestor = auth()->user()->name ?? 'Gestor';
@endphp

<div class="space-y-6">
    {{-- Alertas --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
            <button type="button" class="ml-auto text-green-600 hover:text-green-800" onclick="this.parentElement.remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
            <button type="button" class="ml-auto text-red-600 hover:text-red-800" onclick="this.parentElement.remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Encabezado con saludo y citas hoy --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">{{ $saludo }}, {{ $nombreGestor }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
        </div>
        <div class="bg-blue-50 rounded-2xl p-6 text-center border border-blue-100">
            <p class="text-4xl font-bold text-blue-700">{{ $citasHoy ?? 0 }}</p>
            <p class="text-sm text-blue-600 font-medium mt-1">Citas hoy</p>
        </div>
    </div>

    {{-- ── Estadísticas ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Citas Hoy --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="bg-blue-50 rounded-xl p-3 text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900">{{ $citasHoy ?? 0 }}</p>
                    <p class="text-sm text-gray-500 font-medium">Citas hoy</p>
                </div>
            </div>
        </div>

        {{-- Citas Pendientes --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="bg-amber-50 rounded-xl p-3 text-amber-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900">{{ $citasPendientes ?? 0 }}</p>
                    <p class="text-sm text-gray-500 font-medium">Pendientes</p>
                </div>
            </div>
        </div>

        {{-- Total Pacientes --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="bg-emerald-50 rounded-xl p-3 text-emerald-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalPacientes ?? 0 }}</p>
                    <p class="text-sm text-gray-500 font-medium">Pacientes</p>
                </div>
            </div>
        </div>

        {{-- Total Médicos --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="bg-purple-50 rounded-xl p-3 text-purple-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalMedicos ?? 0 }}</p>
                    <p class="text-sm text-gray-500 font-medium">Médicos</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Acciones rápidas ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Agendar Cita --}}
        <button class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-left hover:shadow-lg hover:border-purple-200 transition-all cursor-pointer"
                data-bs-toggle="modal" data-bs-target="#modalAgendar">
            <div class="flex items-center gap-4">
                <div class="bg-purple-50 rounded-xl p-3 text-purple-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-gray-900">Agendar Cita</p>
                    <p class="text-sm text-gray-500">Nuevos pacientes</p>
                </div>
            </div>
        </button>

        {{-- Reprogramar --}}
        <button class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-left hover:shadow-lg hover:border-purple-200 transition-all cursor-pointer"
                data-bs-toggle="modal" data-bs-target="#modalReprogramar">
            <div class="flex items-center gap-4">
                <div class="bg-purple-50 rounded-xl p-3 text-purple-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-gray-900">Reprogramar</p>
                    <p class="text-sm text-gray-500">Cambio de horario</p>
                </div>
            </div>
        </button>

        {{-- Cancelar --}}
        <button class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-left hover:shadow-lg hover:border-red-200 transition-all cursor-pointer"
                data-bs-toggle="modal" data-bs-target="#modalCancelar">
            <div class="flex items-center gap-4">
                <div class="bg-red-50 rounded-xl p-3 text-red-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-gray-900">Cancelar Cita</p>
                    <p class="text-sm text-gray-500">Anulación de registro</p>
                </div>
            </div>
        </button>
    </div>

    {{-- ── Próximas citas ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100">
            <h3 class="font-bold text-gray-900 flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-blue-600 inline-block"></span>
                Próximas Citas ({{ isset($proximasCitas) ? $proximasCitas->count() : 0 }})
            </h3>
            <button class="text-sm text-blue-600 hover:text-blue-800 font-semibold">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filtrar
            </button>
        </div>

        @if(isset($proximasCitas) && $proximasCitas->isNotEmpty())
            <div class="divide-y divide-gray-100">
                @foreach($proximasCitas as $cita)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors flex items-center gap-4">
                        {{-- Hora --}}
                        <div class="w-20 flex-shrink-0 text-center">
                            <p class="text-2xl font-bold text-blue-700">
                                {{ \Carbon\Carbon::parse($cita->fecha . ' ' . $cita->hora)->format('H:i') }}
                            </p>
                            <p class="text-xs text-gray-500 font-medium">
                                {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m') }}
                            </p>
                        </div>

                        {{-- Paciente + servicio --}}
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 truncate">
                                {{ $cita->paciente->nombre ?? 'N/A' }} ({{ $cita->paciente->numero_documento ?? '' }})
                            </p>
                            <p class="text-sm text-gray-600 truncate">
                                {{ $cita->servicio->nombre ?? 'Sin servicio' }}
                                @if(isset($cita->medico))
                                    • Dr. {{ $cita->medico->usuario->name ?? 'N/A' }}
                                @endif
                            </p>
                        </div>

                        {{-- Estado --}}
                        @php
                            $estadoColor = match($cita->estado->nombre ?? '') {
                                'Confirmada' => 'bg-green-100 text-green-700',
                                'Pendiente' => 'bg-amber-100 text-amber-700',
                                'Cancelada' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700'
                            };
                        @endphp
                        <span class="hidden sm:inline-flex text-xs px-3 py-1.5 rounded-full font-semibold flex-shrink-0 {{ $estadoColor }}">
                            {{ $cita->estado->nombre ?? '—' }}
                        </span>

                        {{-- Acciones --}}
                        <div class="flex-shrink-0 flex gap-2">
                            <button class="bg-blue-50 text-blue-600 hover:bg-blue-100 p-2 rounded-lg transition" title="Editar"
                                    onclick="openReprogramarModal({{ $cita->id }})">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button class="bg-red-50 text-red-600 hover:bg-red-100 p-2 rounded-lg transition" title="Cancelar"
                                    onclick="openCancelarModal({{ $cita->id }})">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-16 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-600 font-semibold">No hay citas próximas</p>
                <p class="text-sm text-gray-500 mt-1">Las citas aparecerán aquí cuando sean agendadas</p>
            </div>
        @endif
    </div>
</div>

{{-- ── MODALES ──────────────────────────────────────────────────────── --}}

{{-- Modal Agendar --}}
<div class="modal fade" id="modalAgendar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header bg-gradient-to-r from-purple-600 to-purple-700 text-white border-0">
                <h5 class="modal-title font-bold text-lg">
                    <svg class="w-5 h-5 inline mr-2 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Agendar Nueva Cita
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gestor.citas.agendar') }}" method="POST">
                @csrf
                <div class="modal-body p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Documento del Paciente</label>
                            <input type="text" name="paciente_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="CC o TI..." required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Servicio</label>
                            <select name="servicio_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                                <option value="">Seleccione un servicio...</option>
                                <option value="1">Medicina General</option>
                                <option value="2">Odontología</option>
                                <option value="3">Pediatría</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Médico</label>
                            <select name="medico_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                                <option value="">Seleccione un médico...</option>
                                <option value="1">Dr. Juan Pérez García</option>
                                <option value="2">Dra. María López Rodríguez</option>
                                <option value="3">Dr. Carlos Martínez</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Fecha y Hora</label>
                            <input type="datetime-local" name="fecha_hora" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-gray-50 border-t border-gray-100">
                    <button type="button" class="px-5 py-2.5 text-gray-700 hover:text-gray-900 font-semibold transition" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold transition">Agendar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Reprogramar --}}
<div class="modal fade" id="modalReprogramar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header bg-gradient-to-r from-purple-600 to-purple-700 text-white border-0">
                <h5 class="modal-title font-bold text-lg">
                    <svg class="w-5 h-5 inline mr-2 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Reprogramar Cita
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="reprogramarForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Nueva Fecha y Hora</label>
                            <input type="datetime-local" name="fecha_hora" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Motivo del Cambio</label>
                            <textarea name="motivo" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Explique por qué se reprograma..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-gray-50 border-t border-gray-100">
                    <button type="button" class="px-5 py-2.5 text-gray-700 hover:text-gray-900 font-semibold transition" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold transition">Reprogramar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Cancelar --}}
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-body p-8 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-2h-2m2 0h2"/>
                    </svg>
                </div>
                <h4 class="text-2xl font-bold text-gray-900 mb-2">¿Cancelar Cita?</h4>
                <p class="text-gray-600 mb-6">Esta acción no se puede deshacer. La cita será eliminada permanentemente.</p>
                <form id="cancelarForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex gap-3 justify-center">
                        <button type="button" class="px-6 py-2.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-semibold transition" data-bs-dismiss="modal">No, Regresar</button>
                        <button type="submit" class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold transition">Sí, Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openReprogramarModal(citaId) {
    document.getElementById('reprogramarForm').action = `/citas/reprogramar/${citaId}`;
    new bootstrap.Modal(document.getElementById('modalReprogramar')).show();
}

function openCancelarModal(citaId) {
    document.getElementById('cancelarForm').action = `/citas/cancelar/${citaId}`;
    new bootstrap.Modal(document.getElementById('modalCancelar')).show();
}
</script>

@endsection
