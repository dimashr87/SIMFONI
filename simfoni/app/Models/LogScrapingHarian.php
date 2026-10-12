<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogScrapingHarian extends Model
{
    protected $table = 'log_scraping_harian';

    protected $fillable = [
        'tanggal',
        'status',
        'total_ditemukan',
        'total_diproses',
        'total_gagal_ekstrak',
        'berita_baru',
        'berita_diperbarui',
        'jumlah_percobaan',
        'pesan_error',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function scopeBerhasil($query)
    {
        return $query->where('status', 'berhasil');
    }

    // catat log scraping harian
    public static function catat(string $tanggal, string $status, array $data = []): self
    {
        $log = static::firstOrNew(['tanggal' => $tanggal]);
        $log->jumlah_percobaan = $log->exists ? $log->jumlah_percobaan + 1 : 1;
        // pesan_error dikosongkan dulu, diperbarui bila ada kesalahan
        $log->fill(array_merge(['pesan_error' => null], $data, ['status' => $status]))->save();

        return $log;
    }
}
