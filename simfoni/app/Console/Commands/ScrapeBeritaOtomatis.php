<?php

namespace App\Console\Commands;

use App\Models\BeritaOtomatis;
use App\Models\KeywordScraping;
use App\Models\LogScrapingHarian;
use App\Services\InferenceApiService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ScrapeBeritaOtomatis extends Command
{
    // zona waktu jakarta, bukan UTC
    public const ZONA_WAKTU = 'Asia/Jakarta';

    // catatan hari yang akan dicoba scraping
    public const HARI_SUSULAN = 7;

    protected $signature = 'otomatisasi:scrape';

    protected $description = 'Scraping berita kemarin dan hari yang terlewat via Media Cloud, klasifikasi & ringkas otomatis, simpan ke database';

    public function handle(InferenceApiService $inferenceApi): int
    {
        $daftarTanggal = $this->tanggalBelumBerhasil();

        if (empty($daftarTanggal)) {
            $this->info('Semua tanggal dalam ' . self::HARI_SUSULAN . ' hari terakhir sudah berhasil di-scrape.');
            return self::SUCCESS;
        }

        // query Media Cloud dari pengaturan keyword di dashboard
        $query = KeywordScraping::queryMediaCloud();

        $this->info('Tanggal scraping: ' . implode(', ', $daftarTanggal));
        $this->info("Query Media Cloud: {$query}");

        // cek FastAPI
        if (! $inferenceApi->isHealthy()) {
            $this->error('Inference API tidak bisa dihubungi. Pastikan uvicorn sedang berjalan.');
            return self::FAILURE;
        }

        // diproses dari paling lama
        $adaGagal = false;
        foreach ($daftarTanggal as $tanggal) {
            if (! $this->scrapeTanggal($inferenceApi, $tanggal, $query)) {
                $adaGagal = true;

                // ketika API tidak respons, hentikan scraping
                if (! $inferenceApi->isHealthy()) {
                    $this->error('Inference API berhenti merespons, scraping dihentikan.');
                    break;
                }
            }
        }

        return $adaGagal ? self::FAILURE : self::SUCCESS;
    }

    // tanggal belum scraping
    private function tanggalBelumBerhasil(): array
    {
        // kemarin + 6 hari sebelumnya
        $kemarin = Carbon::yesterday(self::ZONA_WAKTU);
        $awal = $kemarin->copy()->subDays(self::HARI_SUSULAN - 1);

        $sudahBerhasil = LogScrapingHarian::berhasil()
            ->whereBetween('tanggal', [$awal->toDateString(), $kemarin->toDateString()])
            ->pluck('tanggal')
            ->map(fn($tanggal) => $tanggal->toDateString())
            ->all();

        return collect(CarbonPeriod::create($awal, $kemarin))
            ->map(fn($tanggal) => $tanggal->toDateString())
            ->diff($sudahBerhasil)
            ->values()
            ->all();
    }

    // scraping satu tanggal
    private function scrapeTanggal(InferenceApiService $inferenceApi, string $tanggal, string $query): bool
    {
        $this->newLine();
        $this->info("Memulai scraping berita tanggal {$tanggal} (WIB)...");

        // daftar berita dari Media Cloud
        $daftarBerita = $inferenceApi->cariBerita($tanggal, $query);

        if ($daftarBerita === null) {
            LogScrapingHarian::catat($tanggal, 'gagal', [
                'pesan_error' => 'Gagal mengambil daftar berita dari Media Cloud. Cek log Laravel & FastAPI.',
            ]);
            $this->error("Gagal mengambil daftar berita untuk {$tanggal}. Cek log Laravel & log FastAPI untuk detail.");
            return false;
        }

        $total = count($daftarBerita);
        $this->info("Ditemukan: {$total} berita");

        // berita yang sudah selesai diproses di percobaan sebelumnya tidak diproses ulang (cek status URL)
        $sudahSelesai = BeritaOtomatis::whereIn('url', array_column($daftarBerita, 'url'))
            ->whereIn('status', ['summarized', 'classified'])
            ->pluck('url')
            ->flip();

        $baru = $diperbarui = $dilewati = $gagalEkstrak = $gagalApi = $gagalModel = $gagalSimpan = 0;

        // proses ekstrak konten, klasifikasi, SDS
        foreach ($daftarBerita as $i => $story) {
            $nomor = ($i + 1) . "/{$total}";

            if ($sudahSelesai->has($story['url'])) {
                $dilewati++;
                continue;
            }

            $hasil = $inferenceApi->prosesBerita($story);

            if ($hasil === null) {
                $gagalApi++;
                $this->warn("[{$nomor}] gagal diproses API: {$story['judul']}");

                if (! $inferenceApi->isHealthy()) {
                    break;
                }
                continue;
            }

            if ($hasil['status'] === 'gagal_ekstrak') {
                $gagalEkstrak++;
                $this->line("[{$nomor}] gagal ekstrak teks: {$story['judul']}");
                continue;
            }

            // updateOrCreate berdasarkan url
            $item = $hasil['berita'];
            $nilai = [
                'judul' => $item['judul'],
                'konten' => $item['konten'],
                'sumber' => $item['sumber'],
                'tanggal_berita' => $item['tanggal_berita'],
                'wilayah_label' => $item['wilayah_label'],
                'wilayah_confidence' => $item['wilayah_confidence'],
                'indikator_label' => $item['indikator_label'],
                'indikator_confidence' => $item['indikator_confidence'],
                'status' => $item['status'],
                'error_message' => $item['pesan_error'],
            ];

            // ringkasan SDS 3 model
            foreach (array_keys(BeritaOtomatis::MODEL_RINGKASAN) as $model) {
                $kolom = BeritaOtomatis::kolomRingkasan($model);
                if ($item[$kolom] !== null) {
                    $nilai[$kolom] = $item[$kolom];
                }
            }

            try {
                $berita = BeritaOtomatis::updateOrCreate(['url' => $item['url']], $nilai);
            } catch (QueryException $e) {
                $gagalSimpan++;
                Log::error('ScrapeBeritaOtomatis: gagal simpan berita', ['url' => $item['url'], 'error' => $e->getMessage()]);
                $this->warn("[{$nomor}] gagal disimpan ke database: {$item['judul']}");
                continue;
            }

            $berita->wasRecentlyCreated ? $baru++ : $diperbarui++;
            if ($item['status'] === 'failed') {
                $gagalModel++;
            }
            $this->line("[{$nomor}] {$item['status']}: {$item['judul']}"
                . ($item['pesan_error'] ? " ({$item['pesan_error']})" : ''));
        }

        $ringkasanHasil = [
            'total_ditemukan' => $total,
            'total_diproses' => $baru + $diperbarui,
            'total_gagal_ekstrak' => $gagalEkstrak,
            'berita_baru' => $baru,
            'berita_diperbarui' => $diperbarui,
        ];

        $this->info("Selesai {$tanggal}. Baru: {$baru} | Diperbarui: {$diperbarui} | Dilewati (sudah selesai sebelumnya): {$dilewati} | Gagal ekstrak: {$gagalEkstrak} | Gagal API: {$gagalApi} | Gagal model: {$gagalModel} | Gagal simpan: {$gagalSimpan}");

        // gagal karena API atau model akan dicoba lagi
        $masalah = array_filter([
            $gagalApi ? "{$gagalApi} berita gagal diproses API" : null,
            $gagalModel ? "{$gagalModel} berita gagal diklasifikasi/diringkas model" : null,
            $gagalSimpan ? "{$gagalSimpan} berita gagal disimpan ke database" : null,
        ]);

        if ($masalah) {
            LogScrapingHarian::catat($tanggal, 'gagal', $ringkasanHasil + [
                'pesan_error' => implode('; ', $masalah) . '. Akan dicoba lagi di jadwal berikutnya.',
            ]);
            return false;
        }

        LogScrapingHarian::catat($tanggal, 'berhasil', $ringkasanHasil);

        return true;
    }
}
