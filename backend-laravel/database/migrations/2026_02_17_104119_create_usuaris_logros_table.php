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
        Schema::create('usuaris_logros', function (Blueprint $table) {
            $table->unsignedBigInteger('usuari_id');
            $table->unsignedBigInteger('logro_id');
            $table->date('data_obtencio')->useCurrent();

            $table->foreign('usuari_id')->references('id')->on('usuaris')->onDelete('cascade');
            $table->foreign('logro_id')->references('id')->on('logros_medalles')->onDelete('cascade');

            $table->primary(['usuari_id', 'logro_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuaris_logros');
    }
};
