<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
class Ticket extends Model
{
	
	protected $table = 'ticket';
	
	protected $fillable = ['issue','is_urgent'];
	
	public function category() {
		
		return $this->belongsTo('ApartmentApi\Models\TicketCategory','category_id');
	}
	
	public function flat() {
		
		return $this->belongsTo('ApartmentApi\Models\Flat','flat_id');
	}
	
	public function createdBy() {
		
		return $this->belongsTo('ApartmentApi\Models\User','created_by');
	}
	
	public function servicedBy() {
	
		return $this->belongsTo('ApartmentApi\Models\User','serviced_by');
	}
	
	public function supervisedBy() {
	
		return $this->belongsTo('ApartmentApi\Models\User','supervised_by');
	}
	
	public function society() {
		
		return $this->belongsTo('ApartmentApi\Models\Society','society_id');
	}
	
}
