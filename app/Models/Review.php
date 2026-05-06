<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table      = 'reviews';
    protected $primaryKey = 'review_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'appointment_id','user_id','doctor_id',
        'rating','comment','doctor_reply',
    ];

    protected $casts = [
        'rating'     => 'integer',
        'created_at' => 'datetime',
    ];

    public function appointment() { return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id'); }
    public function user()        { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function doctor()      { return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id'); }
}

