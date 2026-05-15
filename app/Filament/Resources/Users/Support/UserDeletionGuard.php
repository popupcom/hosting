<?php

namespace App\Filament\Resources\Users\Support;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class UserDeletionGuard
{
    public static function deletionBlockedReason(User $user): ?string
    {
        if (Auth::id() === $user->getKey()) {
            return 'Der eigene Benutzer-Account kann nicht gelöscht werden.';
        }

        if (self::isLastAdmin($user)) {
            return 'Die letzte Admin-Benutzer:in kann nicht gelöscht werden.';
        }

        return null;
    }

    public static function isLastAdmin(User $user): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        return ! User::query()
            ->whereKeyNot($user->getKey())
            ->where(function ($query): void {
                $query
                    ->where('is_admin', true)
                    ->orWhere('role', UserRole::Admin->value);
            })
            ->exists();
    }
}
