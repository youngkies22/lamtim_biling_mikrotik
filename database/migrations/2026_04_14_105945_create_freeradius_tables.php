<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabel NAS (Daftar MikroTik)
        if (!Schema::hasTable('nas')) {
            Schema::create('nas', function (Blueprint $table) {
                $table->id();
                $table->string('nasname', 128)->index();
                $table->string('shortname', 32)->nullable();
                $table->string('type', 30)->default('other');
                $table->integer('ports')->nullable();
                $table->string('secret', 60);
                $table->string('server', 64)->nullable();
                $table->string('community', 50)->nullable();
                $table->string('description', 200)->default('RADIUS Client');
            });
        }

        // 2. Tabel Radcheck (Username & Password User)
        if (!Schema::hasTable('radcheck')) {
            Schema::create('radcheck', function (Blueprint $table) {
                $table->id();
                $table->string('username', 64)->index();
                $table->string('attribute', 64)->default('Cleartext-Password');
                $table->string('op', 2)->default(':=');
                $table->string('value', 253);
            });
        }

        // 3. Tabel Radreply (Atribut User Individu)
        if (!Schema::hasTable('radreply')) {
            Schema::create('radreply', function (Blueprint $table) {
                $table->id();
                $table->string('username', 64)->index();
                $table->string('attribute', 64);
                $table->string('op', 2)->default('=');
                $table->string('value', 253);
            });
        }

        // 4. Tabel Radgroupreply (Atribut Paket/Grup)
        if (!Schema::hasTable('radgroupreply')) {
            Schema::create('radgroupreply', function (Blueprint $table) {
                $table->id();
                $table->string('groupname', 64)->index();
                $table->string('attribute', 64);
                $table->string('op', 2)->default('=');
                $table->string('value', 253);
            });
        }

        // 5. Tabel Radusergroup (Mapping User ke Paket)
        if (!Schema::hasTable('radusergroup')) {
            Schema::create('radusergroup', function (Blueprint $table) {
                $table->id();
                $table->string('username', 64)->index();
                $table->string('groupname', 64)->index();
                $table->integer('priority')->default(1);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('radusergroup');
        Schema::dropIfExists('radgroupreply');
        Schema::dropIfExists('radreply');
        Schema::dropIfExists('radcheck');
        Schema::dropIfExists('nas');
    }
};
