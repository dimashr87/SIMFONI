<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kabkot extends Model
{
    use HasFactory;

    protected $table = 'kabkot';
    protected $primaryKey = ['id'];
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'id',
        'kode',
        'wilayah'
    ];
}
