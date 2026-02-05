<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lamtim_google_map_markers', function (Blueprint $table) {
            $table->id();

            // Tipe marker: odc, odp, user, custom
            $table->string('tipe', 20);

            // Reference ID ke tabel asli (opsional, null untuk custom marker)
            $table->unsignedBigInteger('ref_id')->nullable();

            // Nama marker (untuk display)
            $table->string('nama');

            // Koordinat di peta (bisa berbeda dari data asli)
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Icon custom (opsional)
            $table->string('icon', 100)->nullable();

            // Warna marker
            $table->string('warna', 20)->default('#3388ff');

            // Data tambahan (JSON untuk info popup)
            $table->json('extra_data')->nullable();

            // Status
            $table->boolean('is_visible')->default(true);

            // Siapa yang buat/edit terakhir
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('tipe');
            $table->index(['tipe', 'ref_id']);
            $table->index('is_visible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lamtim_google_map_markers');
    }
};
