<?php

namespace App\Actions;

use App\Events\ShipmentStatusUpdated;
use App\Models\Shipment;

class UpdateShipmentStatus
{
    public function handle(Shipment $shipment, array $data): void
    {
        $shipment->update($data);

        ShipmentStatusUpdated::dispatch($shipment->fresh());
    }
}