<?php namespace ApartmentApi\Commands\Acl;

use Api\Commands\DeleteCommand;
use DB;

class DeleteRoleModulePermission extends DeleteCommand 
{
    protected $permissionId;
    
    protected $roleId;
    
    protected $rules = [
    ];
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($request, $roleId, $permissionId)
	{
		$this->roleId = $roleId;
        $this->permissionId = $permissionId;
        
        parent::__construct($request);
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		DB::table('acl_role_resource_permission')->where('resource_permission_id',$this->permissionId)
                ->where('acl_role_id', $this->roleId)->delete();
	}

}
