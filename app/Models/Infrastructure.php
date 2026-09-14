<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Infrastructure extends Model
{
    protected $fillable = [
        'resource_id',
        'type',
        'provider',
        'name',
        'hostname',
        'ip_address',
        'os',
        'specs',
        'location',
        'environment',
        'purpose',
        'encrypted_credentials',
        'billing_account',
        'monthly_cost',
        'yearly_cost',
        'start_date',
        'next_billing_date',
        'expiry_date',
        'auto_renewal',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_billing_date' => 'date',
        'expiry_date' => 'date',
        'monthly_cost' => 'decimal:2',
        'yearly_cost' => 'decimal:2',
        'auto_renewal' => 'boolean',
        'encrypted_credentials' => 'encrypted',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
