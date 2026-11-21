<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructionDailyCheck extends Model
{
    protected $table = 'instruction_daily_checks';
    protected $fillable = ['instruction_id', 'user_id', 'checked_date', 'is_done', 'checked_at'];

    protected $casts = [
        'checked_at' => 'datetime',
        'is_done' => 'boolean',
    ];

    public function instruction()
    {
        return $this->belongsTo(TreatmentHomeInstruction::class, 'instruction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
