<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = ['name', 'code', 'password', 'phone', 'photo', 'social_links', 'device', 'active', 'pro', 'firebase'];
    protected $hidden = ['password', 'firebase', 'remember_token'];

    public static function store($data)
    {
        return Static::create([
            'name' => $data['name'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'photo' => '',
            'social_links' => ''
        ]);
    }

    public function lessons()
    {
        return $this->belongsToMany('App\Models\Lesson', 'teachers_lessons', 'teacher_id', 'lesson_id')
            ->where('teachers_lessons.active', 1);
    }

    public function availables()
    {
        return $this->hasMany('App\Models\Available', 'teacher_id', 'id')
            ->where('availables.active', 1)
            ->orderBy('availables.start');
    }

    public function days_off()
    {
        return $this->hasMany('App\Models\DaysOff', 'teacher_id', 'id')
            ->where('days_off.active', 1)
            ->orderBy('days_off.day');
    }
}