<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Enhance financial_accounts table with additional control fields
        Schema::table('financial_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('financial_accounts', 'account_type')) {
                $table->string('account_type')->default('BANK')->after('account_name');
            }
            if (!Schema::hasColumn('financial_accounts', 'provider')) {
                $table->string('provider')->nullable()->after('account_type');
            }
            if (!Schema::hasColumn('financial_accounts', 'owner')) {
                $table->string('owner')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('financial_accounts', 'opening_balance')) {
                $table->decimal('opening_balance', 15, 2)->default(0)->after('balance');
            }
            if (!Schema::hasColumn('financial_accounts', 'currency')) {
                $table->string('currency', 10)->default('IDR')->after('opening_balance');
            }
            if (!Schema::hasColumn('financial_accounts', 'description')) {
                $table->text('description')->nullable()->after('currency');
            }
            if (!Schema::hasColumn('financial_accounts', 'status')) {
                $table->string('status')->default('active')->after('is_active');
            }
        });

        // 2. Create Master Financial Transaction Ledger (Single Source of Truth)
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique()->index();
            $table->string('transaction_type')->index(); // INCOME, EXPENSE, TRANSFER, CAPITAL_IN, CAPITAL_OUT, RECEIVABLE, PAYABLE, REFUND, OPENING_BALANCE, ADJUSTMENT
            $table->string('category')->index()->default('general');
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date')->index();
            $table->foreignId('financial_account_id')->nullable()->constrained('financial_accounts')->nullOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('financial_accounts')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->string('reference_type')->nullable(); // invoice, expense, payroll, reimbursement, payable, capital, manual
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('status')->default('posted')->index(); // draft, submitted, approved, posted, reversed
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Create Payables (Hutang Usaha)
        Schema::create('payables', function (Blueprint $table) {
            $table->id();
            $table->string('payable_code')->unique()->index();
            $table->string('title');
            $table->string('vendor_name');
            $table->string('creditor_type')->default('vendor'); // vendor, freelancer, supplier, service_provider, other
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('category')->default('operational');
            $table->decimal('amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('issue_date');
            $table->date('due_date')->index();
            $table->string('status')->default('pending')->index(); // pending, partial, paid, overdue, cancelled
            $table->foreignId('financial_account_id')->nullable()->constrained('financial_accounts')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 4. Create Invoice Payments (Realisasi Pembayaran Faktur Klien)
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique()->index();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('financial_account_id')->constrained('financial_accounts')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date')->index();
            $table->string('payment_method')->default('bank_transfer'); // bank_transfer, cash, e_wallet, gateway, other
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('payables');
        Schema::dropIfExists('financial_transactions');

        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'provider', 'owner', 'opening_balance', 'currency', 'description', 'status']);
        });
    }
};
