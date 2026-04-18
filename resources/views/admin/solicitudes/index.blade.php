@extends('admin.layouts.app')
@section('title', 'Solicitudes de personal')
@section('page-title', 'Solicitudes de personal')

@section('content')
<div class="space-y-5" x-data="{ tab: 'medico', rechazarId: null, obs: '' }">

    {{-- ── Tabs por rol ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex border-b border-gray-100">
            @php
                $roles = [
                    'medico'       => ['label' => 'Médicos',          'color' => 'emerald'],
                    'gestor_citas' => ['label' => 'Gestores de citas', 'color' => 'violet'],
                    'administrador'=> ['label' => 'Administradores',  'color' => 'blue'],
                ];
            @endphp
            @foreach($roles as $rol => $info)
            @php $cnt = $solicitudes->get($rol, collect())->count(); @endphp
            <button type="button"
                    class="flex-1 px-4 py-3.5 text-sm font-semibold transition border-b-2"
                    :class="tab==='{{ $rol }}'
                        ? 'text-gray-900 border-gray-900'
                        : 'text-gray-400 border-transparent hover:text-gray-600'"
                    @click="tab='{{ $rol }}'">
                {{ $info['label'] }}
                @if($cnt > 0)
                    <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                        {{ $cnt }}
                    </span>
                @endif
            </button>
            @endforeach
        </div>

        {{-- ── Contenido por rol ──────────────────────────────────────── --}}
        @foreach($roles as $rol => $info)
        <div x-show="tab==='{{ $rol }}'" x-cloak class="p-4">
            @php $items = $solicitudes->get($rol, collect()); @endphp

            @if($items->isEmpty())
                <div class="text-center py-12 text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium">Sin solicitudes pendientes</p>
                    <p class="text-xs mt-1">No hay {{ $info['label'] }} esperando aprobación</p>
                </div>
            @else
                @if($rol === 'medico')
                <div class="mb-3 px-4 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs">
                    <strong>Nota RETHUS:</strong> Antes de aprobar médicos, verifica su registro en el sistema RETHUS del Ministerio de Salud de Colombia.
                    <a href="https://rethus.minsalud.gov.co" target="_blank" class="underline ml-1 font-semibold">Consultar RETHUS →</a>
                </div>
                @endif

                <div class="space-y-3">
                    @foreach($items as $s)
                    <div class="flex items-start gap-4 bg-gray-50 rounded-xl px-4 py-4 border border-gray-100">

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $s->nombres }} {{ $s->apellidos }}
                                </p>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-white border border-gray-200 text-gray-500">
                                    {{ $s->tipo_documento }} {{ $s->numero_documento }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $s->correo }}</p>
                            @if($s->departamento || $s->municipio)
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ implode(', ', array_filter([$s->municipio, $s->departamento])) }}
                            </p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">
                                Registrado: {{ $s->created_at->diffForHumans() }}
                            </p>
                        </div>

                        {{-- Foto documento --}}
                        @if($s->foto_url)
                        <a href="{{ $s->foto_url }}" target="_blank"
                           class="flex-shrink-0 w-14 h-14 rounded-lg border border-gray-200 overflow-hidden hover:opacity-80 transition">
                            <img src="{{ $s->foto_url }}" alt="Documento"
                                 class="w-full h-full object-cover">
                        </a>
                        @endif

                        {{-- Acciones --}}
                        <div class="flex flex-col gap-2 flex-shrink-0">
                            <form method="POST" action="{{ route('admin.solicitudes.aprobar', $s) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="px-4 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition">
                                    Aprobar
                                </button>
                            </form>
                            <button type="button"
                                    class="px-4 py-1.5 bg-white border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold rounded-lg transition"
                                    @click="rechazarId = {{ $s->id }}; obs = ''">
                                Rechazar
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ── Historial ──────────────────────────────────────────────────── --}}
    @if($aprobados->isNotEmpty() || $rechazados->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ open: false }">
        <button type="button" class="w-full flex items-center justify-between px-6 py-4 text-sm font-semibold text-gray-700"
                @click="open = !open">
            Historial (aprobados / rechazados)
            <svg class="w-4 h-4 transition" :class="open&&'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" x-cloak class="border-t border-gray-100 divide-y divide-gray-50">
            @foreach($aprobados->merge($rechazados)->sortByDesc('updated_at') as $s)
            <div class="flex items-center gap-3 px-6 py-3 text-sm">
                <span class="px-2 py-0.5 rounded-full text-xs font-bold
                    {{ $s->estado === 'aprobado' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ ucfirst($s->estado) }}
                </span>
                <span class="font-medium text-gray-700">{{ $s->nombres }} {{ $s->apellidos }}</span>
                <span class="text-gray-400 text-xs">{{ $s->rol_solicitado }}</span>
                <span class="text-gray-300 text-xs ml-auto">{{ $s->updated_at->format('d/m/Y') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- ── Modal rechazar ──────────────────────────────────────────────── --}}
<div x-show="rechazarId !== null" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
     @keydown.escape.window="rechazarId = null">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <h3 class="text-base font-bold text-gray-800 mb-1">Rechazar solicitud</h3>
        <p class="text-sm text-gray-500 mb-4">Opcionalmente indica el motivo del rechazo.</p>

        <template x-if="rechazarId !== null">
            <form :action="`/admin/solicitudes/${rechazarId}/rechazar`" method="POST">
                @csrf @method('PATCH')
                <textarea name="observaciones" x-model="obs" rows="3"
                          placeholder="Motivo del rechazo (opcional)..."
                          class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none mb-4"></textarea>
                <div class="flex gap-3 justify-end">
                    <button type="button" @click="rechazarId = null"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                        Confirmar rechazo
                    </button>
                </div>
            </form>
        </template>
    </div>
</div>
@endsection
