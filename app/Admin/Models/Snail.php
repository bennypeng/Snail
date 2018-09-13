<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Snail extends Model
{
    protected $table      = 'snails';
    protected $primaryKey = 'id';
    public    $timestamps = false;
    //protected $dateFormat = 'U';

}
