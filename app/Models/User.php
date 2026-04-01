<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    public function cabang(): HasOne
    {
        return $this->hasOne(Cabang::class);
    }

    public function tutor(): HasOne
    {
        return $this->hasOne(Tutor::class);
    }

    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class);
    }

    public function salariesCreated(): HasMany
    {
        return $this->hasMany(Salary::class, 'created_by');
    }

    /**
     * Notifikasi in-app (lonceng header), bukan Laravel database notifications channel.
     *
     * @return HasMany<UserNotification, $this>
     */
    public function bellNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'user_id')->latest();
    }
}
