<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('clientes', 'nombre_comercial')) {
            return;
        }

        DB::statement('CREATE INDEX IF NOT EXISTS clientes_nombre_comercial_index ON clientes (nombre_comercial)');
        DB::statement('CREATE INDEX IF NOT EXISTS clientes_razon_social_index ON clientes (razon_social)');
        DB::statement('CREATE INDEX IF NOT EXISTS clientes_contacto_nombre_index ON clientes (contacto_nombre)');
        DB::statement('CREATE INDEX IF NOT EXISTS clientes_email_index ON clientes (email)');
        DB::statement('CREATE INDEX IF NOT EXISTS clientes_rfc_index ON clientes (rfc)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS clientes_nombre_comercial_index');
        DB::statement('DROP INDEX IF EXISTS clientes_razon_social_index');
        DB::statement('DROP INDEX IF EXISTS clientes_contacto_nombre_index');
        DB::statement('DROP INDEX IF EXISTS clientes_email_index');
        DB::statement('DROP INDEX IF EXISTS clientes_rfc_index');
    }
};
