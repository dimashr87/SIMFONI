<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catatan hasil scraping per tanggal berita, untuk command otomatisasi:scrape
        Schema::create('log_scraping_harian', function (Blueprint $table) {
            $table->id();

            $table->date('tanggal')->unique();              // tanggal berita yang di-scrape (WIB)
            $table->enum('status', ['berhasil', 'gagal']);

            $table->unsignedInteger('total_ditemukan')->nullable();
            $table->unsignedInteger('total_diproses')->nullable();
            $table->unsignedInteger('total_gagal_ekstrak')->nullable();
            $table->unsignedInteger('berita_baru')->nullable();
            $table->unsignedInteger('berita_diperbarui')->nullable();

            $table->unsignedInteger('jumlah_percobaan')->default(1);
            $table->text('pesan_error')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_scraping_harian');
    }
};
