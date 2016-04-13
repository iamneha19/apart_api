<?php
namespace ApartmentApi\Services;
use ApartmentApi\Models\File;
use ApartmentApi\Models\OauthToken;

class Common {
	
	public function getUser($accessToken) {
		
		$sql = <<<EOF
		
		select society.name as society_name,users.id as user_id,users.first_name,users.password_changed,users.last_name,users.email,users.contact_no,flat.id as flat_id,flat.flat_no,
		block.id as block_id,block.block,oauth_token.society_id,user_society.relation
		from oauth_token 
		inner join users on users.id = oauth_token.user_id
		inner join user_society on user_society.society_id = oauth_token.society_id 
				and user_society.user_id = oauth_token.user_id
        inner join society on user_society.society_id = society.id        
		left join flat on flat.id = user_society.flat_id
		left join block on block.id = user_society.block_id
		where oauth_token.token = :token group by users.id
		
EOF;
		
		return \DB::selectOne($sql,['token'=>$accessToken]);
		
	}
        
        public function getSuperAdminUser($accessToken) {
		
		$sql = <<<EOF
		
		select users.id as user_id,users.first_name,users.last_name,users.email
		from oauth_super_admin_token 
		inner join users on users.id = oauth_super_admin_token.user_id
		
		where oauth_super_admin_token.token = :token
		
EOF;
		
		return \DB::selectOne($sql,['token'=>$accessToken]);
		
	}
	
	
	public function validate($data, $rules) {
		
		$validator = \Validator::make($data, $rules);
		
		if ($validator->fails())
		{
			return ['success'=>false,'msg'=>'Input errors','input_errors'=>$validator->messages()];
		}
		
		return true;
		
	}
        
        public function uploadFile($data,$folder_type,$folder,$id) {
          
            $user = OauthToken::find(\Input::get('access_token'))->user()->first();
            $data['folder_type']=$folder_type;
            $path = public_path(); // root  folder
            $destinationPath = $path.'/'.$folder;
            $file_name = \Request::file('file')->getClientOriginalName();
            $data['name'] = $file_name;
            $filenameitems = explode(".", $file_name);
            $ext=$filenameitems[count($filenameitems) - 1];
//            $new_file_name = time().'_'.rand(1, 20).'.'.$ext;
            $uploaded_name = $filenameitems[0]; 
            $new_file_name = str_replace(" ", "_",$uploaded_name); // Replaced white spaces with _
            $new_file_name = $new_file_name.'_'.date('d-m-y').'_'.rand(1, 20).'.'.$ext;
            
            $data['http_path'] = asset('/').$folder.$new_file_name;
            $data['physical_path'] = $destinationPath.$new_file_name;
            \Request::file('file')->move($destinationPath,$new_file_name);
           
            if(empty($data['id'])){               // To check Update / Create
                $file = new File();
            }  else {
                $file = File::find($data['id']);
                if (file_exists($file->physical_path)) {
                    unlink($file->physical_path); // To delete previous file 
                }
                $file->folder_id = $id;
                
            }
                $file->user()->associate($user);
               
                if(isset($data['category_id']))   
                {
                    $file->category_id = $data['category_id'];
                }
               $file->folder_id = $id;
               // dd($data);
                $file->fill($data);
                if ($folder_type != 'forum')
               		$file->folder_id = $id; 
                $file->save();
                return $file;
            
            
        }
	
	
}
