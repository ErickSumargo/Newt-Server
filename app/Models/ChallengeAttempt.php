<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeAttempt extends Model
{
    protected $fillable = ['challenger_id', 'challenge_question_id', 'attempt', 'status', 'active'];

    public static function store($data)
    {
        return Static::create([
            'challenger_id' => $data['challenger_id'],
            'challenge_question_id' => $data['challenge_question_id'],
            'attempt' => $data['attempt'],
            'status' => $data['status']
        ]);
    }

    public function challenger()
    {
        return $this->belongsTo('App\Models\Challenger', 'challenger_id', 'id');
    }

    public function question()
    {
        return $this->belongsTo('App\Models\ChallengeQuestion', 'challenge_question_id', 'id');
    }
}