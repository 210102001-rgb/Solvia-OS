<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'resource_id',
        'provider',
        'plan_name',
        'cost',
        'billing_cycle',
        'next_billing_date',
        'expiry_date',
        'auto_renewal',
        'max_users',
        'status',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'next_billing_date' => 'date',
        'expiry_date' => 'date',
        'auto_renewal' => 'boolean',
        'max_users' => 'integer',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
