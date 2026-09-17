<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_tariffs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id')
                ->constrained('groups')
                ->cascadeOnDelete();

            $table->enum('billing_type', ['monthly', 'per_session']);

            $table->decimal('student_price', 10, 2);
            $table->decimal('teacher_share', 10, 2);
            $table->decimal('academy_share', 10, 2);

            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['group_id', 'effective_from']);
            $table->index(['group_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_tariffs');
    }
};
