<?php

namespace App\Models;

use App\Enums\ReminderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'remindable_type',
    'remindable_id',
    'assigned_user_id',
    'reminder_at',
    'status',
    'message',
    'is_done',
])]
class Reminder extends Model
{
    protected function casts(): array
    {
        return [
            'reminder_at' => 'date',
            'status' => ReminderStatus::class,
            'is_done' => 'boolean',
        ];
    }

    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function scopeAssignedTo(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->where('assigned_user_id', $userId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ReminderStatus::Pending)->where('is_done', false);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_done', false);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->open()
            ->where(function (Builder $query): void {
                $query
                    ->where('status', ReminderStatus::Overdue)
                    ->orWhereDate('reminder_at', '<', now()->toDateString());
            });
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query
            ->open()
            ->whereDate('reminder_at', now()->toDateString());
    }

    public function scopeCritical(Builder $query): Builder
    {
        $today = now()->toDateString();
        $soon = now()->addDays(3)->toDateString();

        return $query->open()->where(function (Builder $query) use ($today, $soon): void {
            $query
                ->where('status', ReminderStatus::Overdue)
                ->orWhereDate('reminder_at', '<', $today)
                ->orWhere(function (Builder $query) use ($soon): void {
                    $query
                        ->where('status', ReminderStatus::Pending)
                        ->whereDate('reminder_at', '<=', $soon);
                });
        });
    }
}
