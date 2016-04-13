<?php namespace ApartmentApi\Listeners;

use ApartmentApi\Events\SocietyConfigUploaded;
use ApartmentApi\Events\SocietyWasRegistered;
use ApartmentApi\Models\OauthToken;
use Illuminate\Mail\Mailer;
use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\Society;
use ApartmentApi\Models\User;

/*
 *	Sends society configuration data imported mail to Chairperson. 
 * 
 *	@category	Society Configuration
 *	@author		Swapnil Chaudhari <swapnil.chaudhari@sts.in>
 *	@copyright	2015-2016 Apartment Team	
 *	@license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *	@since		Class available since Release 2.2.1
 * 
 */

class EmailSocietyConfigUploaded {

	protected $mailer;
	
	protected $societyId;
	
	protected $userId;


	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct(Mailer $mailer)
	{
//	print_r("test");exit;	
            $this->mailer = $mailer;
                
	}

	/**
	 * Handle the event.
	 *
	 * @param  SocietyWasRegistered  $event
	 * @return void
	 */
	public function handle()
	{
		$oauthToken = OauthToken::find(\Input::get('access_token'));
		$this->societyId = $oauthToken->society_id;
		$this->userId = $oauthToken->user_id;

		
		$toResults		=	AclRole::with(['aclUserRole.user' => function($q)  {
											$q->select('*');
										}])
									->whereSocietyId($this->societyId)
//									->whereRoleNameIN('Chairperson','cha')	
                                     ->whereIN('role_name',array('Chairperson','Chairman'))
									->first();
										
		$fromResults	=	User::Where('id', $this->userId)
							 	 ->first();	
		
		$data = array(
						'society'		=> Society::Where('id', $this->societyId)->first()->name,
						'toFirstName'	=> $toResults->aclUserRole->user->first_name,
						'toLastName'	=>	$toResults->aclUserRole->user->last_name,
						'toEmail'		=>	$toResults->aclUserRole->user->email,
						'fromFirstName' => $fromResults->first_name,
						'fromLastName'	=>	$fromResults->last_name,
						'fromEmail'		=>	$fromResults->email,
					 );
							
		

		$this->mailer->send('emails.societyConfig', ['societyConfig'=>$data], function($m) use ($data){
                              $m->to($data['toEmail'])
								->replyTo($data['fromEmail'])
								->subject('Society Config Imported');
		});
	}

}
