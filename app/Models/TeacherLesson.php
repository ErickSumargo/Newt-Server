<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherLesson extends Model
{
    protected $table = 'teachers_lessons';

    protected $fillable = ['teacher_id', 'lesson_id', 'active'];

    public static function store($data)
    {
        return Static::create([
            'teacher_id' => $data['teacher_id'],
            'lesson_id' => $data['lesson_id']
        ]);
    }
}