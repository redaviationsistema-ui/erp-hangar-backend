<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_portal_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('orden_id')->nullable()->constrained('ordenes')->nullOnDelete();
            $table->string('folio')->index();
            $table->string('concepto');
            $table->decimal('amount_total', 12, 2)->default(0);
            $table->string('currency', 8)->default('MXN');
            $table->string('status', 50)->default('pendiente');
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->text('pdf_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_portal_invoices');
    }
};
