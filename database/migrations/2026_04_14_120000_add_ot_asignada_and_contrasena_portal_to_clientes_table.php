<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (! Schema::hasColumn('clientes', 'ot_asignada_id')) {
                $table->foreignId('ot_asignada_id')->nullable()->after('password')->constrained('ordenes')->nullOnDelete();
            }

            if (! Schema::hasColumn('clientes', 'contrasena_portal')) {
                $table->text('contrasena_portal')->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'ot_asignada_id')) {
                $table->dropConstrainedForeignId('ot_asignada_id');
            }

            if (Schema::hasColumn('clientes', 'contrasena_portal')) {
                $table->dropColumn('contrasena_portal');
            }
        });
    }
};
