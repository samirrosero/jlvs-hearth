# Rutas

Archivo: [routes/web.php](../routes/web.php)

100+ rutas en total. Autenticación por sesión Laravel (no API tokens).

---

## Estructura general

```
POST   /login              — público
POST   /empresas           — público (onboarding IPS - API)
GET    /adquirir           — público (onboarding IPS - Vista)
POST   /adquirir           — público (onboarding IPS - Guardar)
GET    /registro           — público (registro afiliados/empleadores - Vista)
POST   /registro/afiliado  — público (registro paciente)
POST   /registro/empleador — público (solicitud empleador)

middleware('auth') ──────────────────────────────────────────────
│
├─ POST  /logout
├─ GET   /me
│
├─ GET   /modalidades-cita          (todos los roles autenticados)
├─ GET   /modalidades-cita/{id}
├─ GET   /estados-cita
├─ GET   /estados-cita/{id}
│
├─ role:administrador,gestor_citas,medico ────────────────────────
│   ├─ GET /pacientes
│   └─ GET /pacientes/{paciente}
│
├─ role:administrador,gestor_citas ──────────────────────────────
│   ├─ POST   /pacientes
│   ├─ PUT    /pacientes/{paciente}
│   ├─ PATCH  /pacientes/{paciente}
│   ├─ DELETE /pacientes/{paciente}
│   ├─ GET    /citas
│   ├─ GET    /citas/{cita}         (también medico y paciente)
│   ├─ POST   /citas
│   ├─ PUT    /citas/{cita}
│   ├─ PATCH  /citas/{cita}
│   └─ DELETE /citas/{cita}
│
├─ role:administrador,gestor_citas,medico,paciente ───────────────
│   ├─ GET /citas
│   └─ GET /citas/{cita}
│
├─ role:administrador,medico ────────────────────────────────────
│   ├─ GET    /ejecuciones
│   ├─ GET    /ejecuciones/{ejecucion}
│   ├─ POST   /ejecuciones
│   ├─ PUT    /ejecuciones/{ejecucion}
│   ├─ PATCH  /ejecuciones/{ejecucion}
│   ├─ DELETE /ejecuciones/{ejecucion}
│   ├─ POST   /historias-clinicas
│   ├─ PUT    /historias-clinicas/{historia}
│   ├─ PATCH  /historias-clinicas/{historia}
│   ├─ DELETE /historias-clinicas/{historia}
│   ├─ POST   /recetas
│   ├─ PUT    /recetas/{receta}
│   ├─ PATCH  /recetas/{receta}
│   ├─ DELETE /recetas/{receta}
│   ├─ POST   /documentos
│   ├─ PUT    /documentos/{documento}
│   ├─ PATCH  /documentos/{documento}
│   └─ DELETE /documentos/{documento}
│
├─ role:administrador,medico,paciente ───────────────────────────
│   ├─ GET /historias-clinicas
│   ├─ GET /historias-clinicas/{historia}
│   ├─ GET /recetas
│   └─ GET /recetas/{receta}
│
├─ role:administrador,medico,gestor_citas,paciente ──────────────
│   ├─ GET /documentos
│   └─ GET /documentos/{documento}
│
├─ role:administrador,gestor_citas ──────────────────────────────
│   ├─ GET /medicos
│   └─ GET /medicos/{medico}
│
└─ role:administrador ───────────────────────────────────────────
    ├─ GET    /portafolios
    ├─ GET    /portafolios/{portafolio}
    ├─ POST   /portafolios
    ├─ PUT    /portafolios/{portafolio}
    ├─ PATCH  /portafolios/{portafolio}
    ├─ DELETE /portafolios/{portafolio}
    ├─ POST   /medicos
    ├─ PUT    /medicos/{medico}
    ├─ PATCH  /medicos/{medico}
    ├─ DELETE /medicos/{medico}
    ├─ POST   /modalidades-cita
    ├─ PUT    /modalidades-cita/{modalidad}
    ├─ PATCH  /modalidades-cita/{modalidad}
    ├─ DELETE /modalidades-cita/{modalidad}
    ├─ POST   /estados-cita
    ├─ PUT    /estados-cita/{estado}
    ├─ PATCH  /estados-cita/{estado}
    ├─ DELETE /estados-cita/{estado}
    ├─ GET    /roles
    ├─ GET    /roles/{rol}
    ├─ GET    /mi-empresa
    └─ PUT    /mi-empresa
```

---

## Tabla completa de rutas

