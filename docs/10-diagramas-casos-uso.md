# Diagramas de Casos de Uso — JLVS Hearth

> Las imágenes generadas se encuentran en [`docs/diagrams/`](diagrams/).

---

## Actores del sistema

| Actor | Descripción |
|-------|-------------|
| **Público** | Sin autenticación. Puede registrar una IPS o un paciente, y recuperar contraseña. |
| **Administrador** | Usuario con control total sobre su IPS (multi-tenant). |
| **Médico** | Atiende citas y documenta la consulta clínica. |
| **Gestor de Citas** | Agenda y administra la agenda de la IPS. |
| **Paciente** | Consulta su historial y valora su atención. |

---

## Diagrama de Casos de Uso — Actores y Módulos

![Diagrama de Casos de Uso](diagrams/casos-uso.png)

---

## Diagrama de Casos de Uso — Relaciones `<<include>>` / `<<extend>>`

| Tipo | Relación | Significado |
|------|----------|-------------|
| `«include»` | sólida | El sub-caso se ejecuta **siempre** como parte del caso base |
| `«extend»` | punteada | El caso extensor es **opcional / condicional** |

### Relaciones `<<include>>`
- **Agendar cita** incluye → Verificar disponibilidad del médico *(siempre valida horario y solapamiento)*
- **Crear historia clínica** incluye → Iniciar atención *(requiere una ejecución activa)*
- **Descargar historia PDF** incluye → Consultar historia clínica *(debe leer antes de generar)*

### Relaciones `<<extend>>`
- **Forzar cambio de contraseña** extiende → Iniciar sesión *(cuando `debe_cambiar_password = true`)*
- **Crear cuenta con contraseña temporal** extiende → Registrar paciente presencial *(cuando `crear_cuenta = true`)*
- **Registrar signos vitales** extiende → Iniciar atención *(opcional según tipo de consulta)*
- **Buscar código CIE-10** extiende → Crear historia clínica *(el médico puede o no usar CIE-10)*
- **Emitir receta médica** extiende → Crear historia clínica *(no toda consulta genera receta)*
- **Adjuntar documentos** extiende → Crear historia clínica *(solo si hay archivos que adjuntar)*

![Relaciones include y extend](diagrams/casos-uso-relaciones.png)

