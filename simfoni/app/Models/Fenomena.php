<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fenomena extends Model
{
    use HasFactory;

    protected $table = 'fenomena';
    protected $primaryKey = 'id'; // Hanya gunakan 'id' sebagai primary key
    public $incrementing = true; // Sesuaikan dengan apakah 'id' adalah auto-increment
    public $timestamps = true; // Biarkan true jika menggunakan timestamps

    protected $fillable = [
        'id',
        'niplama',
        'tanggal_awal',
        'tanggal_akhir',
        'sumber',
        'sumberportal',
        'cakupan',
        'cakupankabkot',
        'subjek',
        'tema',
        'resume',
        'arah',
        'periodicity',
        'linkbukti',
        'tanggalcreated',
        'supervisor'
    ];
    
    
}
