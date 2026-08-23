<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posyandu', function (Blueprint $table) {
            $table->string('status_mbg', 20)->nullable()->after('domisili_posyandu');
        });
    }

    public function down(): void
    {
        Schema::table('posyandu', function (Blueprint $table) {
            $table->dropColumn('status_mbg');
        });
    }
};
