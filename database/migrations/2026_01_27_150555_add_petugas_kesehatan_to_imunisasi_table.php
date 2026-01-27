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
        Schema::table('imunisasi', function (Blueprint $table) {
            $table->unsignedBigInteger('id_petugas_kesehatan')->nullable()->after('id_users');

            $table->foreign('id_petugas_kesehatan')->references('id_petugas_kesehatan')->on('petugas_kesehatan')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imunisasi', function (Blueprint $table) {
            $table->dropForeign(['id_petugas_kesehatan']);
            $table->dropColumn('id_petugas_kesehatan');
        });
    }
};
