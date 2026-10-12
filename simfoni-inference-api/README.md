# SIMFONI Inference API

Folder ini menyatukan model IndoBERT (wilayah + indikator), IndoBART dan idT5 (ringkasan, lokal),
serta Gemini (ringkasan, lewat API) dengan REST API yang dipanggil Laravel. Setiap berita DKI
diringkas oleh ketiga model ringkasan.

## Endpoint yang tersedia

| Endpoint                       | Kegunaan                                                                                                                                                   |
| ------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `GET /health`                  | Cek API hidup, device (cpu/cuda), model ringkasan, dan apakah `GEMINI_API_KEY` sudah diisi                                                                 |
| `POST /classify/wilayah`       | Klasifikasi DKI / NON_DKI saja                                                                                                                             |
| `POST /classify/indikator`     | Klasifikasi indikator saja                                                                                                                                 |
| `POST /summarize/sds`          | Ringkas satu berita: `{"text": "...", "model": "indobart" \| "idt5" \| "gemini"}`                                                                          |
| `POST /summarize/mds`          | Gabungkan & ringkas beberapa ringkasan: `{"texts": [...], "model": ...}` (**dipakai dashboard Otomatisasi**)                                               |
| `POST /pipeline/cari-berita`   | **Dipakai scheduler harian (tahap 1)** — daftar berita Media Cloud untuk 1 tanggal: `{"tanggal": "YYYY-MM-DD"}`                                            |
| `POST /pipeline/proses-berita` | **Dipakai scheduler harian (tahap 2)** — 1 berita hasil tahap 1: ekstrak konten, klasifikasi wilayah, kalau DKI lanjut klasifikasi indikator + SDS 3 model |
| `POST /process/article`        | Tes manual 1 teks — klasifikasi wilayah, kalau DKI lanjut klasifikasi indikator + SDS 3 model                                                              |

Scheduler harian (`php artisan otomatisasi:scrape` di SIMFONI, jam 04:00 WIB) memanggil `cari-berita` sekali, lalu `proses-berita` untuk tiap berita dan langsung menyimpan hasilnya ke database.
