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
        Schema::create('ratxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuari_id');
            $table->integer('ratxa_actual')->default(0);
            $table->integer('ratxa_maxima')->default(0);
            $table->date('ultima_data')->nullable();
            $table->timestamps();

            $table->foreign('usuari_id')->references('id')->on('usuaris')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratxes');
    }
};
