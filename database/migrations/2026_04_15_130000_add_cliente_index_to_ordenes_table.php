<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ordenes', 'cliente')) {
            return;
        }

        DB::statement('CREATE INDEX IF NOT EXISTS ordenes_cliente_index ON ordenes (cliente)');
    }

    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropIndex('ordenes_cliente_index');
        });
    }
};
