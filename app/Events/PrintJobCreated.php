<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrintJobCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $job;
    public $host;

    public function __construct($job, $host)
    {
        $this->job = $job;
        $this->host = $host;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('print.' . $this->host);
    }

    public function broadcastAs()
    {
        return 'print.job.created';
    }
}
