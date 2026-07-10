<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
    protected $fillable = [
        'user_id', 'photo_path', 'nrp', 'nip', 'prodi', 'angkatan', 'phone',
        'bio', 'skills', 'youtube_url', 'github_url', 'portfolio_url',
    ];
    protected $casts = ['skills' => 'array', 'angkatan' => 'integer'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function documents(): HasMany { return $this->hasMany(Document::class); }

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo_path
            ? Storage::url($this->photo_path)
            : 'https://ui-avatars.com/api/?background=1e3a8a&color=fff&name=' . urlencode($this->user->name ?? 'User');
    }
}
