<?php

namespace App\Providers;

use App\Models\HistoriaClinica;
use App\Observers\HistoriaClinicaObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        HistoriaClinica::observe(HistoriaClinicaObserver::class);
    }
}
