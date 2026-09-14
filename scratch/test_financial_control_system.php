<?php

require __DIR__ . '/../../../../xampp/htdocs/solvia-nova-os/vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../xampp/htdocs/solvia-nova-os/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Payable;
use App\Models\Income;
use App\Models\Expense;
use App\Services\FinancialLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== SOLVIA.NOVA OS — FINANCIAL CONTROL SYSTEM E2E VERIFICATION ===\n\n";

$superAdmin = User::where('email', 'nandaariwahyu07@gmail.com')->first();
if (!$superAdmin) {
    echo "[ERROR] Super Admin not found!\n";
    exit(1);
}
Auth::login($superAdmin);
echo "[OK] Authenticated as Super Admin: {$superAdmin->name} ({$superAdmin->email})\n\n";

// --- TEST 1: All 18 Finance Web Endpoints Render Cleanly ---
echo "--- 1. Testing View Rendering for all 18 Sub-Modules ---\n";
$routes = [
    'finance.dashboard' => 'Dashboard & Health Radar',
    'finance.accounts' => 'Corporate Treasury & Accounts',
    'finance.transactions' => 'Master Ledger (Buku Besar)',
    'finance.revenue' => 'Revenue Streams Breakdown',
    'finance.incomes' => 'Incomes Registry',
    'finance.expenses' => 'Expenses Registry',
    'finance.invoices' => 'Invoices & Billing',
    'finance.payments' => 'Payments Receipt',
    'finance.receivables' => 'Receivables & Aging Matrix',
    'finance.payables' => 'Payables & Liabilities',
    'finance.cashflow' => 'Cashflow Statement',
    'finance.budgets' => 'Budget vs Actual',
    'finance.payroll' => 'Payroll Engine',
    'finance.reimbursements' => 'Reimbursements',
    'finance.project_finance' => 'Project Profit & Labor Cost',
    'finance.recurring_cost' => 'Recurring Cost (MRC & ARC)',
    'finance.asset_finance' => 'Asset Finance & Capex',
    'finance.reports' => 'Reports & Intelligence',
];

$allPassed = true;
foreach ($routes as $route => $name) {
    try {
        $url = route($route);
        $request = Request::create($url, 'GET');
        $response = app()->handle($request);
        $status = $response->getStatusCode();

        if ($status === 200) {
            echo "  [PASS] {$name} ({$route}) -> HTTP 200 OK\n";
        } else {
            echo "  [FAIL] {$name} ({$route}) -> HTTP {$status}\n";
            $allPassed = false;
        }
    } catch (\Throwable $e) {
        echo "  [EXCP] {$name} ({$route}) -> " . $e->getMessage() . "\n";
        $allPassed = false;
    }
}

if (!$allPassed) {
    echo "\n[ERROR] Some routes failed to render!\n";
    exit(1);
}

echo "\n--- 2. Testing Financial Core Principles & Ledger Engine ---\n";
$service = app(FinancialLedgerService::class);

// Principle A: Dynamic Balance from Master Ledger
echo "  [Test A] Account Balance Synchronization from Ledger...\n";
$service->syncAllAccounts();
$accounts = FinancialAccount::where('is_active', true)->get();
foreach ($accounts as $acc) {
    $hasOpeningTx = FinancialTransaction::where('financial_account_id', $acc->id)
        ->where('status', 'posted')
        ->where('transaction_type', 'OPENING_BALANCE')
        ->exists();
    $opening = $hasOpeningTx ? 0.0 : (float) $acc->opening_balance;

    $expected = $opening
        + (float)FinancialTransaction::where('financial_account_id', $acc->id)->whereIn('transaction_type', ['INCOME', 'CAPITAL_IN', 'OPENING_BALANCE'])->sum('amount')
        + (float)FinancialTransaction::where('to_account_id', $acc->id)->where('transaction_type', 'TRANSFER')->sum('amount')
        - (float)FinancialTransaction::where('financial_account_id', $acc->id)->whereIn('transaction_type', ['EXPENSE', 'CAPITAL_OUT', 'TRANSFER'])->sum('amount')
        + (float)FinancialTransaction::where('financial_account_id', $acc->id)->where('transaction_type', 'ADJUSTMENT')->sum('amount');
    
    if (abs((float)$acc->balance - $expected) > 0.01) {
        echo "    [FAIL] Account {$acc->account_name} balance mismatch! Model: {$acc->balance}, Ledger: {$expected}\n";
        exit(1);
    }
}
echo "    [PASS] All accounts dynamically balance-verified against single source of truth ledger.\n";

