<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AtaController;
use App\Http\Controllers\AtaTaskTemplateController;
use App\Http\Controllers\AeronaveController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsumibleController;
use App\Http\Controllers\DiscrepanciaController;
use App\Http\Controllers\HerramientaController;
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
        Route::apiResource('areas', AreaController::class);
        Route::apiResource('aeronaves', AeronaveController::class)->parameters([
            'aeronaves' => 'aeronave',
        ]);
        Route::apiResource('motores', MotorController::class)->parameters([
            'motores' => 'motor',
        ]);
        Route::get('/ata', [AtaController::class, 'index']);
        Route::get('/ata/chapters/{chapter}/subchapters', [AtaController::class, 'subchapters']);
        Route::get('/ata/subchapters/{subchapter}', [AtaController::class, 'showSubchapter']);
        Route::get('/ata/templates', [AtaTaskTemplateController::class, 'index']);
        Route::get('/ata/subchapters/{subchapter}/templates', [AtaTaskTemplateController::class, 'getBySubAta']);

        Route::get('/ordenes/{ordene}/completo', [OrdenController::class, 'showCompleto']);
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
