<?php

namespace App\Http\Controllers;

use App\Actions\CreateOrder;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, CreateOrder $action): OrderResource
    {
        $order = $action->handle($request->validated());

        return new OrderResource($order);
    }
}
