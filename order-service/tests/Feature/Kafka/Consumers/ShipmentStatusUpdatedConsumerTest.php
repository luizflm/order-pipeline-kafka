<?php

use App\Actions\UpdateOrderStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\ConsumedMessage;

it('updates the order status', function () {
    Kafka::fake();

    $order = Order::factory()->create()->fresh();

    $payload = [
        'order_id' => $order->id,
        'status'   => 'processing',
        'updated_at' => now(),
    ];

    Kafka::shouldReceiveMessages([
        new ConsumedMessage(
            topicName: 'shipment.updated',
            partition: 0,
            headers: [],
            body: $payload,
            key: (string) $payload['order_id'],
            offset: 0,
            timestamp: 0
        ),
    ]);

    $consumer = Kafka::consumer(['shipment.updated'])
        ->withHandler(function (ConsumerMessage $message) {
            $data = $message->getBody();

            $action = app(UpdateOrderStatus::class);
            $action->handle($data);

            return 0;

        })->build();
        
    $consumer->consume();

    expect($order->refresh()->status)->toBe(OrderStatus::PROCESSING);
}); 
