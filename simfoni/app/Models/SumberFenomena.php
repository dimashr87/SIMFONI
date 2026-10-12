<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SumberFenomena extends Model
{
    use HasFactory;

    protected $table = 'sumberfenomena';

    protected $fillable = ['nama_sumber'];
}
