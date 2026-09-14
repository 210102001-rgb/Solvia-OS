<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('name');
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->string('project_type')->default('Software Development');
            $table->date('start_date');
            $table->date('deadline');
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('budget', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->decimal('profit_margin', 5, 2)->default(0);
            $table->string('status')->default('planning'); // planning, active, on_hold, completed, cancelled
            $table->string('health')->default('on_track'); // on_track, at_risk, off_track
            $table->timestamp('closed_at')->nullable();
            $table->text('closure_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role'); // Content Creator, Designer, Frontend Developer, Backend Developer, IoT Engineer, etc.
            $table->text('responsibility')->nullable();
            $table->date('assigned_date');
            $table->string('status')->default('active'); // active, released
            $table->timestamps();
        });

        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('deadline');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('milestones')->nullOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('to_do'); // backlog, to_do, in_progress, waiting_review, revision, blocked, done, cancelled
            $table->unsignedTinyInteger('progress')->default(0);
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable();
            $table->decimal('estimated_hours', 8, 2)->default(0);
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('depends_on_task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('type')->default('blocked_by'); // blocked_by, blocks
            $table->timestamps();
        });

        Schema::create('daily_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->text('completed_work');
            $table->text('next_plan');
            $table->text('blocker')->nullable();
            $table->decimal('working_hours', 5, 2)->default(8.00);
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        Schema::create('blockers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('priority')->default('high'); // low, medium, high, urgent
            $table->string('type')->default('technical'); // technical, resource, dependency, client, other
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('open'); // open, in_progress, resolved, closed
            $table->timestamp('resolved_date')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->json('mentions')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('blockers');
        Schema::dropIfExists('daily_progress');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};
