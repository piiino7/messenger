<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageEdited implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var App\Models\Message
     */

    public $message;

    /**
     * Create a new event instance.
     * @param App\Models\Message $message
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $other_user = $this->message->conversation->participants()
            ->where('user_id', '<>', $this->message->user_id)
            ->first();

        return [
            new PresenceChannel('EditedMessages.' . $other_user->id),
        ];
    }

    public function broadcastAs()
    {
        return 'edited-message';
    }
}
