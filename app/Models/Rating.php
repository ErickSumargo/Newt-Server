<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = ['student_id', 'teacher_id', 'rating', 'lesson_id'];

    public static function store($data)
    {
        return Static::create([
            'student_id' => $data['student_id'],
            'teacher_id' => $data['teacher_id'],
            'rating' => $data['rating'],
            'lesson_id' => $data['lesson_id']
        ]);
    }
}