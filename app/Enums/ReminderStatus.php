<?php

namespace App\Enums;

enum ReminderStatus: string
{
    case Pending = 'pending';
    case Overdue = 'overdue';
    case Snoozed = 'snoozed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
