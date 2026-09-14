<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Budget;
use App\Models\Client;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use App\Models\Income;
use App\Models\Infrastructure;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Notification;
use App\Models\Payable;
use App\Models\Payroll;
use App\Models\Project;
use App\Models\Reimbursement;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\FinancialLedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceController extends Controller
{
    protected FinancialLedgerService $ledgerService;

    public function __construct(FinancialLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /* -------------------------------------------------------------------------
     * 1. DASHBOARD — Financial Control Command Center
     * ------------------------------------------------------------------------- */
    public function dashboard(Request $request)
    {
        $this->authorizeFinance();

        $metrics = $this->ledgerService->getCommandCenterMetrics();

        $accounts = FinancialAccount::where('is_active', true)->get();
        $recentIncomes = Income::with('client', 'project', 'account')->orderByDesc('date')->take(5)->get();
        $recentExpenses = Expense::with('project', 'account')->orderByDesc('date')->take(5)->get();
        $recentTransactions = FinancialTransaction::with('account', 'toAccount', 'project')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        $unpaidInvoices = Invoice::with('client')
            ->where('payment_status', '!=', 'paid')
            ->where('payment_status', '!=', 'cancelled')
            ->orderBy('due_date')
            ->take(5)
            ->get();

        $expensesByCategory = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        $totalIncome = $metrics['total_revenue'];
        $totalExpense = $metrics['total_expense'];
        $netCashflow = $metrics['net_cashflow'];
        $totalBankBalance = $metrics['total_cash'];

        return view('finance.dashboard', compact(
            'metrics',
            'accounts',
            'recentIncomes',
            'recentExpenses',
            'recentTransactions',
            'unpaidInvoices',
            'expensesByCategory',
            'totalIncome',
            'totalExpense',
            'netCashflow',
            'totalBankBalance'
        ));
    }

    /* -------------------------------------------------------------------------
     * 2. FINANCIAL ACCOUNTS (Bank, E-Wallet, Cash, Gateway)
     * ------------------------------------------------------------------------- */
    public function accounts(Request $request)
    {
        $this->authorizeFinance();

        $accounts = FinancialAccount::withCount('transactions')->get();
        $totalCash = (float) $accounts->where('is_active', true)->sum('balance');

        return view('finance.accounts', compact('accounts', 'totalCash'));
    }

    public function storeAccount(Request $request)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'account_code' => 'required|string|unique:financial_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:BANK,E_WALLET,CASH,PAYMENT_GATEWAY,OTHER',
            'provider' => 'nullable|string',
            'account_number' => 'nullable|string',
            'owner' => 'nullable|string',
            'opening_balance' => 'required|numeric|min:0',
            'currency' => 'required|string|max:5',
            'description' => 'nullable|string',
        ]);

        $account = FinancialAccount::create([
            'account_code' => $data['account_code'],
            'account_name' => $data['account_name'],
            'account_type' => $data['account_type'],
            'type' => strtolower($data['account_type']),
            'provider' => $data['provider'],
            'bank_name' => $data['provider'],
            'account_number' => $data['account_number'],
            'owner' => $data['owner'],
            'opening_balance' => $data['opening_balance'],
            'balance' => $data['opening_balance'],
            'currency' => $data['currency'],
            'description' => $data['description'] ?? null,
            'is_active' => true,
            'status' => 'active',
        ]);

        // Record OPENING_BALANCE in ledger
        if ($data['opening_balance'] > 0) {
            $this->ledgerService->recordTransaction([
                'transaction_type' => 'OPENING_BALANCE',
                'category' => 'Initial Capital / Opening Balance',
                'amount' => $data['opening_balance'],
                'transaction_date' => Carbon::now()->toDateString(),
                'financial_account_id' => $account->id,
                'description' => "Opening balance for account {$account->account_name}",
                'status' => 'posted',
            ]);
        }

        AuditLogger::log('create', 'FinancialAccount', $account->id, null, $account->toArray(), "Account {$account->account_name} created");
        return back()->with('success', 'Financial Account registered and ledger initialized.');
    }

    public function transferAccount(Request $request)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'from_account_id' => 'required|exists:financial_accounts,id',
            'to_account_id' => 'required|different:from_account_id|exists:financial_accounts,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->ledgerService->transferFunds(
                (int) $data['from_account_id'],
                (int) $data['to_account_id'],
                (float) $data['amount'],
                $data['date'],
                $data['notes'] ?? null
            );
            return back()->with('success', 'Transfer antar rekening berhasil dibukukan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function adjustAccount(Request $request)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'account_id' => 'required|exists:financial_accounts,id',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'reason' => 'required|string|min:5',
        ]);

        $account = FinancialAccount::findOrFail($data['account_id']);

        $this->ledgerService->recordTransaction([
            'transaction_type' => 'ADJUSTMENT',
            'category' => 'Balance Adjustment',
            'amount' => (float) $data['amount'],
            'transaction_date' => $data['date'],
            'financial_account_id' => $account->id,
            'description' => "Audit adjustment: " . $data['reason'],
            'status' => 'posted',
        ]);

        return back()->with('success', 'Penyesuaian saldo berhasil dicatat dalam audit trail.');
    }

    /* -------------------------------------------------------------------------
     * 3. TRANSACTIONS (Master Ledger)
     * ------------------------------------------------------------------------- */
    public function transactions(Request $request)
    {
        $this->authorizeFinance();

        $query = FinancialTransaction::with('account', 'toAccount', 'project', 'invoice', 'employee')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }
        if ($request->filled('account_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('financial_account_id', $request->account_id)
                  ->orWhere('to_account_id', $request->account_id);
            });
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('transaction_code', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhere('category', 'like', "%{$request->search}%");
            });
        }

        $transactions = $query->paginate(25)->withQueryString();
        $accounts = FinancialAccount::where('is_active', true)->get();
        $projects = Project::where('status', 'active')->get();

        return view('finance.transactions', compact('transactions', 'accounts', 'projects'));
    }

    public function storeTransaction(Request $request)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'transaction_type' => 'required|in:INCOME,EXPENSE,CAPITAL_IN,CAPITAL_OUT,ADJUSTMENT',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'financial_account_id' => 'required|exists:financial_accounts,id',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'required|string',
        ]);

        $this->ledgerService->recordTransaction($data);

        return back()->with('success', 'Transaksi berhasil dibukukan ke dalam Buku Besar Keuangan.');
    }

    /* -------------------------------------------------------------------------
     * 4. REVENUE
     * ------------------------------------------------------------------------- */
    public function revenue(Request $request)
    {
        $this->authorizeFinance();

        $incomes = Income::with('client', 'project', 'account')->orderByDesc('date')->paginate(20);
        $totalRevenue = (float) Income::sum('amount');

        $byCategory = Income::select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('finance.revenue', compact('incomes', 'totalRevenue', 'byCategory'));
    }

    public function incomes(Request $request)
    {
        $this->authorizeFinance();

        $incomes = Income::with('client', 'project', 'account')->orderByDesc('date')->paginate(20);
        $clients = Client::where('status', 'active')->get();
        $projects = Project::where('status', 'active')->get();
        $accounts = FinancialAccount::where('is_active', true)->get();

        return view('finance.incomes', compact('incomes', 'clients', 'projects', 'accounts'));
    }

    public function storeIncome(Request $request)
    {
        $this->authorizeFinance();

        foreach (['client_id', 'project_id', 'notes'] as $field) {
            if (!$request->filled($field)) {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'income_number' => 'required|string|unique:incomes,income_number',
            'date' => 'required|date',
            'source' => 'required|string',
            'client_id' => 'nullable|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'amount' => 'required|numeric|min:1',
            'category' => 'required|string',
            'account_id' => 'required|exists:financial_accounts,id',
            'notes' => 'nullable|string',
        ]);

        $income = Income::create($data);

        // Record in Master Ledger
        $this->ledgerService->recordTransaction([
            'transaction_type' => 'INCOME',
            'category' => $income->category ?: 'Project Revenue',
            'amount' => $income->amount,
            'transaction_date' => $income->date,
            'financial_account_id' => $income->account_id,
            'project_id' => $income->project_id,
            'reference_type' => 'income',
            'reference_id' => $income->id,
            'description' => $income->source ?: "Income #{$income->income_number}",
            'status' => 'posted',
        ]);

        AuditLogger::log('create', 'Income', $income->id, null, $income->toArray(), "Income recorded: {$income->income_number} (Rp " . number_format($income->amount, 0, ',', '.') . ")");
        return back()->with('success', 'Income entry recorded and ledger synchronized.');
    }

    /* -------------------------------------------------------------------------
     * 5. EXPENSES
     * ------------------------------------------------------------------------- */
    public function expenses(Request $request)
    {
        $this->authorizeFinance();

        $expenses = Expense::with('project', 'account')->orderByDesc('date')->paginate(20);
        $projects = Project::where('status', 'active')->get();
        $accounts = FinancialAccount::where('is_active', true)->get();

        return view('finance.expenses', compact('expenses', 'projects', 'accounts'));
    }

    public function storeExpense(Request $request)
    {
        $this->authorizeFinance();

        foreach (['project_id', 'vendor', 'notes'] as $field) {
            if (!$request->filled($field)) {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'expense_number' => 'required|string|unique:expenses,expense_number',
            'date' => 'required|date',
            'category' => 'required|string',
            'project_id' => 'nullable|exists:projects,id',
            'amount' => 'required|numeric|min:1',
            'account_id' => 'required|exists:financial_accounts,id',
            'vendor' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $expense = Expense::create($data);

        // Record in Master Ledger
        $this->ledgerService->recordTransaction([
            'transaction_type' => 'EXPENSE',
            'category' => $expense->category ?: 'Operational Expense',
            'amount' => $expense->amount,
            'transaction_date' => $expense->date,
            'financial_account_id' => $expense->account_id,
            'project_id' => $expense->project_id,
            'reference_type' => 'expense',
            'reference_id' => $expense->id,
            'description' => ($expense->vendor ? "{$expense->vendor}: " : '') . ($expense->notes ?: "Expense #{$expense->expense_number}"),
            'status' => 'posted',
        ]);

        AuditLogger::log('create', 'Expense', $expense->id, null, $expense->toArray(), "Expense recorded: {$expense->expense_number} (Rp " . number_format($expense->amount, 0, ',', '.') . ")");
        return back()->with('success', 'Expense recorded and ledger synchronized.');
    }

    /* -------------------------------------------------------------------------
     * 6. INVOICES & PAYMENTS
     * ------------------------------------------------------------------------- */
    public function invoices(Request $request)
    {
        $this->authorizeFinance();

        $invoices = Invoice::with('client', 'project', 'items', 'payments')->orderByDesc('issue_date')->paginate(20);
        $clients = Client::where('status', 'active')->get();
        $projects = Project::where('status', 'active')->get();
        $accounts = FinancialAccount::where('is_active', true)->get();

        return view('finance.invoices', compact('invoices', 'clients', 'projects', 'accounts'));
    }

    public function storeInvoice(Request $request)
    {
        $this->authorizeFinance();

        foreach (['project_id', 'notes', 'discount', 'tax'] as $field) {
            if (!$request->filled($field)) {
                $request->merge([$field => null]);
            }
        }

        if (!$request->filled('subtotal') && $request->has('items') && is_array($request->input('items'))) {
            $calcSub = 0;
            foreach ($request->input('items') as $it) {
                $calcSub += (float)($it['quantity'] ?? 0) * (float)($it['unit_price'] ?? 0);
            }
            $request->merge(['subtotal' => $calcSub]);
        }

        $data = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $discount = $data['discount'] ?? 0;
        $tax = $data['tax'] ?? 0;
        $total = ($subtotal - $discount) + $tax;

        DB::transaction(function () use ($data, $subtotal, $discount, $tax, $total) {
            $invoice = Invoice::create([
                'invoice_number' => $data['invoice_number'],
                'client_id' => $data['client_id'],
                'project_id' => $data['project_id'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'payment_status' => 'sent',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            AuditLogger::log('create', 'Invoice', $invoice->id, null, $invoice->toArray(), "Invoice #{$invoice->invoice_number} created for Rp " . number_format($total, 0, ',', '.'));
        });

        return back()->with('success', 'Invoice generated and dispatched successfully.');
    }

    public function markInvoicePaid(Request $request, Invoice $invoice)
    {
        $this->authorizeFinance();

        $accountId = $request->input('account_id') ?? FinancialAccount::first()->id;
        $amount = (float) ($request->input('amount') ?? $invoice->outstanding_amount);

        $this->ledgerService->recordInvoicePayment(
            $invoice->id,
            $accountId,
            $amount,
            Carbon::now()->toDateString(),
            'bank_transfer',
            null,
            "Pembayaran faktur #{$invoice->invoice_number}"
        );

        return back()->with('success', "Invoice #{$invoice->invoice_number} berhasil dicatat pembayarannya dan dana masuk ke rekening.");
    }

    public function payments(Request $request)
    {
        $this->authorizeFinance();

        $payments = InvoicePayment::with('invoice.client', 'account')->orderByDesc('payment_date')->paginate(20);
        $invoices = Invoice::where('payment_status', '!=', 'paid')->get();
        $accounts = FinancialAccount::where('is_active', true)->get();

        return view('finance.payments', compact('payments', 'invoices', 'accounts'));
    }

    public function storePayment(Request $request)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'financial_account_id' => 'required|exists:financial_accounts,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $payment = $this->ledgerService->recordInvoicePayment(
            (int) $data['invoice_id'],
            (int) $data['financial_account_id'],
            (float) $data['amount'],
            $data['payment_date'],
            $data['payment_method'],
            $data['reference_number'] ?? null,
            $data['notes'] ?? null
        );

        return back()->with('success', "Pembayaran invoice sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil dicatat.");
    }

    /* -------------------------------------------------------------------------
     * 7. RECEIVABLES (Piutang) & PAYABLES (Hutang)
     * ------------------------------------------------------------------------- */
    public function receivables(Request $request)
    {
        $this->authorizeFinance();

        $aging = $this->ledgerService->getReceivablesAging();

        return view('finance.receivables', compact('aging'));
    }

    public function payables(Request $request)
    {
        $this->authorizeFinance();

        $payables = Payable::with('project', 'account')->orderBy('due_date')->paginate(20);
        $projects = Project::where('status', 'active')->get();
        $accounts = FinancialAccount::where('is_active', true)->get();

        $totalPayable = (float) Payable::where('status', '!=', 'paid')->sum('amount') - (float) Payable::where('status', '!=', 'paid')->sum('paid_amount');
        $overduePayable = (float) Payable::where('status', '!=', 'paid')->where('due_date', '<', Carbon::now()->toDateString())->sum('amount');

        return view('finance.payables', compact('payables', 'projects', 'accounts', 'totalPayable', 'overduePayable'));
    }

    public function storePayable(Request $request)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'vendor_name' => 'required|string|max:255',
            'creditor_type' => 'required|in:vendor,freelancer,supplier,service_provider,other',
            'project_id' => 'nullable|exists:projects,id',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
        ]);

        $this->ledgerService->recordPayable($data);

        return back()->with('success', 'Hutang / Payable berhasil dicatat.');
    }

    public function payPayable(Request $request, Payable $payable)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'financial_account_id' => 'required|exists:financial_accounts,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $this->ledgerService->payPayable(
            $payable->id,
            (int) $data['financial_account_id'],
            (float) $data['amount'],
            $data['date'],
            $data['notes'] ?? null
        );

        return back()->with('success', "Pembayaran hutang kepada {$payable->vendor_name} berhasil dicatat.");
    }

    /* -------------------------------------------------------------------------
     * 8. CASHFLOW & BUDGETS
     * ------------------------------------------------------------------------- */
    public function cashflow(Request $request)
    {
        $this->authorizeFinance();

        $period = $request->get('period', 'monthly');

        $incomes = Income::with('account')->orderBy('date')->get();
        $expenses = Expense::with('account')->orderBy('date')->get();

        $totalIn = (float) $incomes->sum('amount');
        $totalOut = (float) $expenses->sum('amount');
        $net = $totalIn - $totalOut;

        // Structured cashflow
        $operatingCashIn = $totalIn;
        $operatingCashOut = $totalOut;
        $operatingCashflow = $operatingCashIn - $operatingCashOut;

        $investingCashflow = 0.0;
        $financingCashflow = 0.0;

        return view('finance.cashflow', compact(
            'incomes',
            'expenses',
            'totalIn',
            'totalOut',
            'net',
            'operatingCashIn',
            'operatingCashOut',
            'operatingCashflow',
            'investingCashflow',
            'financingCashflow',
            'period'
        ));
    }

    public function budgets()
    {
        $this->authorizeFinance();

        $budgets = Budget::with('project')->orderBy('year')->orderBy('month')->get();
        $projects = Project::where('status', 'active')->get();

        return view('finance.budgets', compact('budgets', 'projects'));
    }

    public function storeBudget(Request $request)
    {
        $this->authorizeFinance();

        $data = $request->validate([
            'scope_type' => 'required|in:company,project,category',
            'project_id' => 'nullable|exists:projects,id',
            'category' => 'nullable|string',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2040',
            'budgeted_amount' => 'required|numeric|min:0',
        ]);

        $data['actual_amount'] = 0;
        $budget = Budget::create($data);

        AuditLogger::log('create', 'Budget', $budget->id, null, $budget->toArray(), "Budget created for {$budget->scope_type}");
        return back()->with('success', 'Budget defined successfully.');
    }

    /* -------------------------------------------------------------------------
     * 9. PAYROLL & REIMBURSEMENTS
     * ------------------------------------------------------------------------- */
    public function payroll()
    {
        $this->authorizeFinance();

        $payrolls = Payroll::with('user')->orderByDesc('period')->paginate(20);
        $users = User::where('status', 'active')->get();
        $accounts = FinancialAccount::where('is_active', true)->get();

        return view('finance.payroll', compact('payrolls', 'users', 'accounts'));
    }

    public function storePayroll(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'base_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'reimbursement' => 'nullable|numeric|min:0',
            'account_id' => 'nullable|exists:financial_accounts,id',
            'disburse_now' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $base = $data['base_salary'];
        $allowance = $data['allowance'] ?? 0;
        $bonus = $data['bonus'] ?? 0;
        $deduction = $data['deduction'] ?? 0;
        $reimb = $data['reimbursement'] ?? 0;

        $total = ($base + $allowance + $bonus + $reimb) - $deduction;
        $disburseNow = $request->boolean('disburse_now', true);
        $accountId = $data['account_id'] ?? null;
        $account = $accountId ? FinancialAccount::find($accountId) : FinancialAccount::first();

        DB::transaction(function () use ($data, $base, $allowance, $bonus, $deduction, $reimb, $total, $disburseNow, $account) {
            $payroll = Payroll::create([
                'user_id' => $data['user_id'],
                'period' => $data['period'],
                'base_salary' => $base,
                'allowance' => $allowance,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'reimbursement' => $reimb,
                'total' => $total,
                'payment_status' => $disburseNow ? 'paid' : 'approved',
                'paid_at' => $disburseNow ? Carbon::now() : null,
                'notes' => $data['notes'] ?? null,
            ]);

            AuditLogger::log('create', 'Payroll', $payroll->id, null, ['user_id' => $payroll->user_id, 'total' => $total], "Payroll generated for user #{$payroll->user_id} period {$payroll->period}");

            if ($disburseNow) {
                // Record in Expense
                $expense = Expense::create([
                    'expense_number' => 'EXP-PAY-' . strtoupper(uniqid()),
                    'date' => Carbon::now()->toDateString(),
                    'category' => 'salary',
                    'amount' => $payroll->total,
                    'account_id' => $account ? $account->id : null,
                    'vendor' => $payroll->user ? $payroll->user->name : 'Employee',
                    'notes' => "Salary payment for " . ($payroll->user ? $payroll->user->name : 'Employee') . " period {$payroll->period}",
                ]);

                // Record in Ledger
                $this->ledgerService->recordTransaction([
                    'transaction_type' => 'EXPENSE',
                    'category' => 'salary',
                    'amount' => $payroll->total,
                    'transaction_date' => Carbon::now()->toDateString(),
                    'financial_account_id' => $account ? $account->id : null,
                    'employee_id' => $payroll->user_id,
                    'reference_type' => 'payroll',
                    'reference_id' => $payroll->id,
                    'description' => "Salary payment for " . ($payroll->user ? $payroll->user->name : 'Employee') . " period {$payroll->period}",
                    'status' => 'posted',
                ]);

                if ($payroll->user_id) {
                    Notification::create([
                        'user_id' => $payroll->user_id,
                        'type' => 'payroll',
                        'title' => 'Gaji Telah Dicairkan',
                        'message' => 'Gaji periode ' . $payroll->period . ' sebesar Rp ' . number_format($payroll->total, 0, ',', '.') . ' telah dicairkan.',
                        'action_url' => route('dashboard'),
                        'icon' => 'banknotes',
                        'level' => 'success',
                        'is_read' => false,
                    ]);
                }
            }
        });

        $msg = $disburseNow
            ? 'Payroll berhasil dihitung dan langsung dicairkan (Beban gaji dicatat, profit & saldo rekening terpotong).'
            : 'Payroll berhasil dihitung dan disetujui (Menunggu pencairan).';

        return back()->with('success', $msg);
    }

    public function payPayroll(Request $request, Payroll $payroll)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        if ($payroll->payment_status === 'paid') {
            return back()->with('error', 'Payroll ini sudah dicairkan sebelumnya.');
        }

        $accountId = $request->input('account_id');
        $account = $accountId ? FinancialAccount::find($accountId) : FinancialAccount::first();

        DB::transaction(function () use ($payroll, $account) {
            $payroll->update([
                'payment_status' => 'paid',
                'paid_at' => Carbon::now(),
            ]);

            // Record in Expense
            $expense = Expense::create([
                'expense_number' => 'EXP-PAY-' . strtoupper(uniqid()),
                'date' => Carbon::now()->toDateString(),
                'category' => 'salary',
                'amount' => $payroll->total,
                'account_id' => $account ? $account->id : null,
                'vendor' => $payroll->user ? $payroll->user->name : 'Employee',
                'notes' => "Salary payment for " . ($payroll->user ? $payroll->user->name : 'Employee') . " period {$payroll->period}",
            ]);

            // Record in Ledger
            $this->ledgerService->recordTransaction([
                'transaction_type' => 'EXPENSE',
                'category' => 'salary',
                'amount' => $payroll->total,
                'transaction_date' => Carbon::now()->toDateString(),
                'financial_account_id' => $account ? $account->id : null,
                'employee_id' => $payroll->user_id,
                'reference_type' => 'payroll',
                'reference_id' => $payroll->id,
                'description' => "Salary payment for " . ($payroll->user ? $payroll->user->name : 'Employee') . " period {$payroll->period}",
                'status' => 'posted',
            ]);

            if ($payroll->user_id) {
                Notification::create([
                    'user_id' => $payroll->user_id,
                    'type' => 'payroll',
                    'title' => 'Gaji Telah Dicairkan',
                    'message' => 'Gaji periode ' . $payroll->period . ' sebesar Rp ' . number_format($payroll->total, 0, ',', '.') . ' telah dicairkan.',
                    'action_url' => route('dashboard'),
                    'icon' => 'banknotes',
                    'level' => 'success',
                    'is_read' => false,
                ]);
            }
        });

        return back()->with('success', "Gaji untuk " . ($payroll->user ? $payroll->user->name : 'karyawan') . " berhasil dicairkan dan dicatat sebagai pengeluaran.");
    }

    public function reimbursements()
    {
        $user = Auth::user();
        $query = Reimbursement::with('user', 'project', 'approver');

        if (!$user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        }

        $reimbursements = $query->orderByDesc('date')->paginate(20);
        $projects = Project::where('status', 'active')->get();

        return view('finance.reimbursements', compact('reimbursements', 'projects'));
    }

    public function storeReimbursement(Request $request)
    {
        foreach (['project_id', 'notes'] as $field) {
            if (!$request->filled($field)) {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'category' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $data['user_id'] = Auth::id();
        $data['status'] = 'submitted';

        $reimb = Reimbursement::create($data);
        AuditLogger::log('create', 'Reimbursement', $reimb->id, null, $reimb->toArray(), "Reimbursement submitted by " . Auth::user()->name);

        return back()->with('success', 'Reimbursement submitted for Super Admin review.');
    }

    public function approveReimbursement(Request $request, Reimbursement $reimbursement)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Only Super Admin can approve reimbursements.');
        }

        $status = $request->input('action') === 'approve' ? 'approved' : 'rejected';
        $reimbursement->update([
            'status' => $status,
            'approver_id' => Auth::id(),
        ]);

        AuditLogger::log($status, 'Reimbursement', $reimbursement->id, null, ['status' => $status], "Reimbursement #{$reimbursement->id} {$status}");
        return back()->with('success', "Reimbursement marked as {$status}.");
    }

    /* -------------------------------------------------------------------------
     * 10. PROJECT FINANCE & LABOR COST
     * ------------------------------------------------------------------------- */
    public function projectFinance(Request $request)
    {
        $this->authorizeFinance();

        $projects = Project::with('client')->orderByDesc('created_at')->get();
        $projectSnapshots = [];

        foreach ($projects as $project) {
            $projectSnapshots[] = $this->ledgerService->getProjectFinancialSnapshot($project->id);
        }

        return view('finance.project_finance', compact('projectSnapshots'));
    }

    /* -------------------------------------------------------------------------
     * 11. RECURRING COST (MRC / ARC)
     * ------------------------------------------------------------------------- */
    public function recurringCost(Request $request)
    {
        $this->authorizeFinance();

        $recurring = $this->ledgerService->calculateRecurringCosts();
        $infrastructures = Infrastructure::where('status', 'active')->get();
        $subscriptions = Subscription::where('status', 'active')->get();

        return view('finance.recurring_cost', compact('recurring', 'infrastructures', 'subscriptions'));
    }

    /* -------------------------------------------------------------------------
     * 12. ASSET FINANCE
     * ------------------------------------------------------------------------- */
    public function assetFinance(Request $request)
    {
        $this->authorizeFinance();

        $assets = Asset::with('resource')->get();
        $totalPurchaseCost = (float) $assets->sum('purchase_cost');
        $totalMaintenanceCost = (float) $assets->sum('maintenance_cost');
        $totalAccumulatedCost = (float) $assets->sum('accumulated_cost');

        return view('finance.asset_finance', compact('assets', 'totalPurchaseCost', 'totalMaintenanceCost', 'totalAccumulatedCost'));
    }

    /* -------------------------------------------------------------------------
     * 13. FINANCIAL REPORTS & CSV EXPORT
     * ------------------------------------------------------------------------- */
    public function reports(Request $request)
    {
        $this->authorizeFinance();

        $metrics = $this->ledgerService->getCommandCenterMetrics();
        $incomes = Income::with('client', 'project', 'account')->orderByDesc('date')->take(10)->get();
        $expenses = Expense::with('project', 'account')->orderByDesc('date')->take(10)->get();
        $aging = $this->ledgerService->getReceivablesAging();
        $budgets = Budget::with('project')->get();

        return view('finance.reports', compact('metrics', 'incomes', 'expenses', 'aging', 'budgets'));
    }

    public function exportReport(Request $request)
    {
        $this->authorizeFinance();

        $type = $request->get('type', 'transactions');
        $filename = "financial_{$type}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'transactions') {
                fputcsv($handle, ['Code', 'Type', 'Category', 'Amount', 'Date', 'Account', 'Project', 'Description', 'Status']);
                $records = FinancialTransaction::with('account', 'project')->get();
                foreach ($records as $r) {
                    fputcsv($handle, [
                        $r->transaction_code,
                        $r->transaction_type,
                        $r->category,
                        $r->amount,
                        $r->transaction_date ? $r->transaction_date->toDateString() : '',
                        $r->account->account_name ?? '',
                        $r->project->title ?? '',
                        $r->description,
                        $r->status,
                    ]);
                }
            } elseif ($type === 'incomes') {
                fputcsv($handle, ['Number', 'Date', 'Source', 'Client', 'Project', 'Amount', 'Category', 'Account']);
                $records = Income::with('client', 'project', 'account')->get();
                foreach ($records as $r) {
                    fputcsv($handle, [
                        $r->income_number,
                        $r->date ? $r->date->toDateString() : '',
                        $r->source,
                        $r->client->company_name ?? '',
                        $r->project->title ?? '',
                        $r->amount,
                        $r->category,
                        $r->account->account_name ?? '',
                    ]);
                }
            } else {
                fputcsv($handle, ['Number', 'Date', 'Category', 'Vendor', 'Project', 'Amount', 'Account', 'Notes']);
                $records = Expense::with('project', 'account')->get();
                foreach ($records as $r) {
                    fputcsv($handle, [
                        $r->expense_number,
                        $r->date ? $r->date->toDateString() : '',
                        $r->category,
                        $r->vendor,
                        $r->project->title ?? '',
                        $r->amount,
                        $r->account->account_name ?? '',
                        $r->notes,
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* -------------------------------------------------------------------------
     * INTERNAL HELPER
     * ------------------------------------------------------------------------- */
    private function authorizeFinance(): void
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized. Financial lifecycle management is restricted to Super Admin.');
        }
    }
}
