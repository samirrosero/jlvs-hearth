@extends('layouts.app')

@section('content')
<style>
    /* Estilos para igualar la profesionalidad de SaludIT */
    .btn-hover {
        transition: all 0.3s ease;
        border-radius: 12px;
    }
    .btn-hover:hover {
        transform: translateY(-5px);
        background-color: #f8f9fa;
        cursor: pointer;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
    }
    .card { border-radius: 12px; }
    .bg-success-soft { background-color: #e6fcf5; color: #0ca678; }
    .bg-warning-soft { background-color: #fff9db; color: #f08c00; }
    .bg-danger-soft { background-color: #fff5f5; color: #f03e3e; }
    .table thead th {
        border-top: none;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
</style>

<div class="container-fluid py-4">
    <!-- Encabezado Estilo SaludIT -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center bg-white p-3 shadow-sm rounded-3">
                <div>
                    <h4 class="fw-bold text-primary mb-0">Gestión de Citas - IPS SaludIT</h4>
                    <small class="text-muted">Panel de Operaciones Administrativas</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary px-3 py-2">{{ now()->format('d M, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas de Sistema (Basado en image_21d6ba.png) -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Botones de Acción Rápida (Ventanas Flotantes) -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3 btn-hover" role="button" data-bs-toggle="modal" data-bs-target="#modalAgendar">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-calendar-plus text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                    <h6 class="fw-bold text-uppercase">Agendar Cita</h6>
                    <small class="text-muted">Nuevos pacientes</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3 btn-hover" role="button" data-bs-toggle="modal" data-bs-target="#modalReprogramar">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-clock text-warning" style="font-size: 2.5rem;"></i>
                    </div>
                    <h6 class="fw-bold text-uppercase">Reprogramar</h6>
                    <small class="text-muted">Cambio de horario</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-3 btn-hover" role="button" data-bs-toggle="modal" data-bs-target="#modalCancelar">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-calendar-times text-danger" style="font-size: 2.5rem;"></i>
                    </div>
                    <h6 class="fw-bold text-uppercase">Cancelar Cita</h6>
                    <small class="text-muted">Anulación de registro</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Citas Programadas -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">Citas Programadas para Hoy</h5>
                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-filter me-1"></i> Filtrar</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4">HORARIO</th>
                            <th>PACIENTE / DOCUMENTO</th>
                            <th>ESPECIALIDAD</th>
                            <th class="text-center">ESTADO</th>
                            <th class="text-end pe-4">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Fila de ejemplo -->
                        <tr>
                            <td class="ps-4 fw-bold text-primary">08:30 AM</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">Juan Pérez Lugo</span>
                                    <small class="text-muted">CC: 1.067.890.123</small>
                                </div>
                            </td>
                            <td>Medicina General</td>
                            <td class="text-center">
                                <span class="badge bg-success-soft px-3 py-2">Confirmada</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-warning me-1 shadow-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-light text-danger shadow-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <!-- Fila vacía para visualización -->
                        <tr>
                            <td class="ps-4 fw-bold text-primary">09:15 AM</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">Rubiela Lugo</span>
                                    <small class="text-muted">CC: 34.567.890</small>
                                </div>
                            </td>
                            <td>Odontología</td>
                            <td class="text-center">
                                <span class="badge bg-warning-soft px-3 py-2">Pendiente</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-light text-warning me-1 shadow-sm"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-light text-danger shadow-sm"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALES (Ventanas Flotantes) ================= -->

<!-- 1. Modal Agendar -->
<div class="modal fade" id="modalAgendar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-notes-medical me-2"></i> Agendar Cita Médica</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gestor.citas.agendar') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Documento del Paciente</label>
                        <input type="text" name="paciente_id" class="form-control form-control-lg bg-light" placeholder="CC o TI..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Especialidad Requerida</label>
                        <select class="form-select form-control-lg bg-light" name="especialidad" required>
                            <option value="">Seleccione...</option>
                            <option>Medicina General</option>
                            <option>Odontología</option>
                            <option>Pediatría</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha y Hora</label>
                        <input type="datetime-local" name="fecha_hora" class="form-control form-control-lg bg-light" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 shadow">Confirmar Cita</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Modal Cancelar (Simplificado) -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-body p-5 text-center">
                <i class="fas fa-exclamation-circle text-danger mb-4" style="font-size: 4rem;"></i>
                <h4 class="fw-bold">¿Cancelar Cita Médica?</h4>
                <p class="text-muted">Por favor, ingrese el ID de la cita que desea anular del sistema SaludIT.</p>
                <form action="{{ route('gestor.citas.cancelar', 'ID_A_REEMPLAZAR') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="text" class="form-control mb-3 text-center" placeholder="ID de Cita (Ej: #1234)">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg shadow">Confirmar Anulación</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Regresar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection