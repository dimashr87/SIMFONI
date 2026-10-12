<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cakupan extends Model
{
    use HasFactory;

    protected $table = 'cakupan';
    protected $primaryKey = ['id'];
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'id',
        'cakupan'
    ];
}
