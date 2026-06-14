<?php

namespace App\Enums;

/**
 * Monthly SaaS plans the white-label booking engine is sold under.
 * Pricing is indicative and lives here so the admin UI and billing
 * exports share a single source of truth.
 */
enum SubscriptionPlan: string
{
    case Starter = 'starter';
    case Growth = 'growth';
    case Pro = 'pro';

    public function label(): string
    {
        return match ($this) {
            self::Starter => 'Starter',
            self::Growth => 'Growth',
            self::Pro => 'Pro',
        };
    }

    /** Indicative monthly price in EUR. */
    public function monthlyPrice(): int
    {
        return match ($this) {
            self::Starter => 49,
            self::Growth => 99,
            self::Pro => 199,
        };
    }
}