```mermaid
flowchart TD
    %% ─── Actores ───────────────────────────────────────────────
    PUB(["🌐 Público"])
    ADM(["👤 Administrador"])
    MED(["🩺 Médico"])
    GES(["📋 Gestor de Citas"])
    PAC(["🙋 Paciente"])

    %% ─── Módulo: Autenticación ──────────────────────────────────
    subgraph AUTH["🔐 Autenticación"]
        direction TB
        UC_REG_IPS["Registrar nueva IPS"]
        UC_LOGIN["Iniciar / Cerrar sesión"]
        UC_FORGOT["Recuperar contraseña"]
        UC_PASS["Cambiar contraseña propia"]
        UC_REG_PAC_WEB["Registro de paciente (web)"]
    end

    %% ─── Módulo: Administración ─────────────────────────────────
    subgraph ADMMOD["⚙️ Administración de la IPS"]
        direction TB
        UC_EMPRESA["Gestionar datos de empresa"]
        UC_PORTAFOLIO["Gestionar portafolios / EPS"]
        UC_CATALOGO["Gestionar catálogos\n(modalidades, estados, servicios)"]
        UC_USUARIOS["Gestionar usuarios internos"]
        UC_MEDICOS["Gestionar médicos"]
        UC_HORARIOS["Gestionar horarios del médico"]
        UC_DASHBOARD["Ver dashboard de métricas"]
        UC_REPORTES["Generar reportes PDF / Excel"]
        UC_AUDITORIA["Consultar auditoría"]
    end

    %% ─── Módulo: Agendamiento ───────────────────────────────────
    subgraph CITASMOD["📅 Agendamiento"]
        direction TB
        UC_REG_PAC_GES["Registrar paciente (presencial)"]
        UC_AGENDAR["Agendar cita"]
        UC_VER_CITAS["Ver citas"]
        UC_CANCELAR["Cancelar cita"]
    end

    %% ─── Módulo: Atención Clínica ───────────────────────────────
    subgraph CLINICO["🏥 Atención Clínica"]
        direction TB
        UC_EJECUCION["Iniciar / Cerrar atención"]
        UC_SIGNOS["Registrar signos vitales"]
        UC_CIE10["Buscar código CIE-10"]
        UC_HISTORIA["Crear historia clínica"]
        UC_RECETA["Emitir receta médica"]
        UC_DOCS["Adjuntar documentos"]
    end

    %% ─── Módulo: Portal del Paciente ────────────────────────────
    subgraph PACMOD["🙋 Portal del Paciente"]
        direction TB
        UC_HISTORIAL["Consultar historial clínico"]
        UC_PDF["Descargar historia en PDF"]
        UC_ANTEC["Ver antecedentes médicos"]
        UC_VALORAR["Valorar consulta"]
        UC_VER_VAL["Ver valoraciones"]
    end

    %% ─── Relaciones: Público ────────────────────────────────────
    PUB --> UC_REG_IPS
    PUB --> UC_LOGIN
    PUB --> UC_FORGOT
    PUB --> UC_REG_PAC_WEB

    %% ─── Relaciones: Administrador ──────────────────────────────
    ADM --> UC_LOGIN
    ADM --> UC_FORGOT
    ADM --> UC_PASS
    ADM --> UC_EMPRESA
    ADM --> UC_PORTAFOLIO
    ADM --> UC_CATALOGO
    ADM --> UC_USUARIOS
    ADM --> UC_MEDICOS
    ADM --> UC_HORARIOS
    ADM --> UC_DASHBOARD
    ADM --> UC_REPORTES
    ADM --> UC_AUDITORIA
    ADM --> UC_REG_PAC_GES
    ADM --> UC_AGENDAR
    ADM --> UC_VER_CITAS
    ADM --> UC_CANCELAR
    ADM --> UC_EJECUCION
    ADM --> UC_SIGNOS
    ADM --> UC_CIE10
    ADM --> UC_HISTORIA
    ADM --> UC_RECETA
    ADM --> UC_DOCS
    ADM --> UC_HISTORIAL
    ADM --> UC_PDF
    ADM --> UC_VER_VAL

    %% ─── Relaciones: Médico ─────────────────────────────────────
    MED --> UC_LOGIN
    MED --> UC_PASS
    MED --> UC_VER_CITAS
    MED --> UC_EJECUCION
    MED --> UC_SIGNOS
    MED --> UC_CIE10
    MED --> UC_HISTORIA
    MED --> UC_RECETA
    MED --> UC_DOCS
    MED --> UC_HISTORIAL
    MED --> UC_PDF
    MED --> UC_VER_VAL

    %% ─── Relaciones: Gestor de Citas ────────────────────────────
    GES --> UC_LOGIN
    GES --> UC_PASS
    GES --> UC_REG_PAC_GES
    GES --> UC_AGENDAR
    GES --> UC_VER_CITAS
    GES --> UC_CANCELAR
    GES --> UC_CIE10

    %% ─── Relaciones: Paciente ───────────────────────────────────
    PAC --> UC_LOGIN
    PAC --> UC_FORGOT
    PAC --> UC_PASS
    PAC --> UC_VER_CITAS
    PAC --> UC_HISTORIAL
    PAC --> UC_PDF
    PAC --> UC_ANTEC
    PAC --> UC_VALORAR
    PAC --> UC_VER_VAL
    PAC --> UC_CIE10
```

---

## Diagrama Narrativo — Flujo Principal Clínico

Este diagrama muestra el flujo completo de atención desde el agendamiento hasta la valoración de la consulta.

![Flujo Principal Clínico](diagrams/narrativo-flujo-clinico.png)

