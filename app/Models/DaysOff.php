<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaysOff extends Model
{
    protected $table = 'days_off';

    protected $fillable = ['teacher_id', 'day', 'active'];

    public static function store($data)
    {
        return Static::create([
            'teacher_id' => $data['teacher_id'],
            'day' => $data['day']
        ]);
    }
}