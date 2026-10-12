<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk berkomunikasi dengan FastAPI
 */
class InferenceApiService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.inference.url'), '/');
        $this->timeout = config('services.inference.timeout'); // per request (1 berita)
    }

    // gabungan ringkasan SDS menjadi ringkasan MDS
    public function summarizeMds(array $texts, string $model): ?string
    {
        if (empty($texts)) {
            return null;
        }

        return $this->kirim('/summarize/mds', ['texts' => $texts, 'model' => $model])['summary'] ?? null;
    }

    // scraping media cloud untuk daftar berita (judul, tanggal, sumber, url) pada 1 tanggal (YYYY-MM-DD)
    // $query: query Media Cloud dari KeywordScraping::queryMediaCloud()
    public function cariBerita(string $tanggal, string $query): ?array
    {
        return $this->kirim('/pipeline/cari-berita', ['tanggal' => $tanggal, 'query' => $query]);
    }

    // ekstrak berita, klasifikasi, ringkas SDS
    // $story: judul, tanggal, sumber, url
    public function prosesBerita(array $story): ?array
    {
        return $this->kirim('/pipeline/proses-berita', $story);
    }

    // cek respon FastAPI
    public function isHealthy(): bool
    {
        try {
            return Http::timeout(5)->get("{$this->baseUrl}/health")->successful();
        } catch (ConnectionException) {
            return false;
        }
    }

    // kirim POST ke FastAPI untuk endpoint tertentu
    private function kirim(string $endpoint, array $data): ?array
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . $endpoint, $data);

            if ($response->failed()) {
                Log::error("InferenceApiService: {$endpoint} gagal", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (ConnectionException $e) {
            Log::error("InferenceApiService: tidak bisa terhubung ke API ({$endpoint})", ['error' => $e->getMessage()]);
            return null;
        }
    }
}