| Método | URI | Controlador | Roles permitidos |
|--------|-----|-------------|-----------------|
| POST | `/login` | AuthController@login | público |
| POST | `/empresas` | CompanyController@store | público |
| POST | `/logout` | AuthController@logout | auth |
| GET | `/me` | AuthController@me | auth |
| GET | `/modalidades-cita` | AppointmentModalidadController@index | auth |
| GET | `/modalidades-cita/{modalidad}` | AppointmentModalidadController@show | auth |
| GET | `/estados-cita` | AppointmentStatusController@index | auth |
| GET | `/estados-cita/{estado}` | AppointmentStatusController@show | auth |
| GET | `/pacientes` | PatientController@index | admin, gestor, medico |
| GET | `/pacientes/{paciente}` | PatientController@show | admin, gestor, medico |
| POST | `/pacientes` | PatientController@store | admin, gestor |
| PUT/PATCH | `/pacientes/{paciente}` | PatientController@update | admin, gestor |
| DELETE | `/pacientes/{paciente}` | PatientController@destroy | admin, gestor |
| GET | `/citas` | AppointmentController@index | admin, gestor, medico, paciente |
| GET | `/citas/{cita}` | AppointmentController@show | admin, gestor, medico, paciente |
| POST | `/citas` | AppointmentController@store | admin, gestor |
| PUT/PATCH | `/citas/{cita}` | AppointmentController@update | admin, gestor |
| DELETE | `/citas/{cita}` | AppointmentController@destroy | admin, gestor |
| GET | `/ejecuciones` | AppointmentExecutionController@index | admin, medico |
| GET | `/ejecuciones/{ejecucion}` | AppointmentExecutionController@show | admin, medico |
| POST | `/ejecuciones` | AppointmentExecutionController@store | admin, medico |
| PUT/PATCH | `/ejecuciones/{ejecucion}` | AppointmentExecutionController@update | admin, medico |
| DELETE | `/ejecuciones/{ejecucion}` | AppointmentExecutionController@destroy | admin, medico |
| GET | `/historias-clinicas` | ClinicalHistoryController@index | admin, medico, paciente |
| GET | `/historias-clinicas/{historia}` | ClinicalHistoryController@show | admin, medico, paciente |
| POST | `/historias-clinicas` | ClinicalHistoryController@store | admin, medico |
| PUT/PATCH | `/historias-clinicas/{historia}` | ClinicalHistoryController@update | admin, medico |
| DELETE | `/historias-clinicas/{historia}` | ClinicalHistoryController@destroy | admin, medico |
| GET | `/recetas` | MedicalPrescriptionController@index | admin, medico, paciente |
| GET | `/recetas/{receta}` | MedicalPrescriptionController@show | admin, medico, paciente |
| POST | `/recetas` | MedicalPrescriptionController@store | admin, medico |
| PUT/PATCH | `/recetas/{receta}` | MedicalPrescriptionController@update | admin, medico |
| DELETE | `/recetas/{receta}` | MedicalPrescriptionController@destroy | admin, medico |
| GET | `/documentos` | AttachedDocumentController@index | admin, medico, gestor, paciente |
| GET | `/documentos/{documento}` | AttachedDocumentController@show | admin, medico, gestor, paciente |
| POST | `/documentos` | AttachedDocumentController@store | admin, medico |
| PUT/PATCH | `/documentos/{documento}` | AttachedDocumentController@update | admin, medico |
| DELETE | `/documentos/{documento}` | AttachedDocumentController@destroy | admin, medico |
| GET | `/medicos` | DoctorController@index | admin, gestor |
| GET | `/medicos/{medico}` | DoctorController@show | admin, gestor |
| POST | `/medicos` | DoctorController@store | admin |
| PUT/PATCH | `/medicos/{medico}` | DoctorController@update | admin |
| DELETE | `/medicos/{medico}` | DoctorController@destroy | admin |
| GET | `/portafolios` | PortfolioController@index | admin |
| GET | `/portafolios/{portafolio}` | PortfolioController@show | admin |
| POST | `/portafolios` | PortfolioController@store | admin |
| PUT/PATCH | `/portafolios/{portafolio}` | PortfolioController@update | admin |
| DELETE | `/portafolios/{portafolio}` | PortfolioController@destroy | admin |
| POST | `/modalidades-cita` | AppointmentModalidadController@store | admin |
| PUT/PATCH | `/modalidades-cita/{modalidad}` | AppointmentModalidadController@update | admin |
| DELETE | `/modalidades-cita/{modalidad}` | AppointmentModalidadController@destroy | admin |
| POST | `/estados-cita` | AppointmentStatusController@store | admin |
| PUT/PATCH | `/estados-cita/{estado}` | AppointmentStatusController@update | admin |
| DELETE | `/estados-cita/{estado}` | AppointmentStatusController@destroy | admin |
| GET | `/roles` | RoleController@index | admin |
| GET | `/roles/{rol}` | RoleController@show | admin |
| GET | `/mi-empresa` | closure | admin |
| PUT | `/mi-empresa` | closure | admin |

---

## Rutas especiales

### `/empresas` — Onboarding público

```php
Route::post('/empresas', [CompanyController::class, 'store'])->name('empresas.store');
```

