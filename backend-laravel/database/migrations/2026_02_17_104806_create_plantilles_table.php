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
        Schema::create('plantilles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creador_id')->nullable();
            $table->string('titol', 100);
            $table->string('categoria', 50)->nullable();
            $table->boolean('es_publica')->default(false);
            $table->timestamps();

            $table->foreign('creador_id')->references('id')->on('usuaris')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantilles');
    }
};
