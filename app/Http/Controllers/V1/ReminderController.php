<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers\V1;

use ApartmentApi\Commands\Reminder\MeetingReminder;
use ApartmentApi\Commands\Reminder\FlatDocumentReports;
use ApartmentApi\Commands\Reminder\FlatDocumentReminder;
use ApartmentApi\Commands\Reminder\SocietyReminder;
use ApartmentApi\Commands\Reminder\OfficialCommReminder;
use ApartmentApi\Models\Category;
use ApartmentApi\Models\Meeting;
use ApartmentApi\Models\OauthToken;
use ApartmentApi\Models\Reminders;
use Carbon\Carbon;
use Illuminate\Http\Request;

Class ReminderController extends ApiController 
{
    protected $input;
    
//    public function getMeetingRemindersList(Request $request)
//        {
//            $oauthToken = OauthToken::find(\Input::get('access_token'));
//            $society_id = $oauthToken->society_id;
//            $search = $request->get('search',null);
//            $where = 'category.society_id="'.$society_id.'" and type = "meeting"';
//            $sort = '';
//            $whereSep = '';
//            $bindings = array();
//            
//            if ($request->get('search',null)) {
//                $where .= ' and (category.name like :name)';
////                print_r($where);exit;
//                $whereSep = true;
//                $bindings['name'] = '%'.$request->get('search').'%';
////                $bindings['venue'] = '%'.\Input::get('search').'%';
//            }
//            $where = $where ? ' where '.$where : '';
//            
//            if (\Input::get('sort',null)){
//                $sort = ' order by '.$request->get('sort',null).' '.$request->get('sort_order','asc').' ';
//            }
////            $results = \DB::select("select meeting.*,users.first_name as user_name from meeting INNER JOIN users ON meeting.user_id=users.id $where $sort limit :limit offset :offset",
//                     $results = \DB::select("SELECT * from category $where $sort limit :limit offset :offset",
//                            array_merge($bindings,array(
//                                    'limit'=>\Input::get('limit',5),
//                                    'offset'=>\Input::get('offset',0)
//                                    )
//                            )
//                        );
//                     print_r($results);exit;
//            $count = \DB::selectOne(
//				"select count(category.id) total from category
//				 $where ",
//					$bindings
//				);        
//		return ['total'=>$count->total,'data'=>$results];
//           }
           
    public function createReminder(Request $request)
    {
        $society_id = OauthToken::find($request->get('access_token'))->society()->first()->id;
        $reminder = new Reminders();
        $alert = $request->get('alert');
        $compare_date = strtotime('January 1 1970 00:00:00');
        $new_date = strtotime('January 1 1970 '.$alert.':00:00');
        $result = ($new_date)-($compare_date);
        $reminder->alert = $alert;
        $reminder->alert_unix = $result;
        $reminder->type_id = $request->get('type_id');
        $reminder->save();
        
        return $this->presentor->make200Response('Successfully loaded.', $reminder);
    }
           
    public function listReminders(Request $request,$type)
    {
//        print_r($type);exit;
         $society_id = OauthToken::find($request->get('access_token'))->society()->first()->id;
//         print_r($society_id);exit;
        $data =  Category::with(['reminder' => function($q)
        {
            $q->select('id', 'type_id','alert');
        }])->where('society_id', $society_id)->where('type',$type)->get();
//        print_r($data);
        if(count($data)==0)
        {
            return $this->presentor->make404Response('Not found.');
        }else{
           return $this->presentor->make200Response('Successfully loaded.', $data);
        }
            
    }
    public function updateReminder(Request $request,$id)
    {
        $society_id = OauthToken::find($request->get('access_token'))->society()->first()->id;
        $alert = $request->get('alert');
        $reminder = Reminders::find($id);
        $compare_date = strtotime('January 1 1970 00:00:00');
        $new_date = strtotime('January 1 1970 '.$alert.':00:00');
        $result = ($new_date)-($compare_date);
        $reminder->alert = $alert;
        $reminder->alert_unix = $result;
        $reminder->type_id = $request->get('type_id');
        $reminder->save();
        
        return $this->presentor->make200Response('Successfully loaded.', $reminder);
    }
    
    public function MeetingTypeCategoryList(Request $request,$type)
    {
        $society_id = OauthToken::find($request->get('access_token'))->society()->first()->id;
         $results = Category::where('type', $type)
                    ->where('society_id', $society_id)->get();
         return $this->presentor->make200Response('Successfully loaded.', $results);
    }
    
    public function reminder(Request $request)
    {
        $society_id = OauthToken::find($request->get('access_token'))->society_id;
        $user_id = OauthToken::find($request->get('access_token'))->user_id;
        $meetingReminder = new MeetingReminder($society_id,$user_id);
        $meetings = $this->dispatch($meetingReminder);
        if($meetings)
        {
            return $this->presentor->make200Response('Successfully loaded.', $meetings);
        }else{
            return $this->presentor->make404Response('Not found.');
        }
    }
    
    public function getSocietyReminder(Request $request) {
            
        $society_id = OauthToken::find($request->get('access_token'))->society_id;
        $societyReminder = new SocietyReminder($society_id);
        $results = $this->dispatch($societyReminder);
//        print_r($results);exit;
        if($results)
        {
            return $this->presentor->make200Response('Successfully loaded.', $results);
        }else{
            return $this->presentor->make404Response('Not found.');
        }     
    }
    
    /*
         * Reminder for flats whose mandatory documents are not uploaded
         */
        public function getFlatDocumentReminder()
        {
//            $arr = [];
//            $new_arr = [];
            $cat_arr = [];
            $flats = [];
            $flat_cat_arr = [];
            $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
            $user_id = OauthToken::find(\Input::get('access_token'))->user_id;
            $flatReminder = new FlatDocumentReminder($society_id,$user_id);
            $results = $this->dispatch($flatReminder);
//            print_r($results);exit;
            if($results)
            {
                return $this->presentor->make200Response('Successfully loaded.', $results);
            }else{
                return $this->presentor->make404Response('Not found.');
            }     
//            $categories = \DB::select("select distinct id AS category_id,name from category where type='flat_document' and is_mandatory = 1 and society_id='".$society_id."'");
//            foreach($categories as $category)
//            {
//                $category_arr = (array)$category;
//               array_push($cat_arr,$category_arr);
//            }
//           $folder_ids = \DB::select("SELECT flat.flat_no,admin_folder.id,user_society.flat_id FROM user_society LEFT JOIN admin_folder ON admin_folder.flat_id = user_society.flat_id INNER JOIN flat ON user_society.flat_id = flat.id WHERE user_society.society_id = '".$society_id."' and user_society.user_id='".$user_id."'"); 
//           foreach($folder_ids as $folder_id)
//           {
//               if($folder_id->id=='')
//               {
////                   $arr['flat_no'] = $folder_id->flat_no;
//                   $flats[$folder_id->flat_no]['cat'] = $cat_arr;
//               }
//               
//           }
//            $folder_ids = \DB::select("SELECT flat.flat_no,file.folder_id,admin_folder.society_id,GROUP_CONCAT(distinct category_id) as categories_id FROM file INNER JOIN admin_folder ON admin_folder.id = file.folder_id INNER JOIN flat ON flat.id = admin_folder.flat_id WHERE file.folder_id IN (SELECT admin_folder.id FROM user_society LEFT JOIN admin_folder ON admin_folder.flat_id = user_society.flat_id WHERE user_society.society_id = '".$society_id."' and user_society.user_id='".$user_id."') GROUP BY folder_id");
//           foreach($folder_ids as $folder_id)
//        {
//            $categories_id = $folder_id->categories_id;
//            $category_id = explode(",",$categories_id);
//             foreach($categories as $c_id)
//             {
//                if(!in_array($c_id->category_id,$category_id))
//                {
//                   $category_data = (array)$c_id;
//                   array_push($flat_cat_arr,$category_data);
//                   $flats[$folder_id->flat_no]['cat'] = $flat_cat_arr;
//                }
//             }
//            
//        }
//       return ['data'=>$flats,'success'=>true];
           
        }
        
        public function getOfficialCommReminder()
        {
            $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
            $user_id = OauthToken::find(\Input::get('access_token'))->user_id;
             $Offcomm_Reminder = new OfficialCommReminder($society_id,$user_id);
            $results = $this->dispatch($Offcomm_Reminder);
            if($results)
            {
                return $this->presentor->make200Response('Successfully loaded.', $results);
            }else{
                return $this->presentor->make404Response('Not found.');
            }   
        }
        
        public function getFlatDocumentReports()
        {
            $cat_arr = [];
            $flats = [];
            $flat_cat_arr = [];
            $society_id = OauthToken::find(\Input::get('access_token'))->society_id;
            $user_id = OauthToken::find(\Input::get('access_token'))->user_id;
            $flatReports = new FlatDocumentReports($society_id,$user_id);
            $results = $this->dispatch($flatReports);
//            print_r($results);exit;
            if($results)
            {
                return $this->presentor->make200Response('Successfully loaded.', $results);
            }else{
                return $this->presentor->make404Response('Not found.');
            }     
        }
}