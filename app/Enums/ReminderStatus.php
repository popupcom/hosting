<?php

namespace App\Enums;

use App\Filament\Support\GermanLabels;

enum ReminderStatus: string
{
    case Pending = 'pending';
    case Overdue = 'overdue';
    case Snoozed = 'snoozed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return GermanLabels::todoStatus($this);
    }
}
