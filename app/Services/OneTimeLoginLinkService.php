<?php

namespace App\Services;

use App\Models\OneTimeLoginToken;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class OneTimeLoginLinkService
{
    public function createSignedUrlForUser(User $user): string
    {
        $minutes = (int) config('auth.one_time_login_expire_minutes', 20);

        OneTimeLoginToken::query()->where('user_id', $user->id)->delete();

        $plain = Str::random(64);

        OneTimeLoginToken::query()->create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => now()->addMinutes($minutes),
        ]);

        return URL::temporarySignedRoute(
            'filament.admin.auth.password-reset.reset',
            now()->addMinutes($minutes),
            ['token' => $plain],
        );
    }
}
