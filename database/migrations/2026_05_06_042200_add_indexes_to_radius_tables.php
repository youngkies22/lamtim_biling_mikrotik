<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cek apakah index sudah ada
     */
    private function hasIndex($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // === radcheck: Index pada username ===
        if (!$this->hasIndex('radcheck', 'idx_radcheck_username')) {
            Schema::table('radcheck', function (Blueprint $table) {
                $table->index('username', 'idx_radcheck_username');
            });
        }

        // === radusergroup: Index pada username dan groupname ===
        if (!$this->hasIndex('radusergroup', 'idx_radusergroup_username')) {
            Schema::table('radusergroup', function (Blueprint $table) {
                $table->index('username', 'idx_radusergroup_username');
            });
        }
        if (!$this->hasIndex('radusergroup', 'idx_radusergroup_groupname')) {
            Schema::table('radusergroup', function (Blueprint $table) {
                $table->index('groupname', 'idx_radusergroup_groupname');
            });
        }

        // === radreply: Index pada username ===
        if (!$this->hasIndex('radreply', 'idx_radreply_username')) {
            Schema::table('radreply', function (Blueprint $table) {
                $table->index('username', 'idx_radreply_username');
            });
        }

        // === radgroupreply: Index pada groupname ===
        if (!$this->hasIndex('radgroupreply', 'idx_radgroupreply_groupname')) {
            Schema::table('radgroupreply', function (Blueprint $table) {
                $table->index('groupname', 'idx_radgroupreply_groupname');
            });
        }

        // === radacct: Index pada username + acctstoptime untuk cek online ===
        if (!$this->hasIndex('radacct', 'idx_radacct_username_stop')) {
            Schema::table('radacct', function (Blueprint $table) {
                $table->index(['username', 'acctstoptime'], 'idx_radacct_username_stop');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->hasIndex('radcheck', 'idx_radcheck_username')) {
            Schema::table('radcheck', function (Blueprint $table) {
                $table->dropIndex('idx_radcheck_username');
            });
        }

        if ($this->hasIndex('radusergroup', 'idx_radusergroup_username')) {
            Schema::table('radusergroup', function (Blueprint $table) {
                $table->dropIndex('idx_radusergroup_username');
            });
        }
        if ($this->hasIndex('radusergroup', 'idx_radusergroup_groupname')) {
            Schema::table('radusergroup', function (Blueprint $table) {
                $table->dropIndex('idx_radusergroup_groupname');
            });
        }

        if ($this->hasIndex('radreply', 'idx_radreply_username')) {
            Schema::table('radreply', function (Blueprint $table) {
                $table->dropIndex('idx_radreply_username');
            });
        }

        if ($this->hasIndex('radgroupreply', 'idx_radgroupreply_groupname')) {
            Schema::table('radgroupreply', function (Blueprint $table) {
                $table->dropIndex('idx_radgroupreply_groupname');
            });
        }

        if ($this->hasIndex('radacct', 'idx_radacct_username_stop')) {
            Schema::table('radacct', function (Blueprint $table) {
                $table->dropIndex('idx_radacct_username_stop');
            });
        }
    }
};
