<?php

namespace App\Admin\Models;

use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{

    use AdminBuilder;

    protected $table      = 'configs';
    protected $primaryKey = 'id';
    public    $timestamps = false;
    //protected $dateFormat = 'U';

}
