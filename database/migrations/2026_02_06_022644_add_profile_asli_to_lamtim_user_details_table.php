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
        Schema::table('lamtim_user_details', function (Blueprint $table) {
            $table->string('profileAsli')->nullable()->after('statusIsolir')->comment('Profile PPPoE asli sebelum diisolir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_user_details', function (Blueprint $table) {
            $table->dropColumn('profileAsli');
        });
    }
};
