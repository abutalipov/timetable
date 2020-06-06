<?php

namespace App\Models;

use App\Models\AbstractModel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends AbstractModel implements AuthenticatableContract

{
    use Authenticatable;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'email', 'password','first_name','second_name','patronymic',
    ];
    /**
     * Устанавливается в JWTGuard после валидации JWT токена.
     *
     * @var UserSession
     */
    public $currentSession = null;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return HasMany
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }
    public function userSessions()
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * @return UserSession
     */
    public function getCurrentSession(): UserSession
    {
        return $this->currentSession;
    }

    /**
     * @param UserSession $currentSession
     */
    public function setCurrentSession(UserSession $currentSession): void
    {
        $this->currentSession = $currentSession;
    }
}
