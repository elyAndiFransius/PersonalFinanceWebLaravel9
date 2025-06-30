<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 
        'jumlah', 
        'budget_id'
    ];

    public function Budget()
    {
        return $this->belongsTo(Budget::class);
    }
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'categories_id');
    }
}
