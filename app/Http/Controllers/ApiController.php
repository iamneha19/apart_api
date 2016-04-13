<?php
namespace ApartmentApi\Http\Controllers;

use Illuminate\Controller
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
	public function index() {

		return new JsonResponse(['data'=>'This data is returned from apartment api']);

	}

}
