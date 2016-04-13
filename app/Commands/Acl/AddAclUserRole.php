<?php namespace ApartmentApi\Commands\Acl;

use ApartmentApi\Models\AclUserRole;
use ApartmentApi\Models\AclRole;
use Api\Commands\CreateCommand;

class AddAclUserRole extends CreateCommand {

	protected $user;
    protected $aclRole;
    
    protected $rules = [
    ];
    /**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($request, $user, $aclRole)
	{
		$this->user = $user;
        $this->aclRole = $aclRole;
        
        parent::__construct($request);
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(AclUserRole $aclUserRole)
	{
        $aclUserRole->user()
                    ->associate($this->user)
                    ->aclRole()
                    ->associate($this->aclRole)
                    ->save();
        
       $child_roles = AclRole::where('parent_id','=',$this->aclRole->id)->get();
       
       if(!empty($child_roles)){
           $aclUserRoles = array();
           foreach($child_roles as $child_role ){
                                $aclUserRoles[] = array('user_id'=>$this->user->id, 'acl_role_id'=>$child_role->id);
            }
           return AclUserRole::insert($aclUserRoles); 
       }else{
           return true;
       }
       
       
	}

}
