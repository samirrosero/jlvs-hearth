# Panel Paciente — Guía de implementación (Valeri)

## Contexto del proyecto

Sistema multi-tenant para IPS colombianas. Cada paciente pertenece a una empresa (`empresa_id`).
**Regla fundamental:** todo query debe ir filtrado por el `paciente_id` del usuario autenticado.
Stack: Laravel + Blade + Tailwind + Alpine.js

---

## 1. Rutas — agregar en `routes/web.php`

```php
// Importar al inicio del archivo
use App\Http\Controllers\Paciente\PacienteDashboardController;
use App\Http\Controllers\Paciente\PacienteCitasController;
use App\Http\Controllers\Paciente\PacienteHistorialController;
use App\Http\Controllers\Paciente\AgendarCitaPacienteController;

// Agregar ANTES del bloque del panel admin
Route::prefix('paciente')->name('paciente.')->middleware(['auth', 'role:paciente'])->group(function () {
    Route::get('/',            fn () => redirect()->route('paciente.dashboard'));
    Route::get('/dashboard',   PacienteDashboardController::class)->name('dashboard');

    Route::get('/citas',       [PacienteCitasController::class, 'index'])->name('citas');

    // Agendamiento: el paciente elige especialidad+fecha+hora; el backend asigna el médico
    Route::post('/citas/agendar', AgendarCitaPacienteController::class)->name('citas.agendar');

    Route::patch('/citas/{cita}/cancelar', [PacienteCitasController::class, 'cancelar'])->name('citas.cancelar');

    Route::get('/historial',   [PacienteHistorialController::class, 'index'])->name('historial');
    Route::get('/historial/{historia}', [PacienteHistorialController::class, 'show'])->name('historial.show');
});
```

---

## 2. Redirect del login — editar `LoginController.php`

En `app/Http/Controllers/Admin/LoginController.php`, línea con `'paciente'`:

```php
// Cambiar esto:
'paciente' => redirect()->route('home'),

// Por esto:
'paciente' => redirect()->route('paciente.dashboard'),
```

---

## 3. Obtener el paciente autenticado

El `User` tiene una relación `paciente()`. Úsala así en todos los controllers:

```php
$paciente = auth()->user()->paciente;  // objeto Paciente
$pacienteId = $paciente->id;
```

---

## 4. Controladores — crear en `app/Http/Controllers/Paciente/`

### PacienteDashboardController.php

```php
<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HistoriaClinica;

class PacienteDashboardController extends Controller
{
    public function __invoke()
    {
        $paciente = auth()->user()->paciente;

        // Próximas citas del paciente
        $proximasCitas = Cita::where('paciente_id', $paciente->id)
            ->where('activo', true)
            ->where('fecha', '>=', now()->toDateString())
            ->with('medico.usuario', 'estado', 'servicio')
            ->orderBy('fecha')->orderBy('hora')
            ->limit(5)
            ->get();

        // Total de citas históricas
        $totalCitas = Cita::where('paciente_id', $paciente->id)->count();

        // Total de historias clínicas
        $totalHistorias = HistoriaClinica::where('paciente_id', $paciente->id)->count();

        return view('paciente.dashboard', compact(
            'paciente', 'proximasCitas', 'totalCitas', 'totalHistorias'
        ));
    }
}
```

### PacienteCitasController.php

```php
<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\EstadoCita;

class PacienteCitasController extends Controller
{
    public function index()
    {
        $pacienteId = auth()->user()->paciente->id;

        $citas = Cita::where('paciente_id', $pacienteId)
            ->with('medico.usuario', 'estado', 'servicio', 'modalidad')
            ->when(request('estado_id'), fn ($q) => $q->where('estado_id', request('estado_id')))
            ->orderByDesc('fecha')
            ->paginate(10)
            ->withQueryString();

        $estados = EstadoCita::all();

        return view('paciente.citas.index', compact('citas', 'estados'));
    }
}
```

### PacienteHistorialController.php

```php
<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\HistoriaClinica;

class PacienteHistorialController extends Controller
{
    public function index()
    {
        $pacienteId = auth()->user()->paciente->id;

        $historias = HistoriaClinica::where('paciente_id', $pacienteId)
            ->with('ejecucionCita.cita.medico.usuario', 'recetasMedicas')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('paciente.historial.index', compact('historias'));
    }

    public function show(HistoriaClinica $historia)
    {
        // Seguridad: solo el dueño puede ver su historia
        abort_if($historia->paciente_id !== auth()->user()->paciente->id, 403);

        $historia->load('ejecucionCita.cita.medico.usuario', 'recetasMedicas');

        return view('paciente.historial.show', compact('historia'));
    }
}
```

