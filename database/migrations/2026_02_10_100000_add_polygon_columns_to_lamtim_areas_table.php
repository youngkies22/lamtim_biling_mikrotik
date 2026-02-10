<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lamtim_areas', function (Blueprint $table) {
            $table->json('coordinates')->nullable()->after('code_area');
            $table->string('color', 7)->default('#696cff')->after('coordinates');
        });
    }

    public function down(): void
    {
        Schema::table('lamtim_areas', function (Blueprint $table) {
            $table->dropColumn(['coordinates', 'color']);
        });
    }
};
