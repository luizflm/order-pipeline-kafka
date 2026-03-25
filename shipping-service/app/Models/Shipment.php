<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property-read int $order_id
 * @property-read string $customer_name
 * @property-read string $shipping_address
 * @property-read string $city
 * @property-read string $country
 * @property-read ShipmentStatus $status
 * @property-read string|null $tracking_code
 * @property-read CarbonInterface $estimated_at
 * @property-read CarbonInterface $shipped_at
 * @property-read CarbonInterface $delivered_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
class Shipment extends Model
{
    /** @use HasFactory<\Database\Factories\ShipmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status'       => ShipmentStatus::class,
            'estimated_at' => 'datetime',
            'shipped_at'   => 'datetime',
            'delivered_at' => 'datetime',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }
}
