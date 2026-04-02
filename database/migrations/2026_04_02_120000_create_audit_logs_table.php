<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_label')->nullable();
            $table->foreignId('order_id')->nullable()->constrained('ordenes')->nullOnDelete();
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('context')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['entity_type', 'entity_id'], 'audit_logs_entity_index');
            $table->index(['order_id', 'occurred_at'], 'audit_logs_order_index');
            $table->index(['user_id', 'occurred_at'], 'audit_logs_user_index');
            $table->index(['action', 'occurred_at'], 'audit_logs_action_index');
            $table->index('occurred_at', 'audit_logs_occurred_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
