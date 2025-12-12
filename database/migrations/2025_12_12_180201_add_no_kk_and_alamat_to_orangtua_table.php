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
        Schema::table('orangtua', function (Blueprint $table) {
            $table->unsignedBigInteger('no_kk')->nullable()->after('nama');
            $table->text('alamat')->nullable()->after('kelamin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orangtua', function (Blueprint $table) {
            $table->dropColumn(['no_kk', 'alamat']);
        });
    }
};
