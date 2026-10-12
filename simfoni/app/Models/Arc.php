<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Arc extends Model
{
    protected $fillable = [
        'nama_kegiatan',
        'tanggal_kegiatan',
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'date',
    ];

    // Accessor status otomatis
    public function getStatusAttribute()
    {
        $today = Carbon::today();

        if ($this->tanggal_kegiatan->lt($today)) {
            return 'selesai'; // sudah lewat
        } elseif ($this->tanggal_kegiatan->eq($today)) {
            return 'berjalan'; // hari ini
        } else {
            return 'akan datang'; // tanggal di masa depan
        }
    }
}
