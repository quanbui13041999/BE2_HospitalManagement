<?php
namespace App\Events;

use App\Models\QueueTicket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TicketCalled implements ShouldBroadcast
{
    public QueueTicket $ticket;

    public function __construct(QueueTicket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function broadcastOn(): Channel
    {
        return new Channel("queue.{$this->ticket->schedule_id}");
    }

    public function broadcastAs(): string 
    { 
        return 'ticket.called'; 
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id'    => $this->ticket->ticket_id,
            'queue_number' => $this->ticket->queue_number,
            'patient_name' => $this->ticket->patient_name,
            'priority'     => $this->ticket->priority,
            'priority_icon'=> $this->ticket->priority_icon,
            'priority_label'=> $this->ticket->priority_label,
        ];
    }
}
