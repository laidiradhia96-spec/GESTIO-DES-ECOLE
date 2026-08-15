<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('primaire')->default(false)->after('level');
            $table->boolean('moyen')->default(false)->after('primaire');
            $table->boolean('lycee')->default(false)->after('moyen');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn([
                'primaire',
                'moyen',
                'lycee',
            ]);
        });
    }
};
