# Diagrama EER — JLVS Hearth

Este documento tiene todo lo que necesitan para construir el diagrama Entidad-Relación Extendido (EER) del sistema. Incluye cada entidad con sus atributos, tipo de dato, si es clave primaria (PK), clave foránea (FK), y las relaciones entre entidades.

---

## Resumen de entidades

El sistema tiene **20 tablas de negocio** + 4 tablas internas de Laravel (que pueden omitirse del diagrama EER o ponerse en un grupo aparte).

| # | Entidad | Descripción corta |
|---|---------|-------------------|
| 1 | `empresas` | IPS clientes del sistema |
| 2 | `roles` | Perfiles de acceso (administrador, médico, etc.) |
| 3 | `users` | Usuarios que pueden iniciar sesión |
| 4 | `pacientes` | Datos personales de pacientes |
| 5 | `medicos` | Perfil profesional de los médicos |
| 6 | `horarios_medico` | Disponibilidad semanal de cada médico |
| 7 | `portafolios` | Convenios y EPS de cada IPS |
| 8 | `servicios` | Catálogo de procedimientos de la IPS |
| 9 | `modalidades_cita` | Tipo de atención (presencial, telemedicina) |
| 10 | `estados_cita` | Estado del ciclo de vida de una cita |
| 11 | `cie10` | Clasificación Internacional de Enfermedades |
| 12 | `citas` | Agendamiento de consultas médicas |
| 13 | `ejecuciones_cita` | Registro real de la atención |
| 14 | `historias_clinicas` | Documento clínico principal |
| 15 | `signos_vitales` | Mediciones clínicas por consulta |
| 16 | `antecedentes_paciente` | Historial médico permanente del paciente |
| 17 | `recetas_medicas` | Prescripciones médicas |
| 18 | `documentos_adjuntos` | Archivos adjuntos de la historia |
| 19 | `valoraciones` | Calificación del paciente a la consulta |
| 20 | `logs_auditoria` | Trazabilidad de acceso (exigido por ley) |

---

## Entidades con atributos

---

### 1. `empresas`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| nit | VARCHAR(20) | UNIQUE, NOT NULL |
| nombre | VARCHAR(255) | NOT NULL |
| telefono | VARCHAR(20) | NULL |
| correo | VARCHAR(255) | NULL |
| direccion | VARCHAR(500) | NULL |
| ciudad | VARCHAR(100) | NULL |
| activo | BOOLEAN | NOT NULL, default TRUE |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 2. `roles`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| nombre | VARCHAR(50) | UNIQUE, NOT NULL |
| descripcion | VARCHAR(255) | NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

> Valores: `administrador`, `medico`, `gestor_citas`, `paciente`

---

### 3. `users`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *empresa_id* | BIGINT | FK → empresas.id, NOT NULL |
| *rol_id* | BIGINT | FK → roles.id, NOT NULL |
| nombre | VARCHAR(150) | NOT NULL |
| email | VARCHAR(255) | UNIQUE, NOT NULL |
| identificacion | VARCHAR(20) | UNIQUE, NOT NULL |
| password | VARCHAR(255) | NOT NULL |
| activo | BOOLEAN | NOT NULL, default TRUE |
| debe_cambiar_password | BOOLEAN | NOT NULL, default FALSE |
| email_verified_at | TIMESTAMP | NULL |
| remember_token | VARCHAR(100) | NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 4. `pacientes`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *usuario_id* | BIGINT | FK → users.id, NULL |
| *empresa_id* | BIGINT | FK → empresas.id, NOT NULL |
| nombre_completo | VARCHAR(150) | NOT NULL |
| fecha_nacimiento | DATE | NOT NULL |
| sexo | ENUM | NOT NULL ('M','F','Otro') |
| telefono | VARCHAR(20) | NOT NULL |
| correo | VARCHAR(255) | NULL |
| direccion | VARCHAR(500) | NULL |
| identificacion | VARCHAR(20) | NOT NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

> Índice único compuesto: `(identificacion, empresa_id)`

---

