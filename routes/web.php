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
use App\Http\Controllers\Admin\AdminServicioController;
use App\Http\Controllers\Admin\AdminPortafolioController;
use App\Http\Controllers\Admin\AdminValoracionesController;
use App\Http\Controllers\Medico\MedicoDashboardController;
use App\Http\Controllers\Medico\MedicoCitasController;
use App\Http\Controllers\Medico\MedicoPacientesController;
use App\Http\Controllers\Medico\MedicoValoracionesController;
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
use App\Http\Controllers\Paciente\AgendarCitaPacienteController;
use App\Http\Controllers\Paciente\AgendarCitaVistaController;
use App\Http\Controllers\Paciente\PacientePerfilController;
use App\Http\Controllers\Paciente\PacienteValoracionesController;
use App\Http\Controllers\DisponibilidadEspecialidadController;
use App\Http\Controllers\GestorCitas\ReasignarCitasMedicoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Gestor\GestorDashboardController;
use App\Http\Controllers\Gestor\GestorCitasController;
use App\Http\Controllers\Gestor\GestorPacientesController;

// use App\Http\Controllers\GestorController;

// ... el resto de tus rutas ...

/*
Route::prefix('gestor')->group(function () {
    // Dashboard
    Route::get('/dashboard', [GestorController::class, 'index'])->name('gestor.dashboard');

    // Rutas para SaludIT
    Route::post('/citas/agendar', [GestorController::class, 'store'])->name('gestor.citas.agendar');
    Route::put('/citas/reprogramar/{id}', [GestorController::class, 'update'])->name('gestor.citas.update');
    Route::delete('/citas/cancelar/{id}', [GestorController::class, 'destroy'])->name('gestor.citas.cancelar');
});
*/

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
Route::get('/politicas-de-datos',   fn () => view('legal.politicas'))->name('politicas');
Route::get('/terminos-y-condiciones', fn () => view('legal.terminos'))->name('terminos');
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
        Route::get('/medicos/pdf',     [ReporteController::class, 'medicosPdf'])->name('reportes.medicos.pdf');
        Route::get('/medicos/excel',   [ReporteController::class, 'medicosExcel'])->name('reportes.medicos.excel');
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
        Route::get('/citas/{cita}', [AppointmentController::class, 'show'])->whereNumber('cita');
    });
    Route::middleware('role:administrador,gestor_citas')->group(function () {
        Route::post('/citas', [AppointmentController::class, 'store']);
        Route::put('/citas/{cita}',    [AppointmentController::class, 'update'])->whereNumber('cita');
        Route::patch('/citas/{cita}',  [AppointmentController::class, 'update'])->whereNumber('cita');
        Route::delete('/citas/{cita}', [AppointmentController::class, 'destroy'])->whereNumber('cita');
    });

    // Disponibilidad — slots libres y días disponibles de un médico
    Route::middleware('role:administrador,gestor_citas,paciente')->group(function () {
        Route::get('/citas/disponibilidad',                    DisponibilidadController::class)->name('citas.disponibilidad');
        Route::get('/citas/disponibilidad-por-especialidad',   DisponibilidadEspecialidadController::class)->name('citas.disponibilidad-especialidad');
        Route::get('/medicos/{medico}/dias-disponibles',       [DisponibilidadController::class, 'diasDisponibles'])->name('medicos.dias-disponibles');
    });

    // Reasignación masiva de citas — médico ausente (gestor/admin)
    Route::middleware('role:administrador,gestor_citas')->group(function () {
        Route::post('/citas/reasignar-medico', ReasignarCitasMedicoController::class)->name('citas.reasignar-medico');
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
    // Órdenes médicas
    // (lectura: admin, medico, paciente; escritura: admin, medico; autorizar: todos)
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:administrador,medico,paciente,gestor_citas')->group(function () {
        Route::get('/ordenes-medicas', [\App\Http\Controllers\OrdenMedicaController::class, 'index']);
        Route::patch('/ordenes-medicas/{ordenMedica}', [\App\Http\Controllers\OrdenMedicaController::class, 'update']);
    });
    Route::middleware('role:administrador,medico')->group(function () {
        Route::post('/ordenes-medicas', [\App\Http\Controllers\OrdenMedicaController::class, 'store']);
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
        Route::get('/documentos/{documento}/descargar', [AttachedDocumentController::class, 'descargar'])->name('documentos.descargar');
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
    Route::patch('/citas/{cita}/link-video', [MedicoCitasController::class, 'actualizarLink'])->name('citas.link-video');

    Route::get('/pacientes',                [MedicoPacientesController::class, 'index'])->name('pacientes');
    Route::get('/pacientes/{paciente}',     [MedicoPacientesController::class, 'show'])->name('pacientes.show');

    Route::get('/agenda',  [\App\Http\Controllers\Medico\MedicoAgendaController::class, 'index'])->name('agenda');

    Route::get('/perfil',  [\App\Http\Controllers\Medico\MedicoPerfilController::class, 'edit'])->name('perfil');
    Route::patch('/perfil', [\App\Http\Controllers\Medico\MedicoPerfilController::class, 'update'])->name('perfil.update');

    Route::get('/horario', [\App\Http\Controllers\Medico\MedicoHorarioController::class, 'index'])->name('horario');

    Route::get('/ordenes', [\App\Http\Controllers\Medico\MedicoOrdenesController::class, 'index'])->name('ordenes');

    Route::get('/valoraciones', [\App\Http\Controllers\Medico\MedicoValoracionesController::class, 'index'])->name('valoraciones');

    Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot');
});

