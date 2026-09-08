<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imunisasi', function (Blueprint $table) {
            $table->decimal('lingkar_kepala', 5, 2)->nullable()->after('berat_badan')->comment('cm — khusus sasaran bayibalita');
        });
    }

    public function down(): void
    {
        Schema::table('imunisasi', function (Blueprint $table) {
            $table->dropColumn('lingkar_kepala');
        });
    }
};
