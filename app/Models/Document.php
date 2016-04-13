<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model {
        use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'document';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['id','visible_to', 'file_name'];
	
    protected $dates = ['deleted_at'];
	
	public $timestamps = false;
	
	public function entity() {
		
		return $this->morphOne('ApartmentApi\Models\Entity', 'entitiable');
		
	}
	
}