<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class NewOrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public string $orderType,
        public string $orderNumber,
        public string $customerName,
        public int $totalAmount,
        public string $url,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admins'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.created';
    }

    /**
     * @return array{type: string, number: string, customer_name: string, total_amount: int, url: string}
     */
    public function broadcastWith(): array
    {
        return [
            'type' => $this->orderType,
            'number' => $this->orderNumber,
            'customer_name' => $this->customerName,
            'total_amount' => $this->totalAmount,
            'url' => $this->url,
        ];
    }
}
