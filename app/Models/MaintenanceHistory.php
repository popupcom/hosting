<?php

namespace App\Models;

use App\Enums\MaintenanceType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MaintenanceHistory extends Model
{
    protected $table = 'maintenance_histories';

    #[Fillable([
        'project_id',
        'maintenance_type',
        'performed_by',
        'performed_on',
        'result',
        'has_errors',
        'notes',
        'managewp_reference',
    ])]
    protected static function booted(): void
    {
        static::saving(function (MaintenanceHistory $history): void {
            if ($history->managewp_reference === '') {
                $history->managewp_reference = null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'maintenance_type' => MaintenanceType::class,
            'performed_on' => 'date',
            'has_errors' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }

    public function scopeWithoutErrors(Builder $query): Builder
    {
        return $query->where('has_errors', false);
    }

    public function scopeWithErrors(Builder $query): Builder
    {
        return $query->where('has_errors', true);
    }
}
