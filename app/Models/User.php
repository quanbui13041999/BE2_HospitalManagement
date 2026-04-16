<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'user_id';
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'role_id',
        'avatar_url',
        'status',
    ];

    protected $hidden = [
        'password',
    ];
}