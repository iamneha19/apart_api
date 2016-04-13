<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\File;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\Category;
use ApartmentApi\Models\Users;
use Illuminate\Http\Request;
use ApartmentApi\Models\AdminFolder;
use ApartmentApi\Models\Society;



Class AdminFileController extends Controller {
    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('rest');
	}
	
	public function getFilesList() {
                
                $search = \Input::get('search',null);
                $folder_id = \Input::get('folder_id',null);
		
		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
                
                $where = " where fi.folder_type = 'admin_folder' and fi.deleted_at IS NULL ";
		if ($search) {
			$where .= ' and fi.name like :search and fi.folder_id = :folder_id';
			$binds['search'] = '%'.$search.'%';
                        $binds['folder_id'] = $folder_id;
		}else{
                    $where .= ' and fi.folder_id = :folder_id';
                    $binds['folder_id'] = $folder_id;
                }
                
                $results = \DB::select("select fi.*,u.first_name,u.last_name,fi.name as file_name from file as fi JOIN users as u ON fi.user_id=u.id  $where limit :limit offset :offset",$binds);
                $count = \DB::selectOne(
					"select count(*) as total from file as fi $where",
					$search ? array('search'=>'%'.$search.'%','folder_id'=>$folder_id) : array('folder_id'=>$folder_id)
				);

		return ['total'=>$count->total,'data'=>$results];

	}
        
        public function listSocietyDocuments(Request $request) 
        {
           
            $and = "";
            $search =  $request->get('search');
            $limitBinds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
            $binds = array();
            $societyId = OauthToken::find($request->get('access_token'))->society()->first()->id;
            $type = 3;
            if ($search) {
			$and .= " and admin_folder.society_id =" .$societyId ." and admin_folder.`type`=".$type." and file.name like :search ";
			$binds['search'] = '%'.$search.'%';
                       
		}else{
                    $and .= " and admin_folder.society_id =" .$societyId .' and admin_folder.`type`='.$type;
                  
                }
            
           
            $count  = count(\DB::select("select file.*,category.`type`,category.name as category_type,category.is_mandatory,users.first_name,users.last_name from file
                        inner join users on file.user_id = users.id 
                        left join category on file.category_id = category.id
                        inner join admin_folder on file.folder_id = admin_folder.id
                        where file.deleted_at IS null" .$and,$binds));
            $results = \DB::select("select file.*,category.`type`,category.name as category_type,category.is_mandatory,users.first_name,users.last_name , date_format(file.created_at,'%d-%m-%Y') as created_at from file
                        inner join users on file.user_id = users.id 
                        left join category on file.category_id = category.id
                        inner join admin_folder on file.folder_id = admin_folder.id
                        where file.deleted_at IS null ".$and." limit :limit offset :offset",  array_merge($binds,$limitBinds)) ;
           
            
           return ['total'=>$count,'data'=>$results];
        } 
        
         public function getFileMandatoryDetails(Request $request,$type) {
            
         
            $societyId = OauthToken::find($request->get('access_token'))->society()->first()->id;             
            
            $binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
            $mandatory = \DB::select("SELECT DISTINCT category.id,category.name,category.is_mandatory,file.name AS file_name FROM category 
                                    LEFT JOIN `file` ON  category.id = file.category_id AND file.`deleted_at` IS NULL 
                                    WHERE category.type = 'Society Document' 
                                    AND category.society_id = $societyId 
                                    GROUP BY category.id  limit :limit offset :offset",$binds
                        );
            $total = count( \DB::select("SELECT DISTINCT category.id,category.name,category.is_mandatory,file.name AS file_name FROM category 
                                    LEFT JOIN `file` ON  category.id = file.category_id AND file.`deleted_at` IS NULL 
                                    WHERE category.type = 'Society Document' 
                                    AND category.society_id = $societyId 
                                    GROUP BY category.id" ));
           
            return ['total'=>$total,'data'=>$mandatory];
           
        }
	
        public function search(Request $request)
        {
            $societyId = OauthToken::find($request->get('access_token'))->society()->first()->id; 
            $search = $request->get('search');
            $and = " and society_id =" .$societyId ;
            $sql =  "select file.*,category.`type`,category.name as category_type,category.is_mandatory,users.first_name,users.last_name from file
                        inner join users on file.user_id = users.id 
                        left join category on file.category_id = category.id
                        where file.name like :search
                        and file.deleted_at IS null".$and ;

            $file = \DB::select($sql,['search'=>'%'.$search.'%']);
            if($file){
                return ['success'=>true,'data'=>$file,'msg'=>'Successfully fetched Files.'];
            }else{
               return ['success'=>false,'msg'=>'File not found'];
            }
        }
        
	public function getFile($id) {
		
                $where = " where fi.folder_type = 'admin_folder' and fi.deleted_at IS NULL and fi.id =".$id;
		$file = \DB::selectOne("select fi.*,u.first_name,u.last_name,fi.name as file_name ,category.name as type ,date_format(fi.created_at,'%d-%m-%Y') as created_at from file as fi"
                        . " INNER JOIN category on fi.category_id = category.id"
                        . " JOIN users as u ON fi.user_id=u.id $where ");
       
		if ($file)
                    return ['msg'=>'Successfully fetched file','data'=>$file,'success'=>true];
		else
                    return ['msg'=>'File doesnot exist with id - '.$id,'success'=>false];    
	}
        
        /// used for both update and delete action
        public function create() {
                $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
		// get all posted form data
		$data = \Input::all();
                if (\Request::hasFile('file'))
                {
                    $folder_path = 'uploads/'.$society_id.'/files/admin/'.$data['folder_id'].'/';
                    $folder_type = 'admin_folder';
//                    $data['visible_to']= 3;
                    $file =  \App::make('services')->uploadFile($data,$folder_type,$folder_path,$data['folder_id']);
//                    print_r($file->folder_id);exit;
                   if(!empty($file))
                    {
                        return ['msg'=>'File created successfully','success'=>true,'folder_id'=>$file->folder_id];
                    }else{

                        return ['msg'=>'Error in file upload','success'=>false];
                    }
                }else{
                    return ['msg'=>'Error in file upload','success'=>false];
                }
                
	}
        
        public function uploadSocietyDocument(Request $request) 
        { 
            $folderId ="";
            $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
            $societyName = society::join('oauth_token', 'society.id', '=', 'oauth_token.society_id')
                                    ->where('society.id','=', $society_id)->first()->name;

            $user_id = OauthToken::find(\Input::get('access_token'))->user_id;
            $data = \Input::all();
           
        
           $checkFolder = AdminFolder::where('type','=',3)
                                 ->where('society_id','=',$society_id)->first();
           
           if($checkFolder){
               $id = $checkFolder->id;
               
           } else 
           {
            $AdminFolder = new AdminFolder();
            $AdminFolder->name = $societyName ;
            $AdminFolder->type = 3;
            $AdminFolder->user_id = $user_id;
            $AdminFolder->society_id = $society_id;            
            $AdminFolder->save();
            $id = $AdminFolder->id;
           }
           
            
            if (\Request::hasFile('file'))
                {
                    $folder_path = 'uploads/'.$society_id.'/files/admin/'.$id.'/';
                    $folder_type = 'admin_folder';
//                    $data['visible_to']= 3;
                    $file =  \App::make('services')->uploadFile($data,$folder_type,$folder_path,$id);
//                    print_r($file->folder_id);exit;
                     
                   if(!empty($file))
                    {
                        return ['msg'=>'File created successfully','success'=>true,'folder_id'=>$file->folder_id];
                    }else{

                        return ['msg'=>'Error in file upload','success'=>false];
                    }
                }else{
                    return ['msg'=>'Error in file upload','success'=>false];
                }
       
        }
        
         
        
        public function update(Request $request,$id = null) {
            
                $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
		// get all posted form data
		$data = \Input::all();
//                dd($data);
                if (\Request::hasFile('file'))
                {
                    $folder_path = 'uploads/'.$society_id.'/files/admin/'.$data['folder_id'].'/';
                    $folder_type = 'admin_folder';
                    $id = $request->get('folder_id');
                  
                    $fileId = $request->get('id');
                    $file =  \App::make('services')->uploadFile($data,$folder_type,$folder_path,$id);
                   if(!empty($file))
                    {
                        return ['msg'=>'File changed successfully','data'=>$file,'success'=>true];
                    }else{

                        return ['msg'=>'Error in file upload','success'=>false];
                    }
                }else{
                    $adminFile = File::find($id);
                    $adminFile->fill($data);
                    $adminFile->save();
                    return ['msg'=>'File data updated successfully','data'=>$adminFile,'success'=>true];
                }
                
           		
                foreach($roles as $role) {
                	
                	$userRole = UsserRole::where('user_id','=',$userid)->where('role','=',$role);
                	
                	if (!$userRole) {
                		$userRole = new UserRole();
                		$userRole->user()->associate($user);
                		$userRole->role()->associate($role);
                	}
                	
                	$userRole->save();
                	
                }
		 
	}
        
        public function delete() {
		
                $id = \Input::get('id');
		File::where('id','=',$id)->delete();
		
		return ['msg'=>'file deleted successfully.','success'=>true];
	}
        
       
  
        public function getFlatFiles()
        {  
            $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
            $user_id = OauthToken::find(\Input::get('access_token'))->user_id;
            $search = \Input::get('search',null);
            $folder_id = \Input::get('folder_id',null);
		
            $binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
            $where = " where file.folder_type = 'admin_folder' and file.deleted_at IS NULL and (visible_to = 1 OR role_id != '') ";
            if ($search) {
                $where .= ' and file.name like :search and file.folder_id = :folder_id';
                $binds['search'] = '%'.$search.'%';
                $binds['folder_id'] = $folder_id;
            }else{
                $where .= ' and file.folder_id = :folder_id';
                $binds['folder_id'] = $folder_id;
            }
            $role_ids = \DB::selectOne("SELECT GROUP_CONCAT(acl_role.id) AS role_id FROM acl_user_role AS aur INNER JOIN acl_role ON acl_role.id = aur.acl_role_id WHERE acl_role.society_id = '".$society_id."' AND aur.user_id = '".$user_id."'");
            $role_id = $role_ids->role_id;  
             
//            $query = \DB::select("SELECT file.id,file.name,GROUP_CONCAT(file_access.role_id) AS role_id,file.visible_to FROM file LEFT JOIN file_access ON file.id = file_access.file_id AND file_access.role_id IN ($role_ids->role_id) WHERE file.folder_id = '".$folder_id."' AND (visible_to = 1 OR role_id != '') GROUP BY file.id");
            $results = \DB::select("SELECT file.*,category.name as category_name,CONCAT(users.first_name,' ',users.last_name) AS user_name,GROUP_CONCAT(file_access.role_id) AS role_id,file.visible_to,date_format(file.created_at,'%d-%m-%Y') as created_at  FROM file INNER JOIN category ON file.category_id = category.id INNER JOIN users ON file.user_id = users.id LEFT JOIN file_access ON file.id = file_access.file_id AND file_access.role_id IN ($role_ids->role_id) $where GROUP BY file.id limit :limit offset :offset",$binds);
//            print_r($query);exit;
        
                $count = \DB::selectOne(
					"select count(DISTINCT file.id) as total from file LEFT JOIN file_access ON file.id = file_access.file_id AND file_access.role_id IN ($role_ids->role_id) $where",
					$search ? array('search'=>'%'.$search.'%','folder_id'=>$folder_id) : array('folder_id'=>$folder_id)
				);
//                print_r($results);exit;

		return ['total'=>$count->total,'data'=>$results];
          
        }
}

