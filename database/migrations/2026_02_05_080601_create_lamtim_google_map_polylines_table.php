<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lamtim_google_map_polylines', function (Blueprint $table) {
            $table->id();

            // Nama dan deskripsi polyline
            $table->string('nama')->nullable();
            $table->text('deskripsi')->nullable();

            // Tipe koneksi: odc_to_odp, odp_to_odp, odp_to_user, custom
            $table->string('tipe', 50)->default('custom');

            // Relasi ke ODC (source bisa ODC)
            $table->unsignedBigInteger('id_odc_from')->nullable();
            $table->unsignedBigInteger('id_odc_to')->nullable();

            // Relasi ke ODP (source/target bisa ODP)
            $table->unsignedBigInteger('id_odp_from')->nullable();
            $table->unsignedBigInteger('id_odp_to')->nullable();

            // Relasi ke User/Pelanggan
            $table->unsignedBigInteger('id_user')->nullable();

            // Styling
            $table->string('warna', 20)->default('#3388ff');
            $table->integer('ketebalan')->default(3);
            $table->boolean('animasi')->default(true);

            // Koordinat custom (untuk polyline custom yang tidak terhubung ke node)
            $table->json('koordinat')->nullable();

            // Status aktif
            $table->boolean('is_active')->default(true);

            // Siapa yang buat
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('tipe');
            $table->index('id_odc_from');
            $table->index('id_odc_to');
            $table->index('id_odp_from');
            $table->index('id_odp_to');
            $table->index('id_user');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lamtim_google_map_polylines');
    }
};
