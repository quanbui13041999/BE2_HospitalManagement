<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $primaryKey = 'prescription_id';
    
    protected $fillable = [
        'record_id',
        'drug_name',
        'dosage',
        'instructions',
        'duration_days',
        'quantity',
        'unit'
    ];
}