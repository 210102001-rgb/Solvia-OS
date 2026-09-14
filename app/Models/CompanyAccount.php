<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyAccount extends Model
{
    protected $fillable = [
        'resource_id',
        'platform',
        'account_identifier',
        'encrypted_credentials',
        'two_factor_status',
        'recovery_method',
        'status',
    ];

    protected $casts = [
        'two_factor_status' => 'boolean',
        'encrypted_credentials' => 'encrypted',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
