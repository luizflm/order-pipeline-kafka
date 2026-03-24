<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case PENDING    = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED    = 'shipped';
    case DELIVERED  = 'delivered';
    case FAILED     = 'failed';
}
