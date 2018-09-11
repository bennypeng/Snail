<?php

namespace App\Admin\Models\User;

use App\Admin\Models\User;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'users';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
