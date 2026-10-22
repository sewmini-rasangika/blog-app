<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Support\Panel;
use App\Support\Activity;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRole($slug)
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

private ?array $permissionKeys = null;


public function hasPermission(string $permission): bool
{
    if ($this->isAdmin()) {
        return true;  
    }

    $this->permissionKeys ??= Role::where('slug', $this->role)->value('permissions') ?? [];

    return in_array($permission, $this->permissionKeys, true);
}

    public function getRoleNameAttribute()
    {
        return $this->roles->first()->name ?? 'User';
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
protected static function booted(): void
{
    static::creating(function (User $user) {
        if (empty($user->slug)) {
            $user->slug = Panel::uniqueSlug($user->name);
        }
    });
}
   
}
