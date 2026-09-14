<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'department',
        'team_id',
        'join_date',
        'status',
        'avatar_url',
        'profile_bio',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'join_date' => 'date',
            'permissions' => 'array',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $userPerms = $this->permissions ?? [];
        if (in_array($permission, $userPerms, true)) {
            return true;
        }

        // Standard operational role defaults
        $roleDefaults = [
            'content_creator' => ['project.view', 'project.create', 'task.view', 'task.create', 'task.update', 'milestone.create', 'progress.create', 'blocker.create', 'reimbursement.create', 'announcement.view', 'asset.view', 'asset.create', 'report.view'],
            'designer' => ['project.view', 'project.create', 'task.view', 'task.create', 'task.update', 'milestone.create', 'progress.create', 'blocker.create', 'reimbursement.create', 'announcement.view', 'asset.view', 'asset.create'],
            'frontend_developer' => ['project.view', 'project.create', 'task.view', 'task.create', 'task.update', 'milestone.create', 'progress.create', 'blocker.create', 'reimbursement.create', 'announcement.view', 'asset.view', 'asset.create'],
            'backend_developer' => ['project.view', 'project.create', 'task.view', 'task.create', 'task.update', 'milestone.create', 'progress.create', 'blocker.create', 'reimbursement.create', 'announcement.view', 'asset.view', 'asset.create'],
            'iot_engineer' => ['project.view', 'project.create', 'task.view', 'task.create', 'task.update', 'milestone.create', 'progress.create', 'blocker.create', 'reimbursement.create', 'announcement.view', 'asset.view', 'asset.create'],
            'viewer' => ['project.view', 'task.view', 'announcement.view', 'asset.view'],
        ];

        return in_array($permission, $roleDefaults[$this->role] ?? [], true);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function projectMemberships(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function dailyProgress(): HasMany
    {
        return $this->hasMany(DailyProgress::class);
    }

    public function reportedBlockers(): HasMany
    {
        return $this->hasMany(Blocker::class, 'reporter_id');
    }

    public function assignedAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'current_holder_id');
    }

    public function reimbursements(): HasMany
    {
        return $this->hasMany(Reimbursement::class);
    }

    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->where('is_read', false)->orderByDesc('created_at');
    }
}
