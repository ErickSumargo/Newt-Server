<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['student_id', 'package_id'];

    public static function store($data)
    {
        return Static::create([
            'student_id' => $data['student_id'],
            'package_id' => $data['package_id']
        ]);
    }
}