<?php

namespace App\Models;

use App\Enums\HotelStatus;
use App\Enums\SubscriptionPlan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'client_id',
    'name',
    'slug',
    'status',
    'public_domain',
    'location',
    'support_phone',
    'support_email',
    'locale',
    'currency',
    'branding',
    'tracking',
    'legal',
    'pms_config',
    'payment_config',
    'city_tax_per_person_night',
    'insurance_rate',
    'tax_percent',
    'subscription_plan',
    'subscription_started_on',
    'trial_ends_on',
    'notes',
])]
class Hotel extends Model
{
    /** White-label brand tokens applied when a tenant leaves a value blank. */
    public const BRAND_DEFAULTS = [
        'green' => '#7A9A5A',
        'green_dark' => '#4D6B33',
        'green_darker' => '#344824',
        'green_bg' => '#f3f6ed',
        'ink' => '#1a1a1a',
        'accent' => '#c4a26a',
        'font_family' => 'Mulish',
        'logo_url' => null,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'status' => HotelStatus::class,
            'subscription_plan' => SubscriptionPlan::class,
            'branding' => 'array',
            'tracking' => 'array',
            'legal' => 'array',
            // Provider secrets are encrypted at rest and never serialised to
            // the browser; only the backend ever decrypts them.
            'pms_config' => 'encrypted:array',
            'payment_config' => 'encrypted:array',
            'city_tax_per_person_night' => 'decimal:2',
            'insurance_rate' => 'decimal:4',
            'subscription_started_on' => 'date',
            'trial_ends_on' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(BookingUnit::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(BookingRate::class);
    }

    public function extras(): HasMany
    {
        return $this->hasMany(BookingExtra::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(BookingEvent::class);
    }

    /** Brand tokens merged over the platform defaults. */
    public function brand(): array
    {
        return array_merge(self::BRAND_DEFAULTS, array_filter(
            $this->branding ?? [],
            static fn ($value) => $value !== null && $value !== '',
        ));
    }

    public function isBookable(): bool
    {
        return $this->status instanceof HotelStatus && $this->status->isBookable();
    }
}