### 5. `medicos`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *usuario_id* | BIGINT | FK → users.id, UNIQUE, NOT NULL |
| *empresa_id* | BIGINT | FK → empresas.id, NOT NULL |
| especialidad | VARCHAR(255) | NOT NULL |
| registro_medico | VARCHAR(100) | UNIQUE, NOT NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 6. `horarios_medico`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *medico_id* | BIGINT | FK → medicos.id, NOT NULL |
| *empresa_id* | BIGINT | FK → empresas.id, NOT NULL |
| dia_semana | TINYINT | NOT NULL (0=dom … 6=sáb) |
| hora_inicio | TIME | NOT NULL |
| hora_fin | TIME | NOT NULL |
| activo | BOOLEAN | NOT NULL, default TRUE |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 7. `portafolios`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *empresa_id* | BIGINT | FK → empresas.id, NOT NULL |
| nombre_convenio | VARCHAR(255) | NOT NULL |
| descripcion | TEXT | NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 8. `servicios`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *empresa_id* | BIGINT | FK → empresas.id, NOT NULL |
| nombre | VARCHAR(150) | NOT NULL |
| descripcion | TEXT | NULL |
| duracion_minutos | SMALLINT | NOT NULL, default 30 |
| activo | BOOLEAN | NOT NULL, default TRUE |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

> Índice único compuesto: `(nombre, empresa_id)`

---

### 9. `modalidades_cita`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| nombre | VARCHAR(100) | UNIQUE, NOT NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

> Catálogo global. Valores típicos: `Presencial`, `Telemedicina`, `Domiciliaria`

---

### 10. `estados_cita`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| nombre | VARCHAR(100) | UNIQUE, NOT NULL |
| color_hex | VARCHAR(7) | NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

> Catálogo global. Valores: `Pendiente`, `Confirmada`, `Atendida`, `Cancelada`, `No asistió`

---

### 11. `cie10`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| codigo | VARCHAR(10) | UNIQUE, NOT NULL |
| descripcion | VARCHAR(255) | NOT NULL |
| categoria | VARCHAR(3) | NOT NULL, indexado |

> Catálogo global. Sin timestamps (datos estáticos). Ejemplo: `J00` / `Rinofaringitis aguda`

---

### 12. `citas`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *empresa_id* | BIGINT | FK → empresas.id, NOT NULL |
| *medico_id* | BIGINT | FK → medicos.id, NOT NULL |
| *paciente_id* | BIGINT | FK → pacientes.id, NOT NULL |
| *estado_id* | BIGINT | FK → estados_cita.id, NOT NULL |
| *modalidad_id* | BIGINT | FK → modalidades_cita.id, NOT NULL |
| *portafolio_id* | BIGINT | FK → portafolios.id, NULL |
| *servicio_id* | BIGINT | FK → servicios.id, NULL |
| fecha | DATE | NOT NULL |
| hora | TIME | NOT NULL |
| activo | BOOLEAN | NOT NULL, default TRUE |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 13. `ejecuciones_cita`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *cita_id* | BIGINT | FK → citas.id, UNIQUE, NOT NULL |
| inicio_atencion | DATETIME | NOT NULL |
| fin_atencion | DATETIME | NULL |
| duracion_minutos | INT | NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 14. `historias_clinicas`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *ejecucion_cita_id* | BIGINT | FK → ejecuciones_cita.id, UNIQUE, NOT NULL |
| *paciente_id* | BIGINT | FK → pacientes.id, NOT NULL |
| motivo_consulta | TEXT | NOT NULL |
| enfermedad_actual | TEXT | NOT NULL |
| antecedentes | JSON | NULL |
| diagnostico | TEXT | NOT NULL |
| codigo_cie10 | VARCHAR(10) | NULL (referencia a cie10.codigo) |
| descripcion_cie10 | VARCHAR(255) | NULL |
| plan_tratamiento | TEXT | NOT NULL |
| evaluacion | TEXT | NULL |
| observaciones | TEXT | NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 15. `signos_vitales`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *ejecucion_cita_id* | BIGINT | FK → ejecuciones_cita.id, UNIQUE, NOT NULL |
| *paciente_id* | BIGINT | FK → pacientes.id, NOT NULL |
| peso_kg | DECIMAL(5,2) | NULL |
| talla_cm | DECIMAL(5,2) | NULL |
| presion_sistolica | SMALLINT | NULL |
| presion_diastolica | SMALLINT | NULL |
| temperatura_c | DECIMAL(4,1) | NULL |
| frecuencia_cardiaca | SMALLINT | NULL |
| saturacion_oxigeno | TINYINT | NULL |
| frecuencia_respiratoria | SMALLINT | NULL |
| observaciones | TEXT | NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 16. `antecedentes_paciente`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *paciente_id* | BIGINT | FK → pacientes.id, NOT NULL |
| tipo | ENUM | NOT NULL ('personal','familiar','quirurgico','alergico','farmacologico','otros') |
| descripcion | TEXT | NOT NULL |
| activo | BOOLEAN | NOT NULL, default TRUE |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 17. `recetas_medicas`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *historia_clinica_id* | BIGINT | FK → historias_clinicas.id, NOT NULL |
| medicamentos | TEXT | NOT NULL |
| indicaciones | TEXT | NOT NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 18. `documentos_adjuntos`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *historia_clinica_id* | BIGINT | FK → historias_clinicas.id, NOT NULL |
| nombre_archivo | VARCHAR(255) | NOT NULL |
| ruta_almacenamiento | VARCHAR(500) | NOT NULL |
| tipo_mime | VARCHAR(100) | NOT NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 19. `valoraciones`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *cita_id* | BIGINT | FK → citas.id, UNIQUE, NOT NULL |
| *paciente_id* | BIGINT | FK → pacientes.id, NOT NULL |
| puntuacion | TINYINT | NOT NULL (1 a 5) |
| comentario | TEXT | NULL |
| created_at | TIMESTAMP | NULL |
| updated_at | TIMESTAMP | NULL |

