<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function doctor() : HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    

    // public function assignRole($role)
    // {
    //     if (is_string($role)) {
    //         $role = Role::where('name', $role)->firstOrFail();
    //     }

    //     if (!$this->roles->contains($role->id)) {
    //         $this->roles()->attach($role);
    //     }
    // }

    // public function roles(): BelongsToMany
    // {
    //     return $this->belongsToMany(Role::class, 'user_roles');
    // }

    // public function hasRole(string $roleName): bool
    // {
    //     return $this->roles->contains('name', $roleName);
    // }
}
