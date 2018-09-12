<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class UserBag extends Model
{
    protected $table      = 'users_bags';
    protected $primaryKey = 'id';
    public $timestamps = false;

}
