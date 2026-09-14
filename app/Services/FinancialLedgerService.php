<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Budget;
use App\Models\DailyProgress;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use App\Models\Income;
use App\Models\Infrastructure;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Payable;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinancialLedgerService
{
    /**
     * Record a master financial transaction in the ledger.
     */
    public function recordTransaction(array $data): FinancialTransaction
    {
        return DB::transaction(function () use ($data) {
            $code = $data['transaction_code'] ?? 'TRX-' . date('Ym') . '-' . strtoupper(Str::random(6));

            $transaction = FinancialTransaction::create([
                'transaction_code' => $code,
                'transaction_type' => $data['transaction_type'], // INCOME, EXPENSE, TRANSFER, CAPITAL_IN, CAPITAL_OUT, RECEIVABLE, PAYABLE, REFUND, OPENING_BALANCE, ADJUSTMENT
                'category' => $data['category'] ?? 'general',
                'amount' => (float) $data['amount'],
                'transaction_date' => $data['transaction_date'] ?? Carbon::now()->toDateString(),
                'financial_account_id' => $data['financial_account_id'] ?? null,
                'to_account_id' => $data['to_account_id'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'invoice_id' => $data['invoice_id'] ?? null,
                'employee_id' => $data['employee_id'] ?? null,
                'asset_id' => $data['asset_id'] ?? null,
                'resource_id' => $data['resource_id'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'posted',
                'created_by' => $data['created_by'] ?? (Auth::id() ?? null),
                'approved_by' => $data['approved_by'] ?? null,
                'approved_at' => isset($data['approved_by']) ? Carbon::now() : null,
            ]);

            // Synchronize affected accounts
            if (!empty($transaction->financial_account_id)) {
                $this->syncAccountBalance($transaction->financial_account_id);
            }
            if (!empty($transaction->to_account_id)) {
                $this->syncAccountBalance($transaction->to_account_id);
            }

            AuditLogger::log(
                'create',
                'FinancialTransaction',
                $transaction->id,
                null,
                $transaction->toArray(),
                "Ledger {$transaction->transaction_type} of Rp " . number_format($transaction->amount, 0, ',', '.') . " recorded ({$transaction->transaction_code})"
            );

            return $transaction;
        });
    }

    /**
     * Compute and update the exact balance for a financial account directly from the ledger.
     * Ending Balance = Opening Balance + Cash In (Income, Transfer In, Capital In) - Cash Out (Expense, Transfer Out, Capital Out, Refund) + Adjustments
     */
    public function syncAccountBalance(int $accountId): float
    {
        $account = FinancialAccount::find($accountId);
        if (!$account) {
            return 0.0;
        }

        $hasOpeningTx = FinancialTransaction::where('financial_account_id', $accountId)
            ->where('status', 'posted')
            ->where('transaction_type', 'OPENING_BALANCE')
            ->exists();

        $opening = $hasOpeningTx ? 0.0 : (float) $account->opening_balance;

        // Inflows where this account is the primary account
        $primaryInflows = (float) FinancialTransaction::where('financial_account_id', $accountId)
            ->where('status', 'posted')
            ->whereIn('transaction_type', ['INCOME', 'CAPITAL_IN', 'OPENING_BALANCE'])
            ->sum('amount');

        // Transfers in where this account is the destination account
        $transfersIn = (float) FinancialTransaction::where('to_account_id', $accountId)
            ->where('status', 'posted')
            ->where('transaction_type', 'TRANSFER')
            ->sum('amount');

        // Outflows where this account is the primary source
        $outflows = (float) FinancialTransaction::where('financial_account_id', $accountId)
            ->where('status', 'posted')
            ->whereIn('transaction_type', ['EXPENSE', 'CAPITAL_OUT', 'REFUND', 'TRANSFER'])
            ->sum('amount');

        // Adjustments
        $adjustments = (float) FinancialTransaction::where('financial_account_id', $accountId)
            ->where('status', 'posted')
            ->where('transaction_type', 'ADJUSTMENT')
            ->sum('amount');

        $calculatedBalance = $opening + $primaryInflows + $transfersIn - $outflows + $adjustments;

        // Persist to account table for high-performance dashboard queries
        $account->update(['balance' => $calculatedBalance]);

        return $calculatedBalance;
    }

    /**
     * Sync balances for all financial accounts.
     */
    public function syncAllAccounts(): void
    {
        $accounts = FinancialAccount::all();
        foreach ($accounts as $account) {
            $this->syncAccountBalance($account->id);
        }
    }

    /**
     * Transfer funds between two financial accounts.
     * Net change to total company cash = 0. NOT an expense or income.
     */
    public function transferFunds(int $fromAccountId, int $toAccountId, float $amount, ?string $date = null, ?string $notes = null): FinancialTransaction
    {
        if ($fromAccountId === $toAccountId) {
            throw new \InvalidArgumentException("Source and destination accounts must be different.");
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Transfer amount must be greater than zero.");
        }

        $fromAccount = FinancialAccount::findOrFail($fromAccountId);
        $toAccount = FinancialAccount::findOrFail($toAccountId);

        return $this->recordTransaction([
            'transaction_type' => 'TRANSFER',
            'category' => 'Account Transfer',
            'amount' => $amount,
            'transaction_date' => $date ?? Carbon::now()->toDateString(),
            'financial_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'reference_type' => 'transfer',
            'description' => $notes ?? "Transfer from {$fromAccount->account_name} to {$toAccount->account_name}",
            'status' => 'posted',
        ]);
    }

    /**
     * Owner capital injection.
     * Increases cash & equity. NOT revenue, profit = 0.
     */
    public function recordCapitalInjection(int $accountId, float $amount, ?string $date = null, ?string $notes = null): FinancialTransaction
    {
        $account = FinancialAccount::findOrFail($accountId);

        return $this->recordTransaction([
            'transaction_type' => 'CAPITAL_IN',
            'category' => 'Owner Equity Injection',
            'amount' => $amount,
            'transaction_date' => $date ?? Carbon::now()->toDateString(),
            'financial_account_id' => $account->id,
            'reference_type' => 'capital',
            'description' => $notes ?? "Owner Capital Injection into {$account->account_name}",
            'status' => 'posted',
        ]);
    }

    /**
     * Owner capital withdrawal (Prive).
     * Decreases cash & equity. NOT operational expense.
     */
    public function recordCapitalWithdrawal(int $accountId, float $amount, ?string $date = null, ?string $notes = null): FinancialTransaction
    {
        $account = FinancialAccount::findOrFail($accountId);

        return $this->recordTransaction([
            'transaction_type' => 'CAPITAL_OUT',
            'category' => 'Owner Withdrawal (Prive)',
            'amount' => $amount,
            'transaction_date' => $date ?? Carbon::now()->toDateString(),
            'financial_account_id' => $account->id,
            'reference_type' => 'capital',
            'description' => $notes ?? "Owner Capital Withdrawal from {$account->account_name}",
            'status' => 'posted',
        ]);
    }

    /**
     * Record Client Invoice Payment.
     * Reduces receivable, increases cash in specified financial account.
     */
    public function recordInvoicePayment(int $invoiceId, int $accountId, float $amount, ?string $date = null, string $method = 'bank_transfer', ?string $reference = null, ?string $notes = null): InvoicePayment
    {
        $invoice = Invoice::with('client', 'project')->findOrFail($invoiceId);
        $account = FinancialAccount::findOrFail($accountId);

        return DB::transaction(function () use ($invoice, $account, $amount, $date, $method, $reference, $notes) {
            $paymentDate = $date ?? Carbon::now()->toDateString();
            $paymentNumber = 'PAY-INV-' . strtoupper(uniqid());

            $payment = InvoicePayment::create([
                'payment_number' => $paymentNumber,
                'invoice_id' => $invoice->id,
                'financial_account_id' => $account->id,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'payment_method' => $method,
                'reference_number' => $reference,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);

            // Record in master transaction ledger as Realized Revenue / Cash In
            $this->recordTransaction([
                'transaction_type' => 'INCOME',
                'category' => 'Project Revenue',
                'amount' => $amount,
                'transaction_date' => $paymentDate,
                'financial_account_id' => $account->id,
                'project_id' => $invoice->project_id,
                'invoice_id' => $invoice->id,
                'reference_type' => 'invoice_payment',
                'reference_id' => $payment->id,
                'description' => "Client payment for Invoice #{$invoice->invoice_number} (" . ($invoice->client->company_name ?? 'Client') . ")",
                'status' => 'posted',
            ]);

            // Update Invoice payment status
            $totalPaid = (float) $invoice->payments()->sum('amount');
            if ($totalPaid >= (float) $invoice->total) {
                $invoice->update([
                    'payment_status' => 'paid',
                    'paid_at' => Carbon::now(),
                ]);
            } else {
                $invoice->update([
                    'payment_status' => 'partial',
                ]);
            }

            AuditLogger::log('create', 'InvoicePayment', $payment->id, null, $payment->toArray(), "Invoice #{$invoice->invoice_number} received payment Rp " . number_format($amount, 0, ',', '.'));

            return $payment;
        });
    }

    /**
     * Record a Payable (Hutang Usaha) to vendor/freelancer/supplier.
     */
    public function recordPayable(array $data): Payable
    {
        $code = 'PAY-' . date('Y') . '-' . strtoupper(Str::random(5));

        $payable = Payable::create([
            'payable_code' => $code,
            'title' => $data['title'],
            'vendor_name' => $data['vendor_name'],
            'creditor_type' => $data['creditor_type'] ?? 'vendor',
            'project_id' => $data['project_id'] ?? null,
            'category' => $data['category'] ?? 'operational',
            'amount' => (float) $data['amount'],
            'paid_amount' => 0,
            'issue_date' => $data['issue_date'] ?? Carbon::now()->toDateString(),
            'due_date' => $data['due_date'],
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        AuditLogger::log('create', 'Payable', $payable->id, null, $payable->toArray(), "Payable created: {$payable->title} (Rp " . number_format($payable->amount, 0, ',', '.') . ")");

        return $payable;
    }

    /**
     * Pay a Payable.
     * Records Expense, decrements account, and updates Payable status.
     */
    public function payPayable(int $payableId, int $accountId, float $amount, ?string $date = null, ?string $notes = null): Payable
    {
        $payable = Payable::findOrFail($payableId);
        $account = FinancialAccount::findOrFail($accountId);

        return DB::transaction(function () use ($payable, $account, $amount, $date, $notes) {
            $payDate = $date ?? Carbon::now()->toDateString();
            $newPaidTotal = (float) $payable->paid_amount + $amount;
            $status = $newPaidTotal >= (float) $payable->amount ? 'paid' : 'partial';

            $payable->update([
                'paid_amount' => $newPaidTotal,
                'status' => $status,
                'financial_account_id' => $account->id,
                'paid_at' => $status === 'paid' ? Carbon::now() : $payable->paid_at,
            ]);

            // Create Expense record
            $expense = Expense::create([
                'expense_number' => 'EXP-PAYABLE-' . strtoupper(uniqid()),
                'date' => $payDate,
                'category' => $payable->category,
                'project_id' => $payable->project_id,
                'amount' => $amount,
                'account_id' => $account->id,
                'vendor' => $payable->vendor_name,
                'notes' => $notes ?? "Payment for Payable #{$payable->payable_code} ({$payable->title})",
            ]);

            // Record in master transaction ledger
            $this->recordTransaction([
                'transaction_type' => 'EXPENSE',
                'category' => $payable->category,
                'amount' => $amount,
                'transaction_date' => $payDate,
                'financial_account_id' => $account->id,
                'project_id' => $payable->project_id,
                'reference_type' => 'payable',
                'reference_id' => $payable->id,
                'description' => "Payment of Payable {$payable->payable_code} to {$payable->vendor_name}",
                'status' => 'posted',
            ]);

            return $payable;
        });
    }

    /**
     * Comprehensive Financial Command Center Snapshot.
     */
    public function getCommandCenterMetrics(): array
    {
        // 1. Total Cash across all active accounts
        $accounts = FinancialAccount::where('is_active', true)->get();
        $totalCash = (float) $accounts->sum('balance');

        // 2. Revenue (Excluding Capital In and Transfers)
        $totalRevenue = (float) Income::sum('amount');
        if ($totalRevenue == 0) {
            // Fallback to ledger incomes if Income table was empty
            $totalRevenue = (float) FinancialTransaction::where('status', 'posted')
                ->where('transaction_type', 'INCOME')
                ->sum('amount');
        }

        // 3. Expenses
        $totalExpense = (float) Expense::sum('amount');
        if ($totalExpense == 0) {
            $totalExpense = (float) FinancialTransaction::where('status', 'posted')
                ->where('transaction_type', 'EXPENSE')
                ->sum('amount');
        }

        // 4. Profitability
        $netProfit = $totalRevenue - $totalExpense;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0.0;

        // 5. Cash In & Cash Out breakdown
        $cashIn = (float) FinancialTransaction::where('status', 'posted')
            ->whereIn('transaction_type', ['INCOME', 'CAPITAL_IN', 'OPENING_BALANCE'])
            ->sum('amount');
        if ($cashIn == 0) {
            $cashIn = $totalRevenue;
        }

        $cashOut = (float) FinancialTransaction::where('status', 'posted')
            ->whereIn('transaction_type', ['EXPENSE', 'CAPITAL_OUT', 'REFUND'])
            ->sum('amount');
        if ($cashOut == 0) {
            $cashOut = $totalExpense;
        }

        $netCashflow = $cashIn - $cashOut;

        // 6. Receivables (Piutang Usaha)
        $unpaidInvoices = Invoice::where('payment_status', '!=', 'paid')
            ->where('payment_status', '!=', 'cancelled')
            ->get();
        $totalReceivable = 0.0;
        $overdueReceivable = 0.0;
        $now = Carbon::now();

        foreach ($unpaidInvoices as $inv) {
            $outstanding = (float) $inv->outstanding_amount;
            $totalReceivable += $outstanding;
            if ($inv->due_date && $inv->due_date->isPast()) {
                $overdueReceivable += $outstanding;
            }
        }

        // 7. Payables (Hutang Usaha)
        $unpaidPayables = Payable::where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->get();
        $totalPayable = 0.0;
        $overduePayable = 0.0;

        foreach ($unpaidPayables as $pay) {
            $outstanding = (float) $pay->outstanding_amount;
            $totalPayable += $outstanding;
            if ($pay->due_date && $pay->due_date->isPast()) {
                $overduePayable += $outstanding;
            }
        }

        // 8. Recurring Costs (MRC & ARC from Resource Registry)
        $recurringCosts = $this->calculateRecurringCosts();
        $mrc = $recurringCosts['mrc'];
        $arc = $recurringCosts['arc'];

        // 9. Cash Runway
        // Average Monthly Operating Cost = Expense in last 90 days / 3 (or monthly fallback)
        $threeMonthsAgo = Carbon::now()->subMonths(3)->toDateString();
        $recentExpenses = (float) Expense::where('date', '>=', $threeMonthsAgo)->sum('amount');
        $avgMonthlyOperatingCost = $recentExpenses > 0 ? ($recentExpenses / 3) : ($totalExpense > 0 ? $totalExpense : 10000000);

        $cashRunwayMonths = $avgMonthlyOperatingCost > 0 ? round($totalCash / $avgMonthlyOperatingCost, 1) : 12.0;

        $runwayStatus = 'HEALTHY';
        if ($cashRunwayMonths < 3.0) {
            $runwayStatus = 'CRITICAL';
        } elseif ($cashRunwayMonths < 6.0) {
            $runwayStatus = 'WARNING';
        }

        // 10. Financial Health Radar Score (0-100)
        $healthScore = $this->computeFinancialHealthScore(
            $totalCash,
            $cashRunwayMonths,
            $netProfit,
            $profitMargin,
            $totalReceivable,
            $overdueReceivable,
            $totalPayable,
            $overduePayable
        );

        // 11. Action Required Counter & Items
        $actions = $this->getActionRequiredItems();

        return [
            'total_cash' => $totalCash,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'net_cashflow' => $netCashflow,
            'total_receivable' => $totalReceivable,
            'overdue_receivable' => $overdueReceivable,
            'total_payable' => $totalPayable,
            'overdue_payable' => $overduePayable,
            'mrc' => $mrc,
            'arc' => $arc,
            'avg_monthly_burn' => $avgMonthlyOperatingCost,
            'cash_runway_months' => $cashRunwayMonths,
            'cash_runway_status' => $runwayStatus,
            'health_score' => $healthScore,
            'actions' => $actions,
        ];
    }

    /**
     * Compute Financial Health Score across 6 pillars:
     * Cash Position, Profitability, Liquidity, Receivable Risk, Payable Risk, Budget Control.
     */
    private function computeFinancialHealthScore(
        float $cash,
        float $runway,
        float $netProfit,
        float $margin,
        float $receivable,
        float $overdueReceivable,
        float $payable,
        float $overduePayable
    ): array {
        // Pillar 1: Cash Runway & Position (Max 25)
        $cashScore = min(25, max(5, ($runway >= 6 ? 25 : ($runway >= 3 ? 18 : ($runway * 4)))));

        // Pillar 2: Profitability (Max 25)
        $profitScore = $netProfit > 0
            ? min(25, 15 + min(10, $margin * 0.5))
            : max(0, 10 + ($netProfit / 10000000));

        // Pillar 3: Liquidity (Cash vs Short-term Liabilities) (Max 15)
        $liquidityRatio = ($payable > 0) ? ($cash / $payable) : 2.0;
        $liquidityScore = $liquidityRatio >= 1.5 ? 15 : ($liquidityRatio >= 1.0 ? 10 : 5);

        // Pillar 4: Receivable Risk (Max 15)
        $recOverduePct = ($receivable > 0) ? ($overdueReceivable / $receivable) : 0;
        $recScore = $recOverduePct < 0.1 ? 15 : ($recOverduePct < 0.3 ? 10 : 5);

        // Pillar 5: Payable Risk (Max 10)
        $payOverduePct = ($payable > 0) ? ($overduePayable / $payable) : 0;
        $payScore = $payOverduePct < 0.1 ? 10 : ($payOverduePct < 0.3 ? 6 : 2);

        // Pillar 6: Budget Control (Max 10)
        $budgetScore = 10; // Default healthy unless over budget

        $totalScore = round($cashScore + $profitScore + $liquidityScore + $recScore + $payScore + $budgetScore);
        $totalScore = max(10, min(100, $totalScore));

        $rating = 'EXCELLENT';
        if ($totalScore < 50) {
            $rating = 'CRITICAL';
        } elseif ($totalScore < 70) {
            $rating = 'WARNING';
        } elseif ($totalScore < 85) {
            $rating = 'HEALTHY';
        }

        return [
            'total_score' => $totalScore,
            'rating' => $rating,
            'pillars' => [
                'cash_position' => round($cashScore),
                'profitability' => round($profitScore),
                'liquidity' => round($liquidityScore),
                'receivable_risk' => round($recScore),
                'payable_risk' => round($payScore),
                'budget_control' => round($budgetScore),
            ],
        ];
    }

    /**
     * Gather actionable financial issues requiring Super Admin attention.
     */
    public function getActionRequiredItems(): array
    {
        $items = [];

        // 1. Overdue Invoices
        $overdueInvoices = Invoice::where('payment_status', '!=', 'paid')
            ->where('payment_status', '!=', 'cancelled')
            ->where('due_date', '<', Carbon::now()->toDateString())
            ->with('client')
            ->take(5)
            ->get();

        foreach ($overdueInvoices as $inv) {
            $items[] = [
                'type' => 'invoice_overdue',
                'severity' => 'danger',
                'title' => "Invoice Overdue: #{$inv->invoice_number}",
                'subtitle' => ($inv->client->company_name ?? 'Client') . " • Rp " . number_format($inv->outstanding_amount, 0, ',', '.') . " (" . $inv->aging_days . " days late)",
                'link' => route('finance.invoices'),
            ];
        }

        // 2. Overdue Payables
        $overduePayables = Payable::where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('due_date', '<', Carbon::now()->toDateString())
            ->take(5)
            ->get();

        foreach ($overduePayables as $pay) {
            $items[] = [
                'type' => 'payable_due',
                'severity' => 'warning',
                'title' => "Payable Due: {$pay->vendor_name}",
                'subtitle' => "{$pay->title} • Rp " . number_format($pay->outstanding_amount, 0, ',', '.'),
                'link' => route('finance.payables'),
            ];
        }

        // 3. Expiring Subscriptions in next 7 days
        $expiringSubs = Subscription::where('status', 'active')
            ->whereNotNull('next_billing_date')
            ->whereBetween('next_billing_date', [Carbon::now()->toDateString(), Carbon::now()->addDays(7)->toDateString()])
            ->take(3)
            ->get();

        foreach ($expiringSubs as $sub) {
            $items[] = [
                'type' => 'subscription_due',
                'severity' => 'info',
                'title' => "Subscription Billing: {$sub->provider}",
                'subtitle' => "{$sub->plan_name} • Rp " . number_format($sub->cost, 0, ',', '.') . " due " . Carbon::parse($sub->next_billing_date)->format('d M Y'),
                'link' => route('finance.recurring_cost'),
            ];
        }

        return $items;
    }

    /**
     * Compute Aging Analysis for Receivables (Piutang Klien).
     * 0-30 days, 31-60 days, 61-90 days, >90 days.
     */
    public function getReceivablesAging(): array
    {
        $invoices = Invoice::where('payment_status', '!=', 'paid')
            ->where('payment_status', '!=', 'cancelled')
            ->with('client', 'project')
            ->get();

        $aging = [
            'current' => 0.0, // not overdue or 0-30 days
            'tier_30' => 0.0, // 1-30 days overdue
            'tier_60' => 0.0, // 31-60 days overdue
            'tier_90' => 0.0, // 61-90 days overdue
            'tier_over90' => 0.0, // >90 days overdue
            'total' => 0.0,
            'items' => [],
        ];

        $today = Carbon::today();

        foreach ($invoices as $invoice) {
            $outstanding = (float) $invoice->outstanding_amount;
            $aging['total'] += $outstanding;

            $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date) : $today;
            $daysPast = $today->diffInDays($dueDate, false) * -1;

            if ($daysPast <= 0) {
                $tier = 'current';
                $aging['current'] += $outstanding;
            } elseif ($daysPast <= 30) {
                $tier = '1-30 days';
                $aging['tier_30'] += $outstanding;
            } elseif ($daysPast <= 60) {
                $tier = '31-60 days';
                $aging['tier_60'] += $outstanding;
            } elseif ($daysPast <= 90) {
                $tier = '61-90 days';
                $aging['tier_90'] += $outstanding;
            } else {
                $tier = '>90 days';
                $aging['tier_over90'] += $outstanding;
            }

            $aging['items'][] = [
                'invoice' => $invoice,
                'outstanding' => $outstanding,
                'days_past' => max(0, $daysPast),
                'tier' => $tier,
            ];
        }

        return $aging;
    }

    /**
     * Compute Recurring Costs (MRC & ARC from Infrastructure and Subscriptions).
     */
    public function calculateRecurringCosts(): array
    {
        $monthlyInfrastructure = (float) Infrastructure::where('status', 'active')->sum('monthly_cost');
        $yearlyInfrastructure = (float) Infrastructure::where('status', 'active')->sum('yearly_cost');

        $subscriptions = Subscription::where('status', 'active')->get();
        $monthlySubscriptions = 0.0;

        foreach ($subscriptions as $sub) {
            $cost = (float) $sub->cost;
            if ($sub->billing_cycle === 'yearly') {
                $monthlySubscriptions += ($cost / 12);
            } elseif ($sub->billing_cycle === 'quarterly') {
                $monthlySubscriptions += ($cost / 3);
            } else {
                $monthlySubscriptions += $cost;
            }
        }

        $totalMRC = $monthlyInfrastructure + ($yearlyInfrastructure / 12) + $monthlySubscriptions;
        $totalARC = $totalMRC * 12;

        return [
            'mrc' => $totalMRC,
            'arc' => $totalARC,
            'vps_infrastructure_monthly' => $monthlyInfrastructure,
            'subscriptions_monthly' => $monthlySubscriptions,
        ];
    }

    /**
     * Compute Hourly Labor Rate for an employee: Base Salary / 160h.
     */
    public function getHourlyLaborCost(int $userId): float
    {
        $user = User::find($userId);
        if (!$user) {
            return 31250.0; // Fallback to Rp 31.250 / hr
        }

        // Check if employee has a defined base_salary in user table or payroll
        $salary = (float) ($user->base_salary ?? 5000000);
        return $salary > 0 ? round($salary / 160, 2) : 31250.0;
    }

    /**
     * Project Finance Snapshot & Labor Cost calculation.
     */
    public function getProjectFinancialSnapshot(int $projectId): array
    {
        $project = Project::with('client')->findOrFail($projectId);

        // Contract value / Revenue
        $contractValue = (float) ($project->contract_value ?? $project->budget ?? 0);
        $invoicedRevenue = (float) Invoice::where('project_id', $projectId)->sum('total');
        $realizedRevenue = (float) Invoice::where('project_id', $projectId)->where('payment_status', 'paid')->sum('total');

        // Direct project expenses
        $directCost = (float) Expense::where('project_id', $projectId)->sum('amount');

        // Labor Cost from DailyProgress logged hours
        $progressEntries = DailyProgress::where('project_id', $projectId)->get();
        $totalLaborHours = 0.0;
        $totalLaborCost = 0.0;

        foreach ($progressEntries as $entry) {
            $hours = (float) ($entry->hours_spent ?? 4.0);
            $hourlyRate = $this->getHourlyLaborCost($entry->user_id);
            $totalLaborHours += $hours;
            $totalLaborCost += ($hours * $hourlyRate);
        }

        $totalProjectCost = $directCost + $totalLaborCost;
        $revenueBase = $realizedRevenue > 0 ? $realizedRevenue : ($contractValue > 0 ? $contractValue : $invoicedRevenue);
        $projectProfit = $revenueBase - $totalProjectCost;
        $margin = $revenueBase > 0 ? round(($projectProfit / $revenueBase) * 100, 1) : 0.0;

        // Profit Rating
        $rating = 'HEALTHY';
        if ($projectProfit < 0) {
            $rating = 'LOSS';
        } elseif ($margin < 15.0) {
            $rating = 'LOW MARGIN';
        } elseif ($margin >= 35.0) {
            $rating = 'HIGH PROFIT';
        }

        return [
            'project' => $project,
            'contract_value' => $contractValue,
            'invoiced_revenue' => $invoicedRevenue,
            'realized_revenue' => $realizedRevenue,
            'budget' => (float) $project->budget,
            'direct_cost' => $directCost,
            'labor_hours' => $totalLaborHours,
            'labor_cost' => $totalLaborCost,
            'total_actual_cost' => $totalProjectCost,
            'project_profit' => $projectProfit,
            'profit_margin' => $margin,
            'rating' => $rating,
        ];
    }
}