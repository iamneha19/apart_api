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
class SocietyReminder implements SelfHandling  {
    //put your code here
    protected $societyId;
    
    function __construct($societyId) 
    {
        $this->societyId = $societyId;
    }
    function handle()
    {
        $results = $this->getSocietyDocument();
        return $results;
    }
    
    public function getSocietyDocument()
    {
        $results = \DB::select("SELECT DISTINCT category.id,category.name,category.is_mandatory,file.name AS file_name,society.name AS society_name FROM category 
                                    LEFT JOIN `file` ON  category.id = file.category_id AND file.`deleted_at` IS NULL
                                    INNER JOIN society ON category.society_id = society.id
                                    WHERE category.type = 'Society Document'
                                    AND category.is_mandatory = 1
                                    AND category.society_id = $this->societyId 
                                    GROUP BY category.id
                        ");
        
        return $results;
    }
}
