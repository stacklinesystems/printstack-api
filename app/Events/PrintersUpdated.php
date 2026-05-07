<?php

namespace App\Events;

use App\Models\Printer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrintersUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $printers;

    public function __construct()
    {
        $this->printers = Printer::all();
    }

    public function broadcastOn()
    {
        return new Channel('printers');
    }

    public function broadcastAs()
    {
        return 'printers.updated';
    }
}
