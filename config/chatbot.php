<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Secciones del panel de administración
    |--------------------------------------------------------------------------
    | Cada entrada define una sección navegable. El chatbot construye
    | automáticamente la lista de secciones y los marcadores [NAVEGAR:] e [IR:]
    | a partir de este array.
    |
    | Para agregar una sección nueva solo añade un bloque aquí.
    |--------------------------------------------------------------------------
    */
    'secciones' => [
        [
            'clave'       => 'dashboard',
            'label'       => 'Dashboard',
            'descripcion' => 'métricas generales y resumen del estado de la IPS',
            'ruta'        => 'admin.dashboard',
            'boton'       => 'Ir al Dashboard',
        ],
        [
            'clave'       => 'pacientes',
            'label'       => 'Pacientes',
            'descripcion' => 'listado, registro y edición de pacientes',
            'ruta'        => 'admin.pacientes.index',
            'boton'       => 'Ver Pacientes',
        ],
        [
            'clave'       => 'medicos',
            'label'       => 'Médicos',
            'descripcion' => 'listado, registro y edición de médicos',
            'ruta'        => 'admin.medicos.index',
            'boton'       => 'Ver Médicos',
        ],
        [
            'clave'       => 'reportes',
            'label'       => 'Reportes',
            'descripcion' => 'descarga de PDF y Excel de citas y pacientes',
            'ruta'        => 'admin.reportes',
            'boton'       => 'Ver Reportes',
        ],
        [
            'clave'       => 'branding',
            'label'       => 'Identidad Visual',
            'descripcion' => 'personalización del logo y colores de la IPS',
            'ruta'        => 'admin.branding',
            'boton'       => 'Ir a Identidad Visual',
        ],
        [
            'clave'       => 'solicitudes',
            'label'       => 'Solicitudes',
            'descripcion' => 'gestión de solicitudes de empleadores y médicos pendientes de aprobación',
            'ruta'        => 'admin.solicitudes.index',
            'boton'       => 'Ver Solicitudes',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Datos estadísticos dinámicos
    |--------------------------------------------------------------------------
    | Cada entrada consulta un modelo filtrado por empresa_id.
    | Opcionalmente se puede agregar un campo 'where' con condiciones extra.
    |
    | Para agregar un nuevo conteo solo añade un bloque aquí.
    |--------------------------------------------------------------------------
    */
    'datos' => [
        [
            'clave' => 'total_pacientes',
            'label' => 'Pacientes registrados',
            'model' => \App\Models\Paciente::class,
        ],
        [
            'clave' => 'total_medicos',
            'label' => 'Médicos registrados',
            'model' => \App\Models\Medico::class,
        ],
        [
            'clave' => 'solicitudes_pendientes',
            'label' => 'Solicitudes pendientes de revisión',
            'model' => \App\Models\SolicitudEmpleador::class,
            'where' => ['estado' => 'pendiente'],
        ],
    ],

];
