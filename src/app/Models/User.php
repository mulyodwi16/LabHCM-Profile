<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public function profile(): HasOne { return $this->hasOne(Profile::class); }
    public function projects(): HasMany { return $this->hasMany(Project::class); }
    public function galleryItems(): HasMany { return $this->hasMany(GalleryItem::class); }

    public function isAdmin(): bool { return $this->hasRole('admin'); }
    public function isAlumni(): bool { return $this->hasRole('alumni'); }
    public function isMember(): bool { return $this->hasRole('member'); }
}
