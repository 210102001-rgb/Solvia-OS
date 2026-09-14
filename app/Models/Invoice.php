<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'client_id',
        'project_id',
        'issue_date',
        'due_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'payment_status',
        'attachment_path',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function getPaidAmountAttribute(): float
    {
        // Sum from actual invoice_payments if available, or if marked paid without payments, fall back to total
        $sum = (float) $this->payments()->sum('amount');
        if ($sum > 0) {
            return $sum;
        }
        return $this->payment_status === 'paid' ? (float) $this->total : 0.0;
    }

    public function getOutstandingAmountAttribute(): float
    {
        if ($this->payment_status === 'paid') {
            return 0.0;
        }
        return max(0, (float) $this->total - $this->paid_amount);
    }

    public function getAgingDaysAttribute(): int
    {
        if (!$this->due_date) {
            return 0;
        }
        return max(0, now()->diffInDays($this->due_date, false) * -1);
    }
}
