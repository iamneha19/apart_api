<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class BlockConfigurationFloorInfo extends Model
{
	//use SoftDeletes;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'block_config_floor_info';

	public $timestamps = false;
	//protected $primaryKey = 'id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['block_configuration_id','floor_no','no_of_flat'];

	/**
	 * Used for soft deleting
	 * @var unknown
	 */
//protected $dates = ['deleted_at'];

    public function blockConfiguration()
    {
        return $this->belongsTo('ApartmentApi\Models\BlockConfiguration','block_configuration_id','id');
    }

    
}
