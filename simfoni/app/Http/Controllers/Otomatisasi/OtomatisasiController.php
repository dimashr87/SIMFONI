<?php

namespace App\Http\Controllers\Otomatisasi;

use App\Http\Controllers\Controller;
use App\Models\BeritaOtomatis;
use App\Models\KeywordScraping;
use App\Services\InferenceApiService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OtomatisasiController extends Controller
{
    protected InferenceApiService $inferenceApi;

    public function __construct(InferenceApiService $inferenceApi)
    {
        $this->inferenceApi = $inferenceApi;
    }

    public function index()
    {
        return view('otomatisasi.dashboard', [
            'indikatorList' => BeritaOtomatis::INDIKATOR_LABELS,
            'modelList' => BeritaOtomatis::MODEL_RINGKASAN,
        ]);
    }

    /**
     * Dipanggil via AJAX setiap kali klik "Tampilkan".
     * Mengembalikan daftar berita (SDS model terpilih) + ringkasan akhir gabungan (MDS model terpilih).
     */
    public function ringkasan(Request $request)
    {
        $validated = $request->validate([
            'indikator' => ['required', 'string', 'in:' . implode(',', array_keys(BeritaOtomatis::INDIKATOR_LABELS))],
            'model' => ['required', 'string', 'in:' . implode(',', array_keys(BeritaOtomatis::MODEL_RINGKASAN))],
            'tanggal_awal' => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_awal'],
        ], [
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
            'tanggal_awal.required' => 'Pilih tanggal awal terlebih dahulu.',
        ]);

        $model = $validated['model'];
        $kolomRingkasan = BeritaOtomatis::kolomRingkasan($model);

        $berita = BeritaOtomatis::query()
            ->siapTampil($model) // hanya berita dengan ringkasan model terpilih yang tersedia
            ->untukIndikator($validated['indikator'])
            ->rentangTanggal($validated['tanggal_awal'], $validated['tanggal_akhir'])
            ->orderByDesc('tanggal_berita')
            ->get(['id', 'judul', 'tanggal_berita', $kolomRingkasan, 'sumber', 'url', 'indikator_confidence']);

        if ($berita->isEmpty()) {
            return response()->json([
                'ringkasan_akhir' => null,
                'pesan' => 'Tidak ada berita dengan ringkasan ' . BeritaOtomatis::MODEL_RINGKASAN[$model]
                    . ' untuk indikator & rentang tanggal ini.',
                'berita' => [],
            ]);
        }

        // Ringkasan akhir (MDS): gabungkan ringkasan SDS model terpilih yang cocok filter, diringkas dengan model yang dipilih
        $ringkasanMds = $berita->sortByDesc('indikator_confidence')->pluck($kolomRingkasan)->values()->all();
        $ringkasanAkhir = $this->inferenceApi->summarizeMds($ringkasanMds, $model);

        return response()->json([
            'ringkasan_akhir' => $ringkasanAkhir
                ?? 'Ringkasan akhir gagal dibuat (API tidak merespons / model error). Ringkasan per berita tetap tersedia di bawah.',
            'berita' => $berita->map(fn($b) => [
                'judul' => $b->judul,
                'tanggal' => $b->tanggal_berita->format('d-m-Y'),
                'tanggal_iso' => $b->tanggal_berita->format('Y-m-d'), // untuk sorting di tabel
                'ringkasan' => $b->{$kolomRingkasan},
                'sumber' => $b->sumber,
                'url' => $b->url,
            ]),
        ]);
    }

    // daftar keyword tambahan
    public function daftarKeyword()
    {
        return response()->json($this->dataKeyword());
    }

    public function tambahKeyword(Request $request)
    {
        $request->validate([
            'keyword' => ['required', 'string', 'max:100'],
        ]);

        $keyword = KeywordScraping::bersihkan($request->input('keyword'));

        if ($keyword === '') {
            throw ValidationException::withMessages(['keyword' => 'Keyword hanya boleh berisi huruf, angka, dan spasi.']);
        }
        if (KeywordScraping::sudahAda($keyword)) {
            throw ValidationException::withMessages(['keyword' => "Keyword \"{$keyword}\" sudah ada di query."]);
        }

        KeywordScraping::create(['keyword' => $keyword]);

        return response()->json($this->dataKeyword());
    }

    public function hapusKeyword(KeywordScraping $keyword)
    {
        $keyword->delete();

        return response()->json($this->dataKeyword());
    }

    private function dataKeyword(): array
    {
        return [
            'dasar' => KeywordScraping::KEYWORD_DASAR,
            'wilayah' => KeywordScraping::WILAYAH,
            'keywords' => KeywordScraping::orderBy('id')->get(['id', 'keyword']),
            'query' => KeywordScraping::queryMediaCloud(),
        ];
    }
}
