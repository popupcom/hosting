<?php

namespace App\Enums;

enum LicenseAssignmentStatus: string
{
    case Active = 'active';
    case PendingCancellation = 'pending_cancellation';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
}
