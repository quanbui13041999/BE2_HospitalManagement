<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordAllergy extends Model
{
    protected $table = 'record_allergies';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'record_id',
        'allergen',
        'severity',
        'reaction'
    ];
}