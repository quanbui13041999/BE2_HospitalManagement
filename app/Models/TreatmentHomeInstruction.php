<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentHomeInstruction extends Model
{
    protected $table = 'treatment_home_instructions';
    protected $fillable = ['record_id', 'user_id', 'instruction_text', 'detail', 'icon', 'sort_order', 'is_active'];

    public function dailyCheck(?string $date = null)
    {
        $date ??= today()->toDateString();
        return $this->hasOne(InstructionDailyCheck::class, 'instruction_id')
                    ->where('user_id', $this->user_id)
                    ->whereDate('checked_date', $date);
    }

    public function isCheckedToday(): bool
    {
        return $this->dailyCheck()->where('is_done', 1)->exists();
    }
}
