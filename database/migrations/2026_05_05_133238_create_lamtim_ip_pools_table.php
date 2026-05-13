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
        Schema::create('lamtim_ip_pools', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('ranges')->nullable();
            $table->string('next_pool')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_ip_pools');
    }
};
