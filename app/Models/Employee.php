<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// this is important to make this the model looked at when using Auth::user
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $primaryKey = 'emp_id';

    protected $hidden = [
        'password',
    ];
}
