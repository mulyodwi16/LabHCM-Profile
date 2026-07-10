<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = ['profile_id', 'label', 'file_path'];

    public function profile(): BelongsTo { return $this->belongsTo(Profile::class); }
}
