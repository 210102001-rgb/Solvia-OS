<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class License extends Model
{
    protected $fillable = [
        'resource_id',
        'software_name',
        'license_key_encrypted',
        'user_id',
        'device_name',
        'purchase_date',
        'expiry_date',
        'cost',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'cost' => 'decimal:2',
        'license_key_encrypted' => 'encrypted',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
