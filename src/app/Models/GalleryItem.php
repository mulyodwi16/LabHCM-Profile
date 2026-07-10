<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    protected $fillable = ['user_id', 'title', 'caption', 'image_path', 'taken_at'];
    protected $casts = ['taken_at' => 'date'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getImageUrlAttribute(): string
    {
        return str_starts_with($this->image_path, 'http') ? $this->image_path : Storage::url($this->image_path);
    }
}
