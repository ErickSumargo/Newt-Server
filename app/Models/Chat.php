<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['sender_code', 'receiver_code', 'content', 'content_type', 'lesson_id', 'sent'];

    public static function store($data)
    {
        return Static::create([
            'sender_code' => $data['sender_code'],
            'receiver_code' => $data['receiver_code'],
            'content' => $data['content'],
            'content_type' => $data['content_type'],
            'lesson_id' => $data['lesson_id'],
            'sent' => $data['sent']
        ]);
    }
}