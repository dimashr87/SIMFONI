<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE berita_otomatis MODIFY url VARCHAR(700) NOT NULL');
        // judul: TEXT tanpa batas
        DB::statement('ALTER TABLE berita_otomatis MODIFY judul TEXT NOT NULL');

        Schema::table('berita_otomatis', function (Blueprint $table) {
            // 1 berita DKI punya 3 ringkasan SDS, null bila NON_DKI / model gagal
            $table->dropColumn('ringkasan');
            $table->text('ringkasan_indobart')->nullable()->after('indikator_confidence');
            $table->text('ringkasan_idt5')->nullable()->after('ringkasan_indobart');
            $table->text('ringkasan_gemini')->nullable()->after('ringkasan_idt5');

            $table->dropIndex('idx_filter_otomatisasi');
            $table->index(['indikator_label', 'tanggal_berita'], 'idx_dashboard_otomatisasi');
        });
    }

    public function down(): void
    {
        Schema::table('berita_otomatis', function (Blueprint $table) {
            $table->dropIndex('idx_dashboard_otomatisasi');
            $table->index(['wilayah_label', 'indikator_label', 'tanggal_berita'], 'idx_filter_otomatisasi');

            // ringkasan ketiga model ikut terhapus
            $table->dropColumn(['ringkasan_indobart', 'ringkasan_idt5', 'ringkasan_gemini']);
            $table->text('ringkasan')->nullable()->after('indikator_confidence');
        });

        DB::statement('ALTER TABLE berita_otomatis MODIFY judul VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE berita_otomatis MODIFY url VARCHAR(255) NOT NULL');
    }
};
