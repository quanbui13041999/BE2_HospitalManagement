<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentConfirmation extends Model
{
    protected $table = 'treatment_confirmations';
    protected $fillable = ['reminder_id', 'user_id', 'confirmed_at', 'confirm_type', 'note'];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function reminder()
    {
        return $this->belongsTo(TreatmentReminder::class, 'reminder_id', 'reminder_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
