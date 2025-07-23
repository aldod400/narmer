<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case ONDELIVERY = 'on_delivery';
    case DELIVERED = 'delivered';
    case CANCELED = 'canceled';
}
