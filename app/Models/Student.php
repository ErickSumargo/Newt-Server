<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Student extends Model
{
    protected $fillable = ['name', 'code', 'password', 'phone', 'school', 'photo', 'device', 'subscription', 'active', 'pro', 'firebase'];
    protected $hidden = ['password', 'firebase', 'remember_token'];

    public static function store($data)
    {
        return Static::create([
            'name' => $data['name'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'school' => $data['school'],
            'photo' => '',
            'device' => $data['device'],
            'subscription' => $data['subscription'],
            'firebase' => $data['firebase']
        ]);
    }
}