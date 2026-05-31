<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class QueueUpdated implements ShouldBroadcast
{
    public int $scheduleId;

    public function __construct(int $scheduleId)
    {
        $this->scheduleId = $scheduleId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel("queue.{$this->scheduleId}");
    }

    public function broadcastAs(): string 
    { 
        return 'queue.updated'; 
    }

    public function broadcastWith(): array
    {
        return [
            'schedule_id' => $this->scheduleId, 
            'timestamp'   => now()->toISOString()
        ];
    }
}
