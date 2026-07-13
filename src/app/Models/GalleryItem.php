<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    protected $fillable = ['user_id', 'title', 'caption', 'image_path', 'taken_at'];
    protected $casts = ['taken_at' => 'date'];

    protected $appends = ['image_url'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getImageUrlAttribute(): string
    {
        if (!$this->image_path || str_starts_with($this->image_path, 'http')) {
            return $this->image_path
                ? $this->image_path
                : asset('images/HCMBlue.svg');
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
