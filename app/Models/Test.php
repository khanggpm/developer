<?php

namespace App\Models;
use Jenssegers\Mongodb\Eloquent\Model;

class Test extends Model
{
    protected $connection = 'mongodbName';
    protected $collection = 'test';
    protected $primaryKey = 'id';
    public $fillable = [
        'id',
        'text',
    ];

}