// Valoración pública desde correo (sin necesidad de login, protegida por firma)
Route::get('/valorar-atencion/{id}', [PacienteCitasController::class, 'valorarClick'])
    ->name('public.valorar.atencion')
    ->middleware('signed');

// ═════════════════════════════════════════════════════════════
// PANEL DE PACIENTE — Vistas Blade
// ═════════════════════════════════════════════════════════════
Route::prefix('paciente')->name('paciente.')->middleware(['auth', 'role:paciente'])->group(function () {
    Route::get('/', fn () => redirect()->route('paciente.dashboard'));

    Route::get('/dashboard', PacienteDashboardController::class)->name('dashboard');

      Route::get('/certificado-afiliacion', [App\Http\Controllers\Paciente\PacienteDashboardController::class, 'descargarCertificado'])
        ->name('certificado.descargar');

    Route::get('/citas', [PacienteCitasController::class, 'index'])->name('citas');
    Route::post('/citas', [PacienteCitasController::class, 'store'])->name('citas.store');

    Route::get('/historial', [PacienteHistorialController::class, 'index'])->name('historial');
    Route::get('/historial/{historia}', [PacienteHistorialController::class, 'show'])->name('historial.show');
    Route::get('/historial/{historia}/pdf', [PacienteHistorialController::class, 'pdf'])->name('historial.pdf');
    Route::post('/historial/{historia}/correo', [PacienteHistorialController::class, 'enviarCorreo'])->name('historial.correo');

    // Dentro del grupo de rutas del paciente
Route::get('/paciente/certificado-afiliacion', [App\Http\Controllers\Paciente\PacienteDashboardController::class, 'descargarCertificado'])
    ->name('paciente.certificado.descargar');

    Route::post('/citas/agendar', AgendarCitaPacienteController::class)->name('citas.agendar');

    Route::patch('/citas/{cita}/cancelar', [PacienteCitasController::class, 'cancelar'])
        ->name('citas.cancelar');

    Route::get('/citas/{cita}/videollamada', [PacienteCitasController::class, 'videollamada'])
        ->name('citas.videollamada');

    Route::get('/agendar', [AgendarCitaVistaController::class, 'index'])->name('agendar');
    Route::get('/agendar/disponible', [AgendarCitaVistaController::class, 'disponible'])->name('agendar.disponible');
    Route::post('/agendar/reservar', [AgendarCitaVistaController::class, 'reservar'])->name('agendar.reservar');

    // Valoración de citas
    Route::get('/citas/{cita}/valorar', [PacienteCitasController::class, 'valorar'])->name('citas.valorar');
    Route::post('/citas/{cita}/valorar', [PacienteCitasController::class, 'guardarValoracion'])->name('citas.valorar.store');

    Route::get('/ordenes', [\App\Http\Controllers\Paciente\PacienteOrdenesController::class, 'index'])->name('ordenes');
    Route::patch('/ordenes/{ordenMedica}/autorizar', [\App\Http\Controllers\Paciente\PacienteOrdenesController::class, 'autorizar'])->name('ordenes.autorizar');

    Route::get('/valoraciones', [PacienteValoracionesController::class, 'index'])->name('valoraciones');

    Route::get('/certificados', function () {
        $paciente = \App\Models\Paciente::with('empresa')
            ->where('usuario_id', auth()->id())
            ->firstOrFail();
        return view('paciente.certificados.index', compact('paciente'));
    })->name('certificados');

    Route::get('/perfil', [PacientePerfilController::class, 'edit'])->name('perfil');
    Route::patch('/perfil', [PacientePerfilController::class, 'update'])->name('perfil.update');

    Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot');
});

