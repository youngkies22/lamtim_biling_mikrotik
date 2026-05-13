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
        Schema::table('lamtim_user_mikrotik_details', function (Blueprint $table) {
            // Kolom pelacakan kapan terakhir data di-sync ke RADIUS
            $table->timestamp('radius_synced_at')->nullable()->after('status');
            
            // Index pada namaMikrotikUser sebagai key relasi ke tabel RADIUS (username)
            $table->index('namaMikrotikUser', 'idx_radius_username');
            $table->index('status', 'idx_user_status');
            $table->index('statusIsolir', 'idx_user_isolir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lamtim_user_mikrotik_details', function (Blueprint $table) {
            $table->dropColumn('radius_synced_at');
            $table->dropIndex('idx_radius_username');
            $table->dropIndex('idx_user_status');
            $table->dropIndex('idx_user_isolir');
        });
    }
};
