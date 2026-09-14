<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('resource_code')->unique();
            $table->string('name');
            $table->string('category'); // asset, infrastructure, account, subscription, license, contract, inventory, service
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->decimal('cost', 15, 2)->default(0);
            $table->date('purchase_date')->nullable();
            $table->string('status')->default('active'); // active, available, in_use, maintenance, expiring_soon, expired, retired, disposed
            $table->string('location')->nullable();
            $table->date('relevant_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('document_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->string('asset_tag')->unique();
            $table->string('brand');
            $table->string('model');
            $table->string('serial_number')->nullable();
            $table->text('specs')->nullable();
            $table->string('condition')->default('good'); // excellent, good, fair, damaged
            $table->string('lifecycle_status')->default('available'); // purchased, available, assigned, in_use, maintenance, returned, retired, disposed
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('upgrade_cost', 15, 2)->default(0);
            $table->decimal('maintenance_cost', 15, 2)->default(0);
            $table->decimal('repair_cost', 15, 2)->default(0);
            $table->decimal('accumulated_cost', 15, 2)->default(0);
            $table->foreignId('current_holder_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('assigned_date');
            $table->date('returned_date')->nullable();
            $table->string('condition_on_assignment')->default('good');
            $table->string('condition_on_return')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('provider');
            $table->date('start_date');
            $table->date('expiry_date');
            $table->string('document_path')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('maintenance_type'); // preventative, repair, upgrade, inspection
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->string('technician_vendor')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, cancelled
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('infrastructures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->string('type'); // vps, hosting, domain, ssl, cloud, other
            $table->string('provider');
            $table->string('name');
            $table->string('hostname')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('os')->nullable();
            $table->text('specs')->nullable();
            $table->string('location')->nullable();
            $table->string('environment')->default('production'); // development, staging, production
            $table->text('purpose')->nullable();
            $table->text('encrypted_credentials')->nullable();
            $table->string('billing_account')->nullable();
            $table->decimal('monthly_cost', 15, 2)->default(0);
            $table->decimal('yearly_cost', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('next_billing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('auto_renewal')->default(false);
            $table->string('status')->default('active'); // active, expiring_soon, expired, suspended
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->string('provider');
            $table->string('plan_name');
            $table->decimal('cost', 15, 2);
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly, quarterly
            $table->date('next_billing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('auto_renewal')->default(true);
            $table->unsignedInteger('max_users')->default(1);
            $table->string('status')->default('active'); // active, billing_due, paid, renewed, cancelled
            $table->timestamps();
        });

        Schema::create('company_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->string('platform');
            $table->string('account_identifier');
            $table->text('encrypted_credentials');
            $table->boolean('two_factor_status')->default(false);
            $table->text('recovery_method')->nullable();
            $table->string('status')->default('active'); // active, suspended, archived
            $table->timestamps();
        });

        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->string('software_name');
            $table->text('license_key_encrypted');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('device_name')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('status')->default('active'); // active, expired, revoked
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->string('contract_number');
            $table->string('party_name');
            $table->string('party_type')->default('client'); // client, vendor, partner
            $table->date('start_date');
            $table->date('expiry_date');
            $table->decimal('contract_value', 15, 2)->default(0);
            $table->string('document_path')->nullable();
            $table->string('status')->default('active'); // draft, active, expiring_soon, expired, terminated
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('licenses');
        Schema::dropIfExists('company_accounts');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('infrastructures');
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('asset_assignments');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('resources');
    }
};
