<?php

use App\Actions\CreateShipment;
use App\Models\Shipment;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\ConsumedMessage;

it('creates a shipment record', function () {
    Kafka::fake();

    $payload = [
        'order_id' => 1,
        'total' => 500,
        'customer' => [
            'name' => 'Luiz',
            'country' => 'Brasil',
            'address' => 'Rio de Janeiro',
            'city' => 'Rio de Janeiro',
        ],
        'items' => [
            ['product_id' => 1, 'quantity' => 1]
        ]
    ];

    Kafka::shouldReceiveMessages([
        new ConsumedMessage(
            topicName: 'order.placed',
            partition: 0,
            headers: [],
            body: $payload,
            key: (string) $payload['order_id'],
            offset: 0,
            timestamp: 0
        ),
    ]);

    $consumer = Kafka::consumer(['order.placed'])
        ->withHandler(function (ConsumerMessage $message) {
            $data = $message->getBody();

            $action = app(CreateShipment::class);
            $action->handle($data);

            return 0;

        })->build();
        
    $consumer->consume();

    expect(Shipment::first()->order_id)->toBe(1);
    expect(Shipment::count())->toBe(1);
}); 
