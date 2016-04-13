<?php

namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = "type";
    protected $primaryKey = 'id';
    protected $fillable = ['type'];
    public $timestamps = false;
}