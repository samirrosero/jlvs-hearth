# Flujos del Sistema — JLVS Hearth

Descripción de todos los flujos de negocio del sistema, en el orden en que ocurren en la vida real.

---

## Índice

1. [Registro de nueva IPS (Onboarding)](#1-registro-de-nueva-ips-onboarding)
2. [Login y autenticación](#2-login-y-autenticación)
3. [Recuperación de contraseña](#3-recuperación-de-contraseña)
4. [Registro de paciente](#4-registro-de-paciente)
5. [Registro público de afiliados (nuevo)](#5-registro-público-de-afiliados)
6. [Registro público de empleadores (nuevo)](#6-registro-público-de-empleadores)
7. [Gestión de solicitudes de empleadores (nuevo)](#7-gestión-de-solicitudes-de-empleadores)
8. [Branding e identidad visual (nuevo)](#8-branding-e-identidad-visual)
9. [Gestión de usuarios internos](#9-gestión-de-usuarios-internos)
10. [Gestión de médicos](#10-gestión-de-médicos)
11. [Gestión de horarios del médico](#11-gestión-de-horarios-del-médico)
12. [Agendamiento de cita](#12-agendamiento-de-cita)
13. [Atención de la cita (flujo principal clínico)](#13-atención-de-la-cita-flujo-principal-clínico)
14. [Recetas y documentos adjuntos](#14-recetas-y-documentos-adjuntos)
15. [Consulta de historial por el paciente](#15-consulta-de-historial-por-el-paciente)
16. [Administración de la empresa](#16-administración-de-la-empresa)
17. [Auditoría](#17-auditoría)

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

## 5. Registro público de afiliados

**Quién:** Público (sin autenticación) - Vista Blade `/registro`
**Cuándo:** Un paciente quiere registrarse directamente en el portal

```
POST /registro/afiliado
```

**Body:**
```json
{
  "tipo_documento": "CC",
  "numero_documento": "1234567890",
  "nombres": "María Elena",
  "apellidos": "Gómez Pérez",
  "correo": "maria@correo.com",
  "correo_confirmation": "maria@correo.com",
  "password": "claveSegura123",
  "password_confirmation": "claveSegura123"
}
```

**Resultado:**
- Se crea el `User` con `rol = paciente`
- Se crea el `Paciente` vinculado al usuario
- El paciente puede iniciar sesión inmediatamente

**Validaciones:**
- El número de documento no puede existir ya en la IPS
- El correo debe ser único en la IPS
- Contraseña mínimo 8 caracteres

---

## 6. Registro público de empleadores

**Quién:** Público (sin autenticación) - Vista Blade `/registro`
**Cuándo:** Un médico, gestor o administrador quiere solicitar acceso al sistema

```
POST /registro/empleador
```

**Body:**
```json
{
  "tipo_documento": "CC",
  "numero_documento": "9876543210",
  "rol_solicitado": "medico",
  "nombres": "Dr. Carlos",
  "apellidos": "Rodríguez López",
  "departamento": "Bogotá D.C.",
  "municipio": "Bogotá",
  "correo": "carlos@clinica.com",
  "correo_confirmation": "carlos@clinica.com",
  "password": "claveSegura123",
  "password_confirmation": "claveSegura123",
  "foto_documento": [archivo imagen opcional]
}
```

**Roles disponibles:** `administrador`, `medico`, `gestor_citas`

**Resultado:**
- Se crea una `SolicitudEmpleador` en estado `pendiente`
- El administrador recibe la solicitud para revisión
- El solicitante debe esperar aprobación

**Validaciones:**
- No puede haber una solicitud pendiente con el mismo correo
- El correo no puede estar registrado ya en el sistema
- La foto del documento es opcional pero recomendada

---

## 7. Gestión de solicitudes de empleadores

**Quién:** `administrador`
**Cuándo:** Desde el panel `/admin/solicitudes`

### Ver solicitudes pendientes

```
GET /admin/solicitudes
```

Muestra:
- Solicitudes agrupadas por rol solicitado
- Solicitudes aprobadas (últimas 20)
- Solicitudes rechazadas (últimas 20)

### Aprobar solicitud

```
PATCH /admin/solicitudes/{id}/aprobar
```

**Resultado:**
- Se crea el `User` con los datos de la solicitud
- Se asigna el rol solicitado
- La solicitud cambia a estado `aprobado`
- El usuario puede iniciar sesión inmediatamente

### Rechazar solicitud

```
PATCH /admin/solicitudes/{id}/rechazar
```

**Body:**
```json
{
  "observaciones": "Documento no legible, favor enviar nueva foto"
}
```

**Resultado:**
- La solicitud cambia a estado `rechazado`
- Se guardan las observaciones para el solicitante

---

## 8. Branding e identidad visual

**Quién:** `administrador`
**Cuándo:** Desde el panel `/admin/branding`

### Configurar branding de la IPS

```
GET /admin/branding   → Vista del formulario
POST /admin/branding  → Guardar cambios
```

**Body (multipart/form-data):**
```
slogan_login: "Tu salud, nuestra prioridad"
slogan_registro: "Únete a la mejor red de salud"
color_primario: #1e40af
color_secundario: #1e3a8a
color_admin: #1e293b
color_doctor: #064e3b
color_gestor: #4c1d95
color_paciente: #0c4a6e
color_pdf: #1e40af
logo: [archivo imagen png/jpg/svg/webp max 2MB]
favicon: [archivo imagen png/ico/svg max 512KB]
imagen_login: [archivo imagen max 4MB]
imagen_registro: [archivo imagen max 4MB]
```

### Campos de colores

| Campo | Uso | Default |
|-------|-----|---------|
| `color_primario` | Botones, acentos, elementos activos | `#1e40af` |
| `color_secundario` | Textos de título, acentos secundarios | `#1e3a8a` |
| `color_admin` | Fondo sidebar panel administración | `#1e293b` |
| `color_doctor` | Fondo sidebar panel médico | `#064e3b` |
| `color_gestor` | Fondo sidebar panel gestor | `#4c1d95` |
| `color_paciente` | Fondo sidebar portal paciente | `#0c4a6e` |
| `color_pdf` | Encabezados y tablas en PDFs | `#1e40af` |

**Resultado:**
- Las imágenes se almacenan en `storage/app/public/empresas/{id}/`
- Los colores se aplican inmediatamente en todas las vistas
- Los PDFs usan el color configurado para encabezados

---

## 9. Gestión de usuarios internos

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

## 10. Gestión de médicos

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

## 11. Gestión de horarios del médico

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

## 12. Agendamiento de cita

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

## 13. Atención de la cita (flujo principal clínico)

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

## 14. Recetas y documentos adjuntos

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

## 15. Consulta de historial por el paciente

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

## 16. Administración de la empresa

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

## 17. Auditoría

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

---

## 14. Registro presencial por el gestor de citas

**Quién:** `gestor_citas`, `administrador`
**Cuándo:** Un paciente llega presencialmente a la IPS y el gestor lo registra en el sistema

El gestor tiene dos opciones:

### Opción A — Solo perfil (sin cuenta de acceso web)

```
POST /pacientes/registro-gestor
Body:
{
  "nombre_completo": "Carlos Muñoz",
  "identificacion": "1090401234",
  "fecha_nacimiento": "1985-03-12",
  "sexo": "M",
  "telefono": "3101234567",
  "crear_cuenta": false
}
```

Se crea el paciente sin usuario — no puede iniciar sesión en el portal.

### Opción B — Perfil + cuenta con contraseña temporal

```
POST /pacientes/registro-gestor
Body:
{
  "nombre_completo": "Carlos Muñoz",
  "identificacion": "1090401234",
  "fecha_nacimiento": "1985-03-12",
  "sexo": "M",
  "telefono": "3101234567",
  "crear_cuenta": true,
  "email_cuenta": "carlos@correo.com"
}
```

**Respuesta `201`:**
```json
{
  "message": "Paciente registrado con cuenta de acceso. Entrega la contraseña temporal al paciente.",
  "paciente": { ... },
  "password_temporal": "AXBK-4821"
}
```

El gestor anota o imprime la contraseña temporal y se la entrega al paciente. Al primer login, el sistema detecta `debe_cambiar_password: true` y el frontend redirige al formulario de cambio de contraseña.

---

## 15. Cambio de contraseña propia

**Quién:** Cualquier rol autenticado (muy importante para pacientes con contraseña temporal)
**Cuándo:** El usuario quiere cambiar su contraseña, o el sistema lo obliga en el primer login

```
POST /mi-cuenta/cambiar-password
Body:
{
  "password_actual": "AXBK-4821",
  "password": "MiNuevaClaveSegura123",
  "password_confirmation": "MiNuevaClaveSegura123"
}
```

**Respuesta `200`:**
```json
{ "message": "Contraseña actualizada correctamente." }
```

Tras el cambio, `debe_cambiar_password` queda en `false` y el usuario puede navegar normalmente.

**Errores:**
- `422 "La contraseña actual no es correcta."` — escribió mal la contraseña temporal
- `422 "La nueva contraseña no puede ser igual a la actual."` — intentó reutilizar la misma

---

## 16. Dashboard de métricas

**Quién:** `administrador`
**Cuándo:** Entra al panel de administración para ver el estado general de la IPS

```
GET /dashboard
```

**Respuesta `200`:**
```json
{
  "totales": {
    "pacientes": 150,
    "medicos": 4,
    "citas": 320,
    "citas_mes": 42,
    "ejecuciones": 290,
    "ejecuciones_mes": 38
  },
  "duracion_promedio_minutos": 24.5,
  "citas_por_estado": [
    { "estado": "Atendida", "total": 210 },
    { "estado": "Pendiente", "total": 80 },
    { "estado": "Cancelada", "total": 30 }
  ],
  "citas_por_mes": [
    { "mes": "2025-11", "total": 45 },
    { "mes": "2025-12", "total": 38 }
  ],
  "pacientes_por_mes": [ ... ],
  "especialidades_top": [
    { "especialidad": "Medicina General", "total_citas": 180 }
  ],
  "medicos_top": [
    { "medico": "Dra. Laura García", "especialidad": "Medicina General", "total_citas": 120 }
  ],
  "valoraciones": {
    "total": 95,
    "promedio": 4.3
  },
  "proximas_citas": [ ... ]
}
```

---

## 17. Reportes con filtros

**Quién:** `administrador`
**Cuándo:** Necesita exportar datos para revisión o entrega a la Secretaría de Salud

Todos los reportes aceptan filtros por querystring. Si no se envía ningún filtro, exporta todos los registros.

### Reporte de citas en PDF

```
GET /reportes/citas/pdf?fecha_desde=2026-01-01&fecha_hasta=2026-04-30&estado_id=3
```
Descarga un archivo `reporte-citas-2026-04-07.pdf` en formato tabular.

### Reporte de citas en Excel

```
GET /reportes/citas/excel?medico_id=2
```
Descarga `reporte-citas-2026-04-07.xlsx`.

### Reporte de pacientes en PDF

```
GET /reportes/pacientes/pdf?sexo=F
```

### Reporte de pacientes en Excel

```
GET /reportes/pacientes/excel?buscar=López
```

**Filtros disponibles para citas:** `fecha_desde`, `fecha_hasta`, `estado_id`, `medico_id`
**Filtros disponibles para pacientes:** `buscar` (nombre o cédula), `sexo`, `fecha_nacimiento_desde`, `fecha_nacimiento_hasta`

---

## 18. Valoraciones de consulta

**Quién:** `paciente` (crea), `administrador` y `medico` (leen)
**Cuándo:** Después de una cita, el paciente califica la atención

### El paciente califica una cita

```
POST /valoraciones
Body:
{
  "cita_id": 15,
  "puntuacion": 5,
  "comentario": "Excelente atención, muy puntual."
}
```

Reglas:
- Solo el paciente dueño de la cita puede valorarla
- Solo se puede valorar una vez por cita
- Puntuación entre 1 y 5

### Ver valoraciones

```
GET /valoraciones         ← paciente: ve las suyas / admin-medico: ven todas de la empresa
GET /valoraciones/{id}
```

### Resumen por médico (solo admin)

```
GET /valoraciones/resumen/medicos
```

**Respuesta:**
```json
[
  {
    "medico": "Dra. Laura García",
    "especialidad": "Medicina General",
    "total_valoraciones": 45,
    "promedio": 4.62,
    "cinco_estrellas": 28,
    "cuatro_estrellas": 12,
    "tres_estrellas": 4,
    "dos_estrellas": 1,
    "una_estrella": 0
  }
]
```

---

## 19. Búsqueda de CIE-10

**Quién:** `medico`, `administrador` (al crear una historia clínica)
**Cuándo:** El médico busca el código oficial de la enfermedad diagnosticada

```
GET /cie10?buscar=diabetes
GET /cie10?buscar=J00
```

**Respuesta:**
```json
[
  { "codigo": "E11.9", "descripcion": "Diabetes mellitus tipo 2 sin complicaciones", "categoria": "E" },
  { "codigo": "E10.9", "descripcion": "Diabetes mellitus tipo 1 sin complicaciones", "categoria": "E" }
]
```

El médico selecciona uno y ese `codigo` y `descripcion` se envían al crear la historia clínica:

```
POST /historias-clinicas
Body:
{
  ...,
  "diagnostico": "Paciente con DM2 compensada, buen control glucémico.",
  "codigo_cie10": "E11.9",
  "descripcion_cie10": "Diabetes mellitus tipo 2 sin complicaciones"
}
```

Ambos campos quedan en la historia clínica y aparecen en el PDF descargable.

---

## 20. Descarga de historia clínica en PDF

**Quién:** `administrador`, `medico`, `paciente` (solo la suya)
**Cuándo:** Se necesita el documento físico de la historia clínica

```
GET /historias-clinicas/{id}/pdf
```

Descarga un archivo PDF (`historia-clinica-00000001.pdf`) con:
- Encabezado de la IPS (nombre, NIT, dirección, teléfono)
- Datos del paciente
- Datos de la consulta (médico, especialidad, fecha, duración)
- Signos vitales en tabla
- Contenido clínico completo (motivo, diagnóstico con código CIE-10, tratamiento)
- Recetas médicas
- Bloque de firmas (médico + paciente)
- Referencia legal: Resolución 1995/1999 Minsalud Colombia

---

## Resumen de roles y accesos por flujo

| Flujo | administrador | medico | gestor_citas | paciente | público |
|-------|:---:|:---:|:---:|:---:|:---:|
| Registro IPS (onboarding) | — | — | — | — | ✓ |
| Login / Logout | ✓ | ✓ | ✓ | ✓ | ✓ |
| Recuperar contraseña | ✓ | ✓ | ✓ | ✓ | ✓ |
| Registro afiliado (público) | — | — | — | — | ✓ |
| Registro empleador (solicitud) | — | — | — | — | ✓ |
| Aprobar/rechazar solicitudes | ✓ | — | — | — | — |
| Branding / Identidad visual | ✓ | — | — | — | — |
| Registro de paciente (gestor) | ✓ | — | ✓ | — | — |
| Cambio de contraseña propia | ✓ | ✓ | ✓ | ✓ | — |
| Gestión de usuarios | ✓ | — | — | — | — |
| Gestión de médicos (escritura) | ✓ | — | — | — | — |
| Gestión de médicos (lectura) | ✓ | — | ✓ | — | — |
| Horarios de médicos | ✓ | — | ✓ | — | — |
| Agendar citas | ✓ | — | ✓ | — | — |
| Ver citas | ✓ | ✓ (propias) | ✓ | ✓ (propias) | — |
| Iniciar ejecución | ✓ | ✓ | — | — | — |
| Signos vitales (escritura) | ✓ | ✓ | — | — | — |
| Historia clínica (escritura) | ✓ | ✓ | — | — | — |
| Búsqueda CIE-10 | ✓ | ✓ | ✓ | ✓ | — |
| Descargar historia en PDF | ✓ | ✓ | — | ✓ (propia) | — |
| Recetas y documentos (escritura) | ✓ | ✓ | — | — | — |
| Consulta historial | ✓ | ✓ | — | ✓ (propio) | — |
| Valorar cita | — | — | — | ✓ | — |
| Ver valoraciones | ✓ | ✓ | — | ✓ (propias) | — |
| Dashboard de métricas | ✓ | — | — | — | — |
| Reportes PDF / Excel | ✓ | — | — | — | — |
| Administración empresa | ✓ | — | — | — | — |
| Auditoría | ✓ | — | — | — | — |
