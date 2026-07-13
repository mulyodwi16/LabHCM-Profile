<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectImage extends Model
{
    protected $fillable = ['project_id', 'path', 'sort_order'];

    protected $appends = ['url'];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }

    public function getUrlAttribute(): string
    {
        if (!$this->path || $this->path === '0' || str_starts_with($this->path, 'http')) {
            return str_starts_with((string) $this->path, 'http')
                ? $this->path
                : asset('images/HCMBlue.svg');
        }

        return Storage::disk('public')->url($this->path);
    }
}
