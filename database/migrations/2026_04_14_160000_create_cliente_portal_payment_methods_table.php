<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_portal_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('cliente_portal_payment_methods')->insert([
            [
                'code' => 'transferencia',
                'name' => 'Transferencia SPEI',
                'description' => 'Pago por transferencia bancaria empresarial.',
                'instructions' => 'Realiza el pago y comparte tu referencia o comprobante desde la app.',
                'active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'tarjeta',
                'name' => 'Tarjeta',
                'description' => 'Pago con debito o credito.',
                'instructions' => 'Solicita liga o terminal con tu asesor comercial.',
                'active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'credito',
                'name' => 'Credito autorizado',
                'description' => 'Aplica para clientes con linea aprobada.',
                'instructions' => 'La solicitud quedara pendiente de validacion administrativa.',
                'active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_portal_payment_methods');
    }
};
