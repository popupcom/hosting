<?php

namespace App\Enums;

/**
 * How an extra's unit price is multiplied out in the price calculator.
 * Mirrors the pricing rules from the original booking prototype.
 */
enum ExtraPricingUnit: string
{
    case PerPersonPerDay = 'per_person_per_day';
    case PerPersonPerEvening = 'per_person_per_evening';
    case PerPerson = 'per_person';
    case PerNight = 'per_night';
    case Once = 'once';

    public function label(): string
    {
        return match ($this) {
            self::PerPersonPerDay => 'pro Person/Tag',
            self::PerPersonPerEvening => 'pro Person/Abend',
            self::PerPerson => 'pro Person',
            self::PerNight => 'pro Nacht',
            self::Once => 'einmalig',
        };
    }
}
