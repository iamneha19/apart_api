<?php namespace ApartmentApi\Commands\Acl;

use Api\Commands\DeleteCommand;
use DB;

class DeleteRoleModuleAccess extends DeleteCommand {

	protected $roleId;
    protected $resourceId;
    /**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($request, $roleId, $resourceId)
	{
		$this->roleId = $roleId;
        $this->resourceId = $resourceId;
        
        parent::__construct($request);
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		DB::table('acl_role_resource')->where('resource',$this->resourceId)
                ->where('acl_role_id',$this->roleId)->delete();
                
                $sql = 'delete arrp from acl_role_resource_permission arrp 
					inner join acl_resource_permission arp on arp.id = arrp.resource_permission_id
					where arp.resource_acl_name = :resource and arrp.acl_role_id = :role_id';
			
        return DB::delete($sql,['resource'=>$this->resourceId,'role_id'=>$this->roleId]);
	}

}
