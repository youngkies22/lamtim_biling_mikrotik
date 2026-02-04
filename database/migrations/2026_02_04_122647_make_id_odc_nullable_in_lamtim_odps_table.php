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
        Schema::table('lamtim_odps', function (Blueprint $table) {
            $table->bigInteger('idOdc')->nullable()->change();
            $table->integer('portOdc')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_odps', function (Blueprint $table) {
            $table->bigInteger('idOdc')->nullable(false)->change();
            $table->integer('portOdc')->nullable(false)->change();
        });
    }
};
