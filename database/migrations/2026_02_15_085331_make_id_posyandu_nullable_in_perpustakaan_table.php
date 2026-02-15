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
        Schema::table('perpustakaan', function (Blueprint $table) {
            $table->dropForeign(['id_posyandu']);
            $table->unsignedBigInteger('id_posyandu')->nullable()->change();
            $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perpustakaan', function (Blueprint $table) {
            $table->dropForeign(['id_posyandu']);
            $table->unsignedBigInteger('id_posyandu')->nullable(false)->change();
            $table->foreign('id_posyandu')->references('id_posyandu')->on('posyandu')->onDelete('cascade');
        });
    }
};
