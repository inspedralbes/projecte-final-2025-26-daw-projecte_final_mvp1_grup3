<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuaris', function (Blueprint $table) {
            $table->string('monstre_tipus', 2)->nullable()->after('logros_showcase');
            $table->timestamp('data_naixement_monstre')->nullable()->after('monstre_tipus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuaris', function (Blueprint $table) {
            $table->dropColumn(['monstre_tipus', 'data_naixement_monstre']);
        });
    }
};