<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'client_code',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'website',
        'notes',
        'status',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function getCompanyNameAttribute(): ?string
    {
        return $this->name;
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
