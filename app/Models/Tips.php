<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tips extends Model
{
    protected $table = 'tips';

    protected $fillable = ['content', 'active'];

    public static function store($data)
    {
        return Static::create([
            'content' => $data['content']
        ]);
    }
}