# Onboarding, Registro Público, Branding y Solicitudes

Documentación detallada de las nuevas funcionalidades implementadas en Abril 2026.

---

## Contenido

1. [Onboarding de IPS](#1-onboarding-de-ips)
2. [Registro Público](#2-registro-público)
3. [Branding e Identidad Visual](#3-branding-e-identidad-visual)
4. [Gestión de Solicitudes de Empleadores](#4-gestión-de-solicitudes-de-empleadores)
5. [Panel de Administración Blade](#5-panel-de-administración-blade)
6. [Implementación Técnica](#6-implementación-técnica)

---

## 1. Onboarding de IPS

### Descripción

Permite a nuevas IPS (Instituciones Prestadoras de Servicios de Salud) registrarse en el sistema sin necesidad de autenticación previa. El flujo crea automáticamente la empresa y su administrador inicial.

### Rutas

| Método | URI | Controlador | Descripción |
|--------|-----|-------------|-------------|
| GET | `/adquirir` | OnboardingController@show | Vista del formulario de registro |
| POST | `/adquirir` | OnboardingController@store | Procesar registro de nueva IPS |
| POST | `/empresas` | CompanyController@store | API alternativa para registro |

### Flujo

```
┌─────────────┐      ┌──────────────┐      ┌────────────────┐      ┌─────────────────┐
│   Público   │─────▶│  /adquirir   │─────▶│     Validar    │─────▶│   Transaction   │
│             │      │   (vista)    │      │     datos      │      │                 │
└─────────────┘      └──────────────┘      └────────────────┘      └─────────────────┘
                                                                           │
                    ┌─────────────────┐                                    │
                    │  Redirigir a      │◀───────────────────────────────────┘
                    │  /login con       │      ┌─────────────────┐
                    │  mensaje éxito    │      │  Crear:         │
                    └─────────────────┘      │  - Empresa      │
                                              │  - Usuario      │
                                              │    admin        │
                                              └─────────────────┘
```

### Validaciones

| Campo | Reglas | Mensaje personalizado |
|-------|--------|----------------------|
| `nombre` | required, string, max:255 | — |
| `nit` | required, string, max:20, unique:empresas | "Ya existe una empresa registrada con ese NIT." |
| `ciudad` | nullable, string, max:100 | — |
| `telefono` | nullable, string, max:20 | — |
| `correo` | nullable, email, max:255 | — |
| `admin_nombre` | required, string, max:150 | — |
| `admin_tipo_documento` | required, in:CC,TI,CE,PP,NUIP,RC | — |
| `admin_identificacion` | required, string, max:20, unique:users | "Ya existe un usuario con ese número de documento." |
| `admin_email` | required, email, max:255, unique:users | "Ya existe un usuario con ese correo electrónico." |
| `admin_password` | required, string, min:8, confirmed | — |

### Proceso de Creación

```php
DB::transaction(function () use ($request) {
    // 1. Crear empresa
    $empresa = Empresa::create([
        'nit' => $request->nit,
        'nombre' => $request->nombre,
        'ciudad' => $request->ciudad,
        'telefono' => $request->telefono,
        'correo' => $request->correo,
        'activo' => true,
    ]);

    // 2. Obtener rol administrador
    $rolAdmin = Rol::where('nombre', 'administrador')->firstOrFail();

    // 3. Crear usuario administrador
    User::create([
        'empresa_id' => $empresa->id,
        'rol_id' => $rolAdmin->id,
        'nombre' => $request->admin_nombre,
        'email' => $request->admin_email,
        'tipo_documento' => $request->admin_tipo_documento,
        'identificacion' => $request->admin_identificacion,
        'password' => Hash::make($request->admin_password),
        'activo' => true,
    ]);
});
```

---

## 2. Registro Público

### 2.1 Registro de Afiliados (Pacientes)

Los pacientes pueden registrarse directamente desde el portal público y acceder inmediatamente al sistema.

#### Rutas

| Método | URI | Controlador |
|--------|-----|-------------|
| GET | `/registro` | RegistroPublicoController@show |
| POST | `/registro/afiliado` | RegistroPublicoController@registrarAfiliado |

#### Datos requeridos

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

#### Tipos de documento válidos

| Código | Descripción |
|--------|-------------|
| `CC` | Cédula de Ciudadanía |
| `TI` | Tarjeta de Identidad |
| `CE` | Cédula de Extranjería |
| `PP` | Pasaporte |
| `NUIP` | Número Único de Identificación Personal |
| `RC` | Registro Civil |

### 2.2 Registro de Empleadores (Solicitud)

Personal médico, gestores y administradores envían una solicitud que debe ser aprobada.

#### Rutas

| Método | URI | Controlador |
|--------|-----|-------------|
| POST | `/registro/empleador` | RegistroPublicoController@registrarEmpleador |

#### Datos requeridos

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

#### Roles disponibles para solicitud

| Rol | Descripción |
|-----|-------------|
| `administrador` | Acceso total al panel de administración |
| `medico` | Acceso al panel médico y agenda |
| `gestor_citas` | Acceso al panel de gestión de citas |

#### Estados de solicitud

```php
enum EstadoSolicitud: string
{
    case PENDIENTE = 'pendiente';
    case APROBADO = 'aprobado';
    case RECHAZADO = 'rechazado';
}
```

---

## 3. Branding e Identidad Visual

### Descripción

Cada IPS puede personalizar completamente la apariencia del sistema con su propia identidad visual.

### Rutas

| Método | URI | Controlador | Middleware |
|--------|-----|-------------|------------|
| GET | `/admin/branding` | BrandingController@edit | auth, role:administrador |
| POST | `/admin/branding` | BrandingController@update | auth, role:administrador |

### Campos configurables

#### Imágenes

| Campo | Descripción | Restricciones |
|-------|-------------|---------------|
| `logo` | Logo principal de la IPS | png, jpg, jpeg, svg, webp. Max: 2MB |
| `favicon` | Icono del navegador | png, ico, svg. Max: 512KB |
| `imagen_login` | Imagen lateral panel login | png, jpg, jpeg, webp. Max: 4MB |
| `imagen_registro` | Imagen lateral panel registro | png, jpg, jpeg, webp. Max: 4MB |

#### Colores

| Campo | Uso | Valor por defecto |
|-------|-----|-------------------|
| `color_primario` | Botones, acentos, elementos activos | `#1e40af` |
| `color_secundario` | Textos de título, acentos secundarios | `#1e3a8a` |
| `color_admin` | Fondo sidebar panel administración | `#1e293b` |
| `color_doctor` | Fondo sidebar panel médico | `#064e3b` |
| `color_gestor` | Fondo sidebar panel gestor de citas | `#4c1d95` |
| `color_paciente` | Fondo sidebar portal paciente | `#0c4a6e` |
| `color_pdf` | Encabezados y tablas en PDFs | `#1e40af` |

#### Slogans

| Campo | Uso | Ejemplo |
|-------|-----|---------|
| `slogan_login` | Mensaje en panel de login | "Tu salud, nuestra prioridad" |
| `slogan_registro` | Mensaje en panel de registro | "Únete a la mejor red de salud" |

### Almacenamiento

```php
// Las imágenes se almacenan en:
storage/app/public/empresas/{empresa_id}/

// Ejemplo de rutas generadas:
empresas/1/logo.png
empresas/1/favicon.ico
empresas/1/login-bg.jpg
empresas/1/register-bg.jpg
```

### Accesores del modelo Empresa

```php
// URL pública para vistas web
public function getLogoUrlAttribute(): string
{
    return $this->logo_path
        ? Storage::disk('public')->url($this->logo_path)
        : asset('img/logos/logo1.png');
}

// Ruta absoluta para DomPDF (no acepta URLs HTTP)
public function getLogoPdfPathAttribute(): string
{
    return $this->logo_path
        ? storage_path('app/public/' . $this->logo_path)
        : public_path('img/logos/logo1.png');
}
```

---

## 4. Gestión de Solicitudes de Empleadores

### Rutas

| Método | URI | Controlador | Descripción |
|--------|-----|-------------|-------------|
| GET | `/admin/solicitudes` | SolicitudEmpleadorController@index | Ver todas las solicitudes |
| PATCH | `/admin/solicitudes/{id}/aprobar` | SolicitudEmpleadorController@aprobar | Aprobar y crear usuario |
| PATCH | `/admin/solicitudes/{id}/rechazar` | SolicitudEmpleadorController@rechazar | Rechazar con observaciones |

### Vista de lista (`index`)

Agrupa las solicitudes en tres categorías:

```php
$solicitudes = SolicitudEmpleador::where('empresa_id', $empresa->id)
    ->where('estado', 'pendiente')
    ->latest()
    ->get()
    ->groupBy('rol_solicitado');

$aprobados = SolicitudEmpleador::where('empresa_id', $empresa->id)
    ->where('estado', 'aprobado')
    ->latest()->take(20)->get();

$rechazados = SolicitudEmpleador::where('empresa_id', $empresa->id)
    ->where('estado', 'rechazado')
    ->latest()->take(20)->get();
```

### Flujo de aprobación

```php
public function aprobar(SolicitudEmpleador $solicitud): RedirectResponse
{
    $this->authorize('update', auth()->user()->empresa);

    if ($solicitud->estado !== 'pendiente') {
        return back()->with('error', 'Esta solicitud ya fue procesada.');
    }

    DB::transaction(function () use ($solicitud) {
        // 1. Obtener el rol solicitado
        $rol = Rol::where('nombre', $solicitud->rol_solicitado)->firstOrFail();

        // 2. Crear el usuario
        User::create([
            'empresa_id' => $solicitud->empresa_id,
            'rol_id' => $rol->id,
            'nombre' => $solicitud->nombres . ' ' . $solicitud->apellidos,
            'email' => $solicitud->correo,
            'identificacion' => $solicitud->numero_documento,
            'tipo_documento' => $solicitud->tipo_documento,
            'password' => $solicitud->password, // ya viene hasheado
            'activo' => true,
        ]);

        // 3. Actualizar estado de la solicitud
        $solicitud->update(['estado' => 'aprobado']);
    });

    return back()->with('exito', 'Solicitud aprobada. El usuario ya puede ingresar.');
}
```

### Flujo de rechazo

```php
public function rechazar(Request $request, SolicitudEmpleador $solicitud): RedirectResponse
{
    $this->authorize('update', auth()->user()->empresa);

    $request->validate([
        'observaciones' => ['nullable', 'string', 'max:500'],
    ]);

    $solicitud->update([
        'estado' => 'rechazado',
        'observaciones' => $request->observaciones,
    ]);

    return back()->with('exito', 'Solicitud rechazada.');
}
```

---

## 5. Panel de Administración Blade

### Estructura de vistas

```
resources/views/
├── admin/
│   ├── auth/
│   │   ├── login.blade.php          # Login con branding
│   │   ├── forgot-password.blade.php # Recuperación
│   │   └── reset-password.blade.php  # Restablecer
│   ├── layouts/
│   │   └── app.blade.php            # Layout principal admin
│   ├── dashboard.blade.php          # Dashboard con métricas
│   ├── medicos/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   ├── pacientes/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   ├── reportes/
│   │   └── index.blade.php
│   ├── branding/
│   │   └── edit.blade.php           # Formulario de branding
│   └── solicitudes/
│       └── index.blade.php          # Gestión de solicitudes
├── auth/
│   └── registro.blade.php           # Registro público
└── onboarding/
    └── index.blade.php              # Formulario onboarding
```

### Login con número de documento

A diferencia de la API que usa email, el panel Blade usa número de documento para login:

```php
public function login(Request $request)
{
    $request->validate([
        'identificacion' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    // Autenticar por identificación
    if (!Auth::attempt(
        ['identificacion' => $request->identificacion, 'password' => $request->password],
        $request->boolean('remember')
    )) {
        return back()->withErrors(['identificacion' => 'Número de documento o contraseña incorrectos.']);
    }
    // ...
}
```

### Roles permitidos en el panel

```php
private const ROLES_PANEL = ['administrador', 'medico', 'gestor_citas'];
```

Los pacientes **no** tienen acceso al panel Blade. Usan una aplicación separada.

---

## 6. Implementación Técnica

### Migraciones nuevas

#### 2026_04_18_000240_agregar_branding_a_empresas.php

Agrega campos de branding a la tabla `empresas`.

#### 2026_04_18_000250_agregar_tipo_documento_y_fotos_login.php

- Agrega `tipo_documento` a `users`
- Agrega `tipo_documento` y `apellidos` a `pacientes`
- Agrega `imagen_login_path` e `imagen_registro_path` a `empresas`

#### 2026_04_18_000260_crear_tabla_solicitudes_empleador.php

Crea la tabla `solicitudes_empleador` completa.

#### 2026_04_18_171812_agregar_slogans_a_empresas.php

Agrega `slogan_login` y `slogan_registro` a `empresas`.

### Controladores nuevos

| Controlador | Ubicación | Descripción |
|-------------|-----------|-------------|
| `OnboardingController` | `app/Http/Controllers/` | Registro de nuevas IPS |
| `RegistroPublicoController` | `app/Http/Controllers/` | Registro público afiliados/empleadores |
| `BrandingController` | `app/Http/Controllers/Admin/` | Configuración de branding |
| `SolicitudEmpleadorController` | `app/Http/Controllers/Admin/` | Gestión de solicitudes |
| `LoginController` | `app/Http/Controllers/Admin/` | Login del panel Blade |
| `AdminPasswordResetController` | `app/Http/Controllers/Admin/` | Recuperación de contraseña panel |
| `AdminDashboardController` | `app/Http/Controllers/Admin/` | Dashboard del panel |
| `AdminMedicoController` | `app/Http/Controllers/Admin/` | CRUD médicos (vistas) |
| `AdminPacienteController` | `app/Http/Controllers/Admin/` | CRUD pacientes (vistas) |
| `ChatbotController` | `app/Http/Controllers/Admin/` | Asistente virtual con Ollama |

### Modelo SolicitudEmpleador

```php
class SolicitudEmpleador extends Model
{
    protected $table = 'solicitudes_empleador';

    protected $fillable = [
        'empresa_id', 'tipo_documento', 'numero_documento',
        'nombres', 'apellidos', 'correo', 'password',
        'rol_solicitado', 'departamento', 'municipio',
        'foto_documento_path', 'estado', 'observaciones',
    ];

    protected $hidden = ['password'];

    // Accesores
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto_documento_path
            ? Storage::disk('public')->url($this->foto_documento_path)
            : null;
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }

    // Relaciones
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
```

### Archivos de configuración

No requiere configuración adicional. Las imágenes usan el disco `public` de Laravel configurado en `config/filesystems.php`.

---

## Resumen de cambios en base de datos

| Tabla | Cambios |
|-------|---------|
| `empresas` | +16 columnas (branding completo) |
| `users` | +1 columna (`tipo_documento`) |
| `pacientes` | +2 columnas (`tipo_documento`, `apellidos`) |
| **Nueva** | `solicitudes_empleador` (14 columnas) |

**Total: 21 tablas** (20 originales + 1 nueva)

---

## Notas de seguridad

1. **Contraseñas**: Siempre hasheadas con `bcrypt` (default de Laravel)
2. **Validación de archivos**: Tipos MIME estrictos y tamaños máximos
3. **Autorización**: Todas las rutas de admin verifican rol `administrador`
4. **Transacciones**: Onboarding y aprobación de solicitudes usan transacciones DB
5. **Soft deletes**: Las imágenes se reemplazan pero no se borran físicamente inmediatamente
