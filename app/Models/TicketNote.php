<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class TicketNote extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'ticket_note';
	
	public function ticket() {
		
		return $this->belongsTo('ApartmentApi\Models\Ticket','ticket_id','id');
	}
	
	public function user() {
	
		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
	}
		
}
