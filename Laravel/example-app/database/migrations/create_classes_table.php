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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('room')->nullable();
            $table->string('schedule')->nullable();
            $table->integer('max_students')->default(30);
            $table->integer('current_students')->default(0);
            $table->timestamps();
            
            // Unique combination to avoid duplicate classes
            $table->unique(['course_id', 'teacher_id', 'room', 'schedule']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};