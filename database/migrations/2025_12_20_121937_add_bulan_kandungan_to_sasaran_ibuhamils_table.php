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
        Schema::table('sasaran_ibuhamils', function (Blueprint $table) {
            $table->integer('bulan_kandungan')->nullable()->after('umur_sasaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sasaran_ibuhamils', function (Blueprint $table) {
            $table->dropColumn('bulan_kandungan');
        });
    }
};