---

### 20. `logs_auditoria`

| Atributo | Tipo | Restricción |
|----------|------|------------|
| **id** | BIGINT | PK, autoincremental |
| *usuario_id* | BIGINT | FK → users.id, NULL |
| *empresa_id* | BIGINT | FK → empresas.id, NULL |
| accion | ENUM | NOT NULL ('ver','crear','actualizar','eliminar') |
| modelo | VARCHAR(100) | NOT NULL |
| modelo_id | BIGINT | NOT NULL |
| ip | VARCHAR(45) | NULL |
| detalles | JSON | NULL |
| created_at | TIMESTAMP | NOT NULL |

> **No tiene `updated_at`** — los logs son inmutables por diseño legal.

---

## Relaciones entre entidades

Esta sección describe cada relación en forma textual para que puedan representarla en el diagrama.

---

### Relaciones de `empresas`

| Relación | Tipo | Descripción |
|----------|------|-------------|
| empresas → users | **1 a N** | Una empresa tiene muchos usuarios |
| empresas → pacientes | **1 a N** | Una empresa tiene muchos pacientes |
| empresas → medicos | **1 a N** | Una empresa tiene muchos médicos |
| empresas → portafolios | **1 a N** | Una empresa tiene muchos convenios |
| empresas → servicios | **1 a N** | Una empresa tiene muchos servicios |
| empresas → horarios_medico | **1 a N** | Una empresa tiene muchos horarios |
| empresas → citas | **1 a N** | Una empresa tiene muchas citas |

---

### Relaciones de `roles`

| Relación | Tipo | Descripción |
|----------|------|-------------|
| roles → users | **1 a N** | Un rol puede tener muchos usuarios |

---

### Relaciones de `users`

| Relación | Tipo | Descripción |
|----------|------|-------------|
| users → medicos | **1 a 1** | Un usuario puede tener un perfil médico (opcional) |
| users → pacientes | **1 a 1** | Un usuario puede tener un perfil de paciente (opcional) |
| users → logs_auditoria | **1 a N** | Un usuario genera muchos logs |

---

### Relaciones de `pacientes`

| Relación | Tipo | Descripción |
|----------|------|-------------|
| pacientes → citas | **1 a N** | Un paciente puede tener muchas citas |
| pacientes → historias_clinicas | **1 a N** | Un paciente puede tener muchas historias |
| pacientes → signos_vitales | **1 a N** | Un paciente puede tener muchos registros de signos vitales |
| pacientes → antecedentes_paciente | **1 a N** | Un paciente puede tener muchos antecedentes |
| pacientes → valoraciones | **1 a N** | Un paciente puede hacer muchas valoraciones |

---

### Relaciones de `medicos`

| Relación | Tipo | Descripción |
|----------|------|-------------|
| medicos → horarios_medico | **1 a N** | Un médico tiene muchos bloques horarios |
| medicos → citas | **1 a N** | Un médico puede tener muchas citas asignadas |

---

### Relaciones de `citas`

| Relación | Tipo | Descripción |
|----------|------|-------------|
| citas → ejecuciones_cita | **1 a 1** | Una cita puede generar exactamente una ejecución |
| citas → valoraciones | **1 a 1** | Una cita puede tener exactamente una valoración |
| estados_cita → citas | **1 a N** | Un estado puede estar en muchas citas |
| modalidades_cita → citas | **1 a N** | Una modalidad puede estar en muchas citas |
| portafolios → citas | **1 a N** | Un portafolio puede estar en muchas citas |
| servicios → citas | **1 a N** | Un servicio puede estar en muchas citas |

