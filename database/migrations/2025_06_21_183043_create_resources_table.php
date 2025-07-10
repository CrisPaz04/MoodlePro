<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 10); // pdf, doc, etc
            $table->integer('file_size'); // en bytes
            $table->enum('category', ['document', 'presentation', 'spreadsheet', 'image', 'other'])->default('document');
            $table->string('tags')->nullable(); // separados por comas
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->decimal('average_rating', 2, 1)->default(0); // 0.0 a 5.0
            $table->integer('total_ratings')->default(0);
            $table->integer('download_count')->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            
            $table->index(['category', 'is_public']);
            $table->index(['uploaded_by']);
            $table->index(['average_rating']);
            $table->fullText(['title', 'description', 'tags']); // búsqueda
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};