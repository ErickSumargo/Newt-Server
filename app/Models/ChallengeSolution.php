<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeSolution extends Model
{
    protected $fillable = ['content'];

    public static function store($data)
    {
        return Static::create([
            'content' => $data['content']
        ]);
    }
}
