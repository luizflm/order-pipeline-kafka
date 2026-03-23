<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use Illuminate\Support\Facades\Log;

class PublishOrderToKafka
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        $message = [
            'data'  => [
                'order_id' => $order->id,
                'total' => $order->total,
                'customer' => [
                    'name' => $order->user->name,
                    'country' => $order->user->country,
                    'address' => $order->user->address,
                    'city' => $order->user->city,
                ],
                'items' => $order->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'quantity'        => $item->quantity
                ])
            ]
        ];

        Log::info("Order {$order->id} sent to Kafka topic.");
    }
}
