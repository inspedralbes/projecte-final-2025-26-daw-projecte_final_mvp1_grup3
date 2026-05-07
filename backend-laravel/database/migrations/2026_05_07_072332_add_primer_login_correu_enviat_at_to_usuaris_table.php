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
        if (Schema::hasColumn('usuaris', 'primer_login_correu_enviat_at')) {
            return;
        }

        Schema::table('usuaris', function (Blueprint $table) {
            $table->timestamp('primer_login_correu_enviat_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuaris', function (Blueprint $table) {
            if (Schema::hasColumn('usuaris', 'primer_login_correu_enviat_at')) {
                $table->dropColumn('primer_login_correu_enviat_at');
            }
        });
    }
};
