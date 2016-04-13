<?php namespace ApartmentApi\Commands\User;

use ApartmentApi\Commands\Command;
use ApartmentApi\Commands\Token\OAuthTokenContainerCommand;
use ApartmentApi\Repositories\UserRepository;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\User;
use Api\Commands\SelectCommand;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class SearchUserCommand extends Command implements SelfHandling
{
    protected $perPage = 10;

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle(
                        Request $request,
                        OAuthTokenContainerCommand $tokenContainer,
                        User $model)
	{
        $this->request = $request;
        $this->tokenContainer = $tokenContainer;
        $this->model   = $model;

        return $this->attachSearch()
                    ->attachUserStatus()
                    ->attachUserSociety()
                    ->userWithoutRolesOrAdminRole()
                    ->attachRelationShip()
                    ->paginate();
	}

    public function paginate()
    {
        if ($this->get('pagination') == 'false') {
            return $this->contactColumns()
                        ->model
                        ->get();
        }

        $sortOrder  = $this->get('sort_order') == 'asc' ? true: false;
        $model      = $this->model->paginate($this->perPage);
        $sortType   = $this->getValidSortType();

        $users = new Paginator(
                $sortOrder ? $model->sortBy($sortType): $model->sortByDesc($sortType),
                $this->perPage
            );

        return collect(array_merge(
                    ['total' => $model->total()],
                    $users->toArray()
                ));
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function getValidSortType()
    {
        $sort = 'id';
        switch ($this->get('sort')) {
            case 'flat':
                $sort = 'userSociety.flat.flat_no';
                break;
        }

        return $sort;
    }

    public function contactColumns()
    {
        switch ($this->get('concat')) {
            case 'name,email':
                return $this->selectRawConcat('id, CONCAT(first_name, " ", last_name, " - ", email) as name');

            default:
                return $this->selectRawConcat('id, CONCAT(first_name, " ", last_name) as name');
        }
    }

    protected function selectRawConcat($rawSql) {
        $this->model = $this->model
             ->selectRaw($rawSql);

        return $this;
    }

    public function attachSearch()
    {
        if ($search = $this->get('search')) {
            $this   ->model = $this->model
                    ->where(function($q) use ($search) {
                        $q  ->orWhere('first_name', 'LIKE', "%$search%")
                            ->orWhere('last_name', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%")
                            ->orWhereHas('userSociety.flat', function($q) use ($search) {
                                $q->where('flat_no', 'LIKE', "%$search%");
                            });
                    });
        }

        return $this;
    }

    public function attachUserSociety()
    {
        $this->model = $this->model
                    ->whereHas('userSociety', function($q) {
                        $q->whereSocietyId($this->getSocietyId());
                        if (($this->get('status') or $this->get('status') === "0")) {
                            $q->where('status' ,"=",$this->get('status'));
                        }else {
                            $q->whereIn('status' ,[0,1,2]);
                        }
                    });
        return $this;
    }

    public function attachUserStatus()
    {
        // Right now attach user status is depended on userSociety
        // $this->model = $this->model->whereActiveStatus($this->get('status', 1));

        return $this;
    }

    public function userWithoutRolesOrAdminRole()
    {
        $this   ->model = $this->model
                ->where(function($q) {
                    $q->whereDoesntHave('roles', function($q) {
                        $q->where('role_name', '=', 'Admin');
                    });
                });

        return $this;
    }

    public function attachRelationShip()
    {
        $this->get('relations') == 'false' ?:
                   $this->model = $this->model->with([
                       'userSocieties.building' => function($q) {
                           $q->select('id', 'name');
                       },
                       'userSocieties.block' => function($q) {
                           $q->select('id', 'block');
                       },
                       'userSocieties.flat' => function($q) {
                           $q->select('id', 'type', 'flat_no');
                       },
                       'userSocieties' => function($q) {
                           if (($this->get('status') or $this->get('status') === "0")) {
                            $q->where('status' ,"=",$this->get('status'));
                        }else {
                            $q->whereIn('status' ,[0,1,2]);
                        }
                           $q->whereSocietyId($this->tokenContainer->getSocietyId());
                       }
                   ]);

        return $this;
    }

    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    public function getSocietyId()
    {
        return $this->societyId ?: $this->society = $this->tokenContainer->getToken()->society_id;
    }

    public function get($name, $defaultValue = null)
    {
        return $this->request->get($name, $defaultValue);
    }

    public function __get($name)
    {
        return $this->request->get($name);
    }
}
