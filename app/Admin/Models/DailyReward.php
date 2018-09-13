<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReward extends Model
{
    protected $table      = 'daily_rewards';
    protected $primaryKey = 'id';
    public    $timestamps = false;
    //protected $dateFormat = 'U';

}
