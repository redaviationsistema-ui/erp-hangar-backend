<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->change();
            $table->foreign('area_id')->references('id')->on('areas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable(false)->change();
            $table->foreign('area_id')->references('id')->on('areas')->cascadeOnDelete();
        });
    }
};
