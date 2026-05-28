<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    protected $primaryKey = 'diagnosis_id';
    
    protected $fillable = [
        'record_id',
        'diagnosis_name',
        'icd_code',
        'diagnosis_type',
        'note'
    ];
}