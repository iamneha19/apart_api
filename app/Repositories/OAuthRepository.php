<?php

namespace ApartmentApi\Repositories;

use ApartmentApi\Models\OauthSuperAdminToken;
use Repository\Contracts\AccessTokenContract;
use Repository\Repository;
use ApartmentApi\Repositories\Contracts\OAuthContract;

 /**
  * OAuth Token Repository
  *
  * @author Mohammed Mudasir
  */
 class OAuthRepository extends Repository implements AccessTokenContract
 {
     protected $model;

     public function __construct(OAuthContract $model)
     {
         $this->model = $model;
     }

     public function isAccessTokenValid($accessToken)
     {
         return $this->model->whereToken($accessToken)->count();
     }
 }
