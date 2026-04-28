# JLVS Hearth

Sistema de gestión clínica multi-tenant para IPS colombianas, construido con **Laravel 12**.

Permite administrar pacientes, médicos, citas, historias clínicas, recetas médicas, signos vitales y auditoría, con aislamiento por empresa (`empresa_id`) y soporte para múltiples roles de usuario.

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade + Tailwind CSS 4 + Alpine.js |
| Base de datos | MySQL 8.0+ |
| Bundler | Vite 7 |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/excel |
| Colas | Laravel Queue (database driver) |

---

## Requisitos previos

| Herramienta | Versión mínima |
|-------------|---------------|
| PHP | 8.2+ |
| Composer | 2.x |
| MySQL | 8.0+ |
| Node.js | 18+ |
| Git | cualquier |

> **Windows:** Se recomienda [Laragon](https://laragon.org) para tener PHP, MySQL y Composer listos de una vez.

---

## Instalación desde cero

### 1. Clonar el repositorio

```bash
git clone https://github.com/samirrosero/jlvs-hearth.git
cd jlvs-hearth
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar el entorno

```bash
cp .env.example .env
```

Edita `.env` y ajusta los siguientes valores:

```env
# ── Aplicación ──────────────────────────────────────────────
APP_NAME="JLVS Hearth"
APP_URL=http://localhost:8000

# ── Base de datos ────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jlvs_hearth       # nombre de la BD que crearás
DB_USERNAME=root               # tu usuario de MySQL
DB_PASSWORD=                   # tu contraseña de MySQL

# ── Correo (Gmail SMTP) ──────────────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tucorreo@gmail.com
MAIL_PASSWORD="xxxx xxxx xxxx xxxx"   # contraseña de aplicación Gmail (16 chars)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tucorreo@gmail.com
MAIL_FROM_NAME="JLVS Hearth"
```

> **Contraseña de aplicación Gmail:** Cuenta de Google → Seguridad → Verificación en 2 pasos → Contraseñas de aplicaciones.

### 4. Crear la base de datos en MySQL

```sql
CREATE DATABASE jlvs_hearth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 6. Ejecutar migraciones y seeders

```bash
php artisan migrate:fresh --seed
```

Esto crea todas las tablas y carga los datos de prueba (ver sección **Seeders** más abajo).

### 7. Enlazar el almacenamiento de archivos

```bash
php artisan storage:link
```

### 8. Instalar dependencias JavaScript y compilar assets

```bash
npm install
npm run build
```

---

## Levantar el entorno de desarrollo

Abre **dos terminales** en la raíz del proyecto:

**Terminal 1 — Servidor web:**
```bash
php artisan serve
```
Disponible en `http://localhost:8000`

**Terminal 2 — Cola de correos:**
```bash
php artisan queue:listen
```

### Alternativa: todo en un solo comando

```bash
composer run dev
```

Levanta en paralelo: servidor web, worker de cola, log watcher (`pail`) y Vite con hot-reload.

---

## Comandos útiles de Artisan

### Desarrollo general

```bash
# Limpiar caché de config, rutas y vistas
php artisan optimize:clear

# Ver todas las rutas registradas
php artisan route:list

# Abrir consola interactiva (Tinker)
php artisan tinker

# Ver logs en tiempo real
php artisan pail
```

### Base de datos

```bash
# Crear tablas sin borrar datos existentes
php artisan migrate

# Recrear toda la BD y correr seeders
php artisan migrate:fresh --seed

# Solo correr seeders (sin recrear tablas)
php artisan db:seed

# Rollback de la última migración
php artisan migrate:rollback

# Ver estado de las migraciones
php artisan migrate:status
```

### Seeders individuales

```bash
# Roles del sistema (administrador, médico, gestor_citas, paciente)
php artisan db:seed --class=RolSeeder

# Modalidades de cita (Presencial, Telemedicina, Domiciliaria)
php artisan db:seed --class=ModalidadCitaSeeder

# Estados de cita (Pendiente, Confirmada, Atendida, Cancelada, No asistió)
php artisan db:seed --class=EstadoCitaSeeder

# Códigos CIE-10
php artisan db:seed --class=Cie10Seeder

# Empresa demo + admin + 2 médicos + horarios + servicios + portafolios
php artisan db:seed --class=EmpresaDemoSeeder

# Paciente demo con citas, historias clínicas, recetas y valoraciones
php artisan db:seed --class=PacienteDemoSeeder

# 20 pacientes ficticios con 6 meses de historial de citas
php artisan db:seed --class=DatosHistoricosSeeder
```

### Assets frontend

```bash
# Compilar para producción
npm run build

# Modo desarrollo con hot-reload (Vite)
npm run dev
```

### Tests

```bash
# Correr la suite completa de tests
php artisan test

# Con cobertura
php artisan test --coverage

# Equivalente vía Composer
composer run test
```

### Colas de trabajo

```bash
# Escuchar la cola (necesario para envío de correos)
php artisan queue:listen

# Procesar un lote y salir
php artisan queue:work

# Ver trabajos fallidos
php artisan queue:failed

# Reintentar todos los fallidos
php artisan queue:retry all
```

### Caché y optimización

```bash
# Cachear configuración (recomendado en producción)
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas Blade
php artisan view:cache

# Limpiar todo el caché
php artisan optimize:clear
```

---

## Usuarios de prueba

Después de ejecutar `php artisan migrate:fresh --seed` tendrás estos accesos:

### Empresa demo — Clínica Demo JLVS

| Rol | Correo | Contraseña |
|-----|--------|-----------|
| Administrador | admin@clinicademo.co | password |
| Médico (Dra. García) | dra.garcia@clinicademo.co | password |
| Médico (Dr. Torres) | dr.torres@clinicademo.co | password |
| Paciente | carlos.mendoza@email.com | password |

### Paciente demo — Carlos Alberto Mendoza

El paciente `carlos.mendoza@email.com` tiene datos completos precargados:

- **Antecedentes:** personal, familiar, quirúrgico, alérgico y farmacológico
- **7 citas** en distintos estados: 3 atendidas, 1 cancelada, 1 no asistió, 1 pendiente, 1 confirmada
- **3 historias clínicas** con diagnóstico CIE-10, plan de tratamiento y observaciones
- **2 recetas médicas** con medicamentos e indicaciones
- **2 valoraciones** del servicio
- **Signos vitales** registrados en cada consulta atendida

---

## Funcionalidades del sistema

### Gestión clínica
- Registro y búsqueda de pacientes con datos de cobertura (portafolio/aseguradora)
- Agendamiento de citas con validación de horarios médicos
- Historia clínica con codificación CIE-10
- Recetas médicas y documentos adjuntos
- Signos vitales por consulta
- Antecedentes del paciente (personal, familiar, quirúrgico, alérgico, farmacológico)
- Lista de espera

### Administración
- Dashboard con métricas (citas, ingresos, ocupación por médico)
- Gestión de médicos y sus horarios
- Gestión de servicios y tarifas por portafolio
- Reportes exportables en PDF y Excel
- Auditoría de acciones (Resolución 1995/1999)

### Onboarding y acceso
- Registro público de nuevas IPS
- Registro de afiliados (acceso directo) y empleadores (con aprobación)
- Branding por IPS: logo, favicon, colores, imágenes de login
- Flujo de aprobación de solicitudes de empleadores

---

## Rutas principales

### Públicas

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/` | Landing page |
| GET | `/adquirir` | Registro de nueva IPS |
| GET | `/registro` | Registro de afiliados / empleadores |
| GET | `/login` | Login |
| POST | `/login` | Autenticar usuario |
| POST | `/logout` | Cerrar sesión |

### Panel de administración (`/admin/*`)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/admin/dashboard` | Dashboard con métricas |
| GET/POST | `/admin/pacientes` | Listado y creación de pacientes |
| GET/PUT | `/admin/pacientes/{id}` | Ver y editar paciente |
| GET/POST | `/admin/medicos` | Listado y creación de médicos |
| GET/POST | `/admin/citas` | Gestión de citas |
| GET | `/admin/branding` | Identidad visual de la IPS |
| GET | `/admin/solicitudes` | Solicitudes de empleadores |
| GET | `/admin/reportes` | Reportes PDF/Excel |

### API REST (`/api/*` o raíz con auth)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/me` | Usuario autenticado |
| GET/POST | `/citas` | Citas del tenant |
| GET/PUT/DELETE | `/citas/{id}` | Detalle de cita |
| GET/POST | `/pacientes` | Pacientes del tenant |
| GET/PUT | `/pacientes/{id}` | Detalle de paciente |
| GET/POST | `/medicos` | Médicos del tenant |
| GET/POST | `/historias-clinicas` | Historias clínicas |
| GET | `/cie10/buscar` | Búsqueda de códigos CIE-10 |

> Documentación completa en [`docs/06-rutas.md`](docs/06-rutas.md)

---

## Estructura del proyecto

```
jlvs-hearth/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Controladores REST y de vistas
│   │   ├── Middleware/        # Auth, roles, tenant
│   │   └── Requests/          # Form Requests con validación
│   ├── Models/                # 23 modelos Eloquent
│   └── Policies/              # Autorización por recurso
├── database/
│   ├── migrations/            # 30+ migraciones ordenadas por timestamp
│   └── seeders/               # 8 seeders
│       ├── DatabaseSeeder.php
│       ├── RolSeeder.php
│       ├── ModalidadCitaSeeder.php
│       ├── EstadoCitaSeeder.php
│       ├── Cie10Seeder.php
│       ├── EmpresaDemoSeeder.php
│       ├── PacienteDemoSeeder.php
│       └── DatosHistoricosSeeder.php
├── resources/
│   └── views/                 # Vistas Blade
├── routes/
│   └── web.php                # Todas las rutas
├── docs/                      # Documentación técnica
├── INSTALL.md                 # Guía de instalación detallada
├── GUIA_PANEL_GESTOR.md
└── GUIA_PANEL_PACIENTE.md
```

---

## Documentación técnica

La documentación técnica vive en [`docs/`](docs/):

| Archivo | Contenido |
|---------|-----------|
| `docs/README.md` | Índice general |
| `docs/01-autenticacion.md` | Sistema de autenticación |
| `docs/02-middleware-roles.md` | Middleware y control de roles |
| `docs/03-form-requests.md` | Validaciones |
| `docs/04-controladores.md` | Controladores |
| `docs/05-politicas.md` | Policies de autorización |
| `docs/06-rutas.md` | Referencia completa de rutas |
| `docs/07-base-de-datos.md` | Esquema de base de datos |
| `docs/08-flujos.md` | Flujos principales del sistema |
| `docs/09-diagrama-eer.md` | Diagrama entidad-relación |
| `docs/10-diagramas-casos-uso.md` | Casos de uso |
| `docs/11-onboarding-y-registro.md` | Flujo de onboarding de IPS |

---

## Solución de problemas frecuentes

**`SQLSTATE[HY000] [1045] Access denied`**
→ Revisa `DB_USERNAME` y `DB_PASSWORD` en `.env`.

**`No application encryption key has been specified`**
→ Ejecuta `php artisan key:generate`.

**Los correos no llegan**
→ Verifica la contraseña de aplicación de Gmail y que `php artisan queue:listen` esté corriendo en otra terminal.

**Error al ejecutar `npm run build`**
→ Revisa tu versión de Node.js: `node -v`. Debe ser 18 o superior.

**`php artisan` no se reconoce**
→ PHP no está en el PATH. En Laragon, abre la terminal desde el propio Laragon.

**Error `Class not found` al correr seeders**
→ Ejecuta `composer dump-autoload` y vuelve a intentarlo.

**Imágenes o archivos no se ven**
→ Asegúrate de haber ejecutado `php artisan storage:link`.
