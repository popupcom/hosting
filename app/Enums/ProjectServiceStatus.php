<?php

namespace App\Enums;

enum ProjectServiceStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case PendingCancellation = 'pending_cancellation';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
}
