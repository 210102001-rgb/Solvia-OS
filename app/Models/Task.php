<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'milestone_id',
        'assignee_id',
        'title',
        'description',
        'priority',
        'status',
        'progress',
        'start_date',
        'deadline',
        'estimated_hours',
        'actual_hours',
        'attachment_path',
    ];

    protected $casts = [
        'progress' => 'integer',
        'start_date' => 'date',
        'deadline' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function blockedBy(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id');
    }

    public function blocking(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id');
    }

    public function dailyProgress(): HasMany
    {
        return $this->hasMany(DailyProgress::class);
    }

    public function blockers(): HasMany
    {
        return $this->hasMany(Blocker::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function checkDependencyStatus(): void
    {
        $hasIncompletePredecessor = $this->blockedBy()->where('status', '!=', 'done')->exists();
        if ($hasIncompletePredecessor && $this->status !== 'done') {
            $this->status = 'blocked';
            $this->save();
        } elseif (!$hasIncompletePredecessor && $this->status === 'blocked') {
            $this->status = 'to_do';
            $this->save();
        }
    }
}
