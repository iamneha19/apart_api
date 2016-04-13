<?php
namespace ApartmentApi\Models;

use ApartmentApi\Repositories\Contracts\OAuthContract;
use DB;
use Illuminate\Database\Eloquent\Model;

class OauthToken extends Model implements OAuthContract
{
	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'token';

	public $timestamps = false;

	protected $table = 'oauth_token';

	protected $fillable = array('token','created');

	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	public function user() {

		return $this->belongsTo('ApartmentApi\Models\User','user_id','id');
	}

	public function client() {

		return $this->belongsTo('ApartmentApi\Models\OauthClient','client_id','id');
	}

	public function society() {

		return $this->belongsTo('ApartmentApi\Models\Society','society_id');
	}

	public function userSociety() {

		return $this->hasMany('ApartmentApi\Models\UserSociety','user_id','user_id');
	}

	public function hasRole($role) {

		$sql = <<<EOF

		select count(aur.user_id) from oauth_token
				inner join users on users.id = oauth_token.user_id
				inner join acl_user_role aur on aur.user_id = users.id
				inner join acl_role on acl_role.id = aur.acl_role_id
				where oauth_token.token = :token and
						acl_role.role_name = :role group by aur.user_id

EOF;

		return DB::selectOne($sql, ['token'=>$this->token,'role'=>$role]);


	}
    
    public function hasSocietyPermission($permission,$accessToken) {
		
		$sql = <<<EOF
		
		SELECT COUNT(*) AS total FROM oauth_token 
    INNER JOIN acl_role aro ON aro.society_id = oauth_token.society_id
    INNER JOIN acl_user_role aur ON  aur.user_id = oauth_token.user_id AND aro.id = aur.acl_role_id
    INNER JOIN acl_role_resource_permission arrp ON aro.id = arrp.acl_role_id
    INNER JOIN acl_resource_permission arp ON arrp.resource_permission_id = arp.id 
    WHERE oauth_token.token = :token AND arp.permission = :permission;

EOF;
		
		$result =  DB::selectOne($sql, ['token'=>$accessToken,'permission'=>$permission]);
		return $result->total;
	}
    
    public function hasBuildingPermission($permission,$societyId,$userId) {
		
		$sql = <<<EOF
		
		SELECT COUNT(*) AS total FROM acl_role aro 
    INNER JOIN acl_user_role aur ON aro.id = aur.acl_role_id
    INNER JOIN acl_role_resource_permission arrp ON aro.id = arrp.acl_role_id
    INNER JOIN acl_resource_permission arp ON arrp.resource_permission_id = arp.id 
    WHERE aro.society_id IN (select society.id from society where parent_id = :society_id) AND aur.user_id = :user_id AND arp.permission = :permission;

EOF;
		
		$result =  DB::selectOne($sql, ['permission'=>$permission,'society_id'=>$societyId,'user_id'=>$userId]);
		return $result->total;
	}
    
    public function getAclBuildingId($societyId, $userId){
       
        $sql = <<<EOF
                
        SELECT acl_role.society_id as building_id  
        FROM acl_role 
        INNER JOIN acl_user_role ON acl_role.id = acl_user_role.acl_role_id 
        WHERE society_id IN (SELECT society.id FROM society WHERE parent_id = :society_id) 
        AND acl_user_role.user_id = :user_id limit 1
                
EOF;
        $result = DB::selectOne($sql,['society_id'=>$societyId,'user_id'=>$userId]);
       
        if($result){
            
           return $result->building_id; 
        }else{
            return null;
        }
        
    }

}
