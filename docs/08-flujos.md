# Flujos del Sistema — JLVS Hearth

Descripción de todos los flujos de negocio del sistema, en el orden en que ocurren en la vida real.

---

## Índice

1. [Registro de nueva IPS (Onboarding)](#1-registro-de-nueva-ips-onboarding)
2. [Login y autenticación](#2-login-y-autenticación)
3. [Recuperación de contraseña](#3-recuperación-de-contraseña)
4. [Registro de paciente](#4-registro-de-paciente)
5. [Gestión de usuarios internos](#5-gestión-de-usuarios-internos)
6. [Gestión de médicos](#6-gestión-de-médicos)
7. [Gestión de horarios del médico](#7-gestión-de-horarios-del-médico)
8. [Agendamiento de cita](#8-agendamiento-de-cita)
9. [Atención de la cita (flujo principal clínico)](#9-atención-de-la-cita-flujo-principal-clínico)
10. [Recetas y documentos adjuntos](#10-recetas-y-documentos-adjuntos)
11. [Consulta de historial por el paciente](#11-consulta-de-historial-por-el-paciente)
12. [Administración de la empresa](#12-administración-de-la-empresa)
13. [Auditoría](#13-auditoría)

---

## 1. Registro de nueva IPS (Onboarding)

**Quién:** Público (sin autenticación)
**Cuándo:** Una nueva IPS adquiere el sistema por primera vez

```
POST /empresas
```

**Body:**
```json
{
  "nit": "900123456-1",
  "nombre": "Clínica Salud Total",
  "telefono": "6017001234",
  "correo": "contacto@saludtotal.co",
  "direccion": "Calle 100 # 15-20",
  "ciudad": "Bogotá",
  "admin_nombre": "Juan Pérez",
  "admin_email": "juan@saludtotal.co",
  "admin_identificacion": "12345678",
  "admin_password": "secreto123",
  "admin_password_confirmation": "secreto123"
}
```

**Resultado:**
- Se crea la empresa en `empresas`
- Se crea el usuario administrador en `users` con `rol = administrador`
- Todo en una sola transacción (si algo falla, no queda nada a medias)

**Respuesta `201`:**
```json
{
  "empresa": { "id": 1, "nombre": "Clínica Salud Total", ... },
  "administrador": { "id": 1, "nombre": "Juan Pérez", "email": "juan@saludtotal.co" }
}
```

---

## 2. Login y autenticación

**Quién:** Cualquier usuario registrado (todos los roles)
**Cuándo:** Cada vez que accede al sistema

```
POST /login
```

**Body:**
```json
{
  "email": "juan@saludtotal.co",
  "password": "secreto123",
  "remember": false
}
```

**Respuesta `200`:**
```json
{
  "message": "Sesión iniciada.",
  "usuario": {
    "id": 1,
    "nombre": "Juan Pérez",
    "email": "juan@saludtotal.co",
    "rol": { "nombre": "administrador" },
    "empresa": { "id": 1, "nombre": "Clínica Salud Total" }
  }
}
```

El frontend usa `rol.nombre` para redirigir al usuario a su vista correspondiente:

| `rol.nombre` | Vista |
|---|---|
| `administrador` | Panel de administración |
| `medico` | Agenda del médico |
| `gestor_citas` | Módulo de agendamiento |
| `paciente` | Portal del paciente |

**Errores:**
- `401` — credenciales incorrectas
- `403` — cuenta desactivada por el administrador

**Cerrar sesión:**
```
POST /logout
```

**Consultar usuario activo:**
```
GET /me
```

---

## 3. Recuperación de contraseña

**Quién:** Cualquier usuario (público, sin autenticación)
**Cuándo:** El usuario olvidó su contraseña

### Paso 1 — Solicitar enlace

```
POST /forgot-password
Body: { "email": "juan@saludtotal.co" }
```

- Laravel genera un token único y lo guarda en `password_reset_tokens`
- Envía un correo al email con el enlace de restablecimiento (via Gmail SMTP)
- El enlace contiene el token

**Respuesta `200`:**
```json
{ "message": "Te enviamos un enlace para restablecer tu contraseña. Revisa tu correo." }
```

### Paso 2 — Restablecer contraseña

```
POST /reset-password
Body:
{
  "token": "abc123...",
  "email": "juan@saludtotal.co",
  "password": "nueva_clave",
  "password_confirmation": "nueva_clave"
}
```

- Laravel valida el token (expira en 60 minutos por defecto)
- Actualiza la contraseña del usuario

**Respuesta `200`:**
```json
{ "message": "Contraseña restablecida correctamente. Ya puedes iniciar sesión." }
```

---

## 4. Registro de paciente

**Quién:** Público (sin autenticación)
**Cuándo:** Un paciente quiere crearse su cuenta en el portal de pacientes

```
POST /registro-paciente
```

**Body:**
```json
{
  "empresa_id": 1,
  "nombre_completo": "María López",
  "identificacion": "987654321",
  "fecha_nacimiento": "1990-05-15",
  "sexo": "F",
  "telefono": "3001234567",
  "direccion": "Carrera 7 # 32-10",
  "email": "maria@correo.com",
  "password": "clave1234",
  "password_confirmation": "clave1234"
}
```

**Resultado:**
- Se crea el `User` con `rol = paciente`
- Se crea el `Paciente` vinculado al usuario via `usuario_id`
- La identificación debe ser única dentro de la empresa (`empresa_id`)

> El `empresa_id` define a qué IPS pertenece el paciente.
> El frontend de pacientes debe enviarlo automáticamente según la IPS que esté usando.

---

## 5. Gestión de usuarios internos

**Quién:** `administrador`
**Cuándo:** Necesita crear, ver o desactivar médicos y gestores de citas

### Crear usuario interno

```
POST /usuarios
Body:
{
  "nombre": "Ana Gómez",
  "email": "ana@saludtotal.co",
  "identificacion": "11223344",
  "rol_id": 3,
  "password": "clave1234",
  "password_confirmation": "clave1234"
}
```

> Solo se pueden asignar roles `medico` o `gestor_citas`. No se puede crear otro `administrador` ni un `paciente` desde aquí.

### Listar usuarios internos

```
GET /usuarios
```
Devuelve todos los usuarios de la empresa excepto pacientes.

### Actualizar usuario

```
PUT /usuarios/{id}
Body: { "nombre": "Ana Gómez Rodríguez" }
```

### Desactivar usuario

```
DELETE /usuarios/{id}
```
No borra el registro — marca `activo = false`. El usuario no podrá iniciar sesión.

---

## 6. Gestión de médicos

**Quién:** `administrador`
**Cuándo:** Necesita registrar el perfil profesional de un médico

> Un médico es primero un `Usuario` (creado en el flujo 5) y luego se le crea su perfil médico.

### Crear perfil médico

```
POST /medicos
Body:
{
  "usuario_id": 5,
  "especialidad": "Medicina General",
  "registro_medico": "RM-99999"
}
```

### Listar médicos

```
GET /medicos         ← accesible a administrador y gestor_citas
GET /medicos/{id}
```

### Actualizar / Eliminar

```
PUT /medicos/{id}
DELETE /medicos/{id}
```

---

## 7. Gestión de horarios del médico

**Quién:** `administrador`
**Cuándo:** Define en qué días y horas atiende cada médico

```
POST /horarios
Body:
{
  "medico_id": 2,
  "dia_semana": 1,
  "hora_inicio": "08:00",
  "hora_fin": "12:00"
}
```

| `dia_semana` | Día |
|---|---|
| 0 | Domingo |
| 1 | Lunes |
| 2 | Martes |
| 3 | Miércoles |
| 4 | Jueves |
| 5 | Viernes |
| 6 | Sábado |

Un médico puede tener **varios bloques** el mismo día (mañana y tarde):
```json
{ "medico_id": 2, "dia_semana": 1, "hora_inicio": "08:00", "hora_fin": "12:00" }
{ "medico_id": 2, "dia_semana": 1, "hora_inicio": "14:00", "hora_fin": "18:00" }
```

**Filtros disponibles:**
```
GET /horarios?medico_id=2
GET /horarios?dia=1
GET /horarios?medico_id=2&dia=1
```

---

## 8. Agendamiento de cita

**Quién:** `administrador`, `gestor_citas`
**Cuándo:** Se agenda una consulta para un paciente

```
POST /citas
Body:
{
  "paciente_id": 3,
  "medico_id": 2,
  "estado_id": 1,
  "modalidad_id": 1,
  "portafolio_id": 1,
  "servicio_id": 1,
  "fecha": "2026-04-10",
  "hora": "09:00"
}
```

**Validaciones automáticas:**
1. El médico, paciente y portafolio deben pertenecer a la misma empresa
2. La fecha debe ser igual o posterior a hoy
3. El médico debe tener horario definido ese día y hora (`horarios_medico`)
4. El médico no debe tener otra cita activa a esa misma hora

**Errores posibles:**
- `422 "El médico no tiene disponibilidad el día y hora indicados."` — no hay horario
- `422 "El médico ya tiene una cita programada en ese horario."` — solapamiento

### Consultar citas

```
GET /citas
GET /citas/{id}
```

Cada rol ve cosas distintas:
- `administrador` / `gestor_citas` → todas las citas de la empresa
- `medico` → solo sus propias citas
- `paciente` → solo sus propias citas

### Cancelar cita

```
DELETE /citas/{id}
```
No borra — marca `activo = false`.

---

## 9. Atención de la cita (flujo principal clínico)

**Quién:** `medico` (con apoyo de `administrador`)
**Cuándo:** El paciente llega a la IPS y es atendido

Este es el flujo más importante del sistema. Debe ejecutarse en orden:

---

### Paso 1 — Iniciar atención (crear ejecución)

El médico marca el inicio real de la consulta.

```
POST /ejecuciones
Body:
{
  "cita_id": 5,
  "inicio_atencion": "2026-04-10 09:05:00"
}
```

Esto crea el registro en `ejecuciones_cita`. Guarda el `id` de la ejecución creada — se necesita en todos los pasos siguientes.

---

### Paso 2 — Registrar signos vitales

Con el paciente ya presente, se toman las mediciones clínicas.

```
POST /signos-vitales
Body:
{
  "ejecucion_cita_id": 8,
  "paciente_id": 3,
  "peso_kg": 70.5,
  "talla_cm": 168.0,
  "presion_sistolica": 120,
  "presion_diastolica": 80,
  "temperatura_c": 36.5,
  "frecuencia_cardiaca": 72,
  "saturacion_oxigeno": 98,
  "frecuencia_respiratoria": 16
}
```

> Todos los campos de medición son opcionales — depende del tipo de consulta.

---

### Paso 3 — Crear historia clínica

El médico documenta la consulta.

```
POST /historias-clinicas
Body:
{
  "ejecucion_cita_id": 8,
  "paciente_id": 3,
  "motivo_consulta": "Dolor de cabeza persistente desde hace 3 días.",
  "enfermedad_actual": "Cefalea tensional sin fiebre ni náuseas.",
  "antecedentes": {
    "alergias": "Penicilina",
    "medicamentos_actuales": "Ninguno"
  },
  "diagnostico": "Cefalea tensional (G44.2)",
  "plan_tratamiento": "Ibuprofeno 400mg cada 8 horas por 5 días. Reposo.",
  "evaluacion": null,
  "observaciones": "Paciente en buen estado general."
}
```

> El campo `antecedentes` es JSON libre — puede contener cualquier estructura.
> La historia queda automáticamente registrada en `logs_auditoria` (Observer).

---

### Paso 4 — Cerrar la ejecución

El médico marca el fin de la consulta. Laravel puede calcular `duracion_minutos` automáticamente.

```
PATCH /ejecuciones/{id}
Body:
{
  "fin_atencion": "2026-04-10 09:25:00",
  "duracion_minutos": 20
}
```

---

## 10. Recetas y documentos adjuntos

**Quién:** `medico`, `administrador`
**Cuándo:** Durante o después de la consulta, vinculados a la historia clínica

### Crear receta médica

```
POST /recetas
Body:
{
  "historia_clinica_id": 12,
  "medicamentos": "Ibuprofeno 400mg — 1 tableta cada 8 horas por 5 días.\nLoratadina 10mg — 1 tableta diaria.",
  "indicaciones": "Tomar con alimentos. No conducir después de tomar Loratadina."
}
```

Una historia clínica puede tener **varias recetas** (ej: una para medicamentos, otra para exámenes).

### Adjuntar documento

```
POST /documentos
Content-Type: multipart/form-data
Body:
{
  "historia_clinica_id": 12,
  "archivo": [archivo PDF o imagen],
  "nombre": "Resultado hemograma",
  "tipo": "resultado_laboratorio"
}
```

El archivo se guarda en `storage/app/documentos/`. Solo se guardan los metadatos en la base de datos.

---

## 11. Consulta de historial por el paciente

**Quién:** `paciente`
**Cuándo:** El paciente consulta su información desde el portal

```
GET /citas                  → sus citas (activas y pasadas)
GET /historias-clinicas     → sus historias clínicas
GET /recetas                → sus recetas médicas
GET /documentos             → sus documentos adjuntos
GET /signos-vitales         → su historial de signos vitales
GET /antecedentes           → sus antecedentes médicos
```

> El sistema filtra automáticamente — el paciente solo puede ver sus propios registros.
> Intentar acceder a registros de otro paciente retorna `403`.

### Antecedentes médicos

Los antecedentes son el historial médico permanente del paciente (no de una sola consulta):

```
GET /antecedentes?paciente_id=3
GET /antecedentes?tipo=alergico
```

| Tipo | Descripción |
|------|-------------|
| `personal` | Enfermedades crónicas, condiciones previas |
| `familiar` | Antecedentes hereditarios |
| `quirurgico` | Cirugías realizadas |
| `alergico` | Alergias a medicamentos, alimentos, etc. |
| `farmacologico` | Medicamentos de uso habitual |
| `otros` | Cualquier otro antecedente relevante |

---

## 12. Administración de la empresa

**Quién:** `administrador`
**Cuándo:** Gestiona la configuración de su IPS

### Ver y actualizar datos de la empresa

```
GET /mi-empresa
PUT /mi-empresa
Body: { "telefono": "6017009999", "ciudad": "Medellín" }
```

### Gestionar portafolios (convenios/EPS)

```
GET /portafolios
POST /portafolios
Body: { "nombre_convenio": "EPS Sura", "descripcion": "Convenio con EPS Sura" }

PUT /portafolios/{id}
DELETE /portafolios/{id}
```

### Gestionar catálogos

**Modalidades de cita:**
```
GET /modalidades-cita           ← todos los roles autenticados
POST /modalidades-cita          ← solo admin
PUT /modalidades-cita/{id}
DELETE /modalidades-cita/{id}
Body: { "nombre": "Telemedicina" }
```

**Estados de cita:**
```
GET /estados-cita               ← todos los roles autenticados
POST /estados-cita              ← solo admin
PUT /estados-cita/{id}
DELETE /estados-cita/{id}
Body: { "nombre": "Confirmada", "color_hex": "#22C55E" }
```

**Servicios / Procedimientos:**
```
GET /servicios                  ← admin, medico, gestor_citas
POST /servicios                 ← solo admin
PUT /servicios/{id}
DELETE /servicios/{id}
Body: { "nombre": "Consulta Medicina General", "duracion_minutos": 20 }
```

---

## 13. Auditoría

**Quién:** `administrador`
**Cuándo:** Consulta quién accedió o modificó historias clínicas (Resolución 1995/1999)

```
GET /logs
GET /logs/{id}
```

**Filtros disponibles:**
```
GET /logs?usuario_id=5
GET /logs?accion=actualizar
GET /logs?modelo=HistoriaClinica
GET /logs?desde=2026-04-01&hasta=2026-04-30
```

**Acciones registradas automáticamente:**

| Acción | Cuándo |
|--------|--------|
| `ver` | Alguien consulta una historia clínica |
| `crear` | Se crea una historia clínica |
| `actualizar` | Se modifica una historia clínica (guarda qué campos cambiaron) |
| `eliminar` | Se elimina una historia clínica |

> Los logs son **inmutables** — no tienen `updated_at` y no pueden modificarse ni eliminarse desde la API.

---

## Resumen de roles y accesos por flujo

| Flujo | administrador | medico | gestor_citas | paciente | público |
|-------|:---:|:---:|:---:|:---:|:---:|
| Registro IPS | — | — | — | — | ✓ |
| Login / Logout | ✓ | ✓ | ✓ | ✓ | ✓ |
| Recuperar contraseña | ✓ | ✓ | ✓ | ✓ | ✓ |
| Registro de paciente | — | — | — | — | ✓ |
| Gestión de usuarios | ✓ | — | — | — | — |
| Gestión de médicos (escritura) | ✓ | — | — | — | — |
| Gestión de médicos (lectura) | ✓ | — | ✓ | — | — |
| Horarios de médicos | ✓ | — | ✓ | — | — |
| Agendar citas | ✓ | — | ✓ | — | — |
| Ver citas | ✓ | ✓ (propias) | ✓ | ✓ (propias) | — |
| Iniciar ejecución | ✓ | ✓ | — | — | — |
| Signos vitales (escritura) | ✓ | ✓ | — | — | — |
| Historia clínica (escritura) | ✓ | ✓ | — | — | — |
| Recetas y documentos (escritura) | ✓ | ✓ | — | — | — |
| Consulta historial | ✓ | ✓ | — | ✓ (propio) | — |
| Administración empresa | ✓ | — | — | — | — |
| Auditoría | ✓ | — | — | — | — |
