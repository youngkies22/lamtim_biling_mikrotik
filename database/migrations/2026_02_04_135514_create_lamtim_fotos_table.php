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
        Schema::create('lamtim_fotos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idUser');
            $table->string('path');
            $table->string('nama')->nullable();
            $table->timestamps();
            
            $table->foreign('idUser')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamtim_fotos');
    }
};
