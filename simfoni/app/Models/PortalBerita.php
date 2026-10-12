<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalBerita extends Model
{
    use HasFactory;

    protected $table = 'portalberita';
    protected $primaryKey = ['id'];
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'id',
        'portalberita',
        'tim'
    ];
}
