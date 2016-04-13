<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model {

	//use SoftDeletes;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'member';

	//protected $primaryKey = 'id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['flat_id','first_name','last_name','dob','relation_id','unique_id','voter_id','contact_no','email', 'associate_member'];


        public function flat() {

                return $this->belongsTo('ApartmentApi\Models\Flat');
        }
}
