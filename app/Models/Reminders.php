<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reminders extends Model {
    
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'reminder';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['alert','alert_unix','type_id'];
    
    public function category() {
        return $this->belongsTo('ApartmentApi\Models\Category', 'type_id');
    }
}