# Base de Datos — Estructura Completa

Motor: **MySQL**. Todas las tablas usan `id` autoincremental (BIGINT UNSIGNED) como PK y `timestamps` (`created_at`, `updated_at`) salvo indicación contraria.

---

## Diagrama de relaciones (resumen)

```
empresas
  ├── users (empresa_id)
  │     └── medicos (usuario_id)
  │           └── horarios_medico (medico_id)
  ├── pacientes (empresa_id)
  │     └── antecedentes_paciente (paciente_id)
  ├── portafolios (empresa_id)
  ├── servicios (empresa_id)
  ├── modalidades_cita  ← global (sin empresa_id)
  └── estados_cita      ← global (sin empresa_id)

roles
  └── users (rol_id)

citas (empresa_id, medico_id, paciente_id, estado_id, modalidad_id, portafolio_id, servicio_id)
  └── ejecuciones_cita (cita_id)
        └── historias_clinicas (ejecucion_cita_id, paciente_id)
              ├── recetas_medicas (historia_clinica_id)
              ├── documentos_adjuntos (historia_clinica_id)
              └── signos_vitales (ejecucion_cita_id, paciente_id)

logs_auditoria (usuario_id, empresa_id)
```

---

## Tablas — Detalle de atributos

---

### `empresas`
Raíz del modelo multi-tenant. Cada IPS es una empresa.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK autoincremental |
| `nit` | VARCHAR | NO | NIT de la IPS (único nacional) |
| `nombre` | VARCHAR | NO | Nombre comercial de la IPS |
| `telefono` | VARCHAR | SÍ | Teléfono de contacto |
| `correo` | VARCHAR | SÍ | Correo institucional |
| `direccion` | VARCHAR | SÍ | Dirección de la sede principal |
| `ciudad` | VARCHAR | SÍ | Ciudad donde opera |
| `activo` | BOOLEAN | NO | `true` por defecto — soft delete lógico |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `roles`
Catálogo global de perfiles de acceso. No pertenece a ninguna empresa.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `nombre` | VARCHAR | NO | Identificador del rol (`administrador`, `medico`, `gestor_citas`, `paciente`) |
| `descripcion` | VARCHAR | SÍ | Descripción legible del rol |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `users`
Credenciales de acceso de todos los usuarios del sistema.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `empresa_id` | BIGINT UNSIGNED | NO | FK → `empresas.id` |
| `rol_id` | BIGINT UNSIGNED | NO | FK → `roles.id` |
| `nombre` | VARCHAR | NO | Nombre completo del usuario |
| `email` | VARCHAR | NO | Correo electrónico (único global, usado para login) |
| `identificacion` | VARCHAR | NO | Cédula o documento de identidad (único global) |
| `password` | VARCHAR | NO | Contraseña cifrada (bcrypt) |
| `email_verified_at` | TIMESTAMP | SÍ | Fecha de verificación del correo |
| `remember_token` | VARCHAR | SÍ | Token para "recordarme" |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `pacientes`
Datos personales de los pacientes registrados en una IPS.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `usuario_id` | BIGINT UNSIGNED | SÍ | FK → `users.id` (nullable: el paciente puede no tener login) |
| `empresa_id` | BIGINT UNSIGNED | NO | FK → `empresas.id` |
| `nombre_completo` | VARCHAR | NO | Nombre y apellidos completos |
| `fecha_nacimiento` | DATE | NO | Fecha de nacimiento |
| `sexo` | ENUM | NO | `M`, `F`, `Otro` |
| `telefono` | VARCHAR | NO | Teléfono de contacto |
| `correo` | VARCHAR | SÍ | Correo electrónico |
| `direccion` | VARCHAR | SÍ | Dirección de residencia |
| `identificacion` | VARCHAR | NO | Cédula o documento (único POR empresa) |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

**Índice único:** `(identificacion, empresa_id)` — un mismo número de cédula puede existir en dos IPS distintas.

---

