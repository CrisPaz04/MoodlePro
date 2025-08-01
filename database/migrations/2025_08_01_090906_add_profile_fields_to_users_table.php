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
        Schema::table('users', function (Blueprint $table) {
            // Información personal
            $table->string('phone', 20)->nullable()->after('email');
            $table->date('birth_date')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('birth_date');
            
            // Información académica
            $table->string('institution')->nullable()->after('bio');
            $table->string('career')->nullable()->after('institution');
            $table->string('semester', 50)->nullable()->after('career');
            $table->string('student_id', 50)->nullable()->after('semester');
            $table->string('languages')->nullable()->after('student_id');
            
            // Avatar y preferencias
            $table->string('avatar')->nullable()->after('languages');
            $table->boolean('email_notifications')->default(true)->after('avatar');
            $table->boolean('public_profile')->default(true)->after('email_notifications');
            
            // Índices para búsquedas
            $table->index('institution');
            $table->index('career');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex(['institution']);
            $table->dropIndex(['career']);
            
            // Eliminar columnas
            $table->dropColumn([
                'phone',
                'birth_date',
                'bio',
                'institution',
                'career',
                'semester',
                'student_id',
                'languages',
                'avatar',
                'email_notifications',
                'public_profile'
            ]);
        });
    }
};