---

## 5. Layout — crear `resources/views/paciente/layouts/app.blade.php`

Copiar la estructura de `resources/views/medico/layouts/app.blade.php` y ajustar:

- Cambiar `id="medico-sidebar"` → `id="paciente-sidebar"`
- Cambiar el `$nav` a las secciones del paciente:

```php
$nav = [
    [
        'route' => 'paciente.dashboard',
        'icon'  => $empresa?->icono_dashboard_url ?? asset('img/icons/dashboard.png'),
        'label' => 'Inicio',
    ],
    [
        'route' => 'paciente.citas',
        'match' => 'paciente.citas*',
        'icon'  => $empresa?->icono_card_citas_url ?? asset('img/icons/citas-mes.png'),
        'label' => 'Mis Citas',
    ],
    [
        'route' => 'paciente.historial',
        'match' => 'paciente.historial*',
        'icon'  => $empresa?->icono_pacientes_url ?? asset('img/icons/pacientes.png'),
        'label' => 'Mi Historial',
    ],
];
```

- El sidebar muestra: nombre del paciente, `rol: Paciente`, botón logout.

### Branding dinámico — obligatorio en el layout

El sistema es multi-tenant: cada IPS personaliza su logo, colores e íconos desde el panel admin.
**Todo esto debe leerse desde `$empresa`, nunca hardcodeado.**

```blade
{{-- Al inicio del <head> --}}
@php $empresa = auth()->user()?->empresa; @endphp

{{-- Título con nombre de la IPS --}}
<title>@yield('title') — {{ $empresa?->nombre ?? 'JLVS Hearth' }}</title>

{{-- Favicon dinámico --}}
@php $fv = ($empresa?->favicon_url ?? asset('favicon.ico')) . '?v=' . ($empresa?->updated_at?->timestamp ?? '1'); @endphp
<link rel="icon" href="{{ $fv }}" type="image/x-icon">

{{-- Colores del sidebar via CSS variables --}}
<style>
    :root {
        --color-sidebar:    {{ $empresa?->color_paciente   ?? '#0f172a' }};
        --color-primario:   {{ $empresa?->color_primario   ?? '#0369a1' }};
        --color-secundario: {{ $empresa?->color_secundario ?? '#075985' }};
    }
    #paciente-sidebar { background-color: var(--color-sidebar) !important; }
</style>

{{-- Logo en el sidebar --}}
<img src="{{ $empresa?->logo_url ?? asset('img/logos/logo1.png') }}"
     alt="{{ $empresa?->nombre ?? 'JLVS Hearth' }}"
     class="h-10 w-auto object-contain">
```

**Íconos del sidebar** — cada ítem del `$nav` usa el ícono de la empresa con fallback:

```php
$nav = [
    [
        'route' => 'paciente.dashboard',
        'icon'  => $empresa?->icono_dashboard_url ?? asset('img/icons/dashboard.png'),
        'label' => 'Inicio',
    ],
    // ...
];
```

Campos disponibles en `$empresa` para íconos:

| Campo | Uso |
|---|---|
| `$empresa->logo_url` | Logo principal del sidebar |
| `$empresa->favicon_url` | Favicon del navegador |
| `$empresa->color_paciente` | Color de fondo del sidebar del paciente |
| `$empresa->color_primario` | Color primario de la IPS |
| `$empresa->color_secundario` | Color secundario |
| `$empresa->icono_dashboard_url` | Ícono sección Dashboard |
| `$empresa->icono_pacientes_url` | Ícono sección Pacientes |
| `$empresa->icono_card_citas_url` | Ícono sección Citas |

> Si el campo es `null` (la IPS no lo ha personalizado), siempre cae al `asset()` de fallback.

---

## 6. Vistas — crear en `resources/views/paciente/`

```
resources/views/paciente/
├── layouts/
│   └── app.blade.php          ← layout principal
├── dashboard.blade.php        ← inicio: próximas citas + resumen
├── citas/
│   └── index.blade.php        ← lista de citas con filtro por estado
└── historial/
    ├── index.blade.php        ← lista de historias clínicas
    └── show.blade.php         ← detalle de una historia + receta
```

---

