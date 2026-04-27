<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\SolicitudEmpleadorController;
use App\Http\Controllers\RegistroPublicoController;
use App\Http\Controllers\Admin\AdminMedicoController;
use App\Http\Controllers\Admin\AdminPacienteController;
use App\Http\Controllers\Admin\AdminPasswordResetController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\ChatbotController;
use App\Http\Controllers\Admin\AdminHorarioController;
use App\Http\Controllers\Medico\MedicoDashboardController;
use App\Http\Controllers\Medico\MedicoCitasController;
use App\Http\Controllers\Medico\MedicoPacientesController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\AntecedentesPacienteController;
use App\Http\Controllers\CambiarPasswordController;
use App\Http\Controllers\Cie10Controller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegistroPacienteGestorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ValoracionController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentExecutionController;
use App\Http\Controllers\AppointmentModalidadController;
use App\Http\Controllers\AppointmentStatusController;
use App\Http\Controllers\AttachedDocumentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicalHistoryController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\ListaEsperaController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PlanesController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\HorarioMedicoController;
use App\Http\Controllers\LogAuditoriaController;
use App\Http\Controllers\MedicalPrescriptionController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\RegistroPacienteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\SignosVitalesController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Paciente\PacienteDashboardController;
use App\Http\Controllers\Paciente\PacienteCitasController;
use App\Http\Controllers\Paciente\PacienteHistorialController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Gestor\GestorDashboardController;
use App\Http\Controllers\Gestor\GestorCitasController;
use App\Http\Controllers\Gestor\GestorPacientesController;

// ─────────────────────────────────────────────────────────────
// Landing page
// ─────────────────────────────────────────────────────────────
Route::get('/', fn () => view('welcome'))->name('home');

// Planes y precios (público)
Route::get('/planes', [PlanesController::class, 'show'])->name('planes.show');

