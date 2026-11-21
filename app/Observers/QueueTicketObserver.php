<?php
namespace App\Observers;

use App\Models\QueueTicket;
use App\Events\QueueUpdated;

class QueueTicketObserver
{
    public function created(QueueTicket $queueTicket): void
    {
        broadcast(new QueueUpdated($queueTicket->schedule_id))->toOthers();
    }

    public function updated(QueueTicket $queueTicket): void
    {
        if ($queueTicket->wasChanged(['status', 'queue_number', 'priority_sort'])) {
            broadcast(new QueueUpdated($queueTicket->schedule_id))->toOthers();
        }
    }

    public function deleted(QueueTicket $queueTicket): void
    {
        broadcast(new QueueUpdated($queueTicket->schedule_id))->toOthers();
    }
}
