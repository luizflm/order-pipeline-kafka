<?php

namespace App\Actions;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatus
{
    /**
     * Handle the order status update from Kafka data.
     */
    public function handle(array $data): void
    {
        $orderId = $data['order_id'];
        $status = $data['status'];

        $order = Order::find($orderId);

        if (!$order) {
            Log::warning("Order #{$orderId} not found during status sync.");
            return;
        }

        $statusEnum = OrderStatus::tryFrom($status);

        if (!$statusEnum) {
            Log::error("Invalid status '{$status}' received for Order #{$orderId}");
            return;
        }

        $order->update(['status' => $statusEnum]);

        Log::info("Order #{$orderId} status successfully synchronized to: {$status}");
    }
}