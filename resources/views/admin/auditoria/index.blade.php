@extends('admin.layouts.app')
@section('title', 'Auditoría')
@section('page-title', 'Registro de Auditoría')

@section('content')
<div x-data="auditoria()" x-init="cargar()">

    {{-- ── Filtros ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Acción</label>
                <select x-model="filtros.accion" @change="cargar()"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900">
                    <option value="">Todas</option>
                    <option value="ver">Ver</option>
                    <option value="crear">Crear</option>
                    <option value="actualizar">Actualizar</option>
                    <option value="eliminar">Eliminar</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Entidad</label>
                <select x-model="filtros.modelo" @change="cargar()"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900">
                    <option value="">Todas</option>
                    <option value="HistoriaClinica">Historia Clínica</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                <input type="date" x-model="filtros.desde" @change="cargar()"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                <input type="date" x-model="filtros.hasta" @change="cargar()"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900">
            </div>

            <div class="flex items-end">
                <button @click="limpiar()"
                    class="w-full px-4 py-2 text-sm border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                    Limpiar filtros
                </button>
            </div>
        </div>
    </div>

    {{-- ── Tabla ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Estado de carga --}}
        <div x-show="cargando" class="flex items-center justify-center py-16 text-gray-400 text-sm gap-2">
            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Cargando registros...
        </div>

        {{-- Tabla --}}
        <div x-show="!cargando" x-cloak>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-left">Fecha</th>
                        <th class="px-5 py-3 text-left">Usuario</th>
                        <th class="px-5 py-3 text-left">Acción</th>
                        <th class="px-5 py-3 text-left">Entidad</th>
                        <th class="px-5 py-3 text-left">ID</th>
                        <th class="px-5 py-3 text-left">IP</th>
                        <th class="px-5 py-3 text-left"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-if="logs.length === 0">
                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm font-medium">Sin registros</p>
                                <p class="text-xs mt-1">No hay actividad para los filtros seleccionados</p>
                            </td>
                        </tr>
                    </template>
                    <template x-for="log in logs" :key="log.id">
                        <tr class="hover:bg-gray-50 transition cursor-pointer" @click="verDetalle(log)">
                            <td class="px-5 py-3 text-gray-600 whitespace-nowrap" x-text="formatFecha(log.created_at)"></td>
                            <td class="px-5 py-3">
                                <span class="font-medium text-gray-800" x-text="log.usuario ? (log.usuario.nombre + ' ' + (log.usuario.apellidos ?? '')) : '—'"></span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                      :class="badgeAccion(log.accion)"
                                      x-text="log.accion"></span>
                            </td>
                            <td class="px-5 py-3 text-gray-600" x-text="formatModelo(log.modelo)"></td>
                            <td class="px-5 py-3 text-gray-400 font-mono text-xs" x-text="'#' + log.modelo_id"></td>
                            <td class="px-5 py-3 text-gray-400 font-mono text-xs" x-text="log.ip ?? '—'"></td>
                            <td class="px-5 py-3 text-gray-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            {{-- Paginación --}}
            <div x-show="meta && meta.last_page > 1"
                 class="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
                <span x-text="`${meta?.from ?? 0}–${meta?.to ?? 0} de ${meta?.total ?? 0} registros`"></span>
                <div class="flex gap-1">
                    <button @click="cambiarPagina(paginaActual - 1)" :disabled="paginaActual <= 1"
                        class="px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                        ←
                    </button>
                    <template x-for="p in paginas" :key="p">
                        <button @click="cambiarPagina(p)"
                            class="px-3 py-1.5 border rounded-lg transition"
                            :class="p === paginaActual
                                ? 'bg-gray-900 text-white border-gray-900'
                                : 'border-gray-200 hover:bg-gray-50'"
                            x-text="p">
                        </button>
                    </template>
                    <button @click="cambiarPagina(paginaActual + 1)" :disabled="paginaActual >= meta?.last_page"
                        class="px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                        →
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Nota legal ──────────────────────────────────────────────── --}}
    <div class="mt-5 bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-medium text-blue-800">Resolución 1995 de 1999 — Ministerio de Salud</p>
            <p class="text-xs text-blue-600 mt-0.5">
                Este registro es de solo lectura e inmutable. Documenta todos los accesos y modificaciones
                a historias clínicas, cumpliendo la normativa colombiana de trazabilidad de datos de salud.
            </p>
        </div>
    </div>

    {{-- ── Modal detalle ────────────────────────────────────────────── --}}
    <div x-show="logSeleccionado !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
         @keydown.escape.window="logSeleccionado = null">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>

            {{-- Cabecera --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-800">Detalle del registro</h3>
                <button @click="logSeleccionado = null" class="text-gray-400 hover:text-gray-600 transition text-xl leading-none">&times;</button>
            </div>

            <template x-if="logSeleccionado">
                <div class="px-6 py-5 space-y-4">

                    {{-- Badge acción --}}
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold"
                              :class="badgeAccion(logSeleccionado.accion)"
                              x-text="logSeleccionado.accion"></span>
                        <span class="text-sm text-gray-500" x-text="formatFecha(logSeleccionado.created_at)"></span>
                    </div>

                    {{-- Info principal --}}
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase">Usuario</dt>
                            <dd class="text-gray-800 mt-0.5 font-medium"
                                x-text="logSeleccionado.usuario ? (logSeleccionado.usuario.nombre + ' ' + (logSeleccionado.usuario.apellidos ?? '')) : '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase">IP</dt>
                            <dd class="text-gray-800 mt-0.5 font-mono" x-text="logSeleccionado.ip ?? '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase">Entidad</dt>
                            <dd class="text-gray-800 mt-0.5" x-text="formatModelo(logSeleccionado.modelo)"></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase">ID del registro</dt>
                            <dd class="text-gray-800 mt-0.5 font-mono" x-text="'#' + logSeleccionado.modelo_id"></dd>
                        </div>
                    </dl>

                    {{-- Campos modificados (solo en actualizar) --}}
                    <template x-if="logSeleccionado.detalles && Object.keys(logSeleccionado.detalles).length > 0">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase mb-2">Campos modificados</p>
                            <div class="bg-gray-50 rounded-xl p-3 space-y-1.5 max-h-48 overflow-y-auto">
                                <template x-for="[campo, valor] in Object.entries(logSeleccionado.detalles)" :key="campo">
                                    <div class="flex gap-2 text-xs">
                                        <span class="font-mono text-gray-500 shrink-0" x-text="campo + ':'"></span>
                                        <span class="text-gray-700 break-all" x-text="JSON.stringify(valor)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
                <button @click="logSeleccionado = null"
                    class="px-4 py-2 text-sm border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function auditoria() {
    return {
        logs:           [],
        meta:           null,
        cargando:       false,
        paginaActual:   1,
        logSeleccionado: null,
        filtros: {
            accion:  '',
            modelo:  '',
            desde:   '',
            hasta:   '',
        },

        get paginas() {
            if (!this.meta) return [];
            const total = this.meta.last_page;
            const actual = this.paginaActual;
            const rango = [];
            for (let i = Math.max(1, actual - 2); i <= Math.min(total, actual + 2); i++) {
                rango.push(i);
            }
            return rango;
        },

        async cargar() {
            this.cargando = true;
            try {
                const params = new URLSearchParams({ page: this.paginaActual });
                if (this.filtros.accion)  params.set('accion',  this.filtros.accion);
                if (this.filtros.modelo)  params.set('modelo',  this.filtros.modelo);
                if (this.filtros.desde)   params.set('desde',   this.filtros.desde);
                if (this.filtros.hasta)   params.set('hasta',   this.filtros.hasta);

                const res  = await fetch('/logs?' + params.toString(), {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                this.logs  = data.data;
                this.meta  = data;
            } catch (e) {
                console.error('Error cargando auditoría:', e);
            } finally {
                this.cargando = false;
            }
        },

        async verDetalle(log) {
            this.logSeleccionado = log;
            if (!log.detalles) {
                try {
                    const res  = await fetch(`/logs/${log.id}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    this.logSeleccionado = await res.json();
                } catch {}
            }
        },

        cambiarPagina(p) {
            if (!this.meta || p < 1 || p > this.meta.last_page) return;
            this.paginaActual = p;
            this.cargar();
        },

        limpiar() {
            this.filtros = { accion: '', modelo: '', desde: '', hasta: '' };
            this.paginaActual = 1;
            this.cargar();
        },

        formatFecha(fecha) {
            if (!fecha) return '—';
            return new Date(fecha).toLocaleString('es-CO', {
                day:    '2-digit',
                month:  '2-digit',
                year:   'numeric',
                hour:   '2-digit',
                minute: '2-digit',
            });
        },

        formatModelo(modelo) {
            const mapa = {
                HistoriaClinica: 'Historia Clínica',
            };
            return mapa[modelo] ?? modelo;
        },

        badgeAccion(accion) {
            const mapa = {
                ver:        'bg-blue-100 text-blue-700',
                crear:      'bg-green-100 text-green-700',
                actualizar: 'bg-amber-100 text-amber-700',
                eliminar:   'bg-red-100 text-red-700',
            };
            return mapa[accion] ?? 'bg-gray-100 text-gray-600';
        },
    };
}
</script>
@endpush

@endsection
