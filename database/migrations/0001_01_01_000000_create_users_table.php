<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Clase anónima de migración
return new class extends Migration
{
    /**
     * Ejecuta la migración (CREA LAS TABLAS)
     */
    public function up(): void
    {
        /**
         * 📌 TABLA: users
         * Aquí se almacenan todos los usuarios del sistema
         */
        Schema::create('users', function (Blueprint $table) {

            // 🔑 ID principal (auto incremental)
            $table->id();

            // 👤 Nombre del usuario
            $table->string('name');

            // 📧 Email único (no puede repetirse)
            $table->string('email')->unique();

            // ✔️ Fecha de verificación del correo (nullable = opcional)
            $table->timestamp('email_verified_at')->nullable();

            // 🔒 Contraseña encriptada
            $table->string('password');

            /**
             * 🔥 RELACIÓN CON AREAS
             * Esto crea:
             * - columna area_id (BIGINT)
             * - llave foránea hacia tabla "areas"
             */
            $table->foreignId('area_id')
                  ->constrained('areas') // referencia a tabla areas (id)
                  ->onDelete('cascade'); // si se elimina el área → se eliminan usuarios

            /**
             * 🎭 (OPCIONAL PERO RECOMENDADO)
             * Tipo de usuario dentro del sistema
             */
            $table->enum('rol', ['admin', 'tecnico', 'supervisor'])
                  ->default('tecnico');

            // 🔐 Token para "recordarme" (login persistente)
            $table->rememberToken();

            // 🕒 created_at y updated_at
            $table->timestamps();
        });

        /**
         * 📌 TABLA: password_reset_tokens
         * Para recuperación de contraseña
         */
        Schema::create('password_reset_tokens', function (Blueprint $table) {

            // 📧 Email como clave primaria
            $table->string('email')->primary();

            // 🔑 Token de recuperación
            $table->string('token');

            // 🕒 Fecha de creación del token
            $table->timestamp('created_at')->nullable();
        });

        /**
         * 📌 TABLA: sessions
         * Manejo de sesiones activas (login)
         */
        Schema::create('sessions', function (Blueprint $table) {

            // 🔑 ID de sesión
            $table->string('id')->primary();

            /**
             * 👤 Usuario relacionado (opcional)
             * No siempre hay usuario (ej: visitante)
             */
            $table->foreignId('user_id')
                  ->nullable()
                  ->index();

            // 🌐 Dirección IP
            $table->string('ip_address', 45)->nullable();

            // 💻 Información del navegador
            $table->text('user_agent')->nullable();

            // 📦 Datos de la sesión
            $table->longText('payload');

            // ⏱ Última actividad (timestamp en entero)
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Revierte la migración (ELIMINA LAS TABLAS)
     */
    public function down(): void
    {
        // ⚠️ Orden importante: primero tablas dependientes
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};