<?php namespace ApartmentApi\Commands\Acl;

use ApartmentApi\Models\AclRoleResourcePermission;
use Api\Commands\CreateCommand;

class AddRoleModulePermission extends CreateCommand 
{
    protected $permission;
    
    protected $aclRole;
    
    protected $rules = [
    ];

    public function __construct($request, $permission, $aclRole) 
    {
        $this->permission   = $permission;
        
        $this->aclRole      = $aclRole;
        
        parent::__construct($request);
    }
	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(AclRoleResourcePermission $roleResourcePermission)
	{
		return $roleResourcePermission
                        ->permission()
                        ->associate($this->permission)
                        ->role()
                        ->associate($this->aclRole)
                        ->save();
	}

}