// Checkout — selección de plan y datos de compra (público)
Route::get('/checkout',  [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Onboarding — registro de nueva IPS (público)
Route::get('/adquirir',  [OnboardingController::class, 'show'])->name('onboarding.show');
Route::post('/adquirir', [OnboardingController::class, 'store'])->name('onboarding.store');

// Ubicación — departamentos y municipios de Colombia (DIVIPOLA)
Route::prefix('ubicacion')->name('ubicacion.')->group(function () {
    Route::get('/departamentos',       [UbicacionController::class, 'departamentos'])->name('departamentos');
    Route::get('/municipios/{codigo}', [UbicacionController::class, 'municipios'])->name('municipios');
});

// ─────────────────────────────────────────────────────────────
// Autenticación (público)
// ─────────────────────────────────────────────────────────────
Route::post('/api/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/forgot-password', [PasswordResetController::class, 'enviarEnlace'])->name('password.email');
Route::post('/reset-password', [PasswordResetController::class, 'resetear'])->name('password.update');

// ─────────────────────────────────────────────────────────────
// Autenticación Blade (panel)
// ─────────────────────────────────────────────────────────────
Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Recuperación de contraseña
Route::get('/forgot-password',        [AdminPasswordResetController::class, 'showForgot'])->name('forgot-password');
Route::post('/forgot-password',       [AdminPasswordResetController::class, 'sendLink'])->name('forgot-password.send');
Route::get('/reset-password/{token}', [AdminPasswordResetController::class, 'showReset'])->name('reset-password');
Route::post('/reset-password',        [AdminPasswordResetController::class, 'reset'])->name('reset-password.update');

// ─────────────────────────────────────────────────────────────
// Registro público (Blade) — afiliados y empleadores
// ─────────────────────────────────────────────────────────────
Route::get('/registro',             [RegistroPublicoController::class, 'show'])->name('registro.show');
Route::post('/registro/afiliado',   [RegistroPublicoController::class, 'registrarAfiliado'])->name('registro.afiliado');
Route::post('/registro/empleador',  [RegistroPublicoController::class, 'registrarEmpleador'])->name('registro.empleador');

// ─────────────────────────────────────────────────────────────
// Registro público de paciente (API — legacy)
// ─────────────────────────────────────────────────────────────
Route::post('/registro-paciente', [RegistroPacienteController::class, 'store'])->name('registro.paciente');

Route::middleware('auth')->group(function () {

    Route::post('/api/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    // Cambio de contraseña propia — cualquier rol autenticado
    Route::post('/mi-cuenta/cambiar-password', CambiarPasswordController::class)->name('mi-cuenta.cambiar-password');

    // Dashboard de métricas — administrador únicamente
    Route::middleware('role:administrador')->get('/dashboard', DashboardController::class)->name('dashboard');

    // Valoraciones — paciente crea, admin y médico leen
    Route::middleware('role:administrador,medico,paciente')->get('/valoraciones', [ValoracionController::class, 'index']);
    Route::middleware('role:administrador,medico,paciente')->get('/valoraciones/{valoracion}', [ValoracionController::class, 'show']);
    Route::middleware('role:paciente')->post('/valoraciones', [ValoracionController::class, 'store']);
    Route::middleware('role:administrador')->get('/valoraciones/resumen/medicos', [ValoracionController::class, 'resumenMedicos']);

    // Reportes (PDF y Excel) — administrador únicamente
    Route::middleware('role:administrador')->prefix('reportes')->group(function () {
        Route::get('/citas/pdf',       [ReporteController::class, 'citasPdf'])->name('reportes.citas.pdf');
        Route::get('/citas/excel',     [ReporteController::class, 'citasExcel'])->name('reportes.citas.excel');
        Route::get('/pacientes/pdf',   [ReporteController::class, 'pacientesPdf'])->name('reportes.pacientes.pdf');
        Route::get('/pacientes/excel', [ReporteController::class, 'pacientesExcel'])->name('reportes.pacientes.excel');
    });

    // ─────────────────────────────────────────────────────────
    // Catálogos compartidos (solo lectura para todos los roles)
    // ─────────────────────────────────────────────────────────
    Route::get('/cie10', [Cie10Controller::class, 'index']);
    Route::get('/modalidades-cita', [AppointmentModalidadController::class, 'index']);
    Route::get('/modalidades-cita/{modalidad}', [AppointmentModalidadController::class, 'show']);
    Route::get('/estados-cita', [AppointmentStatusController::class, 'index']);
    Route::get('/estados-cita/{estado}', [AppointmentStatusController::class, 'show']);

    // ─────────────────────────────────────────────────────────
    // Gestión de pacientes
    // (lectura: todos los roles internos; escritura: admin + gestor_citas)
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,gestor_citas,medico')->group(function () {
        Route::get('/pacientes', [PatientController::class, 'index']);
        Route::get('/pacientes/{paciente}', [PatientController::class, 'show']);
    });
    Route::middleware('role:administrador,gestor_citas')->group(function () {
        Route::post('/pacientes', [PatientController::class, 'store']);
        // Registro presencial: crea paciente con o sin cuenta + contraseña temporal
        Route::post('/pacientes/registro-gestor', [RegistroPacienteGestorController::class, 'store'])->name('pacientes.registro-gestor');
        Route::put('/pacientes/{paciente}', [PatientController::class, 'update']);
        Route::patch('/pacientes/{paciente}', [PatientController::class, 'update']);
        Route::delete('/pacientes/{paciente}', [PatientController::class, 'destroy']);
    });

    // ─────────────────────────────────────────────────────────
    // Citas
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,gestor_citas,medico,paciente')->group(function () {
        Route::get('/citas', [AppointmentController::class, 'index']);
        Route::get('/citas/{cita}', [AppointmentController::class, 'show']);
    });
    Route::middleware('role:administrador,gestor_citas')->group(function () {
        Route::post('/citas', [AppointmentController::class, 'store']);
        Route::put('/citas/{cita}', [AppointmentController::class, 'update']);
        Route::patch('/citas/{cita}', [AppointmentController::class, 'update']);
        Route::delete('/citas/{cita}', [AppointmentController::class, 'destroy']);
    });

    // Disponibilidad — slots libres y días disponibles de un médico
    Route::middleware('role:administrador,gestor_citas,paciente')->group(function () {
        Route::get('/citas/disponibilidad',                    DisponibilidadController::class)->name('citas.disponibilidad');
        Route::get('/medicos/{medico}/dias-disponibles',       [DisponibilidadController::class, 'diasDisponibles'])->name('medicos.dias-disponibles');
    });

    // Lista de espera — pacientes sin slot disponible
    Route::middleware('role:administrador,gestor_citas')->group(function () {
        Route::get('/lista-espera',                  [ListaEsperaController::class, 'index']);
        Route::post('/lista-espera',                 [ListaEsperaController::class, 'store']);
        Route::patch('/lista-espera/{listaEspera}',  [ListaEsperaController::class, 'update']);
        Route::delete('/lista-espera/{listaEspera}', [ListaEsperaController::class, 'destroy']);
    });

    // ─────────────────────────────────────────────────────────
    // Ejecuciones de cita (inicio/fin de atención real)
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,medico')->group(function () {
        Route::get('/ejecuciones', [AppointmentExecutionController::class, 'index']);
        Route::get('/ejecuciones/{ejecucion}', [AppointmentExecutionController::class, 'show']);
        Route::post('/ejecuciones', [AppointmentExecutionController::class, 'store']);
        Route::put('/ejecuciones/{ejecucion}', [AppointmentExecutionController::class, 'update']);
        Route::patch('/ejecuciones/{ejecucion}', [AppointmentExecutionController::class, 'update']);
        Route::delete('/ejecuciones/{ejecucion}', [AppointmentExecutionController::class, 'destroy']);
    });

    // ─────────────────────────────────────────────────────────
    // Historias clínicas
    // (lectura: médico, admin, paciente; escritura: médico, admin)
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,medico,paciente')->group(function () {
        Route::get('/historias-clinicas', [ClinicalHistoryController::class, 'index']);
        Route::get('/historias-clinicas/{historia}', [ClinicalHistoryController::class, 'show']);
        Route::get('/historias-clinicas/{historia}/pdf', [ClinicalHistoryController::class, 'pdf']);
    });
    Route::middleware('role:administrador,medico')->group(function () {
        Route::post('/historias-clinicas', [ClinicalHistoryController::class, 'store']);
        Route::put('/historias-clinicas/{historia}', [ClinicalHistoryController::class, 'update']);
        Route::patch('/historias-clinicas/{historia}', [ClinicalHistoryController::class, 'update']);
        Route::delete('/historias-clinicas/{historia}', [ClinicalHistoryController::class, 'destroy']);
    });

    // ─────────────────────────────────────────────────────────
    // Recetas médicas
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,medico,paciente')->group(function () {
        Route::get('/recetas', [MedicalPrescriptionController::class, 'index']);
        Route::get('/recetas/{receta}', [MedicalPrescriptionController::class, 'show']);
    });
    Route::middleware('role:administrador,medico')->group(function () {
        Route::post('/recetas', [MedicalPrescriptionController::class, 'store']);
        Route::put('/recetas/{receta}', [MedicalPrescriptionController::class, 'update']);
        Route::patch('/recetas/{receta}', [MedicalPrescriptionController::class, 'update']);
        Route::delete('/recetas/{receta}', [MedicalPrescriptionController::class, 'destroy']);
    });

    // ─────────────────────────────────────────────────────────
    // Documentos adjuntos
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,medico,gestor_citas,paciente')->group(function () {
        Route::get('/documentos', [AttachedDocumentController::class, 'index']);
        Route::get('/documentos/{documento}', [AttachedDocumentController::class, 'show']);
    });
    Route::middleware('role:administrador,medico')->group(function () {
        Route::post('/documentos', [AttachedDocumentController::class, 'store']);
        Route::put('/documentos/{documento}', [AttachedDocumentController::class, 'update']);
        Route::patch('/documentos/{documento}', [AttachedDocumentController::class, 'update']);
        Route::delete('/documentos/{documento}', [AttachedDocumentController::class, 'destroy']);
    });

    // ─────────────────────────────────────────────────────────
    // Servicios/procedimientos — lectura para roles internos
    // (escritura solo admin, dentro del bloque administrador)
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,gestor_citas,medico')->group(function () {
        Route::get('/servicios', [ServicioController::class, 'index']);
        Route::get('/servicios/{servicio}', [ServicioController::class, 'show']);
    });

    // ─────────────────────────────────────────────────────────
    // Horarios de médicos
    // (lectura: admin + gestor_citas; escritura: admin)
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,gestor_citas')->group(function () {
        Route::get('/horarios', [HorarioMedicoController::class, 'index']);
        Route::get('/horarios/{horario}', [HorarioMedicoController::class, 'show']);
    });
    Route::middleware('role:administrador')->group(function () {
        Route::post('/horarios', [HorarioMedicoController::class, 'store']);
        Route::put('/horarios/{horario}', [HorarioMedicoController::class, 'update']);
        Route::patch('/horarios/{horario}', [HorarioMedicoController::class, 'update']);
        Route::delete('/horarios/{horario}', [HorarioMedicoController::class, 'destroy']);
    });

    // ─────────────────────────────────────────────────────────
    // Signos vitales
    // (lectura: admin, medico, paciente; escritura: admin, medico)
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,medico,paciente')->group(function () {
        Route::get('/signos-vitales', [SignosVitalesController::class, 'index']);
        Route::get('/signos-vitales/{signosVitales}', [SignosVitalesController::class, 'show']);
    });
    Route::middleware('role:administrador,medico')->group(function () {
        Route::post('/signos-vitales', [SignosVitalesController::class, 'store']);
        Route::put('/signos-vitales/{signosVitales}', [SignosVitalesController::class, 'update']);
        Route::patch('/signos-vitales/{signosVitales}', [SignosVitalesController::class, 'update']);
        Route::delete('/signos-vitales/{signosVitales}', [SignosVitalesController::class, 'destroy']);
    });

    // ─────────────────────────────────────────────────────────
    // Antecedentes del paciente
    // (lectura: admin, medico, paciente; escritura: admin, medico)
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,medico,paciente')->group(function () {
        Route::get('/antecedentes', [AntecedentesPacienteController::class, 'index']);
        Route::get('/antecedentes/{antecedente}', [AntecedentesPacienteController::class, 'show']);
    });
    Route::middleware('role:administrador,medico')->group(function () {
        Route::post('/antecedentes', [AntecedentesPacienteController::class, 'store']);
        Route::put('/antecedentes/{antecedente}', [AntecedentesPacienteController::class, 'update']);
        Route::patch('/antecedentes/{antecedente}', [AntecedentesPacienteController::class, 'update']);
        Route::delete('/antecedentes/{antecedente}', [AntecedentesPacienteController::class, 'destroy']);
    });

    // ─────────────────────────────────────────────────────────
    // Médicos (lectura: admin + gestor_citas; escritura: admin)
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,gestor_citas,medico,paciente')->group(function () {
        Route::get('/especialidades', [DoctorController::class, 'especialidades']);
    });

    Route::middleware('role:administrador,gestor_citas')->group(function () {
        Route::get('/medicos', [DoctorController::class, 'index']);
        Route::get('/medicos/{medico}', [DoctorController::class, 'show']);
    });

    // ─────────────────────────────────────────────────────────
    // Portafolios (convenios/EPS) — solo administrador
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador')->group(function () {
        Route::get('/portafolios', [PortfolioController::class, 'index']);
        Route::get('/portafolios/{portafolio}', [PortfolioController::class, 'show']);
        Route::post('/portafolios', [PortfolioController::class, 'store']);
        Route::put('/portafolios/{portafolio}', [PortfolioController::class, 'update']);
        Route::patch('/portafolios/{portafolio}', [PortfolioController::class, 'update']);
        Route::delete('/portafolios/{portafolio}', [PortfolioController::class, 'destroy']);

        // Médicos de la empresa (escritura solo admin)
        Route::post('/medicos', [DoctorController::class, 'store']);
        Route::put('/medicos/{medico}', [DoctorController::class, 'update']);
        Route::patch('/medicos/{medico}', [DoctorController::class, 'update']);
        Route::delete('/medicos/{medico}', [DoctorController::class, 'destroy']);

        // Catálogos: modalidades y estados (escritura)
        Route::post('/modalidades-cita', [AppointmentModalidadController::class, 'store']);
        Route::put('/modalidades-cita/{modalidad}', [AppointmentModalidadController::class, 'update']);
        Route::patch('/modalidades-cita/{modalidad}', [AppointmentModalidadController::class, 'update']);
        Route::delete('/modalidades-cita/{modalidad}', [AppointmentModalidadController::class, 'destroy']);

        Route::post('/estados-cita', [AppointmentStatusController::class, 'store']);
        Route::put('/estados-cita/{estado}', [AppointmentStatusController::class, 'update']);
        Route::patch('/estados-cita/{estado}', [AppointmentStatusController::class, 'update']);
        Route::delete('/estados-cita/{estado}', [AppointmentStatusController::class, 'destroy']);

        // Roles (solo lectura; escritura reservada para seeder/admin)
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/roles/{rol}', [RoleController::class, 'show']);

        // Servicios: escritura solo admin
        Route::post('/servicios', [ServicioController::class, 'store']);
        Route::put('/servicios/{servicio}', [ServicioController::class, 'update']);
        Route::patch('/servicios/{servicio}', [ServicioController::class, 'update']);
        Route::delete('/servicios/{servicio}', [ServicioController::class, 'destroy']);

        // Gestión de usuarios internos (medicos y gestores) — solo admin
        Route::get('/usuarios', [UsuarioController::class, 'index']);
        Route::post('/usuarios', [UsuarioController::class, 'store']);
        Route::get('/usuarios/{usuario}', [UsuarioController::class, 'show']);
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update']);
        Route::patch('/usuarios/{usuario}', [UsuarioController::class, 'update']);
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy']);

        // Logs de auditoría — solo lectura, solo admin
        Route::get('/logs', [LogAuditoriaController::class, 'index']);
        Route::get('/logs/{log}', [LogAuditoriaController::class, 'show']);

        // Empresa propia del administrador
        Route::get('/mi-empresa', function () {
            return response()->json(auth()->user()->empresa);
        })->name('mi-empresa.show');

        Route::put('/mi-empresa', function (\App\Http\Requests\UpdateCompanyRequest $request) {
            $empresa = auth()->user()->empresa;
            $empresa->update($request->validated());
            return response()->json($empresa);
        })->name('mi-empresa.update');
    });
});

