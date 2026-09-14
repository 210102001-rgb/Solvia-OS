<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $fillable = [
        'resource_id',
        'contract_number',
        'party_name',
        'party_type',
        'start_date',
        'expiry_date',
        'contract_value',
        'document_path',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
        'contract_value' => 'decimal:2',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
