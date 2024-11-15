<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Kita akan menggunakan Sanctum untuk autentikasi API

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['username', 'password', 'role', 'restaurant_id'];

    protected $hidden = ['password', 'remember_token'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
