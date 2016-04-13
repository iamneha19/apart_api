<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\AdminFolder;
use ApartmentApi\Models\OauthToken;

Class AdminFolderController extends Controller {
    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('rest');
	}
	
	public function getFoldersList() {
                
                $oauthToken = OauthToken::find(\Input::get('access_token'));
                $society_id = $oauthToken->society_id;
		
		$search = \Input::get('search',null);
                $type = \Input::get('type',1);
		
		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
                
                $where = ' where f.society_id = "'.$society_id.'" and f.deleted_at IS NULL';
		if ($search) {
			$where .= ' and f.name like :search and f.type= :type';
			$binds['search'] = '%'.$search.'%';
                        $binds['type'] =$type;
		}else{
                        $where .= ' and f.type = :type';
                        $binds['type'] = $type;
                }
                
                $results = \DB::select("select f.*,users.first_name,users.last_name,f.name as folder_name from admin_folder as f INNER JOIN users on f.user_id=users.id $where order by f.id desc limit :limit offset :offset",$binds);
                
                
		$count = \DB::selectOne(
					"select count(*) as total from admin_folder as f $where",
					$search ? array('search'=>'%'.$search.'%','type'=>$type) : array('type'=>$type)
				);

		return ['total'=>$count->total,'data'=>$results];
		
	}
	
	public function getFolder($id) {
		
//		$post = AdminFolder::find($id);
                $post = \DB::selectOne('select admin_folder.*,users.first_name,date_format(admin_folder.updated_at,"%d-%m-%Y") as updated_at,date_format(admin_folder.created_at,"%d-%m-%Y") as created_at from admin_folder INNER JOIN users on admin_folder.user_id=users.id where admin_folder.id="'.$id.'" and admin_folder.deleted_at IS NULL');
		
		if (!$post)
			return ['msg'=>'Folder doesnot exist with id - '.$id];
		
		return $post;
	}
        /// used for both update and delete action
        public function create() {
		
		// get all posted form data
		$attributes = \Input::all();
                $oauthToken = OauthToken::find(\Input::get('access_token'));
		$user = $oauthToken->user()->first();
                $society_id = $oauthToken->society_id;
                
                $attributes['society_id'] = $society_id;
                
                $sql = 'select count(*) as total from admin_folder where society_id = :society_id and name = :folder_name and deleted_at IS NULL ';
                $result = \DB::selectOne($sql,['society_id'=>$society_id,'folder_name'=>$attributes['name']]);
                if($result->total){
                    return ['success'=>false,'msg'=>'This folder already exists'];
                }
                
                $adminFolder = new AdminFolder();
                $adminFolder->user()->associate($user);
		$adminFolder->fill($attributes);
		$adminFolder->save();
		
                return ['msg'=>'Folder created successfully','success'=>true];
                
	}
        
        public function update($id = null) {
		
		// get all posted form data
		$attributes = \Input::all();
		$oauthToken = OauthToken::find(\Input::get('access_token'));
		$user = $oauthToken->user()->first();
                $society_id = $oauthToken->society_id;
                
                $adminFolder = AdminFolder::where('id','=',$id)
				->firstOrFail();
                if (!$adminFolder)
			return ['error'=>'Folder not found with id: '.$id,'success'=>false];
                
                 $sql = 'select count(*) as total from admin_folder where society_id = :society_id and name = :folder_name and deleted_at IS NULL and id != :id';
                $result = \DB::selectOne($sql,['society_id'=>$society_id,'folder_name'=>$attributes['name'],'id'=>$id]);
                if($result->total){
                    return ['success'=>false,'msg'=>'This folder already exists'];
                }
                
		$adminFolder->fill($attributes);
		$adminFolder->save();
		
                return ['msg'=>'Folder updated successfully','success'=>true];
	}
        
        public function delete() {
		
                $id = \Input::get('id');
                $sql = "select count(*) as total from file where folder_id = :folder_id and folder_type = 'admin_folder' and deleted_at IS NULL";
                $result = \DB::selectOne($sql,['folder_id'=>$id]);
                if($result->total)
                {
                    return ['msg'=>'folder error','folder_error'=>'Files are assigned under this folder, please delete those files first!','success'=>false];
                }
		AdminFolder::where('id','=',$id)->delete();
		
		return ['msg'=>'Folder with id '.$id.' deleted successfully.','success'=>true];
	}
        
        public function getAllFolders()
        {
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            $data = \DB::select("select * from admin_folder where society_id = :society_id and deleted_at IS NULL ORDER BY name",['society_id'=>$society_id]);
            return array(
                'data'=>$data,
            );
            
        }
        /*
         * get flat specific folders.
         */
        public function getFlatFolders()
        {
            $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
            $user_id = OauthToken::find(\Input::get('access_token'))->user_id;
		
		$search = \Input::get('search',null);
                $type = \Input::get('type',1);
//		print_r($type);exit;
		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
                
                $where = ' where f.society_id = "'.$society_id.'" and f.deleted_at IS NULL';
		if ($search) {
			$where .= ' and f.name like :search and f.type= :type';
			$binds['search'] = '%'.$search.'%';
                        $binds['type'] =$type;
		}else{
                        $where .= ' and f.type = :type';
                        $binds['type'] = $type;
                }
                
                $results = \DB::select("select f.*,users.first_name,users.last_name,f.name as folder_name,date_format(f.created_at,'%d-%m-%Y') as created_at from admin_folder as f INNER JOIN users on f.user_id=users.id $where order by f.id desc limit :limit offset :offset",$binds);
                
                
		$count = \DB::selectOne(
					"select count(*) as total from admin_folder as f $where",
					$search ? array('search'=>'%'.$search.'%','type'=>$type) : array('type'=>$type)
				);

		return ['total'=>$count->total,'data'=>$results];
        }
}