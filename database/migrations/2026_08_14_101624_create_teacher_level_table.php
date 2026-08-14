<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_level', function (Blueprint $table) {

            $table->id();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            $table->foreignId('level_id')
                ->constrained('levels')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['teacher_id', 'level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_level');
    }
};