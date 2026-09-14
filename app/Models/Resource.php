<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Resource extends Model
{
    protected $fillable = [
        'resource_code',
        'name',
        'category',
        'owner_id',
        'responsible_user_id',
        'project_id',
        'team_id',
        'cost',
        'purchase_date',
        'status',
        'location',
        'relevant_date',
        'expiry_date',
        'document_path',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'relevant_date' => 'date',
        'expiry_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function asset(): HasOne
    {
        return $this->hasOne(Asset::class);
    }

    public function infrastructure(): HasOne
    {
        return $this->hasOne(Infrastructure::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function companyAccount(): HasOne
    {
        return $this->hasOne(CompanyAccount::class);
    }

    public function license(): HasOne
    {
        return $this->hasOne(License::class);
    }

    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
    }
}
