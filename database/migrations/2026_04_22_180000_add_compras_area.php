<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('areas')->updateOrInsert(
            ['codigo' => 'COMPRAS'],
            [
                'nombre' => 'COMPRAS',
                'numero' => '98',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('areas')->where('codigo', 'COMPRAS')->delete();
    }
};
