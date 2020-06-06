<?php

namespace App\Models;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends AbstractModel
{
    public $timestamps = false;
    protected $guarded = [];
    protected $searchable = [
        /**
         * Columns and their priority in search results.
         * Columns with higher values are more important.
         * Columns with equal values have equal importance.
         */
        'columns' => [
            'id' => 1,
            'user_id' => 1,
            'login_at' => 5,
            'logout_at' => 5,
        ],
    ];
    protected $dates = ['login_at', 'logout_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }



    /**
     * Фактически, эта связь позволяет хранить "сессию"
     *
     * @return BelongsTo
     */
    public function token()
    {
        return $this->belongsTo(User::class);
    }


}
