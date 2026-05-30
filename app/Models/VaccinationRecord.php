<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaccinationRecord extends Model
{
    protected $table      = 'vaccinationrecords';
    protected $primaryKey = 'vaccination_id';
    public $timestamps    = false;

    protected $fillable = [
        'user_id','vaccine_id','doctor_id','dose_number',
        'administered_at','batch_number','next_dose_date','status','notes',
    ];

    protected $casts = [
        'administered_at' => 'datetime',
        'next_dose_date'  => 'date',
        'dose_number'     => 'integer',
    ];

    public function user()    { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function vaccine() { return $this->belongsTo(Vaccine::class, 'vaccine_id', 'vaccine_id'); }
    public function doctor()  { return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id'); }
}
