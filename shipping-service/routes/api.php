<?php

use App\Http\Controllers\ShipmentController;
use Illuminate\Support\Facades\Route;

Route::patch('/shipments/{shipment}', [ShipmentController::class, 'update']);