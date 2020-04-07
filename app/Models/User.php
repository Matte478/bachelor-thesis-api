<?php

namespace App\Models;

use App\Observers\UserObserver;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'typeable_id', 'typeable_type',
//        'company_id', 'restaurant_id'
    ];

    protected $appends = [
        'type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getTypeAttribute(): string
    {
        $typeable = explode("\\", $this->typeable_type);
        $type = strtolower(end($typeable));
        return $type;
    }

    public function typeable(): morphTo
    {
        return $this->morphTo();
    }

    public static function boot(): void
    {
        parent::boot();
        User::observe(new UserObserver());
    }
}
