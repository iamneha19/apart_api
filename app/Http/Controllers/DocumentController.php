<?php namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\AclRole;
use ApartmentApi\Models\AdminFolder;
use ApartmentApi\Models\File;
use ApartmentApi\Models\FileAccess;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\ResidentFolder;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('rest');
	}
	
	/**	
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function documentList()
	{
                $oauthToken = OauthToken::find(\Input::get('access_token'));
                $society_id = $oauthToken->society_id;
                $user = $oauthToken->user()->first();
                if($oauthToken->hasRole('Admin')){
                    $visiblity = ' fi.visible_to in (1, 2)';
                }else{
                   $isOwner = $user->isOwner($user->id,$society_id);
                    if($isOwner){
                        $visiblity = ' fi.visible_to in (1, 2)';
                    }else{
                        $visiblity = ' fi.visible_to = 1';
                    } 
                }
                
                
		$search = \Input::get('search',null);
                $folder_id = \Input::get('folder_id',null);
             
		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
                
                $where = " where fi.folder_type = 'resident_folder' and fi.deleted_at IS NULL and ".$visiblity;
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
        
        /**	
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function adminDocumentList()
	{
                $oauthToken = OauthToken::find(\Input::get('access_token'));
                $society_id = $oauthToken->society_id;
                $user = $oauthToken->user()->first();
                
                if($oauthToken->hasRole('Admin')){
                    $visiblity = ' fi.visible_to in (1, 2)';
                }else{
                   $isOwner = $user->isOwner($user->id,$society_id);
                    if($isOwner){
                        $visiblity = ' fi.visible_to in (1, 2)';
                    }else{
                        $visiblity = ' fi.visible_to = 1';
                    } 
                }
		$search = \Input::get('search',null);
                $folder_id = \Input::get('folder_id',null);
             
		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
                
                $where = " where fi.folder_type = 'admin_folder' and fi.deleted_at IS NULL and ".$visiblity;
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
	
	public function folderList() {
		
		$oauthToken = OauthToken::find(\Input::get('access_token'));
                $society_id = $oauthToken->society_id;
		
		$search = \Input::get('search',null);
		
		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
                
                $where = ' where f.society_id = "'.$society_id.'" and f.deleted_at IS NULL';
		if ($search) {
			$where .= ' and f.name like :search';
			$binds['search'] = '%'.$search.'%';
		}
                
                $results = \DB::select("select f.*,users.first_name,users.last_name,f.name as folder_name from resident_folder as f INNER JOIN users on f.user_id=users.id $where order by f.id desc limit :limit offset :offset",$binds);
                
                
		$count = \DB::selectOne(
					"select count(*) as total from resident_folder as f $where",
					$search ? array('search'=>'%'.$search.'%') : array()
				);

		return ['total'=>$count->total,'data'=>$results];
	}
        
        public function adminFolderList() {
		
		$oauthToken = OauthToken::find(\Input::get('access_token'));
                $society_id = $oauthToken->society_id;
		
		$search = \Input::get('search',null);
		
		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
                
                $where = ' where f.society_id = "'.$society_id.'" and f.deleted_at IS NULL';
		if ($search) {
			$where .= ' and f.name like :search';
			$binds['search'] = '%'.$search.'%';
		}
                
                $sql = <<<EOF
		
		select f.*,users.first_name,users.last_name,f.name as folder_name from admin_folder as f 
                    INNER JOIN users on f.user_id=users.id
                    INNER JOIN file on f.id=file.folder_id and file.visible_to = 1     
                    $where group by f.id order by f.id desc limit :limit offset :offset

EOF;
                
                $results = \DB::select($sql,$binds);
                
                 $sql = <<<EOF
		
		select count(DISTINCT f.id) as total from admin_folder as f 
                    INNER JOIN users on f.user_id=users.id
                    INNER JOIN file on f.id=file.folder_id and file.visible_to = 1     
                    $where

EOF;
		$count = \DB::selectOne(
					$sql,
					$search ? array('search'=>'%'.$search.'%') : array()
				);
                
//                die($count->total);

		return ['total'=>$count->total,'data'=>$results];
	}
        
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function createFolder()
	{
		// get all posted form data
		$attributes = \Input::all();
                $oauthToken = OauthToken::find(\Input::get('access_token'));
		$user = $oauthToken->user()->first();
                $society_id = $oauthToken->society_id;
                
                $attributes['society_id'] = $society_id;
                
                $sql = 'select count(*) as total from resident_folder where society_id = :society_id and deleted_at IS NULL and name = :folder_name' ;
                $result = \DB::selectOne($sql,['society_id'=>$society_id,'folder_name'=>$attributes['name']]);
                if($result->total){
                    return ['success'=>false,'msg'=>'This folder already exists'];
                }
                
                $residentFolder = new ResidentFolder();
                $residentFolder->user()->associate($user);
		$residentFolder->fill($attributes);
		$residentFolder->save();
		
                return ['msg'=>'Folder created successfully','success'=>true];
	}
        
        public function  updateFolder($id)
        {
                // get all posted form data
		$attributes = \Input::all();
                $oauthToken = OauthToken::find(\Input::get('access_token'));
		$user = $oauthToken->user()->first();
                $society_id = $oauthToken->society_id;
                
                $residentFolder = ResidentFolder::where('id','=',$id)
				->firstOrFail();
                if (!$residentFolder)
			return ['error'=>'Folder not found with id: '.$id,'success'=>false];
                
                 $sql = 'select count(*) as total from resident_folder where society_id = :society_id and deleted_at IS NULL and name = :folder_name and id != :id';
                $result = \DB::selectOne($sql,['society_id'=>$society_id,'folder_name'=>$attributes['name'],'id'=>$id]);
                if($result->total){
                    return ['success'=>false,'msg'=>'This folder already exists'];
                }
                
		$residentFolder->fill($attributes);
		$residentFolder->save();
		
                return ['msg'=>'Folder updated successfully','success'=>true];
        }        


        /**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function createDocument()
	{
            $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
           // get all posted form data
           $data = \Input::all();
//           print_r($data);exit;
           
           if (\Request::hasFile('file'))
           {
               $folder_path = 'uploads/'.$society_id.'/files/resident/'.$data['folder_id'].'/';
               $folder_type = 'resident_folder';
               $file =  \App::make('services')->uploadFile($data,$folder_type,$folder_path);
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
    
/*
 * create flat specific document
 */
    public function createFlatDocument()
    {
        $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
        $user_id = OauthToken::find(\Input::get('access_token'))->user_id;
        
// get all posted form data
           $data = \Input::all();
           $flat_id = $data['flat_id'];
        
           $folder_data = AdminFolder::where('flat_id',$flat_id)->first();
           if($folder_data == '')
            {
                $flat_details = \DB::selectOne("SELECT flat.flat_no,block.block,society.name AS building_name FROM flat INNER JOIN user_society ON user_society.flat_id = flat.id LEFT JOIN block ON flat.block_id = block.id INNER JOIN society ON user_society.building_id = society.id WHERE flat.id =".$flat_id);
//                $flat_details = \DB::selectOne("SELECT flat.flat_no,block.block,society.name AS building_name FROM flat INNER JOIN user_society ON user_society.flat_id = flat.id INNER JOIN block ON flat.block_id = block.id INNER JOIN society ON user_society.building_id = society.id WHERE flat.id =".$flat_id);
//                print_r($flat_details);exit;
                $flat_no = $flat_details->flat_no;
                if($flat_details->block!='')
                {
                    $block = $flat_details->block;
                }
                $building = $flat_details->building_name;
                
               $admin_folder = new AdminFolder();
               if(isset($block))
               {
                    $admin_folder->name = $building."-".$block."-".$flat_no;
               }else{
                   $admin_folder->name = $building."-".$flat_no;
               }
               $admin_folder->flat_id = $flat_id;
               $admin_folder->user_id = $user_id;
               $admin_folder->society_id = $society_id;
               $admin_folder->type = 2;
               $admin_folder->save();
               $folder = AdminFolder::find($admin_folder->id);
               $folder_id = $folder->id;
//               print_r($folder->id);exit;
               
            }else{
                $folder_id = $folder_data->id;
//               print_r($folder->id);
            }
//            print_r("abc");exit;
           
           if (\Request::hasFile('file'))
           {
               $folder_path = 'uploads/'.$society_id.'/files/admin/'.$folder_id.'/';
               $folder_type = 'admin_folder';
               $file =  \App::make('services')->uploadFile($data,$folder_type,$folder_path,$folder_id);
              if(!empty($file))
               {
                  if(isset($data['role_id']))
                {
                    foreach ($data['role_id'] as $roles_id){

                        $role = AclRole::find($roles_id);
                        $file_access = new FileAccess();
//                        $file_access->flat_id = $flat_id;
                        $file_access->file()->associate($file);
                        $file_access->role()->associate($role);
                        $file_access->save();
                    }
                }
                   return ['msg'=>'File created successfully','success'=>true,'folder_id'=>$folder_id];
               }else{

                   return ['msg'=>'Error in file upload','success'=>false];
               }
           }else{
               return ['msg'=>'Error in file upload','success'=>false];
           }
                
    }
   /*
    * return flat specific document
    */
      public function FlatDocumentList($flat_id)
      {
//          print_r($flat_id);exit;
        $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
        $user_id = OauthToken::find(\Input::get('access_token'))->user_id;
        $search = \Input::get('search',null);
        $where = 'admin_folder.society_id="'.$society_id.'" and file.folder_type = "admin_folder" and admin_folder.flat_id="'.$flat_id.'" and file.deleted_at IS NULL';
        $sort = '';
        $whereSep = '';
        $bindings = array();
//        print_r($where);
        if (\Input::get('search',null)) {
                $where .= ' and (file.name like :name)';
                $whereSep = true;
                $bindings['name'] = '%'.\Input::get('search').'%';
            }
            $where = $where ? ' where '.$where : '';
            
            if (\Input::get('sort',null)){
                $sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
            }
			$sql = "SELECT file.*,category.name as category_name,admin_folder.user_id,users.first_name,users.last_name, date_format(file.created_at,'%d-%m-%Y') as created_at FROM admin_folder INNER JOIN users ON admin_folder.user_id = users.id INNER JOIN file ON admin_folder.id = file.folder_id INNER JOIN category ON file.category_id = category.id $where order by file.created_at DESC limit :limit offset :offset";
			$results = \DB::select($sql,
                  array_merge($bindings,array(
                                    'limit'=>\Input::get('limit',5),
                                    'offset'=>\Input::get('offset',0)
                                    )
                            ));
          
			$sqlCount = "SELECT count(file.id) total FROM admin_folder INNER JOIN users ON admin_folder.user_id = users.id INNER JOIN file ON admin_folder.id = file.folder_id INNER JOIN category ON file.category_id = category.id $where";
			$count = \DB::selectOne(
				$sqlCount,
					$bindings
				);     
          return ['total'=>$count->total,'data'=>$results,'success'=>true];
      }
    
        public function updateDocument($id=null)
	{
                $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
		// get all posted form data
		$data = \Input::all();
                if (\Request::hasFile('file'))
                {
                    $folder_path = 'uploads/'.$society_id.'/files/resident/'.$data['folder_id'].'/';
                    $folder_type = 'resident_folder';
                    $data['id']=$id;
                    $file =  \App::make('services')->uploadFile($data,$folder_type,$folder_path);
                   if(!empty($file))
                    {
                        return ['msg'=>'Document changed successfully','data'=>$file,'success'=>true];
                    }else{

                        return ['msg'=>'Error in document upload','success'=>false];
                    }
                }else{
                    $adminFile = File::find($id);
                    $adminFile->fill($data);
                    $adminFile->save();
                    return ['msg'=>'Document data updated successfully','data'=>$adminFile,'success'=>true];
                }
                
	}
	

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function document($id)
	{
		  $where = " where fi.folder_type = 'resident_folder' and fi.id =".$id;
		$file = \DB::selectOne("select fi.*,u.first_name,u.last_name,fi.name as file_name from file as fi JOIN users as u ON fi.user_id=u.id $where ");
		
		if ($file)
                    return ['msg'=>'Successfully fetched document','data'=>$file,'success'=>true];
		else
                    return ['msg'=>'Document doesnot exist with id - '.$id,'success'=>false];
	}
    
    /**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return flat specific documents
	 */
	public function FlatDocument($id)
	{
        $arr=[];
        $where = " where fi.folder_type = 'admin_folder' and fi.id =".$id;
		$file = \DB::selectOne("select fi.*,u.first_name,u.last_name from file as fi JOIN users as u ON fi.user_id=u.id $where ");
        $role_ids = \DB::select("select role_id from file_access where file_id =".$id);
		foreach($role_ids as $role_id)
        {
            $arr[] = (int)$role_id->role_id;
        }
        if ($file)
                    return ['msg'=>'Successfully fetched document','data'=>$file,'role_ids'=>$arr,'success'=>true];
		else
                    return ['msg'=>'Document doesnot exist with id - '.$id,'success'=>false];
	}
    
    /*
     * update flat specific document
     */
	
    public function updateFlatDocument($id)
    {
        $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
		// get all posted form data
		$data = \Input::all();
//        print_r($data);exit;
        $folder_id = $data['folder_id'];
                if (\Request::hasFile('file'))
                {
                    $folder_path = 'uploads/'.$society_id.'/files/resident/'.$folder_id.'/';
                    $folder_type = 'admin_folder';
                    $data['id']=$id;
                    $file =  \App::make('services')->uploadFile($data,$folder_type,$folder_path,$folder_id);
//                    print_r($file);exit;
                   if(!empty($file))
                    {
                        if(isset($data['role_id']))
                        {                    
                            FileAccess::where('file_id','=',$id)->delete();
                            foreach ($data['role_id'] as $roles_id){

                                $role = AclRole::find($roles_id);
                                $file_access = new FileAccess();
                                $file_access->file()->associate($file);
                                $file_access->role()->associate($role);
                                $file_access->save();
                            }
                        }
                        return ['msg'=>'Document changed successfully','data'=>$file,'success'=>true];
                    }else{

                        return ['msg'=>'Error in document upload','success'=>false];
                    }
                }else{
                    $adminFile = File::find($id);
                    $adminFile->fill($data);
                    $adminFile->save();
                    if(isset($data['role_id']))
                        {                    
                            FileAccess::where('file_id','=',$id)->delete();
                            foreach ($data['role_id'] as $roles_id){

                                $role = AclRole::find($roles_id);
//                                print_r($role);exit;
                                $file_access = new FileAccess();
                                $file_access->file()->associate($adminFile);
                                $file_access->role()->associate($role);
                                $file_access->save();
                            }
                        }
                    return ['msg'=>'Document data updated successfully','data'=>$adminFile,'success'=>true];
                }
    }
    
	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function folder($id)
	{
//		$offset = \Input::get('offset');
//		
//		$item = \DB::select(
//				'select e.*,u.first_name from entity e inner join users u on u.id = e.user_id where e.entity_type_id = ? and e.id = ? limit 1',
//				array(Entity::Entity_TYPE_FOLDER,$id)
//		);
//	
//		return $item;
            $post = \DB::selectOne('select resident_folder.* from resident_folder where id="'.$id.'" and deleted_at IS NULL ');
		
		if (!$post)
			return ['msg'=>'Folder doesnot exist with id - '.$id];
		
		return $post;
	}
	
