<?php

namespace App\Enums;

enum LicenseSharingModel: string
{
    case Shared = 'shared';
    case Dedicated = 'dedicated';
    case SeatBased = 'seat_based';
}