### `medicos`
Perfil profesional de los médicos. Extiende `users` con datos clínicos.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `usuario_id` | BIGINT UNSIGNED | NO | FK → `users.id` (único: un usuario = un médico) |
| `empresa_id` | BIGINT UNSIGNED | NO | FK → `empresas.id` |
| `especialidad` | VARCHAR | NO | Especialidad médica (ej: `Medicina General`, `Pediatría`) |
| `registro_medico` | VARCHAR | NO | Número de registro profesional (único nacional) |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `horarios_medico`
Disponibilidad semanal de cada médico por bloques de tiempo.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `medico_id` | BIGINT UNSIGNED | NO | FK → `medicos.id` |
| `empresa_id` | BIGINT UNSIGNED | NO | FK → `empresas.id` |
| `dia_semana` | TINYINT UNSIGNED | NO | `0`=domingo, `1`=lunes, …, `6`=sábado |
| `hora_inicio` | TIME | NO | Inicio del bloque horario (ej: `08:00`) |
| `hora_fin` | TIME | NO | Fin del bloque horario (ej: `12:00`) |
| `activo` | BOOLEAN | NO | Permite desactivar un horario sin borrarlo |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `portafolios`
Convenios o tipos de cobertura que maneja cada IPS (EPS, Particular, Prepagada).

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `empresa_id` | BIGINT UNSIGNED | NO | FK → `empresas.id` |
| `nombre_convenio` | VARCHAR | NO | Nombre del convenio (ej: `EPS Sura`, `Particular`) |
| `descripcion` | TEXT | SÍ | Descripción adicional |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `servicios`
Catálogo de procedimientos que ofrece la IPS. Define duración para validar disponibilidad.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `empresa_id` | BIGINT UNSIGNED | NO | FK → `empresas.id` |
| `nombre` | VARCHAR(150) | NO | Nombre del servicio (ej: `Consulta Medicina General`) |
| `descripcion` | TEXT | SÍ | Descripción del procedimiento |
| `duracion_minutos` | SMALLINT UNSIGNED | NO | Duración estimada en minutos (default: `30`) |
| `activo` | BOOLEAN | NO | `true` por defecto |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

**Índice único:** `(nombre, empresa_id)`

---

### `modalidades_cita`
Catálogo global de modalidades de atención.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `nombre` | VARCHAR | NO | `Presencial`, `Telemedicina`, `Domiciliaria` (único global) |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `estados_cita`
Catálogo global del ciclo de vida de una cita.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `nombre` | VARCHAR | NO | `Pendiente`, `Confirmada`, `Atendida`, `Cancelada`, `No asistió` |
| `color_hex` | VARCHAR | SÍ | Color para el calendario del frontend (ej: `#3B82F6`) |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `citas`
Entidad central del sistema. Representa el agendamiento de una consulta médica.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `empresa_id` | BIGINT UNSIGNED | NO | FK → `empresas.id` |
| `medico_id` | BIGINT UNSIGNED | NO | FK → `medicos.id` |
| `paciente_id` | BIGINT UNSIGNED | NO | FK → `pacientes.id` |
| `estado_id` | BIGINT UNSIGNED | NO | FK → `estados_cita.id` |
| `modalidad_id` | BIGINT UNSIGNED | NO | FK → `modalidades_cita.id` |
| `portafolio_id` | BIGINT UNSIGNED | NO | FK → `portafolios.id` |
| `servicio_id` | BIGINT UNSIGNED | SÍ | FK → `servicios.id` (nullable) |
| `fecha` | DATE | NO | Fecha programada de la cita |
| `hora` | TIME | NO | Hora programada de la cita |
| `activo` | BOOLEAN | NO | `false` = cita cancelada (soft delete lógico) |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `ejecuciones_cita`
Registra el momento real en que una cita fue atendida. Una cita → máximo una ejecución.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `cita_id` | BIGINT UNSIGNED | NO | FK → `citas.id` (único: una cita se ejecuta una sola vez) |
| `inicio_atencion` | DATETIME | NO | Fecha y hora en que el médico inició la consulta |
| `fin_atencion` | DATETIME | SÍ | Fecha y hora en que finalizó la consulta |
| `duracion_minutos` | INT | SÍ | Duración real calculada de la consulta |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `historias_clinicas`
Registro médico generado durante la atención. Una ejecución → exactamente una historia.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `ejecucion_cita_id` | BIGINT UNSIGNED | NO | FK → `ejecuciones_cita.id` (único) |
| `paciente_id` | BIGINT UNSIGNED | NO | FK → `pacientes.id` |
| `motivo_consulta` | TEXT | NO | ¿Por qué consulta el paciente hoy? |
| `enfermedad_actual` | TEXT | NO | Descripción detallada de la enfermedad actual |
| `antecedentes` | JSON | NO | Antecedentes relevantes de esa consulta (array) |
| `diagnostico` | TEXT | NO | Diagnóstico médico establecido |
| `plan_tratamiento` | TEXT | NO | Plan de tratamiento indicado |
| `evaluacion` | TEXT | SÍ | Evaluación de evolución del paciente |
| `observaciones` | TEXT | SÍ | Notas adicionales del médico |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `signos_vitales`
Mediciones clínicas tomadas al inicio de cada consulta. Una ejecución → un registro.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `ejecucion_cita_id` | BIGINT UNSIGNED | NO | FK → `ejecuciones_cita.id` (único) |
| `paciente_id` | BIGINT UNSIGNED | NO | FK → `pacientes.id` |
| `peso_kg` | DECIMAL(5,2) | SÍ | Peso en kilogramos (ej: `72.50`) |
| `talla_cm` | DECIMAL(5,2) | SÍ | Talla en centímetros (ej: `170.00`) |
| `presion_sistolica` | SMALLINT UNSIGNED | SÍ | Presión sistólica en mmHg (ej: `120`) |
| `presion_diastolica` | SMALLINT UNSIGNED | SÍ | Presión diastólica en mmHg (ej: `80`) |
| `temperatura_c` | DECIMAL(4,1) | SÍ | Temperatura corporal en °C (ej: `36.5`) |
| `frecuencia_cardiaca` | SMALLINT UNSIGNED | SÍ | Frecuencia cardíaca en lpm (ej: `72`) |
| `saturacion_oxigeno` | TINYINT UNSIGNED | SÍ | Saturación de oxígeno en % (ej: `98`) |
| `frecuencia_respiratoria` | SMALLINT UNSIGNED | SÍ | Frecuencia respiratoria en rpm (ej: `16`) |
| `observaciones` | TEXT | SÍ | Notas adicionales sobre los signos |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `antecedentes_paciente`
Historial médico permanente y acumulado del paciente (separado por tipo).

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `paciente_id` | BIGINT UNSIGNED | NO | FK → `pacientes.id` |
| `tipo` | ENUM | NO | `personal`, `familiar`, `quirurgico`, `alergico`, `farmacologico`, `otros` |
| `descripcion` | TEXT | NO | Descripción del antecedente |
| `activo` | BOOLEAN | NO | Permite desactivar sin borrar |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `recetas_medicas`
Prescripciones médicas generadas al finalizar una consulta.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `historia_clinica_id` | BIGINT UNSIGNED | NO | FK → `historias_clinicas.id` |
| `medicamentos` | TEXT | NO | Lista de medicamentos (nombre, dosis, frecuencia) |
| `indicaciones` | TEXT | NO | Instrucciones de uso para el paciente |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `documentos_adjuntos`
Metadatos de archivos digitales asociados a una historia clínica.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `historia_clinica_id` | BIGINT UNSIGNED | NO | FK → `historias_clinicas.id` |
| `nombre_archivo` | VARCHAR | NO | Nombre original del archivo (ej: `resultado_hemograma.pdf`) |
| `ruta_almacenamiento` | VARCHAR | NO | Ruta en el Storage de Laravel |
| `tipo_mime` | VARCHAR | NO | Tipo de archivo (ej: `application/pdf`, `image/jpeg`) |
| `created_at` | TIMESTAMP | SÍ | — |
| `updated_at` | TIMESTAMP | SÍ | — |

