<?php

namespace App\Enums;

enum ProjectDomainStatus: string
{
    case Active = 'active';
    case PendingTransfer = 'pending_transfer';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
