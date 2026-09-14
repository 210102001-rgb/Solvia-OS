<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialAccount extends Model
{
    protected $fillable = [
        'account_code',
        'account_name',
        'account_type', // BANK, E_WALLET, CASH, PAYMENT_GATEWAY, OTHER
        'provider',
        'owner',
        'type',
        'account_number',
        'bank_name',
        'opening_balance',
        'balance',
        'currency',
        'description',
        'is_active',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'financial_account_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class, 'financial_account_id');
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class, 'account_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'account_id');
    }
}
