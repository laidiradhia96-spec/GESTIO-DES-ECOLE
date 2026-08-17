<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_years', function (Blueprint $table) {

            $table->id();

            // Libellé de l'année : "2025-2026"
            $table->string('name', 20)->unique();

            // Début de l'année (ex. 01-09)
            $table->date('start_date');

            // Fin de l'année (ex. 31-08)
            $table->date('end_date');

            // Année courante (une seule à true, gérée par setCurrent)
            $table->boolean('is_current')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_years');
    }
};
