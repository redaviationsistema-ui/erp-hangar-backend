<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 🔥 IMPORTS
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\AreaController;

use App\Http\Controllers\TareaController;
use App\Http\Controllers\DiscrepanciaController;
use App\Http\Controllers\RefaccionController;
use App\Http\Controllers\ConsumibleController;
use App\Http\Controllers\HerramientaController;
use App\Http\Controllers\NdtController;
use App\Http\Controllers\TallerExternoController;
use App\Http\Controllers\MedicionController;

Route::prefix('v1')->group(function () {

    // 🔐 LOGIN (público)
    Route::post('/login', [AuthController::class, 'login']);

    // 🔒 PROTEGIDAS
    Route::middleware(['auth:sanctum'])->group(function () {

        // 📂 ÁREAS
        Route::apiResource('areas', AreaController::class);

        // 📋 ÓRDENES (COMPLETO)
        Route::apiResource('ordenes', OrdenController::class)
            ->except(['store']);

        Route::post('/ordenes', [OrdenController::class, 'store'])
            ->middleware('area:AVCS,HANG');

        // 🔧 MÓDULOS
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