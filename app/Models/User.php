<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function roleLabel(): string
    {
        return UserRole::labelFor($this->role);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin || $this->role === UserRole::Admin->value;
    }

    public function isActiveAccount(): bool
    {
        return (bool) $this->is_active;
    }

    public function dashboardPreference(): HasOne
    {
        return $this->hasOne(DashboardPreference::class);
    }

    public function notificationGroups(): BelongsToMany
    {
        return $this->belongsToMany(NotificationGroup::class, 'notification_group_user')
            ->withTimestamps();
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(ChangeLog::class);
    }

    public function assignedReminders(): HasMany
    {
        return $this->hasMany(Reminder::class, 'assigned_user_id');
    }
}
