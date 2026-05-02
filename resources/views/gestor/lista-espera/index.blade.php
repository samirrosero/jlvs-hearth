@extends('gestor.layouts.app')

@section('title', 'Lista de espera')
@section('page-title', 'Lista de espera')

@section('content')
<div class="space-y-5"
     x-data="listaEspera()"
     x-init="cargar()">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h2 class="text-xl font-bold text-gray-900">Lista de espera</h2>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-amber-100 text-amber-700 px-3 py-1.5 rounded-full">
            <span x-text="total"></span> en espera
        </span>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4">
        <div class="flex flex-wrap items-end gap-3">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select x-model="filtroEstado" @change="cargar()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Todos</option>
                    <option value="esperando">Esperando</option>
                    <option value="asignado">Asignado</option>
                    <option value="descartado">Descartado</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha solicitada</label>
                <input type="date" x-model="filtroFecha" @change="cargar()"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cédula / Documento</label>
                <input type="text" x-model="filtroCedula" @input.debounce.400ms="cargar()"
                       placeholder="Buscar por cédula"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none w-44">
            </div>

            <button type="button" @click="filtroEstado=''; filtroFecha=''; filtroCedula=''; cargar()"
                    class="text-sm text-gray-500 hover:text-gray-800 font-medium px-3 py-2">
                Limpiar
            </button>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Cargando --}}
        <div x-show="cargando" class="flex items-center justify-center py-16 gap-3 text-gray-400" style="display:none">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm">Cargando...</span>
        </div>

        {{-- Sin resultados --}}
        <div x-show="!cargando && registros.length === 0" class="flex flex-col items-center justify-center py-16 text-gray-400" style="display:none">
            <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm font-medium text-gray-500">No hay registros en lista de espera</p>
        </div>

        {{-- Tabla --}}
        <div x-show="!cargando && registros.length > 0" style="display:none" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Paciente</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Servicio</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha solicitada</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="reg in registros" :key="reg.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-800 leading-snug" x-text="reg.paciente?.nombre_completo ?? '—'"></p>
                                <p class="text-xs text-gray-400" x-text="reg.paciente?.identificacion ?? ''"></p>
                            </td>
                            <td class="px-4 py-3.5 text-gray-700" x-text="reg.servicio?.nombre ?? '—'"></td>
                            <td class="px-4 py-3.5 text-gray-700 whitespace-nowrap tabular-nums" x-text="formatFecha(reg.fecha_solicitada)"></td>
                            <td class="px-4 py-3.5 text-gray-500 text-xs max-w-[200px] truncate" x-text="reg.notas ?? '—'"></td>
                            <td class="px-4 py-3.5">
                                <span :class="{
                                        'bg-amber-100 text-amber-700': reg.estado === 'esperando',
                                        'bg-green-100 text-green-700': reg.estado === 'asignado',
                                        'bg-gray-100 text-gray-500':   reg.estado === 'descartado',
                                    }"
                                      class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold capitalize"
                                      x-text="reg.estado"></span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div x-show="reg.estado === 'esperando'" class="flex items-center gap-2" style="display:none">
                                    <button type="button"
                                            @click="cambiarEstado(reg.id, 'descartado')"
                                            class="text-xs text-gray-500 hover:text-red-600 font-medium transition-colors">
                                        Descartar
                                    </button>
                                </div>
                                <span x-show="reg.estado !== 'esperando'" class="text-xs text-gray-400" style="display:none">—</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function listaEspera() {
    return {
        registros:    [],
        cargando:     false,
        filtroEstado:  '',
        filtroFecha:   '',
        filtroCedula:  '',

        get total() {
            return this.registros.filter(r => r.estado === 'esperando').length;
        },

        async cargar() {
            this.cargando = true;
            try {
                let url = '/lista-espera?';
                if (this.filtroEstado)  url += 'estado='         + this.filtroEstado          + '&';
                if (this.filtroFecha)   url += 'fecha='          + this.filtroFecha            + '&';
                if (this.filtroCedula)  url += 'identificacion=' + encodeURIComponent(this.filtroCedula) + '&';
                const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
                this.registros = await res.json();
            } catch (e) {
                this.registros = [];
            } finally {
                this.cargando = false;
            }
        },

        async cambiarEstado(id, estado) {
            const msg = estado === 'descartado' ? '¿Descartar este registro de la lista de espera?' : null;
            if (msg && !confirm(msg)) return;
            try {
                await fetch('/lista-espera/' + id, {
                    method:  'PATCH',
                    headers: {
                        'Accept':       'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ estado }),
                });
                await this.cargar();
            } catch (e) {}
        },

        formatFecha(f) {
            if (!f) return '—';
            const [y, m, d] = f.split('-');
            return `${d}/${m}/${y}`;
        },
    };
}
</script>
@endpush
