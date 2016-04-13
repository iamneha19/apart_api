<?php namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
	protected $table = 'ticket_category';
	
	protected $fillable = ['category_name','description'];
	
	public function user() {
		
		return $this->belongsTo('ApartmentApi\Models\User','created_by');
	}
	
	public function society() {
	
		return $this->belongsTo('ApartmentApi\Models\Society','society_id');
	}
	
	
}