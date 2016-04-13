<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\Album;
use ApartmentApi\Models\File;
use ApartmentApi\Models\OauthToken;

Class AlbumController extends Controller {
    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('rest');
	}
	
        public function getAlbums() 
        {
                $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
                
                $search = \Input::get('search',null);
		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',8)];
                $where = " where a.deleted_at IS NULL and a.society_id = ".$society_id;
                
                if ($search) {
			$where .= ' and a.name like :search';
			$binds['search'] = '%'.$search.'%';
		}
                
                $results = \DB::select("select a.*,f.http_path as image_url,f.name as image_name ,date_format(a.created_at,'%d-%m-%Y') as created_at from album AS a LEFT JOIN file AS f ON a.id = f.folder_id and f.folder_type = 'album'  $where GROUP BY a.id ORDER By a.id DESC limit :limit offset :offset",$binds);
                $count = \DB::selectOne("select count(*) as total from album as a $where",
                        $search ? array('search'=>'%'.$search.'%') : array());
                               
		return ['total'=>$count->total,'data'=>$results,'success'=>true];

	}
        
        /// Create album
        public function create() {
		
		// get all posted form data
		$attributes = \Input::all();
                $oauthToken = OauthToken::find(\Input::get('access_token'));
		$user = $oauthToken->user()->first();
                $society_id = $oauthToken->society_id;
                
                 $sql = 'select count(*) as total from album where society_id = :society_id and name = :album_name';
                $result = \DB::selectOne($sql,['society_id'=>$society_id,'album_name'=>$attributes['name']]);
                if($result->total){
                    return ['success'=>false,'msg'=>'This album already exists'];
                }
                
                $attributes['society_id'] = $society_id;
                $album = new Album();
                $album->user()->associate($user);
		$album->fill($attributes);
		$album->save();
		
                return ['msg'=>'Album created successfully','success'=>true];
                
	}
        
        public function update($id = null) {
		
		// get all posted form data
		$attributes = \Input::all();
                $oauthToken = OauthToken::find(\Input::get('access_token'));
                $society_id = $oauthToken->society_id;
		
                
                $album = Album::where('id','=',$id)
				->firstOrFail();
                if (!$album)
			return ['error'=>'Album not found with id: '.$id,'success'=>false];
                
                 $sql = 'select count(*) as total from album where society_id = :society_id and name = :album_name and id != :id';
                $result = \DB::selectOne($sql,['society_id'=>$society_id,'album_name'=>$attributes['name'],'id'=>$id]);
                if($result->total){
                    return ['success'=>false,'msg'=>'This album already exists'];
                }
                
		$album->fill($attributes);
		$album->save();
		
                return ['msg'=>'Album updated successfully','success'=>true];
	}
        
        public function delete() {
		
		// get all posted form data
		$data = \Input::all();
                $id = $data['id'];
		$oauthToken = OauthToken::find(\Input::get('access_token'));
		$user = $oauthToken->user()->first();
                $society_id = $oauthToken->society_id;
                
                $album = Album::where('id','=',$id)
				->firstOrFail();
                if (!$album)
			return ['error'=>'Album not found with id: '.$id,'success'=>false];
           
		$album->delete();
                
//                $where = " where fi.folder_type = 'album' and fi.folder_id = :folder_id";
//                $binds['folder_id'] = $id;
//                               
//                $photos = \DB::select("select fi.* from file as fi $where",$binds);
//                
//                foreach($photos as $photo){
//                    unlink($photo->physical_path); // To delete photo 
//                }
                
		$affectedRows = File::where('folder_type', '=', 'album')->where('folder_id', '=', $id)->delete();
//                if($affectedRows){
//                    $folder_path = 'uploads/'.$society_id.'/album/'.$id;
//                    $destinationPath = public_path().'/'.$folder_path;
//                    rmdir($destinationPath);  // To delete directory
//                }
                  
                return ['msg'=>'Album deleted successfully','success'=>true];
	}
        
	public function photos($id = null) {
              
                $folder_id = $id;
		$binds = ['offset'=>\Input::get('offset',0),'limit'=>\Input::get('limit',10)];
                
                $where = " where fi.folder_type = 'album' and fi.folder_id = :folder_id and fi.deleted_at IS NULL";
                $binds['folder_id'] = $folder_id;
                               
                $results = \DB::select("select fi.*,u.first_name,u.last_name,fi.name as file_name from file as fi JOIN `users` as u ON fi.user_id=u.id  $where ORDER BY fi.id DESC limit :limit offset :offset",$binds);
//                $count = \DB::selectOne("select count(*) as total from file as fi JOIN `users` as u ON fi.user_id=u.id $where", array('folder_id'=>$folder_id));

		return ['total'=>0,'data'=>$results,'success'=>true];
               
	}
        
        public function deletePhoto() {
		
		// get all posted form data
		$data = \Input::all();
                $id = $data['id'];
		$oauthToken = OauthToken::find(\Input::get('access_token'));
		$user = $oauthToken->user()->first();
                $society_id = $oauthToken->society_id;
                
                $file = File::find($id);
                if (!$file)
			return ['error'=>'Photo not found with id: '.$id,'success'=>false];
//                if($file){
//                    unlink($file->physical_path); // To delete photo 
//                }
           
		$file->delete();
              
                return ['msg'=>'Photo deleted successfully','success'=>true];
                 
                
	}
	
        public function getAlbum($id) {	
                $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
        
                $where = " where a.id = $id and a.society_id = ".$society_id;
              
                $results = \DB::selectOne("select a.*,u.first_name,u.last_name from album as a JOIN users as u ON a.user_id=u.id $where");
               
               
		return ['data'=>$results,'success'=>true];    
	}
        
	public function getFile($id) {
		
                $where = " where fi.folder_type = 'admin_folder' and fi.id =".$id;
		$file = \DB::selectOne("select fi.*,u.first_name,u.last_name,fi.name as file_name from file as fi JOIN users as u ON fi.user_id=u.id $where ");
		
		if ($file)
                    return ['msg'=>'Successfully fetched file','data'=>$file,'success'=>true];
		else
                    return ['msg'=>'File doesnot exist with id - '.$id,'success'=>false];    
	}
        
        /// upload photos
        public function upload() {
                $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
		// get all posted form data
		$data = \Input::all();

                if (\Request::hasFile('file'))
                {
                    $folder_path = 'uploads/'.$society_id.'/album/'.$data['folder_id'].'/';
                    $folder_type = 'album';
                    $data['visible_to']= 1;
                    $file =  \App::make('services')->uploadFile($data,$folder_type,$folder_path,$data['folder_id']);
                   if(!empty($file))
                    {
                        return ['msg'=>'File created successfully','success'=>true];
                    }else{

                        return ['msg'=>'Error in file upload','success'=>false];
                    }
                }else{
                    return ['msg'=>'Error in file upload','success'=>false];
                }
                
	}
       
}