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
            $table->renameColumn('bulan_kandungan', 'minggu_kandungan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sasaran_ibuhamils', function (Blueprint $table) {
            $table->renameColumn('minggu_kandungan', 'bulan_kandungan');
        });
    }
};
