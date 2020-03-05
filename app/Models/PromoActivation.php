<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoActivation extends Model
{
    protected $table = 'promo_activations';

    protected $fillable = ['student_id', 'promo_code_id'];

    public static function store($data)
    {
        return Static::create([
            'student_id' => $data['student_id'],
            'promo_code_id' => $data['promo_code_id']
        ]);
    }
}
