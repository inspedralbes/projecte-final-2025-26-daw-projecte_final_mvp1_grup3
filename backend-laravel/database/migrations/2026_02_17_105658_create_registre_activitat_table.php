<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registre_activitat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('habit_id');
            $table->timestamp('data')->useCurrent();
            $table->boolean('completat')->default(true);
            $table->integer('xp_guanyada')->default(0);
            $table->timestamps();

            $table->foreign('habit_id')->references('id')->on('habits')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registre_activitat');
    }
};
