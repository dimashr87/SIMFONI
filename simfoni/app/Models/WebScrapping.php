<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebScrapping extends Model
{
    use HasFactory;
    protected $table = 'web_scrappings';
    public $timestamps = true;
    protected $fillable = [
        'nama_file',
        'path_file',
        'status',
    ];
}