//	public function deleteFolder($id) {
//		
//		Entity::where('id','=',$id)->first()->delete();
//		
//		return ['msg'=>'folder with id '.$id.' deleted successfully.'];
//	}
        
        public function deleteDocument() {
		
        $id = \Input::get('id');
//        print_r($id);exit;
		$file = File::where('id','=',$id)->delete();
        $file_access = FileAccess::where('file_id','=',$id)->delete();
		
		return ['msg'=>'document with id '.$id.' deleted successfully.','success'=>true];
	}
        public function deleteFlatDocument()
    {
          $id = \Input::get('id');
          $file_details = File::find($id);
          if(file_exists($file_details->physical_path))
          {
            unlink($file_details->physical_path);
          }
        $file_access = FileAccess::where('file_id','=',$id)->delete();
        $file = File::where('id','=',$id)->forceDelete();
        $folder = File::where('folder_id','=',$file_details->folder_id)->where('folder_type','=','admin_folder')->count();
       if(!$folder)
       {
           $admin_folder = AdminFolder::where('id','=',$file_details->folder_id)->forceDelete();
       }
        
        
		
		return ['msg'=>'document with id '.$id.' deleted successfully.','success'=>true];
    }
        public function deleteResidentFolder() {
		
                $id = \Input::get('id');
                $sql = "select count(*) as total from file where folder_id = :folder_id and folder_type = 'resident_folder' and deleted_at IS NULL";
                $result = \DB::selectOne($sql,['folder_id'=>$id]);
                if($result->total)
                {
                    return ['msg'=>'folder error','folder_error'=>'Files are assigned under this folder, please delete those files first!','success'=>false];
                }
		ResidentFolder::where('id','=',$id)->delete();
		
		return ['msg'=>'Folder with id '.$id.' deleted successfully.','success'=>true];
	}
        
        public function getAllResidentFolders()
        {
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            $data = \DB::select("select * from resident_folder where society_id = :society_id and deleted_at IS NULL ORDER BY name",['society_id'=>$society_id]);
            return array(
                'data'=>$data,
            );
            
        }
}
