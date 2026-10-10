<?php

namespace App\Events;

use App\Models\SupportMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportMessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SupportMessage $message,
        public int $customerId,
        public string $customerName,
        public string $url,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('support-conversation.'.$this->message->support_conversation_id),
        ];

        $channels[] = $this->message->sender_role === 'admin'
            ? new PrivateChannel('App.Models.User.'.$this->customerId)
            : new PrivateChannel('admins');

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'support.message.created';
    }

    /**
     * @return array{id: int, conversation_id: int, sender_id: int|null, sender_role: string, body: string, time: string, customer_name: string, url: string}
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->support_conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_role' => $this->message->sender_role,
            'body' => $this->message->body,
            'time' => $this->message->created_at->format('H:i d/m/Y'),
            'customer_name' => $this->customerName,
            'url' => $this->url,
        ];
    }
}
