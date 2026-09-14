<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('item_name');
            $table->string('category')->default('Hardware');
            $table->string('unit')->default('pcs');
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(5);
            $table->string('location')->nullable();
            $table->string('supplier')->nullable();
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->string('status')->default('in_stock'); // in_stock, low_stock, out_of_stock
            $table->timestamps();
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('transaction_type'); // stock_in, stock_out, adjustment, transfer, usage, return
            $table->integer('quantity');
            $table->integer('balance_after');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->string('item_name');
            $table->string('category'); // asset, inventory, infrastructure, subscription, operational, other
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('estimated_cost', 15, 2);
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->text('reason');
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('submitted'); // submitted, in_review, approved, rejected, purchased, received, registered
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('created_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignId('created_inventory_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
    }
};
