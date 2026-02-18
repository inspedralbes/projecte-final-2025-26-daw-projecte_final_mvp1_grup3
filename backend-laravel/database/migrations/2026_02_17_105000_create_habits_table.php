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
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuari_id');
            $table->unsignedBigInteger('plantilla_id')->nullable();
            $table->string('titol');
            $table->string('dificultat')->nullable();
            $table->string('frequencia_tipus')->nullable();
            $table->string('dies_setmana')->nullable();
            $table->integer('objectiu_vegades')->default(1);
            $table->timestamps();

            $table->foreign('usuari_id')->references('id')->on('usuaris')->onDelete('cascade');
            $table->foreign('plantilla_id')->references('id')->on('plantilles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habits');
    }
};
