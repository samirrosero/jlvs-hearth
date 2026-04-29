<div x-data="formularioCita()" class="...">

    <!-- Paso 1: Especialidad -->
    <select x-model="especialidad" @change="cargarMedicos()">
        <option value="">Selecciona especialidad</option>
        <template x-for="esp in especialidades" :key="esp">
            <option :value="esp" x-text="esp"></option>
        </template>
    </select>

    <!-- Paso 2: Médico -->
    <select x-model="medicoId" @change="cargarDiasDelMes()" :disabled="!especialidad">
        <option value="">Selecciona médico</option>
        <template x-for="m in medicos" :key="m.id">
            <option :value="m.id" x-text="m.usuario.nombre"></option>
        </template>
    </select>

    <!-- Paso 3: Fecha (solo habilita días disponibles) -->
    <input type="date" x-model="fecha" @change="cargarSlots()"
           :disabled="!medicoId" :min="hoy">
    <p x-show="fecha && slotsLibres.length === 0 && !cargando" class="text-sm text-red-500">
        El médico no tiene disponibilidad ese día.
    </p>

    <!-- Paso 4: Hora (slots del backend) -->
    <select x-model="hora" :disabled="slotsLibres.length === 0">
        <option value="">Selecciona hora</option>
        <template x-for="slot in slotsLibres" :key="slot">
            <option :value="slot" x-text="slot"></option>
        </template>
    </select>

    <!-- Lista de espera si no hay slots -->
    <div x-show="sinDisponibilidad" class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-2">
        <p class="text-sm text-amber-800">No hay slots disponibles para esa fecha.</p>
        <button type="button" @click="abrirListaEspera()" class="mt-2 text-sm text-amber-700 underline">
            Registrar en lista de espera
        </button>
    </div>

</div>

<script>
function formularioCita() {
    return {
        especialidades: [],
        medicos:        [],
        slotsLibres:    [],
        diasHabilitados: [],  // fechas 'YYYY-MM-DD' del mes actual con horario
        especialidad:   '',
        medicoId:       '',
        servicioId:     '',
        fecha:          '',
        hora:           '',
        cargando:       false,
        sinDisponibilidad: false,
        hoy: new Date().toISOString().split('T')[0],
        mesActual: new Date().toISOString().slice(0, 7), // 'YYYY-MM'

        async init() {
            const res = await fetch('/especialidades', { headers: headers() });
            this.especialidades = await res.json();
        },

        async cargarMedicos() {
            this.medicoId = ''; this.medicos = []; this.slotsLibres = [];
            const res = await fetch(`/medicos?especialidad=${this.especialidad}`, { headers: headers() });
            this.medicos = await res.json();
        },

        // Llamar cuando cambia el médico O cuando el usuario navega a otro mes en el calendario
        async cargarDiasDelMes(mes = null) {
            this.fecha = ''; this.slotsLibres = []; this.sinDisponibilidad = false;
            if (!this.medicoId) return;
            const m = mes ?? this.mesActual;
            const res = await fetch(`/medicos/${this.medicoId}/dias-disponibles?mes=${m}`, { headers: headers() });
            const data = await res.json();
            this.diasHabilitados = data.dias_disponibles ?? [];
        },

        // Verifica si una fecha está habilitada (para colorear el calendario si lo implementas)
        esDiaDisponible(fecha) {
            return this.diasHabilitados.includes(fecha);
        },

        async cargarSlots() {
            if (!this.fecha || !this.medicoId) return;
            this.cargando = true; this.slotsLibres = []; this.sinDisponibilidad = false;
            const url = `/citas/disponibilidad?medico_id=${this.medicoId}&fecha=${this.fecha}`
                + (this.servicioId ? `&servicio_id=${this.servicioId}` : '');
            const res  = await fetch(url, { headers: headers() });
            const data = await res.json();
            this.slotsLibres       = data.slots ?? [];
            this.sinDisponibilidad = !data.disponible;
            this.cargando = false;
        },

        abrirListaEspera() {
            // Aquí puedes abrir un modal o redirigir al formulario de lista de espera
            // con medicoId, fecha y servicioId precargados
        },
    };
}

function headers() {
    return {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    };
}
</script>

<button @click="reasignarMedico(medicoId, fecha)"
        class="bg-red-600 text-white px-4 py-2 rounded">
    Médico ausente — Reasignar citas
</button>

<script>
async function reasignarMedico(medicoId, fecha) {
    if (!confirm('¿Confirmas que el médico está ausente y deseas reasignar sus citas?')) return;

    const res = await fetch('/citas/reasignar-medico', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ medico_id_ausente: medicoId, fecha }),
    });

    const data = await res.json();
    alert(data.message);

    if (data.sin_suplente > 0) {
        // Mostrar tabla con las citas que quedaron sin asignar
        console.warn('Citas sin suplente:', data.detalle.filter(d => d.estado === 'sin_suplente_disponible'));
    }
}
</script>