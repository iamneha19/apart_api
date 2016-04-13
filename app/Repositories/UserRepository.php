<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\FlatBill;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\User;
use Repository\Repository;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use ApartmentApi\Commands\User\SearchUserCommand;
use Illuminate\Pagination\Paginator;

 /**
  * City Repository
  *
  * @author Mohammed Mudasir
  */
class UserRepository extends Repository
{
    protected $model;

    protected $defaultSelection = ['id'];

    protected $fewSelection = [
        'id',
        'flat_id',
        'first_name',
        'last_name',
        'email',
        'committee_member',
        'admin_user',
        'contact_no',
        'dob',
        'unique_id',
        'voter_id',
    ];

    protected $perPage = 5;

    protected $selection;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function isUserAdmin()
    {
        $user = $this->model->firstOrNew(['email' => $this->fields['email']]);

        return $user->admin_user ? true: false;
    }

    public function add($societyId)
    {
        return DB::transaction(function() use ($societyId) {
            $user = $this->model->whereEmail($this->fields['email'])->first();

            if (! $user) {
                $user = $this->model->create($this->fields);
            }

            $userSociety = $user->userSociety()
                                ->firstOrNew([
                                    'status' => 1,
                                    'society_id' => $societyId
                                ]);

            // User already register in current society or an admin of another society
            if ($userSociety->id or $user->roles->where('role_name', 'Admin')->first()) {
                return false;
            }

            $memberRole = AclRole::where([
                'society_id' => $societyId,
                'role_name'  => 'Member'
            ])->first();

            $user->roles()->attach($memberRole);

            return $userSociety->id ?: $userSociety->save();
        });
    }

    public function search()
    {
        return app('Illuminate\Bus\Dispatcher')->dispatch(new SearchUserCommand);
    }

}
