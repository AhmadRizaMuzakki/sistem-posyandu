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
            $table->unsignedSmallInteger('sistol')->nullable()->after('berat_badan')->comment('mmHg');
            $table->unsignedSmallInteger('diastol')->nullable()->after('sistol')->comment('mmHg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imunisasi', function (Blueprint $table) {
            $table->dropColumn(['sistol', 'diastol']);
        });
    }
};
