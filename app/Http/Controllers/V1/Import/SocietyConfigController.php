<?php namespace ApartmentApi\Http\Controllers\V1\Import;

use ApartmentApi\Commands\Import\GetValidSocietyConfig;
use ApartmentApi\Commands\Import\ImportSocietyConfig;
use ApartmentApi\Exceptions\InvalidFieldException;
use ApartmentApi\Http\Requests\ConfigFileRequest;
use ApartmentApi\Models\Society;
use Api\Controllers\ApiController;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\OauthToken;
use Illuminate\Http\Request;
use ApartmentApi\Models\AclUserRole;
ini_set('xdebug.max_nesting_level', 120);
class SocietyConfigController extends ApiController
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(ConfigFileRequest $request, Society $society)
	{
        // Extracting all records(buildings, wings, flat) from request
		$societyConfigJob = new GetValidSocietyConfig($request);
		
		try {
			$societyConfigData = $this->dispatch($societyConfigJob);
		} catch (InvalidFieldException $ex) {
			return $this->presentor()->make500Response('Error occured during import.', $ex->getErrorBag());
		}
		catch (FileNotFoundException $ex) {
			dd($ex->getMessage());
			return $this->presentor()->make500Response('Validation Failed.', $ex->getMessage());
		}
		$society->amenities(
                $societyConfigJob->injectSocietyId(
                    $societyConfigJob->getValidAmenitiesId($request->get('society_amenities'))
            )
        );

        try {
            $this->dispatch(new ImportSocietyConfig($request->get('society_id'), $societyConfigData));
        } catch (Exception $e) {
            return $this->presentor()->make500Response('Import interrupted.', $e->getMessage());
        }
		
		return $this->presentor()->make200Response('Successfully import completed.');
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
		//
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
        
        public function role(Request $request) {
            $societyId = OauthToken::find($request->get('access_token'))->society()->first()->id;
        $role = AclRole::whereIN('role_name',array('Chairperson','Chairman')) 
                        ->where('society_id','=',$societyId)->first();
            $chairmanId = $role->id ;            
            $result = AclUserRole::where('acl_role_id','=',$chairmanId)                                
                                ->count();            
                if($result > 0) {
                    return ['success'=>true];
                } else {
                    return ['success'=>false];
                }
        }
}