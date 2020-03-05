<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['name'];
    protected $hidden = ['pivot'];

    public static function store($data)
    {
        return Static::create([
            'name' => $data['name']
        ]);
    }
}