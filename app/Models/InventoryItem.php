<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'sku',
        'item_name',
        'category',
        'unit',
        'current_stock',
        'minimum_stock',
        'location',
        'supplier',
        'unit_cost',
        'status',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'minimum_stock' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->orderByDesc('created_at');
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function updateStockStatus(): void
    {
        if ($this->current_stock <= 0) {
            $this->status = 'out_of_stock';
        } elseif ($this->current_stock <= $this->minimum_stock) {
            $this->status = 'low_stock';
        } else {
            $this->status = 'in_stock';
        }
        $this->save();
    }
}
