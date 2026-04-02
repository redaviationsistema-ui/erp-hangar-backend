<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AtaController;
use App\Http\Controllers\AtaTaskTemplateController;
use App\Http\Controllers\AeronaveController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsumibleController;
use App\Http\Controllers\DiscrepanciaController;
use App\Http\Controllers\HerramientaController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\ManualSearchController;
use App\Http\Controllers\MedicionController;
use App\Http\Controllers\MotorController;
use App\Http\Controllers\NdtController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\RefaccionController;
use App\Http\Controllers\TallerExternoController;
use App\Http\Controllers\TareaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/user', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/admin/dashboard/resumen', [AdminDashboardController::class, 'resumen']);
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::apiResource('areas', AreaController::class);
        Route::apiResource('aeronaves', AeronaveController::class)->parameters([
            'aeronaves' => 'aeronave',
        ]);
        Route::apiResource('motores', MotorController::class)->parameters([
            'motores' => 'motor',
        ]);
        Route::get('/manuales/source-files', [ManualController::class, 'sourceFiles']);
        Route::post('/manuales/import-source', [ManualController::class, 'importFromSource']);
        Route::post('/manuales/{manuale}/process-source', [ManualController::class, 'processSource'])
            ->whereNumber('manuale');
        Route::apiResource('manuales', ManualController::class)
            ->parameters([
                'manuales' => 'manuale',
            ])
            ->where([
                'manuale' => '[0-9]+',
            ]);
        Route::get('/manuales-busqueda', [ManualSearchController::class, 'search']);
        Route::get('/discrepancias/{discrepancia}/contexto-manual', [ManualSearchController::class, 'discrepancy']);
        Route::get('/ata', [AtaController::class, 'index']);
        Route::get('/ata/chapters/{chapter}/subchapters', [AtaController::class, 'subchapters']);
        Route::get('/ata/subchapters/{subchapter}', [AtaController::class, 'showSubchapter']);
        Route::get('/ata/templates', [AtaTaskTemplateController::class, 'index']);
        Route::get('/ata/subchapters/{subchapter}/templates', [AtaTaskTemplateController::class, 'getBySubAta']);

        Route::get('/ordenes/{ordene}/completo', [OrdenController::class, 'showCompleto']);
        Route::get('/ordenes/{ordene}/trazabilidad', [OrdenController::class, 'showTraceability']);
        Route::apiResource('ordenes', OrdenController::class);

        Route::apiResource('tareas', TareaController::class);
        Route::apiResource('discrepancias', DiscrepanciaController::class);
        Route::apiResource('refacciones', RefaccionController::class);
        Route::apiResource('consumibles', ConsumibleController::class);
        Route::apiResource('herramientas', HerramientaController::class);
        Route::apiResource('ndt', NdtController::class);
        Route::apiResource('talleres', TallerExternoController::class);
        Route::apiResource('mediciones', MedicionController::class);
    });
});
