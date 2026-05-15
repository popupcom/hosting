<?php

namespace App\Enums;

enum NotificationEventCategory: string
{
    case Billing = 'billing';
    case Technical = 'technical';
    case Support = 'support';
    case Management = 'management';
    case License = 'license';
    case Domain = 'domain';
    case Hosting = 'hosting';
    case Todo = 'todo';

    public function label(): string
    {
        return match ($this) {
            self::Billing => 'Abrechnung',
            self::Technical => 'Technik',
            self::Support => 'Support',
            self::Management => 'Management',
            self::License => 'Lizenzen',
            self::Domain => 'Domains',
            self::Hosting => 'Hosting',
            self::Todo => 'ToDos',
        };
    }

    public static function labels(): array
    {
        $labels = [];
        foreach (self::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }
}
