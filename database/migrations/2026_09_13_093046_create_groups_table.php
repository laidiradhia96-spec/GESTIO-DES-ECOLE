<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->string('level');

            $table->foreignId('school_year_id')
                ->constrained('school_years')
                ->restrictOnDelete();

            $table->string('name');

            $table->enum('mode', ['normal', 'special', 'vip'])
                ->default('normal');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique([
                'teacher_id',
                'subject_id',
                'level',
                'school_year_id',
                'name',
            ]);

            $table->index(['school_year_id', 'level']);
            $table->index(['teacher_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
