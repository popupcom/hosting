<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'billing_group_id',
    'billable_type',
    'billable_id',
])]
class BillingGroupItem extends Model
{
    public function billingGroup(): BelongsTo
    {
        return $this->belongsTo(BillingGroup::class);
    }

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }
}
