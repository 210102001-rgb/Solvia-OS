<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->string('category')->default('company'); // company, urgent, event, policy, technical
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('audience_type')->default('all'); // all, team, project, role, specific_user
            $table->string('audience_target')->nullable(); // team_id, project_id, role name, or user_id
            $table->timestamp('publish_date');
            $table->timestamp('expiry_date')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('published'); // draft, scheduled, published, expired, archived
            $table->timestamps();
        });

        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->timestamps();
            $table->unique(['announcement_id', 'user_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->string('icon')->nullable();
            $table->string('level')->default('info'); // info, success, warning, danger
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_event'); // domain_expiring, task_overdue, progress_missing, inventory_low, purchase_approved
            $table->json('condition_config')->nullable();
            $table->json('action_config');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_rule_id')->constrained('automation_rules')->cascadeOnDelete();
            $table->string('trigger_event');
            $table->json('context_data')->nullable();
            $table->string('status')->default('success'); // success, failed
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('general'); // contract, invoice, sop, policy, technical, specification, general
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('file_type')->nullable();
            $table->foreignId('uploader_id')->constrained('users')->cascadeOnDelete();
            $table->nullableMorphs('documentable');
            $table->boolean('is_restricted')->default(false);
            $table->timestamps();
        });

        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->default('sop'); // sop, technical_doc, brand_guideline, faq, tutorial, internal_policy, project_guide, development_guide
            $table->longText('content');
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // create, update, delete, assign, approve, reject, pay, renew, complete, login, logout, credential_access, permission_change
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('knowledge_bases');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('automation_logs');
        Schema::dropIfExists('automation_rules');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcements');
    }
};
