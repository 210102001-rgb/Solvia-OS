<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends Model
{
    protected $fillable = [
        'resource_id',
        'asset_tag',
        'brand',
        'model',
        'serial_number',
        'specs',
        'condition',
        'lifecycle_status',
        'purchase_cost',
        'upgrade_cost',
        'maintenance_cost',
        'repair_cost',
        'accumulated_cost',
        'current_holder_id',
    ];

    protected $casts = [
        'purchase_cost' => 'decimal:2',
        'upgrade_cost' => 'decimal:2',
        'maintenance_cost' => 'decimal:2',
        'repair_cost' => 'decimal:2',
        'accumulated_cost' => 'decimal:2',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function currentHolder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)->orderByDesc('assigned_date');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class)->orderByDesc('scheduled_date');
    }

    public function warranty(): HasOne
    {
        return $this->hasOne(Warranty::class);
    }

    public function recalculateCosts(): void
    {
        $this->maintenance_cost = $this->maintenances()->where('status', 'completed')->sum('cost');
        $this->accumulated_cost = $this->purchase_cost + $this->upgrade_cost + $this->maintenance_cost + $this->repair_cost;
        $this->save();

        if ($this->resource) {
            $this->resource->cost = $this->accumulated_cost;
            $this->resource->save();
        }
    }
}
