<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateTeaching extends Model
{
    protected $table = 'private_teachings';

    protected $fillable = ['private_teacher_id', 'private_lesson_id', 'lesson', 'arrival', 'active'];

    public static function store($data)
    {
        return Static::create([
            'private_teacher_id' => $data['private_teacher_id'],
            'private_lesson_id' => $data['private_lesson_id'],
            'lesson' => $data['lesson'],
            'arrival' => $data['arrival']
        ]);
    }
}