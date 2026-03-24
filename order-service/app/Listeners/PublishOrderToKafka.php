<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Kafka\Producers\KafkaProducer;
use Illuminate\Support\Facades\Log;
use Throwable;

class PublishOrderToKafka
{
    /**
     * Create the event listener.
     */
    public function __construct(private KafkaProducer $producer)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        try {
            $order = $event->order;

            $orderItems = $order->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity
            ])->toArray();

            $payload = [
                'order_id' => $order->id,
                'total' => $order->total,
                'customer' => [
                    'name' => $order->user->name,
                    'country' => $order->user->country,
                    'address' => $order->user->address,
                    'city' => $order->user->city,
                ],
                'items' => $orderItems
            ];

            $this->producer->send(
                topic: 'order.placed',
                payload: $payload,
                key: (string) $order->id
            );

            Log::info("Order {$order->id} sent to Kafka topic.");
        } catch (Throwable $e) {
            $message = $e->getMessage();
            Log::error("Error publishing order to kafka: {$message}");
        }
    }
}
