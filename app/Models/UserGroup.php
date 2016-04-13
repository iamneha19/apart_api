<?php

namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    public $table = 'user_group';
     public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('ApartmentApi\Models\User','user_id');
    }
    
    public function group()
    {
        return $this->belongsTo('ApartmentApi\Models\Entity','group_id');
    }
    
}
