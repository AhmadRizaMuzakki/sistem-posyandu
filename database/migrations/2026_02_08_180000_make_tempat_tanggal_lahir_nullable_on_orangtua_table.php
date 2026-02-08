<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Opsi A: tempat_lahir dan tanggal_lahir di orangtua dibuat nullable
     * agar import sasaran Remaja/Balita bisa jalan tanpa data orangtua lengkap.
     */
    public function up(): void
    {
        Schema::table('orangtua', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orangtua', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable(false)->change();
            $table->date('tanggal_lahir')->nullable(false)->change();
        });
    }
};