// ─────────────────────────────────────────────────────────────
// Registro de nueva empresa (onboarding de IPS — público)
// ─────────────────────────────────────────────────────────────
Route::post('/empresas', [CompanyController::class, 'store'])->name('empresas.store');

// ═════════════════════════════════════════════════════════════
// PANEL MÉDICO — Vistas Blade
// ═════════════════════════════════════════════════════════════
Route::prefix('medico')->name('medico.')->middleware(['auth', 'role:medico'])->group(function () {
    Route::get('/', fn () => redirect()->route('medico.dashboard'));
    Route::get('/dashboard', MedicoDashboardController::class)->name('dashboard');

    Route::get('/citas',              [MedicoCitasController::class, 'index'])->name('citas');
    Route::get('/citas/{cita}',       [MedicoCitasController::class, 'atender'])->name('citas.atender');

    Route::get('/pacientes',                [MedicoPacientesController::class, 'index'])->name('pacientes');
    Route::get('/pacientes/{paciente}',     [MedicoPacientesController::class, 'show'])->name('pacientes.show');

    Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot');
});
// ═════════════════════════════════════════════════════════════
// PANEL DE PACIENTE — Vistas Blade
// ═════════════════════════════════════════════════════════════
Route::prefix('paciente')->name('paciente.')->middleware(['auth', 'role:paciente'])->group(function () {
    Route::get('/', fn () => redirect()->route('paciente.dashboard'));

    Route::get('/dashboard', PacienteDashboardController::class)->name('dashboard');

    Route::get('/citas', [PacienteCitasController::class, 'index'])->name('citas');

    Route::get('/historial', [PacienteHistorialController::class, 'index'])->name('historial');
    Route::get('/historial/{historia}', [PacienteHistorialController::class, 'show'])->name('historial.show');

    Route::patch('/citas/{cita}/cancelar', [PacienteCitasController::class, 'cancelar'])
        ->name('citas.cancelar');

    Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot');
});

