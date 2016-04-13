<?php
namespace ApartmentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
//use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */

	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['first_name','last_name', 'email','password_changed','commitee_member','responsibility','description','designation','contact_no','dob','unique_id','voter_id','password','active_status'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

   public function folders() {

       return $this->hasMany('App\Models\AdminFolder');
   }

   public function block() {

   		return $this->belongsTo('ApartmentApi\Models\Block');
   }

   public function flat() {

   		return $this->belongsTo('ApartmentApi\Models\Flat');
   }

	public function userSocieties() {

		return $this->hasMany('ApartmentApi\Models\UserSociety','user_id');
	}

    public function aclUserRole(){
        return $this->hasMany('ApartmentApi\Models\AclUserRole','user_id');
    }

    public function aclUserResources(){
        return $this->hasMany('ApartmentApi\Models\AclUserResource','user_id');
    }

	public function resourcePermissions() {

		return $this->hasMany('ApartmentApi\Models\AclResourcePermission','user_id','resource_permission_id');
	}

	function hasPermission($resource,$permission,$accessToken) {


		$sql = <<<EOF

		select count(aurp.resource_permission_id) count from oauth_token
				inner join users on users.id = oauth_token.user_id
				inner join acl_user_resource_permission aurp on aurp.user_id = users.id
				inner join acl_resource_permission arp on arp.id = aurp.resource_permission_id
				where oauth_token.token = :token
					and arp.permission = :permission and
						arp.resource_acl_name = :resource
				group by aurp.resource_permission_id
EOF;

		return \DB::selectOne($sql, ['token'=>$accessToken,'permission'=>$permission,'resource'=>$resource]);
	}


    /*
     * Used to check has role in given society
     */
    public function hasRole($role,$accessToken) {

		$sql = <<<EOF

		select count(*) as total from oauth_token
                inner join acl_role on oauth_token.society_id = acl_role.society_id
				inner join acl_user_role on acl_role.id = acl_user_role.acl_role_id and oauth_token.user_id = acl_user_role.user_id where oauth_token.token = :token
                and acl_role.role_name = :role

EOF;

		$result = \DB::selectOne($sql, ['token'=>$accessToken,'role'=>$role]);
               return $result->total;

	}

        /*
         * Used to check owner or tenant in given society
         */
        public function isOwner($user_id,$society_id) {

		$sql = <<<EOF

		select count(*) as total from user_society
				where society_id = :society_id
                                        and user_id = :user_id
                                        and relation = 'owner'
                                        and status = 1

EOF;

		$result = \DB::selectOne($sql, ['user_id'=>$user_id,'society_id'=>$society_id]);
               return $result->total;

	}



        /*
         * Used for send meeting invitation to specific role
         */
        public static function getRoleUsers($role,$society_id) {

		$sql = <<<EOF

		select users.id as user_id,users.first_name,users.last_name,users.email from users
				inner join acl_user_role aur on aur.user_id = users.id
                                inner join (SELECT user_id,society_id from user_society where status = 1 group by user_id,society_id) as society_user on society_user.society_id = aur.society_id and society_user.user_id = aur.user_id
				where aur.role_acl_name = :role and aur.society_id = :society_id

EOF;

		return \DB::select($sql, ['role'=>$role,'society_id'=>$society_id]);

	}

        /*
         * User fot send meeting invitation to all society users
         */
        public static function getAllSocietyUsers($society_id) {

		$sql = <<<EOF

		select users.id as user_id,users.first_name,users.last_name,users.email from user_society
				inner join users on user_society.user_id = users.id
				where  user_society.status = 1 and user_society.society_id = :society_id group by user_society.user_id,user_society.society_id

EOF;

            return \DB::select($sql, ['society_id'=>$society_id]);

        }

        /*
         * User fot send meeting invitation to all society users
         */
        public static function getAllBuildingUsers($building_id) {

		$sql = <<<EOF

		select users.id as user_id,users.first_name,users.last_name,users.email from user_society
				inner join users on user_society.user_id = users.id
				where  user_society.status = 1 and user_society.building_id = :society_id group by user_society.user_id,user_society.society_id

EOF;

            return \DB::select($sql, ['building_id'=>$building_id]);

        }

        /*
         * Used for send meeting invitation to owner society users
         * @society_id
         * @relation owner,tenant
         */
        public static function getSocietyUsersWithRelation($society_id,$relation) {

		$sql = <<<EOF

		select users.id as user_id,users.first_name,users.last_name,users.email from user_society
				inner join users on user_society.user_id = users.id
				where  user_society.status = 1 and user_society.society_id = :society_id and user_society.relation = :relation group by user_society.user_id,user_society.society_id

EOF;

            return \DB::select($sql, ['society_id'=>$society_id,'relation'=>$relation]);

        }

        /*
         * Used for send meeting invitation to owner society users
         * @society_id
         * @relation owner,tenant
         */
        public static function getBuildingUsersWithRelation($building_id,$relation) {

		$sql = <<<EOF

		select users.id as user_id,users.first_name,users.last_name,users.email from user_society
				inner join users on user_society.user_id = users.id
				where  user_society.status = 1 and user_society.building_id = :building_id and user_society.relation = :relation group by user_society.user_id,user_society.society_id

EOF;

            return \DB::select($sql, ['building_id'=>$building_id,'relation'=>$relation]);

        }

	public static function getAttendeesRoleBasedList($meeting_id)
        {
            $users = \DB::select("select DISTINCT role.user_id,users.first_name,users.last_name,users.email from acl_user_role as role INNER JOIN users ON role.user_id = users.id where acl_role_id IN(select role_id from meeting_attendee where meeting_id=$meeting_id)");
            return $users;
        }
        public static function getRoleNameForMeeting($meeting_id)
        {
            $users = \DB::select("select DISTINCT acl_role.role_name from acl_role INNER JOIN meeting_attendee ON acl_role.id = meeting_attendee.role_id where role_id IN(select role_id from meeting_attendee where meeting_id=$meeting_id)");
            foreach($users as $user)
            {
                $arr[] = $user->role_name;
            }
            $arr_implode = implode(",",$arr);
            return $arr_implode;
        }

    public function getFullNameAttribute()
    {
        return ucwords($this->attributes['first_name'] . ' ' . $this->attributes['last_name']);
    }


}
