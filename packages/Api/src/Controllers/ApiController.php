<?php

namespace Api\Controllers;

use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Api\Traits\ApiResponseTrait;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesCommands;

class ApiController extends Controller
{
	use DispatchesCommands, ValidatesRequests;

    protected $presentor;

    public function __construct()
    {
        $this->setPresentor();
    }

    public function setPresentor()
    {
        $this->presentor = app()->make('Api\Presentor');
    }

    public function presentor()
    {
        $this->presentor ? $this->presentor : $this->setPresentor();

        return $this->presentor;
    }
}
