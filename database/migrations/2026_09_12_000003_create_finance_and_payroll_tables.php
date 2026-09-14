<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique();
            $table->string('account_name');
            $table->string('type')->default('bank'); // bank, cash, e_wallet
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('income_number')->unique();
            $table->date('date');
            $table->string('source');
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('category')->default('Client Project Payment');
            $table->foreignId('account_id')->constrained('financial_accounts')->cascadeOnDelete();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->date('date');
            $table->string('category'); // salary, infrastructure, software, hosting, domain, marketing, equipment, project, transportation, operational, other
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->foreignId('account_id')->constrained('financial_accounts')->cascadeOnDelete();
            $table->string('vendor')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('payment_status')->default('draft'); // draft, sent, partial, paid, overdue, cancelled
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('period', 7); // YYYY-MM
            $table->decimal('base_salary', 15, 2);
            $table->decimal('allowance', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('deduction', 15, 2)->default(0);
            $table->decimal('reimbursement', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('payment_status')->default('draft'); // draft, calculated, reviewed, approved, paid
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('reimbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('category')->default('operational');
            $table->string('status')->default('submitted'); // submitted, in_review, approved, rejected, paid
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type')->default('company'); // company, project, category
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('category')->nullable();
            $table->unsignedSmallInteger('month')->nullable();
            $table->unsignedSmallInteger('year');
            $table->decimal('budgeted_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('reimbursements');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('financial_accounts');
    }
};
