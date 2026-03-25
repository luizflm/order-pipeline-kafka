<?php

namespace App\Listeners;

use App\Events\ShipmentStatusUpdated;
use App\Kafka\Producers\KafkaProducer;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShipmentStatusUpdatedListener
{
    public function __construct(private KafkaProducer $producer)
    {}

    public function handle(ShipmentStatusUpdated $event): void
    {
        try {
            $shipment = $event->shipment;

            $this->producer->send(
                topic: 'shipment.updated',
                payload: [
                    'order_id' => $shipment->order_id,
                    'status'   => $shipment->status->value,
                    'updated_at' => $shipment->updated_at,
                ],
                key: (string) $shipment->order_id
            );

            Log::info("Shipment #{$shipment->id} status updated event was published to kafka");
        } catch (Throwable $e) {
            Log::error("Error publishing shipment status updated event to kafka: " . $e->getMessage());
            throw $e;
        }
    }
}
