<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeQuestion extends Model
{
    protected $fillable = ['teacher_id', 'challenge_lesson_id', 'challenge_solution_id', 'content',
        'answer', 'material', 'level', 'point', 'attempt', 'status', 'active'];

    public static function store($data)
    {
        return Static::create([
            'teacher_id' => $data['teacher_id'],
            'challenge_lesson_id' => $data['challenge_lesson_id'],
            'challenge_solution_id' => $data['challenge_solution_id'],
            'content' => $data['content'],
            'answer' => $data['answer'],
            'material' => $data['material'],
            'level' => $data['level'],
            'point' => $data['point'],
            'attempt' => $data['attempt']
        ]);
    }

    public function teacher()
    {
        return $this->belongsTo('App\Models\Teacher', 'teacher_id', 'id');
    }

    public function solution()
    {
        return $this->hasOne('App\Models\ChallengeSolution', 'id', 'challenge_solution_id');
    }
}