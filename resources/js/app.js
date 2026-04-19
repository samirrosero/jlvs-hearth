import './bootstrap';
import Alpine from 'alpinejs';

// ──────────────────────────────────────────────────────────────────────────────
// Alpine.js — framework reactivo para el frontend de JLVS Hearth
// Para usar Alpine en Blade, agrega x-data, x-on, x-show, etc. a tus elementos.
// Documentación: https://alpinejs.dev
// ──────────────────────────────────────────────────────────────────────────────
window.Alpine = Alpine;

// ──────────────────────────────────────────────────────────────────────────────
// ubicacionSelector — componente Alpine para selects en cascada de Colombia
// Debe registrarse ANTES de Alpine.start()
// Uso: x-data="ubicacionSelector(urlDepartamentos, urlBaseMunicipios)"
// ──────────────────────────────────────────────────────────────────────────────
window.ubicacionSelector = function (urlDepartamentos, urlBaseMunicipios) {
    return {
        departamentos:   [],
        municipios:      [],
        depSeleccionado: '',
        munSeleccionado: '',
        cargandoMun:     false,
        _codigoDep:      '',

        async init() {
            try {
                const res  = await fetch(urlDepartamentos);
                const data = await res.json();
                this.departamentos = data;
            } catch (e) {
                console.warn('No se pudieron cargar los departamentos:', e);
            }
        },

        async cargarMunicipios() {
            this.munSeleccionado = '';
            this.municipios      = [];

            const dep = this.departamentos.find(d => d.nombre === this.depSeleccionado);
            if (!dep?.codigo) return;

            this._codigoDep  = dep.codigo;
            this.cargandoMun = true;
            try {
                const res  = await fetch(`${urlBaseMunicipios}/${dep.codigo}`);
                const data = await res.json();
                this.municipios = data;
            } catch (e) {
                console.warn('No se pudieron cargar los municipios:', e);
            } finally {
                this.cargandoMun = false;
            }
        },
    };
};

// ──────────────────────────────────────────────────────────────────────────────
// api — helper global para llamadas a la API de Laravel
//
// Uso desde cualquier componente Alpine o script:
//   const datos = await api.get('/pacientes');
//   const { status, data } = await api.post('/login', { email, password });
//   const { status } = await api.put('/pacientes/1', { nombre: 'Juan' });
//   const { status } = await api.delete('/pacientes/1');
// ──────────────────────────────────────────────────────────────────────────────
window.api = {
    // Lee el token CSRF del meta tag que incluye el layout Blade
    csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    // Cabeceras estándar para todas las peticiones JSON
    headers() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken(),
        };
    },

    // GET — obtiene datos. Si el servidor devuelve paginación, retorna .data
    async get(url) {
        const res = await fetch(url, { headers: this.headers() });
        if (res.status === 401) {
            window.location.href = '/oficina-virtual';
            return [];
        }
        const json = await res.json();
        // Soporta respuestas paginadas (json.data) y normales (json directo)
        return json.data ?? json;
    },

    // POST — envía datos nuevos (crear recurso)
    async post(url, data) {
        const res = await fetch(url, {
            method: 'POST',
            headers: this.headers(),
            body: JSON.stringify(data),
        });
        return { status: res.status, data: await res.json() };
    },

    // PUT — reemplaza un recurso completo
    async put(url, data) {
        const res = await fetch(url, {
            method: 'PUT',
            headers: this.headers(),
            body: JSON.stringify(data),
        });
        return { status: res.status, data: await res.json() };
    },

    // PATCH — actualiza parcialmente un recurso
    async patch(url, data) {
        const res = await fetch(url, {
            method: 'PATCH',
            headers: this.headers(),
            body: JSON.stringify(data),
        });
        return { status: res.status, data: await res.json() };
    },

    // DELETE — elimina un recurso
    async delete(url) {
        const res = await fetch(url, {
            method: 'DELETE',
            headers: this.headers(),
        });
        return { status: res.status };
    },
};

Alpine.start();
