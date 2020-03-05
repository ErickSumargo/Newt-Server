<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = ['phone', 'code', 'status'];

    public static function store($data)
    {
        return Static::create([
            'phone' => $data['phone'],
            'code' => $data['code']
        ]);
    }
}