<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refacciones', function (Blueprint $table) {
            if (! Schema::hasColumn('refacciones', 'solicitante_nombre')) {
                $table->string('solicitante_nombre')->nullable()->after('solicitante_fecha');
            }
            if (! Schema::hasColumn('refacciones', 'recibe_nombre')) {
                $table->string('recibe_nombre')->nullable()->after('recibe_fecha');
            }
            if (! Schema::hasColumn('refacciones', 'certificado_conformidad_imagen')) {
                $table->string('certificado_conformidad_imagen')->nullable()->after('certificado_conformidad');
            }
        });

        Schema::table('consumibles', function (Blueprint $table) {
            if (! Schema::hasColumn('consumibles', 'solicitante_nombre')) {
                $table->string('solicitante_nombre')->nullable()->after('solicitante_fecha');
            }
            if (! Schema::hasColumn('consumibles', 'recibe_nombre')) {
                $table->string('recibe_nombre')->nullable()->after('recibe_fecha');
            }
        });
    }

    public function down(): void
    {
        Schema::table('refacciones', function (Blueprint $table) {
            $drop = [];

            if (Schema::hasColumn('refacciones', 'solicitante_nombre')) {
                $drop[] = 'solicitante_nombre';
            }
            if (Schema::hasColumn('refacciones', 'recibe_nombre')) {
                $drop[] = 'recibe_nombre';
            }
            if (Schema::hasColumn('refacciones', 'certificado_conformidad_imagen')) {
                $drop[] = 'certificado_conformidad_imagen';
            }

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });

        Schema::table('consumibles', function (Blueprint $table) {
            $drop = [];

            if (Schema::hasColumn('consumibles', 'solicitante_nombre')) {
                $drop[] = 'solicitante_nombre';
            }
            if (Schema::hasColumn('consumibles', 'recibe_nombre')) {
                $drop[] = 'recibe_nombre';
            }

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
