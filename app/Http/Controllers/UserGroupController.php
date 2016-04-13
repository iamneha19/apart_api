<?php

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\Entity;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\UserGroup;
use Illuminate\Http\Request;


class UserGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('rest');
    }
    
    public function addUserToGroup(request $request)
    {
        $groupId = $request->get('group_id');
        $oauthToken = OauthToken::find($request->get('access_token'));
        
        $user = $oauthToken->user()->first();
        $group = Entity::find($groupId);
        $userGroup = new UserGroup();
        $userGroup->user()->associate($user);
        $userGroup->group()->associate($group);
        $userGroup->save();
        return ['msg'=>'Successfully joined','sucess'=>'true','user_id'=>$user->id];
    }
}

