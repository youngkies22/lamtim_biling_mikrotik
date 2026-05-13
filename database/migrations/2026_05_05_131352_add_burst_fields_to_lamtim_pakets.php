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
            $table->boolean('is_burst')->default(0)->after('speed_limit');
            $table->string('burst_rate')->nullable()->after('is_burst');
            $table->string('burst_threshold')->nullable()->after('burst_rate');
            $table->string('burst_time')->nullable()->after('burst_threshold');
            $table->integer('priority')->default(8)->after('burst_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_pakets', function (Blueprint $table) {
            $table->dropColumn(['is_burst', 'burst_rate', 'burst_threshold', 'burst_time', 'priority']);
        });
    }
};
