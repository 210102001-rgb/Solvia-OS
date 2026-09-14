<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'request_number',
        'requester_id',
        'item_name',
        'category',
        'quantity',
        'estimated_cost',
        'actual_cost',
        'reason',
        'priority',
        'status',
        'approver_id',
        'approved_at',
        'rejection_reason',
        'created_asset_id',
        'created_inventory_id',
        'expense_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'created_asset_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'created_inventory_id');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }
}
