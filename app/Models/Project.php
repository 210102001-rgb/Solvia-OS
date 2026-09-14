<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    protected $fillable = [
        'project_code',
        'name',
        'client_id',
        'description',
        'project_type',
        'start_date',
        'deadline',
        'revenue',
        'budget',
        'actual_cost',
        'profit',
        'profit_margin',
        'status',
        'health',
        'closed_at',
        'closure_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'closed_at' => 'datetime',
        'revenue' => 'decimal:2',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'profit' => 'decimal:2',
        'profit_margin' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'responsibility', 'assigned_date', 'status')
            ->withTimestamps();
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function dailyProgress(): HasMany
    {
        return $this->hasMany(DailyProgress::class);
    }

    public function blockers(): HasMany
    {
        return $this->hasMany(Blocker::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function getTitleAttribute(): ?string
    {
        return $this->name;
    }

    public function recalculateFinancials(): void
    {
        $this->actual_cost = $this->expenses()->sum('amount');
        $this->profit = $this->revenue - $this->actual_cost;
        $this->profit_margin = $this->revenue > 0 ? round(($this->profit / $this->revenue) * 100, 2) : 0;
        $this->save();
    }
}
