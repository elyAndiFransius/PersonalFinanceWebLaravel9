<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    protected $fillabel = [
        'target',
        'target_saldo',
        'Saldo',
        'tanggal_mulai',
        'tanggal_selesai',
        'file'
    ];

}