```mermaid
sequenceDiagram
    actor GES  as 📋 Gestor de Citas
    actor MED  as 🩺 Médico
    actor PAC  as 🙋 Paciente
    participant API as 🖥️ Backend JLVS
    participant DB  as 🗄️ Base de Datos

    %% ── 1. Agendamiento ──────────────────────────────────────────
    rect rgb(230, 240, 255)
        Note over GES,DB: FASE 1 — Agendamiento
        GES->>API: POST /citas { paciente_id, medico_id, fecha, hora }
        API->>DB: Verifica horario del médico (horarios_medico)
        DB-->>API: Horario disponible
        API->>DB: Verifica que no hay solapamiento (citas activas)
        DB-->>API: Sin conflicto
        API->>DB: INSERT citas
        API-->>GES: 201 { id: 5, estado: "Pendiente" }
    end

    %% ── 2. Inicio de atención ────────────────────────────────────
    rect rgb(230, 255, 230)
        Note over MED,DB: FASE 2 — Inicio de atención (paciente llega a la IPS)
        MED->>API: POST /ejecuciones { cita_id: 5, inicio_atencion }
        API->>DB: INSERT ejecuciones_cita
        API-->>MED: 201 { id: 8 }
    end

    %% ── 3. Signos vitales ────────────────────────────────────────
    rect rgb(255, 250, 220)
        Note over MED,DB: FASE 3 — Signos vitales
        MED->>API: POST /signos-vitales { ejecucion_cita_id: 8, peso, presion, temperatura... }
        API->>DB: INSERT signos_vitales
        API-->>MED: 201 OK
    end

    %% ── 4. Historia clínica ──────────────────────────────────────
    rect rgb(255, 235, 225)
        Note over MED,DB: FASE 4 — Historia clínica
        MED->>API: GET /cie10?buscar=cefalea
        API-->>MED: [{ codigo: "G44.2", descripcion: "Cefalea tensional" }]

        MED->>API: POST /historias-clinicas { ejecucion_cita_id: 8, diagnostico, codigo_cie10, plan_tratamiento... }
        API->>DB: INSERT historias_clinicas
        API->>DB: INSERT logs_auditoria (Observer automático — Res. 1995/1999)
        API-->>MED: 201 { id: 12 }
    end

    %% ── 5. Receta y documentos ───────────────────────────────────
    rect rgb(240, 225, 255)
        Note over MED,DB: FASE 5 — Receta y documentos
        MED->>API: POST /recetas { historia_clinica_id: 12, medicamentos, indicaciones }
        API->>DB: INSERT recetas_medicas
        API-->>MED: 201 OK

        opt Si hay resultados de laboratorio u otros archivos
            MED->>API: POST /documentos (multipart) { historia_clinica_id: 12, archivo }
            API->>DB: Guarda metadatos en documentos_adjuntos
            API-->>MED: 201 OK
        end
    end

    %% ── 6. Cierre de atención ────────────────────────────────────
    rect rgb(230, 255, 230)
        Note over MED,DB: FASE 6 — Cierre de atención
        MED->>API: PATCH /ejecuciones/8 { fin_atencion, duracion_minutos: 20 }
        API->>DB: UPDATE ejecuciones_cita
        API-->>MED: 200 OK
    end

    %% ── 7. Consulta y valoración por el paciente ─────────────────
    rect rgb(225, 245, 255)
        Note over PAC,DB: FASE 7 — El paciente consulta y valora (portal web)
        PAC->>API: GET /historias-clinicas/12/pdf
        API-->>PAC: 📄 historia-clinica-00000012.pdf

        PAC->>API: POST /valoraciones { cita_id: 5, puntuacion: 5, comentario }
        API->>DB: INSERT valoraciones
        API-->>PAC: 201 OK
    end
```

---

## Diagrama Narrativo — Registro de nueva IPS (Onboarding)

![Onboarding nueva IPS](diagrams/narrativo-onboarding.png)

```mermaid
sequenceDiagram
    actor PUB  as 🌐 Público (nueva IPS)
    participant API as 🖥️ Backend JLVS
    participant DB  as 🗄️ Base de Datos
    participant MAIL as 📧 Gmail SMTP

    PUB->>API: POST /empresas { nit, nombre, admin_nombre, admin_email, admin_password }
    API->>DB: BEGIN TRANSACTION
    API->>DB: INSERT empresas
    API->>DB: INSERT users (rol = administrador, empresa_id)
    API->>DB: COMMIT
    API-->>PUB: 201 { empresa, administrador }

    Note over PUB,API: El administrador ya puede iniciar sesión

    PUB->>API: POST /login { email, password }
    API-->>PUB: 200 { usuario: { rol: "administrador" } }

    Note over PUB: El frontend redirige al Panel de Administración
```

---

## Diagrama Narrativo — Registro presencial de paciente

![Registro presencial de paciente](diagrams/narrativo-registro-paciente.png)

```mermaid
sequenceDiagram
    actor GES  as 📋 Gestor de Citas
    actor PAC  as 🙋 Paciente (llega en persona)
    participant API as 🖥️ Backend JLVS
    participant DB  as 🗄️ Base de Datos

    GES->>API: POST /pacientes/registro-gestor { nombre, identificacion, crear_cuenta: true, email }
    API->>DB: INSERT pacientes
    API->>DB: INSERT users (rol = paciente, debe_cambiar_password: true)
    API-->>GES: 201 { paciente, password_temporal: "AXBK-4821" }

    Note over GES,PAC: El gestor entrega la contraseña temporal al paciente

    PAC->>API: POST /login { email, password: "AXBK-4821" }
    API-->>PAC: 200 { usuario: { debe_cambiar_password: true } }

    Note over PAC: El frontend detecta debe_cambiar_password y redirige

    PAC->>API: POST /mi-cuenta/cambiar-password { password_actual, password, password_confirmation }
    API->>DB: UPDATE users SET password, debe_cambiar_password = false
    API-->>PAC: 200 "Contraseña actualizada correctamente."
```
