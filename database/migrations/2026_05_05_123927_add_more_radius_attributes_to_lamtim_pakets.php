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
            $table->string('ip_pool')->nullable()->after('speed_limit')->comment('Framed-Pool');
            $table->string('address_list')->nullable()->after('ip_pool')->comment('Mikrotik-Address-List');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_pakets', function (Blueprint $table) {
            $table->dropColumn(['ip_pool', 'address_list']);
        });
    }
};
