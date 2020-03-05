<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $fillable = ['code', 'name', 'phone', 'password'];
    protected $hidden = ['password', 'remember_token'];
}