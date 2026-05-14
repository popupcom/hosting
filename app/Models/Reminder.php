<?php

namespace App\Models;

use App\Enums\ReminderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reminder extends Model
{
    #[Fillable([
        'remindable_type',
        'remindable_id',
        'reminder_at',
        'status',
        'message',
        'is_done',
    ])]
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

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ReminderStatus::Pending)->where('is_done', false);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_done', false);
    }
}
