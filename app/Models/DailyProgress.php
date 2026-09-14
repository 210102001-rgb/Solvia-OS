<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyProgress extends Model
{
    protected $table = 'daily_progress';

    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'date',
        'progress',
        'completed_work',
        'next_plan',
        'blocker',
        'working_hours',
        'attachment_path',
    ];

    protected $casts = [
        'date' => 'date',
        'progress' => 'integer',
        'working_hours' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
