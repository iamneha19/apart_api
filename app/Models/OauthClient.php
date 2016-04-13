<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;

class OauthClient extends Model
{
	protected $table = 'oauth_client';
	
	protected $fillable = array('id','secret');
	
	public $timestamps = false;
	
}