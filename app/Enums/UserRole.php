<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Mitarbeiter = 'mitarbeiter';
    case Buchhaltung = 'buchhaltung';
    case Technik = 'technik';
    case Support = 'support';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Mitarbeiter => 'Mitarbeiter:in',
            self::Buchhaltung => 'Buchhaltung',
            self::Technik => 'Technik',
            self::Support => 'Support',
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

    public static function labelFor(?string $role): string
    {
        if ($role === null || $role === '') {
            return '—';
        }

        return self::tryFrom($role)?->label() ?? ucfirst(str_replace('_', ' ', $role));
    }
}
