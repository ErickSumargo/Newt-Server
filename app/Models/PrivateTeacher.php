<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateTeacher extends Model
{
    protected $fillable = ['name', 'phone', 'photo', 'address', 'tuition', 'age', 'experience', 'education', 'active'];
    protected $hidden = ['remember_token'];

    public static function store($data)
    {
        return Static::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'photo' => '',
            'address' => $data['address'],
            'tuition' => $data['tuition'],
            'age' => $data['age'],
            'experience' => $data['experience'],
            'education' => $data['education'],
        ]);
    }

    public function private_teaching()
    {
        return $this->hasMany('App\Models\PrivateTeaching', 'private_teacher_id', 'id')
            ->where('private_teachings.active', 1);
    }
}