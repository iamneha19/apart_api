<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class BlockConfiguration extends Model
{
	//use SoftDeletes;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'block_configuration';

	public $timestamps = false;
	//protected $primaryKey = 'id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['block_id','nos_of_floors','is_flat_same_on_each_floor','flat_on_each_floor'];

	/**
	 * Used for soft deleting
	 * @var unknown
	 */
//protected $dates = ['deleted_at'];

    public function block()
    {
        return $this->belongsTo('ApartmentApi\Models\Block','block_id','id');
    }
	
	public function blockConfig()
    {
        return $this->hasMany('ApartmentApi\Models\BlockConfigurationFloorInfo','block_configuration_id','id');
    }

    
}
