<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetPassword extends Model
{
    protected $table = 'reset_passwords';

    protected $fillable = ['user_code', 'phone', 'code', 'status'];

    public static function store($data)
    {
        return Static::create([
            'user_code' => $data['user_code'],
            'phone' => $data['phone'],
            'code' => $data['code']
        ]);
    }
}
