<?php

namespace App\Models;



class Token extends AbstractModel
{
    protected $guarded = [];

    /**
     * Получаем токен по уникальному хэшу и user_uid
     *
     * @deprecated
     * @param $app
     * @param $userUid
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function findUserTokens($app, $userUid)
    {
        return (new self())
            ->where('user_id', $userUid)
            ->where('app', $app)
            ->first();
    }

    /**
     * Получаем токен по уникальному хэшу
     *
     * @param $app
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function findUserTokenByAppHash($app)
    {
        return (new self())
            ->where('app', $app)
            ->first();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userSession()
    {
        return $this->hasOne(UserSession::class);
    }
}