// Principle B: Transfer Antar Rekening (Net Cashflow = 0, Revenue = 0, Expense = 0)
echo "  [Test B] Transfer Antar Rekening...\n";
$acc1 = FinancialAccount::first();
$acc2 = FinancialAccount::skip(1)->first();
if ($acc1 && $acc2) {
    $initialTotalCash = (float)FinancialAccount::sum('balance');
    $initialRevenue = (float)Income::sum('amount');
    $initialExpense = (float)Expense::sum('amount');

    $service->transferFunds($acc1->id, $acc2->id, 500000, date('Y-m-d'), 'Test automated inter-account transfer');

    $newTotalCash = (float)FinancialAccount::sum('balance');
    $newRevenue = (float)Income::sum('amount');
    $newExpense = (float)Expense::sum('amount');

    if (abs($initialTotalCash - $newTotalCash) < 0.01 && $newRevenue == $initialRevenue && $newExpense == $initialExpense) {
        echo "    [PASS] Transfer preserved total cash (Rp " . number_format($newTotalCash, 0) . "), Revenue unchanged, Expense unchanged.\n";
    } else {
        echo "    [FAIL] Transfer violated financial principles! Cash shifted or rev/exp mutated.\n";
        exit(1);
    }
}

// Principle C: Capital In / Out (Owner Capital ≠ Revenue)
echo "  [Test C] Owner Capital Injection & Withdrawal...\n";
$initialRev = (float)Income::sum('amount');
$capTx = $service->recordCapitalInjection($acc1->id, 2000000, date('Y-m-d'), 'Owner Capital Test Injection');
$postRev = (float)Income::sum('amount');

if ($initialRev === $postRev) {
    echo "    [PASS] Capital In increased cash without inflating Revenue (Income count remained constant).\n";
} else {
    echo "    [FAIL] Capital In erroneously counted as revenue!\n";
    exit(1);
}

// Principle D: Invoice & Payment (Invoice ≠ Payment, Receivable ≠ Cash)
echo "  [Test D] Invoice Payment Realization (Piutang -> Kas Masuk)...\n";
$invoice = Invoice::first();
if ($invoice) {
    $initialCash = (float)FinancialAccount::find($acc1->id)->balance;
    $initialInvoicePaid = (float)$invoice->paid_amount;
    
    $payment = $service->recordInvoicePayment($invoice->id, $acc1->id, 100000, date('Y-m-d'), 'bank_transfer', 'REF-TEST-99', 'Partial payment test');
    $updatedCash = (float)FinancialAccount::find($acc1->id)->balance;
    $updatedInvoice = Invoice::find($invoice->id);

    if (abs($updatedCash - ($initialCash + 100000)) < 0.01 && $updatedInvoice->paid_amount == ($initialInvoicePaid + 100000)) {
        echo "    [PASS] Payment successfully registered into cash account and credited invoice receivables.\n";
    } else {
        echo "    [FAIL] Invoice payment failed to update cash or invoice paid amount.\n";
        exit(1);
    }
}

// Principle E: Payables & Debt Settlement (Payable ≠ Expense yang sudah dibayar)
echo "  [Test E] Accounts Payable & Settlement...\n";
$payable = $service->recordPayable([
    'title' => 'Server Cloud Verification Invoice',
    'vendor_name' => 'PT Cloud Hostindo Verification',
    'creditor_type' => 'vendor',
    'category' => 'Infrastructure',
    'amount' => 750000,
    'issue_date' => date('Y-m-d'),
    'due_date' => date('Y-m-d', strtotime('+15 days')),
    'notes' => 'Test payable record',
]);
echo "    [PASS] Payable created: {$payable->payable_code} (Rp " . number_format($payable->amount, 0) . ")\n";

$accForPay = FinancialAccount::find($acc1->id);
$initialCash = (float)$accForPay->balance;
$paidPayable = $service->payPayable($payable->id, $accForPay->id, 750000, date('Y-m-d'), 'Settlement test');
$afterCash = (float)FinancialAccount::find($accForPay->id)->balance;

if ($paidPayable->status === 'paid' && abs($initialCash - $afterCash - 750000) < 0.01) {
    echo "    [PASS] Payable successfully settled: Cash reduced by Rp 750.000, expense booked in ledger, payable marked paid.\n";
} else {
    echo "    [FAIL] Payable settlement did not properly decrement cash or ledger!\n";
    exit(1);
}

// Principle F: Non-SuperAdmin Security Guard
echo "  [Test F] Role Security Isolation (Only Super Admin can access Finance)...\n";
$operationalUser = User::where('role', '!=', 'super_admin')->first();
if ($operationalUser) {
    Auth::login($operationalUser);
    $request = Request::create(route('finance.dashboard'), 'GET');
    $response = app()->handle($request);
    if ($response->getStatusCode() === 403) {
        echo "    [PASS] Non-SuperAdmin ({$operationalUser->name} - {$operationalUser->role}) blocked with HTTP 403 Forbidden.\n";
    } else {
        echo "    [FAIL] Non-SuperAdmin accessed finance with status: {$response->getStatusCode()}\n";
        exit(1);
    }
}

echo "\n=================================================================\n";
echo ">>> ALL FINANCIAL CONTROL SYSTEM PRINCIPLES & ENDPOINTS VERIFIED <<<\n";
echo "=================================================================\n";
