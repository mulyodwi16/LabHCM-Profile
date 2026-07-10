<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectImage extends Model
{
    protected $fillable = ['project_id', 'path', 'sort_order'];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }

    public function getUrlAttribute(): string
    {
        return str_starts_with($this->path, 'http') ? $this->path : Storage::url($this->path);
    }
}