Route::prefix('gestor')->name('gestor.')->middleware(['auth', 'role:gestor_citas'])->group(function () {
    Route::get('/',          fn () => redirect()->route('gestor.dashboard'));
    Route::get('/dashboard', GestorDashboardController::class)->name('dashboard');

    Route::get('/citas',          [GestorCitasController::class, 'index'])->name('citas');
    Route::get('/citas/crear',    [GestorCitasController::class, 'create'])->name('citas.create');
    Route::post('/citas',         [GestorCitasController::class, 'store'])->name('citas.store');
    Route::get('/citas/{cita}',   [GestorCitasController::class, 'show'])->name('citas.show');
    Route::get('/citas/{cita}/editar', [GestorCitasController::class, 'edit'])->name('citas.edit');
    Route::put('/citas/{cita}',   [GestorCitasController::class, 'update'])->name('citas.update');

    Route::get('/pacientes',             [GestorPacientesController::class, 'index'])->name('pacientes');
    Route::get('/pacientes/registrar',   [GestorPacientesController::class, 'create'])->name('pacientes.create');
    Route::post('/pacientes',            [GestorPacientesController::class, 'store'])->name('pacientes.store');
});

// ═════════════════════════════════════════════════════════════
// PANEL DE ADMINISTRACIÓN — Vistas Blade
// ═════════════════════════════════════════════════════════════
Route::prefix('admin')->name('admin.')->group(function () {


    // Rutas protegidas — solo administrador autenticado
    Route::middleware(['auth', 'role:administrador'])->group(function () {

        Route::get('/',          fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        // CRUD #1 — Pacientes
        Route::resource('/pacientes', AdminPacienteController::class)
            ->except(['show'])
            ->names('pacientes');

        // CRUD #2 — Médicos
        Route::resource('/medicos', AdminMedicoController::class)
            ->except(['show'])
            ->names('medicos');

        // Reportes — descarga de PDF/Excel (delega a ReporteController ya existente)
        Route::get('/reportes', fn () => view('admin.reportes.index'))->name('reportes');

        // Identidad visual (branding) de la IPS
        Route::get('/branding',  [BrandingController::class, 'edit'])->name('branding');
        Route::post('/branding', [BrandingController::class, 'update'])->name('branding.update');

        // Solicitudes de personal (empleadores pendientes)
        Route::get('/solicitudes',                            [SolicitudEmpleadorController::class, 'index'])->name('solicitudes.index');
        Route::patch('/solicitudes/{solicitud}/aprobar',      [SolicitudEmpleadorController::class, 'aprobar'])->name('solicitudes.aprobar');
        Route::patch('/solicitudes/{solicitud}/rechazar',     [SolicitudEmpleadorController::class, 'rechazar'])->name('solicitudes.rechazar');

        // Horarios de médicos
        Route::get('/horarios',  [AdminHorarioController::class, 'index'])->name('horarios');
        Route::post('/horarios', [AdminHorarioController::class, 'guardar'])->name('horarios.guardar');

        // Chatbot — asistente virtual con Ollama
        Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot');
    });
});
