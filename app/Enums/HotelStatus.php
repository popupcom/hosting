<?php

namespace App\Enums;

enum HotelStatus: string
{
    case Onboarding = 'onboarding';
    case Active = 'active';
    case Paused = 'paused';
    case Churned = 'churned';

    public function label(): string
    {
        return match ($this) {
            self::Onboarding => 'Onboarding',
            self::Active => 'Aktiv',
            self::Paused => 'Pausiert',
            self::Churned => 'Gekündigt',
        };
    }

    /** Booking flow is publicly reachable only while the tenant is live. */
    public function isBookable(): bool
    {
        return $this === self::Active;
    }
}
