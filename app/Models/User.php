<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'token_code',
        'verification',
        'otp_attempts',
        'last_failed_otp',
        'otp_sent_add',
        'otp_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function target()
    {
        return $this->hasOne(Target::class);
    }

    public function deposit()
    {
        return $this->hasMany(deposit::class);
    }

    public function budget()
    {
        return $this->hasOne(Budget::class);
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
