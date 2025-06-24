<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gol',
        'targetAmount',
        'currentAmount',
        'startDate',
        'endDate',
        'file',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
