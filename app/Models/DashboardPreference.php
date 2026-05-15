<?php

namespace App\Models;

use App\Filament\Support\DashboardWidgetRegistry;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'visible_widget_keys',
    'widget_order',
    'filters',
    'annualized_view',
])]
class DashboardPreference extends Model
{
    protected function casts(): array
    {
        return [
            'visible_widget_keys' => 'array',
            'widget_order' => 'array',
            'filters' => 'array',
            'annualized_view' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function forUser(?User $user = null): self
    {
        $user ??= auth()->user();
        if ($user === null) {
            throw new \InvalidArgumentException('User required.');
        }

        return static::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'visible_widget_keys' => null,
                'widget_order' => null,
                'filters' => self::defaultFilters(),
                'annualized_view' => false,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultFilters(): array
    {
        return [
            'date_from' => null,
            'date_to' => null,
            'client_id' => null,
            'project_id' => null,
            'service_categories' => [],
            'line_types' => [],
            'billing_cadences' => [],
            'moco_sync_statuses' => [],
            'is_active_only' => true,
        ];
    }

    /**
     * @return array<int, class-string>
     */
    public function resolvedWidgetOrder(): array
    {
        $catalog = DashboardWidgetRegistry::widgetClasses();

        if ($this->widget_order !== null && $this->widget_order !== []) {
            $ordered = [];
            foreach ($this->widget_order as $class) {
                if (in_array($class, $catalog, true)) {
                    $ordered[] = $class;
                }
            }
            foreach ($catalog as $class) {
                if (! in_array($class, $ordered, true)) {
                    $ordered[] = $class;
                }
            }

            return $ordered;
        }

        return $catalog;
    }

    /**
     * @return array<int, class-string>
     */
    public function visibleWidgets(): array
    {
        $ordered = $this->resolvedWidgetOrder();

        if ($this->visible_widget_keys === null || $this->visible_widget_keys === []) {
            return $ordered;
        }

        return array_values(array_filter(
            $ordered,
            fn (string $class): bool => in_array($class, $this->visible_widget_keys, true),
        ));
    }
}
