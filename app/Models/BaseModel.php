<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class BaseModel extends Model
{
    protected $connection = 'mongodbname';

}
