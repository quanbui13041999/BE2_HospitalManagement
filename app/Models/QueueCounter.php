<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueCounter extends Model
{
    protected $table = 'queue_counters';
    protected $fillable = [
        'schedule_id', 'current_ticket_id', 'last_called_number',
    ];

    public function schedule()
    {
        return $this->belongsTo(DoctorSchedule::class, 'schedule_id');
    }

    public function currentTicket()
    {
        return $this->belongsTo(QueueTicket::class, 'current_ticket_id');
    }
}
