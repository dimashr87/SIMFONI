# SIMFONI — Modul Otomatisasi

Modul Otomatisasi SIMFONI mengambil berita harian secara otomatis dari Media Cloud, mengklasifikasikan
wilayah (DKI/NON_DKI) dan indikator (Inflasi, Ekspor Impor, Pertumbuhan Ekonomi, Pariwisata,
Lainnya), meringkas setiap berita DKI dengan **3 model** (IndoBART, idT5, Gemini) sebagai SDS, lalu
menampilkan ringkasan gabungan (MDS) per indikator, model, dan rentang tanggal di dashboard.

```
Windows Task Scheduler (tiap menit)
  └─► php artisan schedule:run
        └─► 04:00 WIB: php artisan otomatisasi:scrape
              ├─► FastAPI POST /pipeline/cari-berita   → daftar berita Media Cloud (1 tanggal)
              └─► FastAPI POST /pipeline/proses-berita → per berita: ekstrak teks, klasifikasi,
                    │                                     SDS IndoBART + idT5 + Gemini
                    └─► disimpan langsung ke tabel berita_otomatis
                          (ringkasan_indobart, ringkasan_idt5, ringkasan_gemini)

Dashboard /otomatisasi (pilih model) ──► FastAPI POST /summarize/mds (model yang sama)
                                          → ringkasan akhir per indikator
```

## File Modul Otomatisasi

Berikut 16 file yang dibuat untuk modul ini.

| No | File | Fungsi |
|---|---|---|
| | **Route** | |
| 1 | `routes/otomatisasi.php` | Daftar URL `/otomatisasi/...`: login, logout, dashboard, ringkasan, dan pengaturan keyword |
| | **Controller** | |
| 2 | `app/Http/Controllers/Otomatisasi/OtomatisasiLoginController.php` | Menampilkan halaman login, memproses login dan logout |
| 3 | `app/Http/Controllers/Otomatisasi/OtomatisasiController.php` | Menampilkan dashboard, membuat ringkasan MDS, menambah dan menghapus keyword |
| | **Command & Service** | |
| 4 | `app/Console/Commands/ScrapeBeritaOtomatis.php` | Perintah `otomatisasi:scrape`: scraping berita kemarin dan tanggal yang belum berhasil (maks. 7 hari), lalu menyimpan hasilnya |
| 5 | `app/Services/InferenceApiService.php` | Penghubung Laravel ke FastAPI (cek API, cari berita, proses berita, ringkasan MDS) |
| | **Model** | |
| 6 | `app/Models/BeritaOtomatis.php` | Tabel `berita_otomatis`, daftar indikator dan model ringkasan, serta filter untuk dashboard |
| 7 | `app/Models/LogScrapingHarian.php` | Tabel `log_scraping_harian`: mencatat berhasil atau gagalnya scraping per tanggal |
| 8 | `app/Models/KeywordScraping.php` | Tabel `keyword_scraping`, sekaligus penyusun query Media Cloud dari keyword dasar dan keyword tambahan |
| | **Migration** | |
| 9 | `database/migrations/2026_09_21_000001_create_berita_otomatis_table.php` | Membuat tabel `berita_otomatis` (versi awal, 1 kolom ringkasan) |
| 10 | `database/migrations/2026_09_27_000001_create_log_scraping_harian_table.php` | Membuat tabel `log_scraping_harian` |
| 11 | `database/migrations/2026_09_27_000002_alter_berita_otomatis_table.php` | Mengubah `berita_otomatis`: kolom ringkasan dipecah menjadi 3 (per model), `url` diperpanjang, `judul` menjadi TEXT, index disesuaikan dengan query dashboard |
| 12 | `database/migrations/2026_09_27_165135_create_keyword_scrapings_table.php` | Membuat tabel `keyword_scraping` |
| | **View & CSS** | |
| 13 | `resources/views/layouts/otomatisasi.blade.php` | Kerangka halaman otomatisasi (topbar, CSS, JS, token CSRF) |
| 14 | `resources/views/otomatisasi/login.blade.php` | Halaman login otomatisasi |
| 15 | `resources/views/otomatisasi/dashboard.blade.php` | Dashboard: filter indikator, model, dan tanggal; ringkasan akhir; tabel berita; pop-up pengaturan keyword |
| 16 | `public/assets/css/otomatisasi.css` | Gaya tampilan khusus halaman otomatisasi |
