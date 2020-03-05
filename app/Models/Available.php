<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Available extends Model
{
    protected $fillable = ['teacher_id', 'start', 'end'];

    public static function store($data)
    {
        return Static::create([
            'teacher_id' => $data['teacher_id'],
            'start' => $data['start'],
            'end' => $data['end']
        ]);
    }
}
