<?php

use App\Events\ShipmentStatusUpdated;
use App\Models\Shipment;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

it('sends the shipment status updated event to kafka', function () {
    Kafka::fake();

    $shipment = Shipment::factory()->processing()->create()->fresh();

    event(new ShipmentStatusUpdated($shipment));

    Kafka::assertPublishedOn('shipment.updated');

    Kafka::assertPublishedOn('shipment.updated', null, function (Message $message) use ($shipment) {
        $body = $message->getBody();

        return $message->getKey() == (string) $shipment->order_id &&
               $body['order_id'] == $shipment->order_id &&
               $body['status'] == $shipment->status->value &&
               $body['updated_at'] == $shipment->updated_at;
    });
});