<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeResetRank extends Model
{
    protected $fillable = ['start', 'end'];

    public static function store($data)
    {
        return Static::create([
            'start' => $data['start'],
            'end' => $data['end']
        ]);
    }
}