## 7. Endpoints de la API disponibles para el paciente

El paciente ya tiene acceso a estas rutas (auth middleware + role:paciente):

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/citas` | Sus citas (filtra por paciente_id en el controller) |
| GET | `/citas/{cita}` | Detalle de una cita |
| GET | `/historias-clinicas` | Sus historias clínicas |
| GET | `/historias-clinicas/{historia}` | Detalle de historia |
| GET | `/historias-clinicas/{historia}/pdf` | Descargar historia en PDF |
| GET | `/recetas` | Sus recetas |
| GET | `/signos-vitales` | Sus signos vitales |
| GET | `/antecedentes` | Sus antecedentes |
| GET | `/valoraciones` | Sus valoraciones |
| POST | `/valoraciones` | Crear una valoración a un médico |
| GET | `/especialidades` | Especialidades disponibles en su IPS |
| GET | `/citas/disponibilidad-por-especialidad?especialidad=X&fecha=Y` | **Slots disponibles por especialidad** (nuevo flujo, ver sección 7.3) |
| POST | `/paciente/citas/agendar` | **Agendar cita — el backend asigna el médico** (ver sección 7.4) |
| PATCH | `/paciente/citas/{id}/cancelar` | Cancelar una cita propia (ver reglas abajo) |
| POST | `/lista-espera` | Inscribirse en lista de espera |

> **Nota:** Estos endpoints retornan JSON. Úsalos con `fetch()` desde Alpine.js si necesitas
> interactividad, o consúmelos directamente en el controller Blade con el modelo.

### 7.1 GET /citas — filtrado automático por paciente

El API de citas está protegida: el backend devuelve **sólo las citas del paciente autenticado**.
No necesitas pasar `paciente_id` en la URL — el controller lo toma de `auth()->user()->paciente`.

```js
// Ejemplo Alpine.js
fetch('/citas')
  .then(r => r.json())
  .then(data => { this.citas = data; });