Permite registrar una nueva IPS sin autenticación. Es el punto de entrada al sistema para nuevas clínicas.

### `/mi-empresa` — Closure en lugar de controller

```php
Route::get('/mi-empresa', function () {
    return response()->json(auth()->user()->empresa);
})->name('mi-empresa.show');

Route::put('/mi-empresa', function (UpdateCompanyRequest $request) {
    $empresa = auth()->user()->empresa;
    $empresa->update($request->validated());
    return response()->json($empresa);
})->name('mi-empresa.update');
```

Se usa closure porque el administrador opera sobre **su propia empresa** (obtenida desde la sesión), no desde un parámetro de ruta. Evita conflictos con el model binding de Laravel.

---

## Rutas del Panel de Administración (Blade)

Prefijo: `/admin` — Solo accesible a usuarios con rol `administrador` autenticados por sesión.

### Autenticación del panel
| Método | URI | Controlador | Descripción |
|--------|-----|-------------|-------------|
| GET | `/login` | LoginController@showLogin | Vista de login con branding de la IPS |
| POST | `/login` | LoginController@login | Autenticación por número de documento |
| POST | `/logout` | LoginController@logout | Cierre de sesión |
| GET | `/forgot-password` | AdminPasswordResetController@showForgot | Vista de recuperación de contraseña |
| POST | `/forgot-password` | AdminPasswordResetController@sendLink | Enviar enlace de recuperación |
| GET | `/reset-password/{token}` | AdminPasswordResetController@showReset | Vista de restablecimiento |
| POST | `/reset-password` | AdminPasswordResetController@reset | Guardar nueva contraseña |

### Dashboard y CRUDs del panel
| Método | URI | Controlador | Descripción |
|--------|-----|-------------|-------------|
| GET | `/admin` | redirect | Redirige a dashboard |
| GET | `/admin/dashboard` | AdminDashboardController | Dashboard con métricas de la IPS |
| GET | `/admin/pacientes` | AdminPacienteController@index | Lista de pacientes (vista) |
| GET | `/admin/pacientes/create` | AdminPacienteController@create | Formulario crear paciente |
| POST | `/admin/pacientes` | AdminPacienteController@store | Guardar paciente |
| GET | `/admin/pacientes/{id}/edit` | AdminPacienteController@edit | Formulario editar paciente |
| PUT | `/admin/pacientes/{id}` | AdminPacienteController@update | Actualizar paciente |
| DELETE | `/admin/pacientes/{id}` | AdminPacienteController@destroy | Eliminar paciente |
| GET | `/admin/medicos` | AdminMedicoController@index | Lista de médicos (vista) |
| GET | `/admin/medicos/create` | AdminMedicoController@create | Formulario crear médico |
| POST | `/admin/medicos` | AdminMedicoController@store | Guardar médico |
| GET | `/admin/medicos/{id}/edit` | AdminMedicoController@edit | Formulario editar médico |
| PUT | `/admin/medicos/{id}` | AdminMedicoController@update | Actualizar médico |
| DELETE | `/admin/medicos/{id}` | AdminMedicoController@destroy | Eliminar médico |
| GET | `/admin/reportes` | - | Vista de reportes PDF/Excel |

### Branding y Personalización
| Método | URI | Controlador | Descripción |
|--------|-----|-------------|-------------|
| GET | `/admin/branding` | BrandingController@edit | Vista de configuración de branding |
| POST | `/admin/branding` | BrandingController@update | Guardar cambios de branding |

### Gestión de Solicitudes de Empleadores
| Método | URI | Controlador | Descripción |
|--------|-----|-------------|-------------|
| GET | `/admin/solicitudes` | SolicitudEmpleadorController@index | Lista de solicitudes pendientes/aprobadas/rechazadas |
| PATCH | `/admin/solicitudes/{id}/aprobar` | SolicitudEmpleadorController@aprobar | Aprobar solicitud y crear usuario |
| PATCH | `/admin/solicitudes/{id}/rechazar` | SolicitudEmpleadorController@rechazar | Rechazar solicitud con observaciones |

### Chatbot Asistente
| Método | URI | Controlador | Descripción |
|--------|-----|-------------|-------------|
| POST | `/admin/chatbot` | ChatbotController@chat | Endpoint del asistente virtual (Ollama) |

---

## Notas de diseño

- `PUT` y `PATCH` apuntan al mismo método `update()` — el controller acepta actualizaciones parciales o totales indistintamente.
- No se usan `Route::resource()` para mantener control explícito sobre los permisos de cada ruta.
- Los nombres de ruta en español (`/citas`, `/pacientes`, `/historias-clinicas`) son intencionales para consistencia con el dominio del negocio colombiano.
- El panel de administración (`/admin/*`) usa vistas Blade y autenticación por sesión tradicional de Laravel, separado de la API REST que usa el frontend.
