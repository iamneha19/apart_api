<?php
namespace ApartmentApi\Commands\Reminder;

use Illuminate\Contracts\Bus\SelfHandling;
use DB;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MeetingReminder
 *
 * @author neha.agrawal
 */
class FlatDocumentReminder implements SelfHandling  {
    //put your code here
    protected $societyId;
    
    function __construct($societyId,$userId) 
    {
        $this->societyId = $societyId;
        $this->userId = $userId;
    }
    function handle()
    {
        $results = $this->getFlatDocument();
        return $results;
    }
    
    public function getFlatDocument()
    {
        $cat_arr = [];
        $flats = [];
        $flat_cat_arr = [];
        
        $categories = \DB::select("select distinct id AS category_id,name from category where type='Flat Document' and is_mandatory = 1 and society_id='".$this->societyId."'");    
        foreach($categories as $category)
        {
            $category_arr = (array)$category;
            array_push($cat_arr,$category_arr);
        }
        $folder_ids = \DB::select("SELECT flat.flat_no,admin_folder.id,user_society.flat_id FROM user_society LEFT JOIN admin_folder ON admin_folder.flat_id = user_society.flat_id INNER JOIN flat ON user_society.flat_id = flat.id WHERE user_society.status = 1 and user_society.society_id = '".$this->societyId."' and user_society.user_id='".$this->userId."'"); 
//        print_r($folder_ids);
        foreach($folder_ids as $folder_id)
        {
          if($folder_id->id=='')
              {
//                  $arr['flat_no'] = $folder_id->flat_no;
            $flats[$folder_id->flat_no]['cat'] = $cat_arr;
               }
        }
        $folder_ids = \DB::select("SELECT file.deleted_at, flat.flat_no,file.folder_id,admin_folder.society_id,GROUP_CONCAT(distinct category_id) as categories_id FROM file INNER JOIN admin_folder ON admin_folder.id = file.folder_id INNER JOIN flat ON flat.id = admin_folder.flat_id WHERE file.folder_id IN (SELECT admin_folder.id FROM user_society LEFT JOIN admin_folder ON admin_folder.flat_id = user_society.flat_id WHERE user_society.status = 1 and user_society.society_id = '".$this->societyId."' and user_society.user_id='".$this->userId."')  GROUP BY folder_id");
        if(!empty($folder_ids))
//			 $flats=array();
        {
            foreach($folder_ids as $folder_id)
            {
                $flat_cat_arr = [];
                $categories_id = $folder_id->categories_id;
                $category_id = explode(",",$categories_id);
                foreach($categories as $c_id)
                {
                    if(!in_array($c_id->category_id,$category_id))
                    {
                        $category_data = (array)$c_id;
                        array_push($flat_cat_arr,$category_data);
                        $flats[$folder_id->flat_no]['cat'] = $flat_cat_arr;
                    }
                }
            }
        }
//            print_r($flats);
//            $folder_ids = \DB::select("SELECT file.deleted_at,flat.flat_no,file.folder_id,admin_folder.society_id,GROUP_CONCAT(distinct category_id) as categories_id FROM file INNER JOIN admin_folder ON admin_folder.id = file.folder_id INNER JOIN flat ON flat.id = admin_folder.flat_id WHERE file.deleted_at IS NOT NULL AND file.folder_id IN (SELECT admin_folder.id FROM user_society LEFT JOIN admin_folder ON admin_folder.flat_id = user_society.flat_id WHERE user_society.society_id = '".$this->societyId."' and user_society.user_id='".$this->userId."')  GROUP BY folder_id");
//            foreach($folder_ids as $folder_id)
//            {
//                $categories_id = $folder_id->categories_id;
//                $category_id = explode(",",$categories_id);
//                foreach($categories as $c_id)
//                {
//                    $flat_cat_arr = [];
//                   if(in_array($c_id->category_id,$category_id))
//                   {
//                      $category_data = (array)$c_id;
//                      array_push($flat_cat_arr,$category_data);
//                      $flats[$folder_id->flat_no]['cat'] = $flat_cat_arr;
//                   }
//                }
//            }
//            print_r($flats);exit;
        return $flats;
    }
}
