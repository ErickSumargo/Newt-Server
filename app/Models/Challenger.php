<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenger extends Model
{
    protected $fillable = ['student_id', 'active'];

    public static function store($data)
    {
        return Static::create([
            'student_id' => $data['student_id']
        ]);
    }

    public function student()
    {
        return $this->belongsTo('App\Models\Student', 'student_id', 'id');
    }

    public function attempts()
    {
        return $this->hasMany('App\Models\ChallengeAttempt', 'challenger_id', 'id');
    }
}
