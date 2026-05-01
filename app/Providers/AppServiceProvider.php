<?php

namespace App\Providers;

use App\Models\HistoriaClinica;
use App\Observers\HistoriaClinicaObserver;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('es');
        HistoriaClinica::observe(HistoriaClinicaObserver::class);
    }
}
