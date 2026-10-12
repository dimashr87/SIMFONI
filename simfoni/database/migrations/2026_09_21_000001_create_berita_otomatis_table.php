<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita_otomatis', function (Blueprint $table) {
            $table->id();

            $table->string('judul');
            $table->longText('konten');
            $table->string('url')->unique();
            $table->string('sumber')->nullable();        // nama portal berita
            $table->date('tanggal_berita');

            $table->string('wilayah_label', 20);          // DKI / NON_DKI
            $table->float('wilayah_confidence')->nullable();

            $table->string('indikator_label', 20)->nullable();  // EI / INF / PAR / PE / LAIN, null bila NON_DKI
            $table->float('indikator_confidence')->nullable();

            $table->text('ringkasan')->nullable();         // hasil SDS, null kalau belum diproses/gagal

            // status setiap berita
            $table->enum('status', ['pending', 'classified', 'summarized', 'failed'])
                ->default('pending');
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['wilayah_label', 'indikator_label', 'tanggal_berita'], 'idx_filter_otomatisasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita_otomatis');
    }
};