```

### 7.2 Cancelar una cita — PATCH /citas/{id}

El paciente puede cancelar una cita enviando `estado_id: 4` (Cancelada).

```js
// Ejemplo con fetch
async function cancelarCita(citaId) {
    const res = await fetch(`/citas/${citaId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ estado_id: 4 }),
    });
    if (res.ok) {
        // actualizar lista en la vista
    }
}
```

**Estados de cita (referencia):**

| ID | Nombre |
|----|--------|
| 1 | Pendiente |
| 2 | Confirmada |
| 3 | Atendida |
| 4 | Cancelada |
| 5 | No asistió |

> El paciente solo debe poder cancelar citas en estado Pendiente (1) o Confirmada (2).
> Valida esto en la vista antes de mostrar el botón.

### 7.3 Slots por especialidad — GET /citas/disponibilidad-por-especialidad

Este es el endpoint que usa el flujo del paciente. Devuelve los horarios libres de **todos** los
médicos de una especialidad para una fecha, sin exponer los nombres de los doctores.

**Parámetros (query string):**

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `especialidad` | string | ✅ | Ej: `Medicina General` |
| `fecha` | string (Y-m-d) | ✅ | Fecha a consultar |
| `servicio_id` | integer | ❌ | Duración del turno (default: 30 min) |

**Respuesta con slots:**
```json
{
    "disponible": true,
    "slots": ["06:00", "06:30", "07:00", "07:30", "08:00", "08:30"],
    "fecha": "2026-04-29",
    "especialidad": "Medicina General",
    "duracion_minutos": 30
}
```

**Respuesta sin disponibilidad:**
```json
{
    "disponible": false,
    "slots": [],
    "mensaje": "No hay disponibilidad para esa especialidad en la fecha seleccionada."
}
```

> Si un slot aparece, significa que **al menos un médico** de esa especialidad lo tiene libre.
> El paciente no sabe cuántos médicos hay ni cuál le tocará — eso lo decide el backend al agendar.

Si `disponible` es `false` → mostrar botón de lista de espera.

### 7.4 Agendar cita — POST /paciente/citas/agendar

El paciente confirma la hora elegida. El backend selecciona automáticamente el médico
con menor carga ese día y crea la cita. **No se expone el nombre del médico en la respuesta.**

**Body:**
```json
{
    "especialidad":  "Medicina General",
    "fecha":         "2026-04-29",
    "hora":          "08:00",
    "modalidad_id":  1,
    "portafolio_id": 2,
    "servicio_id":   1
}
```

**Respuesta exitosa (HTTP 201):**
```json
{
    "message":          "Cita agendada con éxito.",
    "cita_id":          145,
    "fecha":            "2026-04-29",
    "hora":             "08:00",
    "especialidad":     "Medicina General",
    "servicio":         "Consulta general",
    "duracion_minutos": 30
}
```

**Errores posibles:**
| HTTP | Motivo |
|------|--------|
| 422 | No hay médicos de esa especialidad |
| 422 | Ningún médico trabaja ese día/hora |
| 422 | Ese slot ya se llenó (otro paciente llegó primero) |

### 7.5 Reglas de agendamiento para el paciente

> **Importante:** El backend valida estas reglas y rechaza la solicitud con HTTP 422 si no se cumplen.
> Muéstralas en la UI para guiar al paciente.

| Modalidad | Restricción de fecha |
|-----------|----------------------|
| Presencial (id=1) | Puede ser desde hoy mismo |
| Telemedicina (id=2) | Mínimo **2 días** de anticipación |
| Domiciliaria (id=3) | Mínimo **2 días** de anticipación |

Ejemplo de validación en Alpine.js antes de enviar:

```js
function fechaMinimaSegunModalidad(modalidadId) {
    const hoy = new Date();
    if ([2, 3].includes(modalidadId)) {
        hoy.setDate(hoy.getDate() + 2); // pasado mañana
    }
    return hoy.toISOString().split('T')[0]; // formato Y-m-d
}
```

### 7.5 Lista de espera — POST /lista-espera

Si no hay slots disponibles, el paciente puede inscribirse en la lista de espera.

```js
const payload = {
    paciente_id:      pacienteId,      // int (del objeto auth)
    medico_id:        medicoId,        // int | null
    servicio_id:      servicioId,      // int | null
    fecha_solicitada: '2026-04-22',    // string Y-m-d
    notas:            'Urgente',       // string | null
};

const res = await fetch('/lista-espera', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify(payload),
});
```

La lista de espera es gestionada por el gestor de citas — cuando haya disponibilidad, cambiará el estado a `asignado` y enlazará la cita creada.

---

## 8. Reglas de seguridad

- Nunca uses solo `HistoriaClinica::find($id)` — siempre agrega `->where('paciente_id', $pacienteId)`
- El `abort_if` en `show()` es obligatorio para evitar que manipulen la URL
- El paciente NO puede crear/editar historias, solo leerlas
- El paciente SÍ puede crear valoraciones (una por cita)

---

## 9. Chatbot — asistente virtual del paciente

El chatbot usa el componente reutilizable `<x-chatbot>` y el endpoint `paciente.chatbot`.
El asistente solo responde sobre las citas e historial del paciente autenticado. **Nunca revela datos de otros pacientes ni información administrativa.**

### 9.1 Agregar la ruta en el grupo paciente (routes/web.php)

```php
use App\Http\Controllers\Admin\ChatbotController;

// Dentro del grupo Route::prefix('paciente')...
Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot');
```

### 9.2 Agregar el chatbot al layout del paciente

Al final del layout (`resources/views/paciente/layouts/app.blade.php`), **antes** de `@stack('scripts')`:

```blade
{{-- Chatbot flotante --}}
@php
    $chatbotRutas = collect([
        ['clave' => 'dashboard',  'label' => 'Ir al Inicio',       'ruta' => 'paciente.dashboard'],
        ['clave' => 'citas',      'label' => 'Ver Mis Citas',       'ruta' => 'paciente.citas'],
        ['clave' => 'historial',  'label' => 'Ver Mi Historial',    'ruta' => 'paciente.historial'],
    ])->filter(fn ($s) => Route::has($s['ruta']))
      ->mapWithKeys(fn ($s) => [$s['clave'] => ['label' => $s['label'], 'url' => route($s['ruta'])]])
      ->toJson();
@endphp

<x-chatbot
    endpoint="{{ route('paciente.chatbot') }}"
    storage-key="paciente"
    :rutas-json="$chatbotRutas"
    mensaje-inicial="¡Hola! Soy tu asistente personal. Puedo decirte cuántas citas tienes o ayudarte a navegar. ¿En qué te puedo ayudar?"
/>
```

### 9.3 Qué puede y no puede responder el chatbot del paciente

| Puede responder | No puede responder |
|---|---|
| Número de próximas citas del paciente | Datos de otros pacientes |
| Total de historias clínicas propias | Estadísticas de la IPS |
| Navegación a sus secciones | Datos administrativos |
| | Información de médicos u otros usuarios |

---

## 10. Flujo completo de agendamiento de cita

El paciente **nunca elige un médico específico** — elige especialidad, fecha y hora.
El backend asigna el médico internamente (balance de carga). Esto evita que los mejores
médicos se saturen solo por ser reconocidos.

```
Paso 1 → Paso 2 → Paso 3 → Paso 4 → Paso 5
```

### Paso 1 — Seleccionar especialidad

```js
GET /especialidades
```

Muestra las especialidades disponibles en la IPS. Renderizar como cards o select.

**Respuesta:**
```json
["Medicina General", "Ortopedia", "Pediatría", "Ginecología"]
```

---

### Paso 2 — Seleccionar fecha

Mostrar un selector de fecha. La fecha mínima es **mañana** (el backend lo valida).

> No hay paso de "elegir médico" — el sistema lo hace por ti.

---

### Paso 3 — Ver slots disponibles del día

Con la especialidad y fecha elegidas, consulta todos los horarios libres:

```js
GET /citas/disponibilidad-por-especialidad?especialidad=Medicina General&fecha=2026-04-29
```

**Respuesta con slots:**
```json
{
    "disponible": true,
    "slots": ["06:00", "06:30", "07:00", "08:00", "09:00", "09:30"],
    "fecha": "2026-04-29",
    "especialidad": "Medicina General",
    "duracion_minutos": 30
}
```

**Respuesta sin disponibilidad:**
```json
{
    "disponible": false,
    "slots": [],
    "mensaje": "No hay disponibilidad para esa especialidad en la fecha seleccionada."
}
```

Renderiza los slots como botones de hora. Si `disponible` es `false` → mostrar botón de lista de espera (paso 3b).

---

### Paso 3b — Lista de espera (si no hay slots)

```js
POST /lista-espera
{
    "paciente_id":     12,
    "servicio_id":     1,
    "fecha_solicitada": "2026-04-29",
    "notas":           "Urgente, dolor fuerte"
}
```

El gestor de citas verá esta solicitud en su panel y asignará la cita cuando haya disponibilidad.

---

### Paso 4 — Confirmar y crear la cita

El paciente elige la hora y confirma. **No envíes `medico_id`** — el backend lo asigna solo.

```js
POST /paciente/citas/agendar
{
    "especialidad":  "Medicina General",
    "fecha":         "2026-04-29",
    "hora":          "08:00",
    "modalidad_id":  1,
    "portafolio_id": 2,
    "servicio_id":   1
}
```

> **Reglas del backend (valida automáticamente con HTTP 422 si no se cumplen):**
> - Modalidad Presencial (id=1): mínimo **mañana**
> - Telemedicina (id=2) o Domiciliaria (id=3): mínimo **pasado mañana**

---

### Paso 5 — Confirmación

Mostrar resumen de la cita con los datos devueltos:

```json
{
    "message":          "Cita agendada con éxito.",
    "cita_id":          145,
    "fecha":            "2026-04-29",
    "hora":             "08:00",
    "especialidad":     "Medicina General",
    "servicio":         "Consulta general",
    "duracion_minutos": 30
}
```

**No mostrar el nombre del médico** — el paciente lo sabrá cuando llegue a ventanilla.
La cita aparecerá en `GET /citas` con estado **Pendiente**.

---

### Diagrama del flujo

```
[Especialidades] → [Fecha] → [Slots combinados de la especialidad]
                                      ↙                 ↘
                              [Hay slots]           [Sin slots]
                                   ↓                     ↓
                    [POST /paciente/citas/agendar]  [Lista de espera]
                    (backend asigna médico solo)
                                   ↓
                           [Confirmación sin nombre de médico]
```

---

### Resumen de endpoints por paso

| Paso | Endpoint |
|------|----------|
| 1 – Especialidades | `GET /especialidades` |
| 2 – (solo UI, selector de fecha) | — |
| 3 – Slots del día por especialidad | `GET /citas/disponibilidad-por-especialidad?especialidad=X&fecha=Y` |
| 3b – Lista espera | `POST /lista-espera` |
| 4 – Agendar (el backend asigna médico) | `POST /paciente/citas/agendar` |
| Ver mis citas | `GET /citas` |
| Cancelar cita | `PATCH /paciente/citas/{id}/cancelar` |
