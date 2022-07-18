<?php

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public $timestamps = false;
    protected static $unguarded = true;
}
