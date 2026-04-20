<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discrepancias', function (Blueprint $table) {
            if (! Schema::hasColumn('discrepancias', 'tecnico')) {
                $table->string('tecnico')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('discrepancias', function (Blueprint $table) {
            if (Schema::hasColumn('discrepancias', 'tecnico')) {
                $table->dropColumn('tecnico');
            }
        });
    }
};
