<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->rememberToken();
        });

        DB::table('clientes')
            ->whereNull('password')
            ->update([
                'password' => Hash::make('Cliente123!'),
            ]);
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'password',
                'remember_token',
            ]);
        });
    }
};
