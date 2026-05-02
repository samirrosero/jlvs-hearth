@props([
    'endpoint',
    'storageKey'      => 'jlvs_chatbot',
    'rutasJson'       => '{}',
    'mensajeInicial'  => '¡Hola! Soy el asistente de JLVS Hearth. ¿En qué te puedo ayudar?',
])

{{-- ── Chatbot flotante ────────────────────────────────────── --}}
<div x-data="chatbot('{{ $endpoint }}', @js(json_decode($rutasJson, true) ?? []), '{{ $storageKey }}', '{{ addslashes($mensajeInicial) }}')"
     class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3">

    {{-- Ventana del chat --}}
    <div x-show="abierto" x-transition
         class="w-80 bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden"
         style="height: 420px;">

        {{-- Cabecera --}}
        <div class="bg-gray-900 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                <span class="text-white text-sm font-medium">Asistente JLVS</span>
            </div>
            <button @click="abierto = false" class="text-gray-400 hover:text-white transition text-lg leading-none">&times;</button>
        </div>

        {{-- Mensajes --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3 text-sm" x-ref="mensajes">
            <template x-for="(m, i) in mensajes" :key="i">
                <div :class="m.rol === 'user' ? 'flex justify-end' : 'flex justify-start flex-col items-start gap-1'">
                    <div :class="m.rol === 'user'
                            ? 'bg-gray-900 text-white rounded-2xl rounded-br-sm px-3 py-2 max-w-[75%]'
                            : 'bg-gray-100 text-gray-800 rounded-2xl rounded-bl-sm px-3 py-2 max-w-[85%]'"
                         x-text="m.texto">
                    </div>
                    {{-- Botones de navegación --}}
                    <template x-if="m.acciones && m.acciones.length > 0">
                        <div class="flex flex-wrap gap-1 ml-1">
                            <template x-for="(a, j) in m.acciones" :key="j">
                                <a :href="a.url"
                                   class="inline-flex items-center gap-1 text-xs bg-white border border-gray-300 hover:border-gray-900 hover:bg-gray-900 hover:text-white text-gray-700 rounded-lg px-2 py-1 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                    <span x-text="a.label"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
            <div x-show="cargando" class="flex justify-start">
                <div class="bg-gray-100 text-gray-400 rounded-2xl rounded-bl-sm px-3 py-2 text-xs italic">
                    Escribiendo...
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-200 p-3 flex gap-2">
            <input
                x-model="mensaje"
                @keydown.enter.prevent="enviar()"
                :disabled="cargando"
                type="text"
                placeholder="Escribe tu pregunta..."
                class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-900 disabled:opacity-50"
            >
            <button
                @click="enviar()"
                :disabled="cargando || mensaje.trim() === ''"
                class="bg-gray-900 text-white rounded-lg px-3 py-2 hover:bg-gray-700 transition disabled:opacity-40">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Botón flotante --}}
    <button @click="abierto = !abierto"
            class="w-14 h-14 bg-gray-900 hover:bg-gray-700 text-white rounded-full shadow-lg flex items-center justify-center transition">
        <svg x-show="!abierto" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
        </svg>
        <svg x-show="abierto" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

<script>
function chatbot(endpoint, rutas, storageKey, mensajeInicialTexto) {

    const STORAGE_KEY    = 'jlvs_chatbot_' + storageKey;
    const NAV_KEY        = 'jlvs_chatbot_nav_' + storageKey;
    const mensajeInicial = [{ rol: 'bot', texto: mensajeInicialTexto, acciones: [] }];

    function cargarHistorial() {
        try {
            const guardado = localStorage.getItem(STORAGE_KEY);
            return guardado ? JSON.parse(guardado) : mensajeInicial;
        } catch { return mensajeInicial; }
    }

    function guardarHistorial(mensajes) {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(mensajes)); } catch {}
    }

    function parsearRespuesta(raw) {
        const acciones = [];

        // [NAVEGAR:clave] o [NAVEGAR:clave:parametro] — redirige automáticamente
        const matchNav = raw.match(/\[NAVEGAR:([\w-]+)(?::([^\]]*))?\]/);
        let navegar = null;
        if (matchNav && rutas[matchNav[1]]) {
            const param = (matchNav[2] ?? '').trim();
            navegar = {
                ...rutas[matchNav[1]],
                url: param
                    ? rutas[matchNav[1]].url + '?buscar=' + encodeURIComponent(param)
                    : rutas[matchNav[1]].url,
            };
        }

        // [IR:clave] — botones de sugerencia
        const marcador = /\[IR:([\w-]+)\]/g;
        let match;
        while ((match = marcador.exec(raw)) !== null) {
            if (rutas[match[1]]) acciones.push(rutas[match[1]]);
        }

        const texto = raw
            .replace(/\[NAVEGAR:[\w-]+(?::[^\]]*)?\]/g, '')
            .replace(/\[IR:\w+\]/g, '')
            .replace(/\s{2,}/g, ' ')
            .trim();

        return { texto, acciones, navegar };
    }

    const historialPrevio  = cargarHistorial();
    const veniaDeNavegacion = localStorage.getItem(NAV_KEY) === '1';
    localStorage.removeItem(NAV_KEY);

    return {
        abierto:  veniaDeNavegacion,
        mensaje:  '',
        mensajes: historialPrevio,
        cargando: false,

        async enviar() {
            const texto = this.mensaje.trim();
            if (!texto || this.cargando) return;

            this.mensajes.push({ rol: 'user', texto, acciones: [] });
            guardarHistorial(this.mensajes);
            this.mensaje  = '';
            this.cargando = true;
            this.$nextTick(() => this.scrollAbajo());

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ mensaje: texto }),
                });

                const data = await res.json();
                const raw  = data.respuesta ?? data.error ?? 'Error al obtener respuesta.';
                const { texto: textoLimpio, acciones, navegar } = parsearRespuesta(raw);
                this.mensajes.push({ rol: 'bot', texto: textoLimpio, acciones });
                guardarHistorial(this.mensajes);

                if (navegar) {
                    this.mensajes.push({ rol: 'bot', texto: `Redirigiendo a ${navegar.label}...`, acciones: [] });
                    guardarHistorial(this.mensajes);
                    localStorage.setItem(NAV_KEY, '1');
                    setTimeout(() => { window.location.href = navegar.url; }, 1500);
                }
            } catch {
                this.mensajes.push({ rol: 'bot', texto: 'No se pudo conectar con el asistente.', acciones: [] });
            } finally {
                this.cargando = false;
                this.$nextTick(() => this.scrollAbajo());
            }
        },

        scrollAbajo() {
            const c = this.$refs.mensajes;
            if (c) c.scrollTop = c.scrollHeight;
        },
    };
}
</script>
