<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PruebasExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'tests/Unit/GestorCitasTest.php',
                'test_retorna_falso_cuando_el_horario_ya_esta_ocupado',
                'Unitaria',
                'Verifica que isHorarioDisponible retorna false cuando el horario ya está ocupado por otra cita',
                '✅ Pasa',
                'Valida lógica de negocio para evitar doble reserva de horarios médicos'
            ],
            [
                'tests/Unit/GestorCitasTest.php',
                'test_retorna_true_cuando_hay_disponibilidad',
                'Unitaria',
                'Verifica que isHorarioDisponible retorna true cuando el horario está disponible',
                '✅ Pasa',
                'Confirma funcionamiento correcto de la lógica de disponibilidad'
            ],
            [
                'tests/Unit/GestorCitasTest.php',
                'test_retorna_falso_cuando_medico_no_tiene_horarios_configurados',
                'Unitaria',
                'Verifica que retorna false cuando el médico no tiene horarios configurados',
                '✅ Pasa',
                'Previene asignación de citas a médicos sin horarios disponibles'
            ],
            [
                'tests/Unit/GestorCitasTest.php',
                'test_retorna_falso_cuando_servicio_tiene_duracion_diferente_y_no_cabe',
                'Unitaria',
                'Verifica que no permite citas cuando la duración del servicio no cabe en el horario disponible',
                '✅ Pasa',
                'Valida compatibilidad entre duración de servicios y horarios médicos'
            ],
            [
                'tests/Unit/GestorCitasTest.php',
                'test_selecciona_medico_con_menor_carga_cuando_hay_varios_disponibles',
                'Unitaria',
                'Verifica que selecciona el médico con menor carga de trabajo cuando hay varios disponibles',
                '✅ Pasa',
                'Implementa algoritmo de balanceo de carga para distribución equitativa'
            ],
            [
                'tests/Feature/GestorCitasControllerTest.php',
                'test_un_gestor_no_puede_crear_cita_para_otra_empresa',
                'Feature',
                'Verifica que un gestor no puede crear citas para empresas diferentes a la suya',
                '✅ Pasa',
                'Asegura aislamiento multi-tenant y seguridad de datos'
            ],
            [
                'tests/Feature/GestorCitasControllerTest.php',
                'test_retorna_error_si_el_horario_del_medico_esta_ocupado',
                'Feature',
                'Verifica que retorna error HTTP cuando se intenta crear cita en horario ocupado',
                '✅ Pasa',
                'Valida respuestas de API y manejo de errores en endpoints'
            ],
            [
                'tests/Feature/Paciente/PacienteCitasTest.php',
                'test_un_paciente_puede_agendar_una_cita_correctamente',
                'Feature',
                'Verifica que un paciente puede agendar una cita correctamente a través del endpoint',
                '✅ Pasa',
                'Confirma flujo completo de agendamiento desde perspectiva del paciente'
            ],
            [
                'tests/Feature/Paciente/PacienteCitasTest.php',
                'test_un_paciente_no_puede_agendar_cita_si_no_hay_medicos_disponibles',
                'Feature',
                'Verifica que retorna error cuando no hay médicos disponibles para el servicio solicitado',
                '✅ Pasa',
                'Maneja escenarios de falta de disponibilidad de manera elegante'
            ],
            [
                'tests/Feature/Paciente/PacienteCitasTest.php',
                'test_un_paciente_no_puede_agendar_cita_en_fecha_pasada',
                'Feature',
                'Verifica que no permite agendar citas en fechas pasadas',
                '✅ Pasa',
                'Previene errores de validación temporal en el sistema'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Archivo de Prueba',
            'Nombre del Método',
            'Tipo de Prueba',
            'Descripción',
            'Estado',
            'Detalles Adicionales'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para el encabezado
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2E75B6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Ajustar altura de filas
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Bordes para todas las celdas
        $sheet->getStyle('A1:F11')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Alineación general
        $sheet->getStyle('A2:F11')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A2:F11')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        return [];
    }
}