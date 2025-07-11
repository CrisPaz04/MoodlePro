<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            
            // Usuario que recibe la notificación
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Tipo de notificación (task_assigned, project_deadline, etc.)
            $table->string('type', 50);
            
            // Título y mensaje de la notificación
            $table->string('title');
            $table->text('message');
            
            // Datos adicionales en formato JSON
            $table->json('data')->nullable();
            
            // Relación polimórfica con el modelo relacionado
            $table->string('related_type', 100)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            
            // Fecha de lectura
            $table->timestamp('read_at')->nullable();
            
            // URL de acción (opcional)
            $table->string('action_url')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Soft deletes para mantener historial
            $table->softDeletes();
            
            // Índices para mejorar rendimiento
            $table->index(['user_id', 'read_at']); // Para consultas de notificaciones no leídas
            $table->index(['user_id', 'created_at']); // Para ordenar por fecha
            $table->index(['type']); // Para filtrar por tipo
            $table->index(['related_type', 'related_id']); // Para relaciones polimórficas
            $table->index(['user_id', 'type', 'created_at']); // Índice compuesto para queries complejas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};