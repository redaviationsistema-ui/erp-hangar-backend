<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            if (!Schema::hasColumn('ordenes', 'area_id')) {
                $table->foreignId('area_id')->nullable()->after('id')->constrained('areas')->nullOnDelete();
            }

            if (!Schema::hasColumn('ordenes', 'ata_chapter_id')) {
                $table->foreignId('ata_chapter_id')->nullable()->after('user_id')->constrained('ata_chapters')->nullOnDelete();
            }

            if (!Schema::hasColumn('ordenes', 'ata_subchapter_id')) {
                $table->foreignId('ata_subchapter_id')->nullable()->after('ata_chapter_id')->constrained('ata_subchapters')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes', 'ata_subchapter_id')) {
                $table->dropConstrainedForeignId('ata_subchapter_id');
            }

            if (Schema::hasColumn('ordenes', 'ata_chapter_id')) {
                $table->dropConstrainedForeignId('ata_chapter_id');
            }

            if (Schema::hasColumn('ordenes', 'area_id')) {
                $table->dropConstrainedForeignId('area_id');
            }
        });
    }
};
