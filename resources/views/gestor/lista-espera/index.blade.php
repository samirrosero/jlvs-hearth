@extends('gestor.layouts.app')

@section('title', 'Lista de espera')
@section('page-title', 'Lista de espera')

@section('content')
<div class="space-y-5"
     x-data="listaEspera()"
     x-init="init()">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h2 class="text-xl font-bold text-gray-900">Lista de espera</h2>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-amber-100 text-amber-700 px-3 py-1.5 rounded-full">
            <span x-text="total"></span> en espera
        </span>
    </div>

    {{-- Banner: Slots liberados hoy --}}
    <div x-show="slotsLiberados.length > 0" style="display:none"
         class="bg-amber-50 border border-amber-300 rounded-2xl px-5 py-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-amber-800">
                    <span x-text="slotsLiberados.length"></span>
                    slot(s) disponible(s) hoy — paciente(s) no asistieron a su cita
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="slot in slotsLiberados" :key="slot.id">
                        <span class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="nombreMedico(slot) + ' · ' + formatHora(slot.hora) + ' — ' + (slot.paciente?.nombre_completo ?? 'paciente')"></span>
                        </span>
                    </template>
                </div>
            </div>
        </div>
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

        <div x-show="cargando" class="flex items-center justify-center py-16 gap-3 text-gray-400" style="display:none">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm">Cargando...</span>
        </div>

        <div x-show="!cargando && registros.length === 0" class="flex flex-col items-center justify-center py-16 text-gray-400" style="display:none">
            <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm font-medium text-gray-500">No hay registros en lista de espera</p>
        </div>

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
                                            @click="abrirAsignarCita(reg)"
                                            :class="slotsLiberados.length > 0
                                                ? 'text-blue-600 hover:text-blue-800'
                                                : 'text-gray-400 cursor-default'"
                                            :title="slotsLiberados.length === 0 ? 'No hay slots liberados aún' : 'Asignar a un slot disponible'"
                                            class="text-xs font-medium transition-colors">
                                        Asignar cita
                                        <template x-if="slotsLiberados.length > 0">
                                            <span class="inline-flex items-center justify-center w-4 h-4 bg-amber-500 text-white text-[10px] font-bold rounded-full ml-1"
                                                  x-text="slotsLiberados.length"></span>
                                        </template>
                                    </button>
                                    <button type="button"
                                            @click="abrirModalDescartar(reg.id, reg.paciente?.nombre_completo ?? 'este paciente')"
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

    {{-- Modal: Confirmar descarte --}}
    <div x-show="modalDescartar" @keydown.escape.window="modalDescartar = false" style="display:none"
         class="fixed inset-0 backdrop-blur-sm bg-white/10 flex items-center justify-center z-50 p-4">
        <div @click.stop class="bg-white rounded-2xl shadow-lg max-w-sm w-full p-6 space-y-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 bg-red-100 rounded-full">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Descartar registro</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        ¿Estás seguro de que deseas descartar a
                        <span class="font-semibold text-gray-700" x-text="descartarNombre"></span>
                        de la lista de espera? Esta acción no se puede deshacer.
                    </p>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
                <button type="button" @click="modalDescartar = false"
                        class="text-gray-600 hover:text-gray-800 font-medium text-sm px-4 py-2">
                    Cancelar
                </button>
                <button type="button" @click="confirmarDescarte()"
                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm px-4 py-2 rounded-xl transition-colors">
                    Sí, descartar
                </button>
            </div>
        </div>
    </div>

    {{-- Modal: Asignar cita desde slot liberado --}}
    <div x-show="modalAsignacion" @keydown.escape.window="cerrarModal()" style="display:none"
         class="fixed inset-0 backdrop-blur-sm bg-white/10 flex items-center justify-center z-50 p-4">
        <div @click.stop class="bg-white rounded-2xl shadow-lg max-w-lg w-full p-6 space-y-4">

            <h3 class="text-lg font-bold text-gray-900">
                Asignar cita a
                <span class="text-blue-600" x-text="registroSeleccionado?.paciente?.nombre_completo ?? '—'"></span>
            </h3>

            {{-- Info del registro en espera --}}
            <div class="bg-gray-50 rounded-xl px-4 py-3 grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Cédula</p>
                    <p class="text-sm font-medium text-gray-800 mt-0.5"
                       x-text="registroSeleccionado?.paciente?.identificacion ?? '—'"></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Servicio solicitado</p>
                    <p class="text-sm font-medium text-gray-800 mt-0.5"
                       x-text="registroSeleccionado?.servicio?.nombre ?? 'No especificado'"></p>
                </div>
            </div>

            {{-- Selector de slot liberado --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Slot disponible <span class="text-red-500">*</span>
                </label>

                <template x-if="slotsLiberados.length === 0">
                    <div class="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-amber-700">
                            No hay slots liberados en este momento. El sistema marca automáticamente como "No asistió" las citas con 5 minutos de retraso.
                        </p>
                    </div>
                </template>

                <template x-if="slotsLiberados.length > 0">
                    <select x-model="slotSeleccionadoId"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">— Seleccionar slot —</option>
                        <template x-for="slot in slotsLiberados" :key="slot.id">
                            <option :value="slot.id"
                                    x-text="nombreMedico(slot) + ' · ' + formatHora(slot.hora) + (slot.servicio ? ' · ' + slot.servicio.nombre : '') + ' — Cita de: ' + (slot.paciente?.nombre_completo ?? '—')">
                            </option>
                        </template>
                    </select>
                </template>
            </div>

            {{-- Modalidad siempre presencial --}}
            <div class="flex items-center gap-2 bg-blue-50 rounded-lg px-4 py-2.5">
                <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="text-sm font-medium text-blue-700">Modalidad: Presencial</span>
            </div>

            {{-- Error --}}
            <div x-show="errorAsignacion" x-text="errorAsignacion"
                 class="text-red-600 text-sm bg-red-50 border border-red-200 rounded-lg px-3 py-2"
                 style="display:none"></div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
                <button type="button" @click="cerrarModal()"
                        class="text-gray-600 hover:text-gray-800 font-medium text-sm px-4 py-2">
                    Cancelar
                </button>
                <button type="button" @click="crearCitaDesdeEspera()"
                        :disabled="cargandoAsignacion || !slotSeleccionadoId || slotsLiberados.length === 0"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold text-sm px-4 py-2 rounded-xl transition-colors">
                    <svg x-show="cargandoAsignacion" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span x-text="cargandoAsignacion ? 'Asignando...' : 'Confirmar asignación'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function listaEspera() {
    return {
        registros:          [],
        cargando:           false,
        filtroEstado:       '',
        filtroFecha:        '',
        filtroCedula:       '',

        slotsLiberados:     [],
        slotSeleccionadoId: '',

        modalAsignacion:      false,
        registroSeleccionado: null,
        cargandoAsignacion:   false,
        errorAsignacion:      '',

        modalDescartar:   false,
        descartarId:      null,
        descartarNombre:  '',

        get total() {
            return this.registros.filter(r => r.estado === 'esperando').length;
        },

        async init() {
            await this.cargar();
            await this.cargarSlotsLiberados();
            // Refresca slots cada 30 s para detectar nuevos no-shows
            setInterval(() => this.cargarSlotsLiberados(), 30000);
        },

        async cargar() {
            this.cargando = true;
            try {
                let url = '/lista-espera?';
                if (this.filtroEstado)  url += 'estado='         + this.filtroEstado                          + '&';
                if (this.filtroFecha)   url += 'fecha='          + this.filtroFecha                           + '&';
                if (this.filtroCedula)  url += 'identificacion=' + encodeURIComponent(this.filtroCedula)      + '&';
                const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
                this.registros = await res.json();
            } catch (e) {
                this.registros = [];
            } finally {
                this.cargando = false;
            }
        },

        async cargarSlotsLiberados() {
            try {
                const res = await fetch('/citas/liberadas-hoy', { headers: { 'Accept': 'application/json' } });
                if (res.ok) this.slotsLiberados = await res.json();
            } catch (e) {}
        },

        async abrirAsignarCita(registro) {
            this.registroSeleccionado = registro;
            this.slotSeleccionadoId   = '';
            this.errorAsignacion      = '';
            this.modalAsignacion      = true;
        },

        cerrarModal() {
            this.modalAsignacion      = false;
            this.registroSeleccionado = null;
            this.slotSeleccionadoId   = '';
            this.errorAsignacion      = '';
        },

        async crearCitaDesdeEspera() {
            if (!this.registroSeleccionado) return;

            const slot = this.slotsLiberados.find(s => s.id == this.slotSeleccionadoId);
            if (!slot) {
                this.errorAsignacion = 'Selecciona un slot disponible.';
                return;
            }

            this.cargandoAsignacion = true;
            this.errorAsignacion    = '';

            try {
                const resCita = await fetch('/citas', {
                    method:  'POST',
                    headers: {
                        'Accept':       'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        paciente_id:  this.registroSeleccionado.paciente_id,
                        medico_id:    slot.medico_id,
                        servicio_id:  this.registroSeleccionado.servicio_id || slot.servicio_id,
                        modalidad_id: 1, // presencial siempre
                        estado_id:    1, // pendiente
                        fecha:        slot.fecha,
                        hora:         (slot.hora ?? '').substring(0, 5),
                    }),
                });

                if (!resCita.ok) {
                    const data = await resCita.json();
                    this.errorAsignacion = data.message || 'Error al crear la cita.';
                    return;
                }

                const cita = await resCita.json();

                const resEspera = await fetch('/lista-espera/' + this.registroSeleccionado.id, {
                    method:  'PATCH',
                    headers: {
                        'Accept':       'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ estado: 'asignado', cita_id: cita.id }),
                });

                if (!resEspera.ok) {
                    this.errorAsignacion = 'Error al actualizar lista de espera.';
                    return;
                }

                this.cerrarModal();
                window.location.href = '/gestor/recepcion/citas/' + cita.id + '/pago';
            } catch (e) {
                this.errorAsignacion = 'Error al procesar la asignación. Intenta de nuevo.';
            } finally {
                this.cargandoAsignacion = false;
            }
        },

        abrirModalDescartar(id, nombre) {
            this.descartarId     = id;
            this.descartarNombre = nombre;
            this.modalDescartar  = true;
        },

        async confirmarDescarte() {
            this.modalDescartar = false;
            await this.cambiarEstado(this.descartarId, 'descartado');
            this.descartarId     = null;
            this.descartarNombre = '';
        },

        async cambiarEstado(id, estado) {
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

        nombreMedico(slot) {
            return slot?.medico?.usuario?.nombre
                ?? slot?.medico?.usuario?.name
                ?? 'Dr. —';
        },

        formatHora(h) {
            if (!h) return '—';
            return (h ?? '').substring(0, 5);
        },

        formatFecha(f) {
            if (!f) return '—';
            const [y, m, d] = f.substring(0, 10).split('-');
            return `${d}/${m}/${y}`;
        },
    };
}
</script>
@endpush
