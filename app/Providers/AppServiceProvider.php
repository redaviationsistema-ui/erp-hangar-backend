<?php

namespace App\Providers;

use App\Models\Aeronave;
use App\Models\Consumible;
use App\Models\Discrepancia;
use App\Models\Herramienta;
use App\Models\Medicion;
use App\Models\Motor;
use App\Models\Ndt;
use App\Models\Orden;
use App\Models\PersonalTecnico;
use App\Models\Refaccion;
use App\Models\TallerExterno;
use App\Models\Tarea;
use App\Observers\AuditableObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $observer = AuditableObserver::class;

        Aeronave::observe($observer);
        Motor::observe($observer);
        Orden::observe($observer);
        PersonalTecnico::observe($observer);
        Tarea::observe($observer);
        Discrepancia::observe($observer);
        Refaccion::observe($observer);
        Consumible::observe($observer);
        Herramienta::observe($observer);
        Ndt::observe($observer);
        TallerExterno::observe($observer);
        Medicion::observe($observer);
    }
}
