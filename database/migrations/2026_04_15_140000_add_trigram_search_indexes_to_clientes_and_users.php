<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        DB::statement('CREATE INDEX IF NOT EXISTS clientes_nombre_comercial_trgm_index ON clientes USING gin (lower(nombre_comercial) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS clientes_razon_social_trgm_index ON clientes USING gin (lower(razon_social) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS clientes_contacto_nombre_trgm_index ON clientes USING gin (lower(contacto_nombre) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS clientes_email_trgm_index ON clientes USING gin (lower(email) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS clientes_rfc_trgm_index ON clientes USING gin (lower(rfc) gin_trgm_ops)');

        DB::statement('CREATE INDEX IF NOT EXISTS usuarios_name_trgm_index ON users USING gin (lower(name) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS usuarios_email_trgm_index ON users USING gin (lower(email) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS usuarios_rol_trgm_index ON users USING gin (lower(rol) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS usuarios_rol_nombre_trgm_index ON users USING gin (lower(rol_nombre) gin_trgm_ops)');

        DB::statement('CREATE INDEX IF NOT EXISTS areas_codigo_trgm_index ON areas USING gin (lower(codigo) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS areas_nombre_trgm_index ON areas USING gin (lower(nombre) gin_trgm_ops)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS clientes_nombre_comercial_trgm_index');
        DB::statement('DROP INDEX IF EXISTS clientes_razon_social_trgm_index');
        DB::statement('DROP INDEX IF EXISTS clientes_contacto_nombre_trgm_index');
        DB::statement('DROP INDEX IF EXISTS clientes_email_trgm_index');
        DB::statement('DROP INDEX IF EXISTS clientes_rfc_trgm_index');

        DB::statement('DROP INDEX IF EXISTS usuarios_name_trgm_index');
        DB::statement('DROP INDEX IF EXISTS usuarios_email_trgm_index');
        DB::statement('DROP INDEX IF EXISTS usuarios_rol_trgm_index');
        DB::statement('DROP INDEX IF EXISTS usuarios_rol_nombre_trgm_index');

        DB::statement('DROP INDEX IF EXISTS areas_codigo_trgm_index');
        DB::statement('DROP INDEX IF EXISTS areas_nombre_trgm_index');
    }
};
