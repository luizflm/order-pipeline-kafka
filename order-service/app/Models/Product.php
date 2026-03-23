<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonInterface;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read int $stock
 * @property-read float $price
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'price' => 'float',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
