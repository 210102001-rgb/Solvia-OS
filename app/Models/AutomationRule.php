<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRule extends Model
{
    protected $fillable = [
        'name',
        'trigger_event',
        'condition_config',
        'action_config',
        'is_active',
        'last_triggered_at',
    ];

    protected $casts = [
        'condition_config' => 'array',
        'action_config' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(AutomationLog::class)->orderByDesc('created_at');
    }
}
