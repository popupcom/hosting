<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Draft = 'draft';
    case PendingPayment = 'pending_payment';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Entwurf',
            self::PendingPayment => 'Zahlung ausstehend',
            self::Confirmed => 'Bestätigt',
            self::Cancelled => 'Storniert',
            self::Expired => 'Abgelaufen',
        };
    }
}