---

### `logs_auditoria`
Trazabilidad de acceso y modificación a datos sensibles. Exigido por Resolución 1995/1999.

| Columna | Tipo | Nulo | Descripción |
|---------|------|------|-------------|
| `id` | BIGINT UNSIGNED | NO | PK |
| `usuario_id` | BIGINT UNSIGNED | SÍ | FK → `users.id` (nullable: puede haber accesos sin sesión) |
| `empresa_id` | BIGINT UNSIGNED | SÍ | FK → `empresas.id` |
| `accion` | ENUM | NO | `ver`, `crear`, `actualizar`, `eliminar` |
| `modelo` | VARCHAR(100) | NO | Modelo afectado (ej: `HistoriaClinica`) |
| `modelo_id` | BIGINT UNSIGNED | NO | ID del registro afectado |
| `ip` | VARCHAR(45) | SÍ | Dirección IP del cliente (IPv4 o IPv6) |
| `detalles` | JSON | SÍ | Campos modificados (solo en actualizar/eliminar) |
| `created_at` | TIMESTAMP | NO | Generado automáticamente — **no tiene `updated_at`** (logs inmutables) |

---

## Conteo total

| Categoría | Tablas |
|-----------|--------|
| Sistema / Auth | `roles`, `users` |
| Multi-tenant base | `empresas` |
| Catálogos globales | `modalidades_cita`, `estados_cita` |
| Configuración por empresa | `portafolios`, `servicios`, `horarios_medico` |
| Clínicas | `pacientes`, `medicos`, `antecedentes_paciente` |
| Flujo de atención | `citas`, `ejecuciones_cita`, `historias_clinicas` |
| Documentos clínicos | `recetas_medicas`, `documentos_adjuntos`, `signos_vitales` |
| Auditoría | `logs_auditoria` |
| **Total** | **18 tablas** |