---

### Relaciones de `ejecuciones_cita`

| Relación | Tipo | Descripción |
|----------|------|-------------|
| ejecuciones_cita → historias_clinicas | **1 a 1** | Una ejecución genera exactamente una historia clínica |
| ejecuciones_cita → signos_vitales | **1 a 1** | Una ejecución tiene exactamente un registro de signos vitales |

---

### Relaciones de `historias_clinicas`

| Relación | Tipo | Descripción |
|----------|------|-------------|
| historias_clinicas → recetas_medicas | **1 a N** | Una historia puede tener muchas recetas |
| historias_clinicas → documentos_adjuntos | **1 a N** | Una historia puede tener muchos documentos |

---

### Relaciones de `cie10`

| Relación | Tipo | Descripción |
|----------|------|-------------|
| cie10 → historias_clinicas | **1 a N** (referencia) | Un código CIE-10 puede aparecer en muchas historias. La relación es por valor (`codigo_cie10` guarda el string), no por FK estricta. |

---

## Cardinalidades para el diagrama

Para el diagrama EER, estas son las cardinalidades en notación mínima:máxima:

| Entidad A | Relación | Entidad B | Cardinalidad A | Cardinalidad B |
|-----------|----------|-----------|----------------|----------------|
| empresas | tiene | users | 1 | N |
| empresas | tiene | pacientes | 1 | N |
| empresas | tiene | medicos | 1 | N |
| empresas | tiene | portafolios | 1 | N |
| empresas | tiene | servicios | 1 | N |
| roles | define | users | 1 | N |
| users | es | medicos | 1 | 0..1 |
| users | es | pacientes | 0..1 | 0..1 |
| medicos | tiene | horarios_medico | 1 | N |
| medicos | atiende | citas | 1 | N |
| pacientes | agenda | citas | 1 | N |
| pacientes | tiene | antecedentes_paciente | 1 | N |
| estados_cita | clasifica | citas | 1 | N |
| modalidades_cita | define | citas | 1 | N |
| portafolios | cubre | citas | 0..1 | N |
| servicios | es en | citas | 0..1 | N |
| citas | genera | ejecuciones_cita | 1 | 0..1 |
| citas | recibe | valoraciones | 1 | 0..1 |
| ejecuciones_cita | produce | historias_clinicas | 1 | 0..1 |
| ejecuciones_cita | registra | signos_vitales | 1 | 0..1 |
| historias_clinicas | incluye | recetas_medicas | 1 | N |
| historias_clinicas | tiene | documentos_adjuntos | 1 | N |
| pacientes | hace | valoraciones | 1 | N |

---

## Agrupación sugerida para el diagrama

Agrupar las entidades en módulos ayuda a que el diagrama quede ordenado:

```
┌─────────────────────────────────────────────────────────────┐
│  MÓDULO SISTEMA                                             │
│  empresas   roles   users                                   │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  MÓDULO CLÍNICO                                             │
│  pacientes   medicos   antecedentes_paciente                │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  MÓDULO CONFIGURACIÓN IPS                                   │
│  portafolios   servicios   horarios_medico                  │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  CATÁLOGOS GLOBALES                                         │
│  modalidades_cita   estados_cita   cie10                    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  MÓDULO DE ATENCIÓN (flujo principal)                       │
│  citas → ejecuciones_cita → historias_clinicas              │
│                           → signos_vitales                  │
│          historias_clinicas → recetas_medicas               │
│                             → documentos_adjuntos           │
│  citas → valoraciones                                       │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  MÓDULO AUDITORÍA                                           │
│  logs_auditoria                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## Tablas internas de Laravel (no incluir en el EER de negocio)

Estas tablas las crea Laravel automáticamente y no forman parte del modelo de negocio del sistema. Si el diagrama pide mostrar **todas** las tablas de la BD, inclúyanlas en un grupo separado etiquetado como "Sistema / Framework":

| Tabla | Para qué sirve |
|-------|----------------|
| `password_reset_tokens` | Tokens temporales para recuperación de contraseña |
| `sessions` | Sesiones de usuarios activos |
| `cache` | Caché de datos para mejorar rendimiento |
| `jobs` | Cola de tareas asíncronas (envío de correos) |
