<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = ['user_id', 'title', 'slug', 'description', 'youtube_url', 'github_url', 'published'];
    protected $casts = ['published' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (Project $p) {
            if (!$p->slug || $p->isDirty('title')) $p->slug = Str::slug($p->title) . '-' . Str::random(4);
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function images(): HasMany { return $this->hasMany(ProjectImage::class)->orderBy('sort_order'); }

    public function getRouteKeyName(): string { return 'slug'; }
}
