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
        Schema::create('usuaris_habits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuari_id');
            $table->unsignedBigInteger('habit_id');
            $table->date('data_inici')->useCurrent();
            $table->boolean('actiu')->default(true);
            $table->integer('objetiu_vegades_personalitzat')->default(1);
            $table->timestamps();

            $table->foreign('usuari_id')->references('id')->on('usuaris')->onDelete('cascade');
            $table->foreign('habit_id')->references('id')->on('habits')->onDelete('cascade');

            $table->unique(['usuari_id', 'habit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuaris_habits');
    }
};
