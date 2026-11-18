<?php

namespace App\Enums;

enum StockMovementType: string
{
    case OUT = 'out';
    case IN = 'in';
    case TRANSFER = 'transfer';
}
