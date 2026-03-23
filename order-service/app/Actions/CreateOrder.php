<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Events\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CreateOrder
{
    public function handle(array $data): Order
    {
        $order = DB::transaction(function () use ($data) {
            $productsId = array_column($data['items'], 'product_id');
            $productsPrice = Product::whereIn('id', $productsId)->pluck('price', 'id');

            $orderItems = [];
            $total = 0;

            foreach ($data['items'] as $item) {
                $productUnitPrice = $productsPrice->get($item['product_id']);
                $subtotal = $productUnitPrice * $item['quantity'];

                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $productUnitPrice,
                ];

                $total += $subtotal;
            }

            $order = Order::create([
                'user_id' => $data['user_id'],
                'status'  => OrderStatus::PENDING,
                'total'   => $total
            ]);

            $order->items()->createMany($orderItems);

            return $order;
        });

        OrderPlaced::dispatch($order->load('items', 'user'));

        return $order;
    }
}