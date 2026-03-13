Route::get('/test', function () {
    return response()->json([
        "status" => "ok",
        "system" => "ERP Hangar API"
    ]);
});