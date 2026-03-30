<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return response()->json([
        'message' => 'API funcionando',
    ]);
});

Route::get('/debug-imagen', function () {
    $path = 'discrepancias/1544ca2b-3983-4a70-8d0e-dc34802df65f.png';

    return response()->json([
        'path_bd' => $path,
        'exists_public_disk' => Storage::disk('public')->exists($path),
        'storage_real' => storage_path('app/public/' . $path),
        'public_path' => public_path('storage/' . $path),
        'url_publica' => asset('storage/' . $path),
    ]);
});
