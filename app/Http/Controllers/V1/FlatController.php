<?php

namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Http\Middleware\Rest;
use ApartmentApi\Http\Middleware\SocietyIdRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use ApartmentApi\Repositories\FlatRepository;
use ApartmentApi\Commands\Flat\AddFlatCommand;
use ApartmentApi\Commands\Flat\UpdateFlatCommand;
use ApartmentApi\Commands\Flat\AttachUserCommand;

class FlatController extends ApiController
{
    protected $flatRepo;

    public function __construct(FlatRepository $flatRepo)
    {
        $this->flatRepo = $flatRepo;

        parent::__construct();
    }

    public function index(SocietyIdRequest $request)
    {
        $response = [];

        if ($request->get('select2') == '❌') {
            $response = $flats = $this->flatRepo->getFlatBlockBuildingList($request->get('society_id'));
           
        } else {
            $flats = $this->flatRepo->getFlatsAndBlocksList(
                            $request->get('society_id'),
                            $request->get('building_id'),
                            $request->get('block_id'),
                            $request->get('attached_flats') == '❌');  
           
            foreach ($flats as $flat) {
                array_push($response, $flat->jQuerySelect2);
            }
        }
       
        
        $total = ($flats instanceof Collection) ? $flats->count(): $flats['total'];
      
        if (! $total > 0)
            return $this->presentor->make404Response('No Flat\'s found.');

        return $this->presentor->make200Response('Success fetched flats.', $response);
    }

    public function store(AddFlatCommand $job)
    {
        return $this->dispatch($job);
    }

    public function update($flatId, Request $request)
    {
        return $this->dispatch(new UpdateFlatCommand($flatId, $request));
    }

    public function destroy($id, FlatRepository $repo)
    {
        if (! $repo->delete($id)) {
            return $this->presentor()->make500Response('Unable to delete, please try again later.');
        }

        return $this->presentor()->make200Response('Successfully deleted.');
    }

    public function attachUser(Request $request)
    {
        return $this->dispatch(new AttachUserCommand($request));
    }
}
