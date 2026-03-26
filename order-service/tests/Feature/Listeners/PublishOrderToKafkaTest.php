<?php

use App\Events\OrderPlaced;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

beforeEach(function () {
    Kafka::fake();
});

it('publishes the correct order payload structure to kafka', function () {
    $user = User::factory()->create([
        'name' => 'Luiz Felipe',
        'country' => 'Brasil',
    ])->fresh();

    $product = Product::factory()->create(['price' => 500])->fresh();

    $order = Order::factory()->for($user)->create([
        'total' => 1000
    ])->fresh();

    $order->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => $product->price
    ]);

    event(new OrderPlaced($order->load(['user', 'items'])));

    Kafka::assertPublishedOn('order.placed');

    Kafka::assertPublishedOn('order.placed', null, function (Message $message) use ($order) {
        $body = $message->getBody();

        return $message->getKey() == (string) $order->id &&
               $body['order_id'] == $order->id &&
               $body['total'] == 1000 &&
               $body['customer']['name'] == 'Luiz Felipe' &&
               $body['customer']['country'] == 'Brasil' &&
               $body['items'][0]['product_id'] == 1 &&
               $body['items'][0]['quantity'] == 2;
    });
});

it('doesnt publish anything to kafka in case of failure', function () {
    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->create();

    $order->setRelation('user', null);

    event(new OrderPlaced($order));

    Kafka::assertNothingPublished();
});