<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_rol_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_rol_check CHECK (rol IN ('admin', 'tecnico', 'supervisor', 'administracion'))");
            DB::statement("ALTER TABLE users ALTER COLUMN rol SET DEFAULT 'tecnico'");

            return;
        }

        DB::statement("ALTER TABLE users MODIFY rol ENUM('admin', 'tecnico', 'supervisor', 'administracion') DEFAULT 'tecnico'");
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        DB::statement("UPDATE users SET rol = 'tecnico' WHERE rol = 'administracion'");

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_rol_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_rol_check CHECK (rol IN ('admin', 'tecnico', 'supervisor'))");
            DB::statement("ALTER TABLE users ALTER COLUMN rol SET DEFAULT 'tecnico'");

            return;
        }

        DB::statement("ALTER TABLE users MODIFY rol ENUM('admin', 'tecnico', 'supervisor') DEFAULT 'tecnico'");
    }
};
