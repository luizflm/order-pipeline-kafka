<?php

namespace App\Http\Controllers;

use App\Actions\UpdateShipmentStatus;
use App\Http\Requests\UpdateShipmentStatusRequest;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ShipmentController extends Controller
{
    public function update(
        UpdateShipmentStatusRequest $request, 
        Shipment $shipment, 
        UpdateShipmentStatus $action
    ): JsonResponse
    {
        $action->handle($shipment, $request->validated());

        return response()->json([
            'data' => $shipment->fresh()->toArray()
        ], Response::HTTP_OK);
    }
}
