<?php

declare(strict_types=1);

namespace App\Modules\Credits\Enums;

enum TransactionType: string
{
    case GRANT = 'grant';
    case CONSUMPTION = 'consumption';
    case RESERVATION = 'reservation';
    case RELEASE = 'release';
    case REFUND = 'refund';
    case EXPIRY = 'expiry';
    case PURCHASE = 'purchase';
}
