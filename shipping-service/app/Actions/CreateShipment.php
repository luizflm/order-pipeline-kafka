<?php

namespace App\Actions;

use App\Models\Shipment;
use App\Enums\ShipmentStatus;

class CreateShipment
{
    public function handle(array $data): Shipment
    {
        return Shipment::create([
            'order_id' => $data['order_id'],
            'customer_name' => $data['customer']['name'],
            'shipping_address' => $data['customer']['address'],
            'city' => $data['customer']['city'],
            'country' => $data['customer']['country'],
            'status' => ShipmentStatus::PENDING,
            'tracking_code' => 'TRK-' . strtoupper(str()->random(10)),
            'estimated_at' => now()->addDays(5),
        ]);
    }
}