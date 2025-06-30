<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    
    protected $table = 'transaksi';

    protected $fillable = [
        'user_id',
        'categories_id',
        'jenis',
        'descripsi',
        'jumlah',
        'date'
    ];

    public function user ()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsTO(Category::class, 'categories_id');
    }
    
}