Route::prefix('gestor')->name('gestor.')->middleware(['auth', 'role:gestor_citas'])->group(function () {
    Route::get('/',          fn () => redirect()->route('gestor.dashboard'));
    Route::get('/dashboard', GestorDashboardController::class)->name('dashboard');

    Route::get('/citas',              [GestorCitasController::class, 'index'])->name('citas');
    Route::get('/citas/crear',        [GestorCitasController::class, 'create'])->name('citas.create');
    Route::get('/citas/buscar-hoy',   [GestorCitasController::class, 'buscarHoy'])->name('citas.buscar-hoy');
    Route::post('/citas',             [GestorCitasController::class, 'store'])->name('citas.store');
    Route::get('/citas/{cita}',       [GestorCitasController::class, 'show'])->name('citas.show');
    Route::get('/citas/{cita}/editar', [GestorCitasController::class, 'edit'])->name('citas.edit');
    Route::put('/citas/{cita}',   [GestorCitasController::class, 'update'])->name('citas.update');

    Route::get('/pacientes',                    [GestorPacientesController::class, 'index'])->name('pacientes');
    Route::get('/pacientes/registrar',          [GestorPacientesController::class, 'create'])->name('pacientes.create');
    Route::get('/pacientes/buscar',             [GestorPacientesController::class, 'buscar'])->name('pacientes.buscar');
    Route::post('/pacientes',                   [GestorPacientesController::class, 'store'])->name('pacientes.store');
    Route::post('/pacientes/registro-rapido',   [GestorPacientesController::class, 'registroRapido'])->name('pacientes.registro-rapido');

    Route::post('/citas/agendar',         [GestorCitasController::class, 'agendar'])->name('citas.agendar');
    Route::patch('/citas/{cita}/estado', [GestorCitasController::class, 'cambiarEstado'])->name('citas.estado');

    Route::get('/lista-espera', fn () => view('gestor.lista-espera.index'))->name('lista-espera');

    // Recepción y cobro de citas
    Route::get('/recepcion',                    [\App\Http\Controllers\Gestor\GestorRecepcionController::class, 'index'])->name('recepcion.index');
    Route::post('/recepcion/buscar',            [\App\Http\Controllers\Gestor\GestorRecepcionController::class, 'buscar'])->name('recepcion.buscar');
    Route::get('/recepcion/citas/{cita}/pago',   [\App\Http\Controllers\Gestor\GestorRecepcionController::class, 'formularioPago'])->name('recepcion.pago');
    Route::post('/recepcion/citas/{cita}/pago',  [\App\Http\Controllers\Gestor\GestorRecepcionController::class, 'registrarPago'])->name('recepcion.pago.store');
    Route::get('/recepcion/citas/{cita}/llegada', [\App\Http\Controllers\Gestor\GestorRecepcionController::class, 'confirmarLlegada'])->name('recepcion.llegada');

    Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot');
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

        // Valoraciones del sistema
        Route::get('/valoraciones', [\App\Http\Controllers\Admin\AdminValoracionesController::class, 'index'])->name('valoraciones.index');

        // Identidad visual (branding) de la IPS
        Route::get('/branding',  [BrandingController::class, 'edit'])->name('branding');
        Route::post('/branding', [BrandingController::class, 'update'])->name('branding.update');

        // Solicitudes de personal (empleadores pendientes)
        Route::get('/solicitudes',                            [SolicitudEmpleadorController::class, 'index'])->name('solicitudes.index');
        Route::patch('/solicitudes/{solicitud}/aprobar',      [SolicitudEmpleadorController::class, 'aprobar'])->name('solicitudes.aprobar');
        Route::patch('/solicitudes/{solicitud}/rechazar',     [SolicitudEmpleadorController::class, 'rechazar'])->name('solicitudes.rechazar');

        // Convenios / Portafolios
        Route::get('/portafolios',                         [AdminPortafolioController::class, 'index'])->name('portafolios.index');
        Route::post('/portafolios',                        [AdminPortafolioController::class, 'store'])->name('portafolios.store');
        Route::get('/portafolios/{portafolio}/editar',     [AdminPortafolioController::class, 'edit'])->name('portafolios.edit');
        Route::put('/portafolios/{portafolio}',            [AdminPortafolioController::class, 'update'])->name('portafolios.update');
        Route::delete('/portafolios/{portafolio}',         [AdminPortafolioController::class, 'destroy'])->name('portafolios.destroy');

        // Servicios médicos
        Route::get('/servicios',                       [AdminServicioController::class, 'index'])->name('servicios.index');
        Route::post('/servicios',                      [AdminServicioController::class, 'store'])->name('servicios.store');
        Route::get('/servicios/{servicio}/editar',     [AdminServicioController::class, 'edit'])->name('servicios.edit');
        Route::put('/servicios/{servicio}',            [AdminServicioController::class, 'update'])->name('servicios.update');
        Route::delete('/servicios/{servicio}',         [AdminServicioController::class, 'destroy'])->name('servicios.destroy');

        // Precios de servicios por portafolio
        Route::get('/servicios/{servicio}/precios',                    [\App\Http\Controllers\Admin\AdminPrecioServicioController::class, 'editarPrecios'])->name('servicios.precios');
        Route::put('/servicios/{servicio}/precios',                    [\App\Http\Controllers\Admin\AdminPrecioServicioController::class, 'actualizarPrecios'])->name('servicios.precios.update');
        Route::get('/precios/matriz',                                   [\App\Http\Controllers\Admin\AdminPrecioServicioController::class, 'matrizPrecios'])->name('precios.matriz');

        // Horarios de médicos
        Route::get('/horarios',  [AdminHorarioController::class, 'index'])->name('horarios');
        Route::post('/horarios', [AdminHorarioController::class, 'guardar'])->name('horarios.guardar');

        // Chatbot — asistente virtual con Ollama
        Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot');

        // Auditoría — CU-009
        Route::get('/auditoria', fn () => view('admin.auditoria.index'))->name('auditoria');
    });
});
//ruta grupo gestor
Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot');
