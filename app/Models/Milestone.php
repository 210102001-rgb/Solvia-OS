<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Milestone extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'description',
        'start_date',
        'deadline',
        'progress',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'progress' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function recalculateProgress(): void
    {
        $avg = $this->tasks()->avg('progress') ?? 0;
        $this->progress = (int) round($avg);
        if ($this->progress >= 100) {
            $this->status = 'completed';
        } elseif ($this->progress > 0) {
            $this->status = 'in_progress';
        }
        $this->save();
    }
}
