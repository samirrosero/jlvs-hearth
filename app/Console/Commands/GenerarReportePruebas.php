<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PruebasExport;

class GenerarReportePruebas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reporte:pruebas {--output= : Ruta del archivo de salida}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un reporte Excel con la documentación de todas las pruebas unitarias y de feature';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $outputPath = $this->option('output') ?: storage_path('app/reporte_pruebas.xlsx');

        $this->info('Generando reporte de pruebas...');

        Excel::store(new PruebasExport(), 'reporte_pruebas.xlsx', 'public');

        $this->info("✅ Reporte generado exitosamente en: storage/app/reporte_pruebas.xlsx");
        $this->info("📁 También disponible en: {$outputPath}");

        return Command::SUCCESS;
    }
}
