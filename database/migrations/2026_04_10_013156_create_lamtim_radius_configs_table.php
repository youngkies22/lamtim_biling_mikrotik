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
        Schema::create('lamtim_radius_configs', function (Blueprint $table) {
            $table->id();
            $table->string('host');
            $table->integer('port')->default(3306);
            $table->string('database');
            $table->string('username');
            $table->string('password')->nullable();
            $table->string('secret')->nullable()->comment('Shared Secret RADIUS');
            $table->boolean('isActive')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_radius_configs');
    }
};
