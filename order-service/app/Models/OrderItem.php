<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\CarbonInterface;


/**
 * @property-read int $id
 * @property-read int $order_id
 * @property-read int $product_id
 * @property-read int $quantity
 * @property-read float $unit_price
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity'   => 'integer',
            'unit_price' => 'float',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
