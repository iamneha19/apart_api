<?php namespace ApartmentApi\Commands\Acl;

use ApartmentApi\Models\AclRoleResource;
use Api\Commands\CreateCommand;

class AddRoleModuleAccess extends CreateCommand 
{

	protected $aclRole;
    
    protected $resource;
    
    protected $rules = [
    ];
    /**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($request, $aclRole, $resource)
	{
		$this->aclRole = $aclRole;
        $this->resource = $resource;
        
        parent::__construct($request);
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(AclRoleResource $roleResource)
	{
        return $roleResource->aclresource()
                    ->associate($this->resource)
                    ->role()
                    ->associate($this->aclRole)
                    ->save();
	}

}
