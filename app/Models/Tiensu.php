<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tiensu extends Model
{
    protected $table = 'tiensu';

    protected $fillable = [
        'user_id', 
        'blood_group', 
        'yeuto_rh', 
        'height', 
        'weight', 
        'bmi', 
        'food_allergies', 
        'drug_allergies', 
        'chronic_diseases', 
        'other_chronic_diseases'
    ];

    // Cấu hình để Laravel tự convert mảng <-> json
    protected $casts = [
        'chronic_diseases' => 'array',
    ];
}