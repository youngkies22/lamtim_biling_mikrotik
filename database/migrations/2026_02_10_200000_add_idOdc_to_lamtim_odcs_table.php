<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lamtim_odcs', function (Blueprint $table) {
            if (!Schema::hasColumn('lamtim_odcs', 'idOdc')) {
                $table->bigInteger('idOdc')->nullable()->after('idOlt');
            }
            $table->index('idOdc');
        });
    }

    public function down(): void
    {
        Schema::table('lamtim_odcs', function (Blueprint $table) {
            $table->dropIndex(['idOdc']);
            $table->dropColumn('idOdc');
        });
    }
};
