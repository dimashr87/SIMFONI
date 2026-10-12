<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaOtomatis extends Model
{
    use HasFactory;

    protected $table = 'berita_otomatis';

    protected $fillable = [
        'judul',
        'konten',
        'url',
        'sumber',
        'tanggal_berita',
        'wilayah_label',
        'wilayah_confidence',
        'indikator_label',
        'indikator_confidence',
        'ringkasan_indobart',
        'ringkasan_idt5',
        'ringkasan_gemini',
        'status',
        'error_message',
    ];

    protected $casts = [
        'tanggal_berita' => 'date',
        'wilayah_confidence' => 'float',
        'indikator_confidence' => 'float',
    ];

    // ubah label jadi indikator lengkap
    public const INDIKATOR_LABELS = [
        'INF'  => 'Inflasi',
        'EI'   => 'Ekspor Impor',
        'PE'   => 'Pertumbuhan Ekonomi',
        'PAR'  => 'Pariwisata',
        'LAIN' => 'Lainnya',
    ];

    // model peringkasan (kode dipakai FastAPI & nama kolom, label di dashboard)
    public const MODEL_RINGKASAN = [
        'indobart' => 'IndoBART',
        'idt5'     => 'idT5',
        'gemini'   => 'Gemini',
    ];

    // nama kolom ringkasan SDS untuk model tertentu, idT5 = ringkasan_idt5
    public static function kolomRingkasan(string $model): string
    {
        if (! array_key_exists($model, self::MODEL_RINGKASAN)) {
            throw new \InvalidArgumentException("Model ringkasan tidak dikenal: {$model}");
        }

        return "ringkasan_{$model}";
    }

    public function scopeUntukIndikator($query, string $kode)
    {
        return $query->where('indikator_label', $kode);
    }

    public function scopeRentangTanggal($query, string $dari, string $sampai)
    {
        return $query->whereBetween('tanggal_berita', [$dari, $sampai]);
    }

    // hanya berita yang sudah punya ringkasan dari model yang dipilih
    public function scopeSiapTampil($query, string $model)
    {
        return $query->whereNotNull(self::kolomRingkasan($model));
    }
}
