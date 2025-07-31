<?php
// Ruta: database/migrations/2025_07_30_fix_notifications_table.php

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
        Schema::table('notifications', function (Blueprint $table) {
            // Verificar y agregar columnas si no existen
            if (!Schema::hasColumn('notifications', 'notifiable_type')) {
                $table->string('notifiable_type')->after('id')->default('App\\Models\\User');
            }
            
            if (!Schema::hasColumn('notifications', 'notifiable_id')) {
                $table->unsignedBigInteger('notifiable_id')->after('notifiable_type')->default(1);
            }
            
            // Agregar índice si no existe
            $indexExists = collect(DB::select("SHOW INDEXES FROM notifications"))
                ->pluck('Key_name')
                ->contains('notifications_notifiable_type_notifiable_id_index');
                
            if (!$indexExists) {
                $table->index(['notifiable_type', 'notifiable_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Primero eliminar el índice si existe
            $table->dropIndex(['notifiable_type', 'notifiable_id']);
            
            // Luego eliminar las columnas
            $table->dropColumn(['notifiable_type', 'notifiable_id']);
        });
    }
};