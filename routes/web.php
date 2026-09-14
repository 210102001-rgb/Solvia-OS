<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\BlockerController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DailyProgressController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Solvia.Nova OS Routes
Route::middleware('auth')->group(function () {
    // Command Center & Operational Dashboards
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Personal User Profile (Accessible by all roles, role preserved)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Global Spotlight Search API (Ctrl+K)
    Route::get('/api/search', [SearchController::class, 'search'])->name('api.search');

    // Company Management
    Route::prefix('company')->name('company.')->group(function () {
        Route::get('/profile', [CompanyController::class, 'profile'])->name('profile');
        Route::post('/profile', [CompanyController::class, 'updateProfile'])->name('profile.update');
        Route::get('/users', [CompanyController::class, 'users'])->name('users');
        Route::post('/users', [CompanyController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [CompanyController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [CompanyController::class, 'destroyUser'])->name('users.destroy');
        Route::patch('/users/{user}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('users.toggleStatus');
        Route::get('/teams', [CompanyController::class, 'teams'])->name('teams');
        Route::post('/teams', [CompanyController::class, 'storeTeam'])->name('teams.store');
        Route::delete('/teams/{team}', [CompanyController::class, 'destroyTeam'])->name('teams.destroy');
        Route::post('/teams/{team}/members', [CompanyController::class, 'addTeamMember'])->name('teams.members.add');
        Route::delete('/teams/{team}/members/{user}', [CompanyController::class, 'removeTeamMember'])->name('teams.members.remove');
        Route::get('/clients', [CompanyController::class, 'clients'])->name('clients');
        Route::post('/clients', [CompanyController::class, 'storeClient'])->name('clients.store');
        Route::put('/clients/{client}', [CompanyController::class, 'updateClient'])->name('clients.update');
        Route::delete('/clients/{client}', [CompanyController::class, 'destroyClient'])->name('clients.destroy');
    });

    // Projects & Tasks
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::post('/{project}/members', [ProjectController::class, 'addMember'])->name('members.store');
        Route::post('/{project}/milestones', [ProjectController::class, 'storeMilestone'])->name('milestones.store');
        Route::post('/{project}/tasks', [ProjectController::class, 'storeTask'])->name('tasks.store');
        Route::post('/{project}/close', [ProjectController::class, 'closeProject'])->name('close');
        Route::post('/{project}/comments', [ProjectController::class, 'storeComment'])->name('comments.store');
    });
    Route::post('/tasks/{task}/status', [ProjectController::class, 'updateTaskStatus'])->name('tasks.status.update');
    Route::post('/tasks/{task}/claim', [ProjectController::class, 'claimTask'])->name('tasks.claim');

    // Daily Progress & Blockers
    Route::prefix('progress')->name('progress.')->group(function () {
        Route::get('/', [DailyProgressController::class, 'index'])->name('index');
        Route::post('/', [DailyProgressController::class, 'store'])->name('store');
    });

    Route::prefix('blockers')->name('blockers.')->group(function () {
        Route::get('/', [BlockerController::class, 'index'])->name('index');
        Route::post('/', [BlockerController::class, 'store'])->name('store');
        Route::post('/{blocker}/resolve', [BlockerController::class, 'resolve'])->name('resolve');
    });

    // Financial Control System (Finance Hub)
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'dashboard'])->name('dashboard');

        // 1. Financial Accounts
        Route::get('/accounts', [FinanceController::class, 'accounts'])->name('accounts');
        Route::post('/accounts', [FinanceController::class, 'storeAccount'])->name('accounts.store');
        Route::post('/accounts/transfer', [FinanceController::class, 'transferAccount'])->name('accounts.transfer');
        Route::post('/accounts/adjust', [FinanceController::class, 'adjustAccount'])->name('accounts.adjust');

        // 2. Transactions (Master Ledger)
        Route::get('/transactions', [FinanceController::class, 'transactions'])->name('transactions');
        Route::post('/transactions', [FinanceController::class, 'storeTransaction'])->name('transactions.store');

        // 3. Revenue
        Route::get('/revenue', [FinanceController::class, 'revenue'])->name('revenue');
        Route::get('/incomes', [FinanceController::class, 'incomes'])->name('incomes');
        Route::post('/incomes', [FinanceController::class, 'storeIncome'])->name('incomes.store');

        // 4. Expenses
        Route::get('/expenses', [FinanceController::class, 'expenses'])->name('expenses');
        Route::post('/expenses', [FinanceController::class, 'storeExpense'])->name('expenses.store');

        // 5. Invoices & Payments
        Route::get('/invoices', [FinanceController::class, 'invoices'])->name('invoices');
        Route::post('/invoices', [FinanceController::class, 'storeInvoice'])->name('invoices.store');
        Route::post('/invoices/{invoice}/pay', [FinanceController::class, 'markInvoicePaid'])->name('invoices.pay');
        Route::get('/payments', [FinanceController::class, 'payments'])->name('payments');
        Route::post('/payments', [FinanceController::class, 'storePayment'])->name('payments.store');

        // 6. Receivables & Payables
        Route::get('/receivables', [FinanceController::class, 'receivables'])->name('receivables');
        Route::get('/payables', [FinanceController::class, 'payables'])->name('payables');
        Route::post('/payables', [FinanceController::class, 'storePayable'])->name('payables.store');
        Route::post('/payables/{payable}/pay', [FinanceController::class, 'payPayable'])->name('payables.pay');

        // 7. Cashflow & Budgets
        Route::get('/cashflow', [FinanceController::class, 'cashflow'])->name('cashflow');
        Route::get('/budgets', [FinanceController::class, 'budgets'])->name('budgets');
        Route::post('/budgets', [FinanceController::class, 'storeBudget'])->name('budgets.store');

        // 8. Payroll & Reimbursements
        Route::get('/payroll', [FinanceController::class, 'payroll'])->name('payroll');
        Route::post('/payroll', [FinanceController::class, 'storePayroll'])->name('payroll.store');
        Route::post('/payroll/{payroll}/pay', [FinanceController::class, 'payPayroll'])->name('payroll.pay');
        Route::get('/reimbursements', [FinanceController::class, 'reimbursements'])->name('reimbursements');
        Route::post('/reimbursements', [FinanceController::class, 'storeReimbursement'])->name('reimbursements.store');
        Route::post('/reimbursements/{reimbursement}/approve', [FinanceController::class, 'approveReimbursement'])->name('reimbursements.approve');

        // 9. Project Finance
        Route::get('/project-finance', [FinanceController::class, 'projectFinance'])->name('project_finance');

        // 10. Recurring Cost
        Route::get('/recurring-cost', [FinanceController::class, 'recurringCost'])->name('recurring_cost');

        // 11. Asset Finance
        Route::get('/asset-finance', [FinanceController::class, 'assetFinance'])->name('asset_finance');

        // 12. Financial Reports
        Route::get('/reports', [FinanceController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [FinanceController::class, 'exportReport'])->name('reports.export');
    });

    // Resource Registry & Infrastructure
    Route::prefix('resources')->name('resources.')->group(function () {
        Route::get('/', [ResourceController::class, 'index'])->name('index');
        Route::get('/assets', [ResourceController::class, 'assets'])->name('assets');
        Route::post('/assets', [ResourceController::class, 'storeAsset'])->name('assets.store');
        Route::post('/assets/{asset}/assign', [ResourceController::class, 'assignAsset'])->name('assets.assign');
        Route::post('/assets/{asset}/return', [ResourceController::class, 'returnAsset'])->name('assets.return');
        Route::post('/assets/{asset}/maintenance', [ResourceController::class, 'logMaintenance'])->name('assets.maintenance');
        Route::get('/infrastructure', [ResourceController::class, 'infrastructure'])->name('infrastructure');
        Route::post('/infrastructure', [ResourceController::class, 'storeInfrastructure'])->name('infrastructure.store');
        Route::post('/infrastructure/{infrastructure}/reveal', [ResourceController::class, 'revealCredential'])->name('infrastructure.reveal');
        Route::get('/subscriptions', [ResourceController::class, 'subscriptions'])->name('subscriptions');
        Route::post('/subscriptions', [ResourceController::class, 'storeSubscription'])->name('subscriptions.store');
        Route::get('/inventory', [ResourceController::class, 'inventory'])->name('inventory');
        Route::post('/inventory', [ResourceController::class, 'storeInventoryItem'])->name('inventory.store');
        Route::post('/inventory/{item}/transaction', [ResourceController::class, 'storeInventoryTransaction'])->name('inventory.transaction');
        Route::get('/contracts', [ResourceController::class, 'contracts'])->name('contracts');
        Route::post('/contracts', [ResourceController::class, 'storeContract'])->name('contracts.store');
        Route::get('/accounts', [ResourceController::class, 'accounts'])->name('accounts');
        Route::post('/accounts', [ResourceController::class, 'storeAccount'])->name('accounts.store');
        Route::get('/licenses', [ResourceController::class, 'licenses'])->name('licenses');
        Route::post('/licenses', [ResourceController::class, 'storeLicense'])->name('licenses.store');
    });

    // Purchasing & Approvals
    Route::prefix('purchasing')->name('purchasing.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::post('/{purchaseRequest}/approve', [PurchaseController::class, 'approve'])->name('approve');
        Route::post('/{purchaseRequest}/reject', [PurchaseController::class, 'reject'])->name('reject');
        Route::post('/{purchaseRequest}/purchased', [PurchaseController::class, 'markPurchased'])->name('purchased');
        Route::post('/{purchaseRequest}/receive', [PurchaseController::class, 'receive'])->name('receive');
    });

    // Schedule & Calendar
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::post('/reminders', [ScheduleController::class, 'triggerReminders'])->name('reminders');
        Route::post('/trigger-reminders', [ScheduleController::class, 'triggerReminders'])->name('triggerReminders');
    });

    // Communication
    Route::get('/announcements', [CommunicationController::class, 'announcements'])->name('communication.announcements');
    Route::post('/announcements', [CommunicationController::class, 'storeAnnouncement'])->name('communication.announcements.store');
    Route::post('/announcements/{announcement}/read', [CommunicationController::class, 'markAnnouncementRead'])->name('communication.announcements.read');
    Route::get('/notifications', [CommunicationController::class, 'notifications'])->name('communication.notifications');
    Route::post('/notifications/{notification}/read', [CommunicationController::class, 'markNotificationRead'])->name('communication.notifications.read');
    Route::post('/notifications/read-all', [CommunicationController::class, 'markAllNotificationsRead'])->name('communication.notifications.readAll');

    // Documents & Knowledge Base
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('/knowledge-base', [DocumentController::class, 'knowledgeBase'])->name('kb');
        Route::get('/knowledge-base/{article:slug}', [DocumentController::class, 'showArticle'])->name('kb.show');
        Route::post('/knowledge-base', [DocumentController::class, 'storeArticle'])->name('kb.store');
        Route::delete('/knowledge-base/{article}', [DocumentController::class, 'destroyArticle'])->name('kb.destroy');
    });

    // KB Route Aliases for view compatibility
    Route::get('/kb', [DocumentController::class, 'knowledgeBase'])->name('kb.index');
    Route::get('/kb/{article:slug}', [DocumentController::class, 'showArticle'])->name('kb.show');
    Route::delete('/kb/{article}', [DocumentController::class, 'destroyArticle'])->name('kb.destroy');

    // Automation
    Route::prefix('automation')->name('automation.')->group(function () {
        Route::get('/', [AutomationController::class, 'index'])->name('index');
        Route::post('/{rule}/toggle', [AutomationController::class, 'toggleRule'])->name('toggle');
        Route::post('/run', [AutomationController::class, 'runTrigger'])->name('run');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export', [ReportController::class, 'exportCsv'])->name('export');
    });

    // Audit Trail
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
});
