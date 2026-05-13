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
        Schema::table('lamtim_pakets', function (Blueprint $table) {
            $table->string('speed_limit')->nullable()->after('price')->comment('Contoh: 10M/10M');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_pakets', function (Blueprint $table) {
            $table->dropColumn('speed_limit');
        });
    }
};
