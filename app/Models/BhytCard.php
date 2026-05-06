<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BhytCard extends Model
{
    use HasFactory;

    protected $table = 'bhyt_cards';
    protected $primaryKey = 'bhyt_card_id';
    public $timestamps = true;

    protected $fillable = [
        'patient_id',
        'card_number',
        'issue_date',
        'expiry_date',
        'coverage_rate',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'coverage_rate' => 'integer',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    // ĐÚNG theo BhytRepository.php -> với('patient')
    // ĐÚNG theo BhytService.php -> $card->patient_id
}