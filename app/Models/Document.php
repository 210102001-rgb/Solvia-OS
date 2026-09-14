<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    protected $fillable = [
        'title',
        'category',
        'file_path',
        'file_size',
        'file_type',
        'uploader_id',
        'documentable_type',
        'documentable_id',
        'is_restricted',
    ];

    protected $casts = [
        'is_restricted' => 'boolean',
        'file_size' => 'integer',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
