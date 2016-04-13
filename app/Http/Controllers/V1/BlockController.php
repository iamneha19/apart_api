<?php namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Http\Requests;
use ApartmentApi\Http\Controllers\Controller;
use ApartmentApi\Http\Middleware\SocietyIdRequest;
use ApartmentApi\Repositories\BlockRepository;
use ApartmentApi\Repositories\SocietyRepository;

use Illuminate\Http\Request;
use ApartmentApi\Presenters\BlockPresenter;

class BlockController extends ApiController
{
    protected $blockRepo;

    public function __construct(BlockRepository $blockRepo)
    {
        $this->blockRepo = $blockRepo;

        parent::__construct();
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(SocietyIdRequest $request)
	{
        $blocks = $this->blockRepo->searchBlockWithSociety($request->get('society_id'), $request->get('q'));

        $response = BlockPresenter::select2Responses($blocks);

		return $blocks->count() > 0 ?
                    $this->presentor->make200Response('Success', $response):
                    $this->presentor->make404Response('No block\'s found.');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $blocks = $this->blockRepo->findWithSociety($id);

        $response = BlockPresenter::select2Response($blocks);

        return $this->presentor()->make200Response('Success.', $response);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
