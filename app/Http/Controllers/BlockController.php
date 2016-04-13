<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApartmentApi\Http\Controllers;

use ApartmentApi\Models\Block;
use ApartmentApi\Models\OauthToken;
use Input;

Class BlockController extends Controller {
     protected $input;
    /**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(Input $input)
	{
            $this->input = $input;
            $this->middleware('rest');
	}
	
	public function getBlockList() {
            
        $oauthToken = OauthToken::find(\Input::get('access_token'));
        $society_id = $oauthToken->society_id;
        $search = $this->input->get('search',null);
        $where = 'block.society_id='.$society_id;
        $sort = '';
        $whereSep = '';
        $bindings = array();
        
        if ($this->input->get('search',null)) {
            $where .= ' and block.block like :block';
            $whereSep = true;
            $bindings['block'] = '%'.\Input::get('search').'%';
        }
        $where = $where ? ' where '.$where : '';
        
        if (\Input::get('sort',null)){
			$sort = ' order by '.\Input::get('sort',null).' '.\Input::get('sort_order','desc').' ';
		}
		
        
        $data = \DB::select("select * from block $where $sort limit :limit offset :offset",
                array_merge($bindings,array(
                                    'limit'=>\Input::get('limit',2),
                                    'offset'=>\Input::get('offset',0)
                                    )
                                )
                            );
	$count = \DB::selectOne(
				"select count(block.id) total from block
				 $where ",
					$bindings
				);        
        return array(
                        'total'=>$count->total,
                        'data'=>$data
                    );
            }
	
	public function getBlock($id) {
		
		$post = Block::find($id);
		
		if (!$post)
			return ['msg'=>'user doesnot exist with id - '.$id];
		
		return $post;
	}
        /// used for both update and delete action
        public function create() {
		
		// get all posted form data
		$attributes = \Input::all();
                $oauthToken = OauthToken::find(\Input::get('access_token'));
                $society_id = $oauthToken->society_id;
                $user = $oauthToken->user()->first();
                
                $attributes['society_id'] = $society_id;
               
		// sample validation
                $validator = \Validator::make(
				$attributes,
				array('block' => 'required')
                               
		);
		
		
		if ($validator->fails())
			return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false]; 
                
                $result = Block::where('society_id','=',$society_id)
				->where('block','=',$attributes['block'])
				->first(['id']);
                if($result)
                    return ['msg'=>'Block error','block_error'=>'Block is already exists','success'=>false]; 
                
                $block = new Block();
                $block->user()->associate($user);
		$block->fill($attributes);
		$block->save();
		
		if($block->id)
		{
			return ['msg'=>'block created successfully','success'=>true];
		}else{
			return ['msg'=>'Could not create the block','success'=>false];
		}
	}
        
        public function update($id = null){
//            get all posted form data
            $attributes = \Input::all();
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            
            
            $block = Block::find($id,['id']);
            if (!$block)
                return ['msg'=>'block with id - '.$attributes[$id].' does not exist','success'=>false];
            // sample validation
            $validator = \Validator::make(
                            $attributes,
                            array('block' => 'required')

            );


            if ($validator->fails())
                    return ['msg'=>'Input errors','input_errors'=>$validator->messages(),'success'=>false]; 
            
            $result = Block::where('society_id','=',$society_id)
				->where('block','=',$attributes['block'])
                                ->where('id','!=',$id)
				->first(['id']);
            if($result)
                return ['msg'=>'Block error','block_error'=>'Block is already exists','success'=>false]; 

            $block->fill($attributes);
            $block->save();
         
            return ['msg'=>'block updated successfully','success'=>true];
            
        }
        
        public function delete(){
            $data = \Input::all();
            $id = $data['id'];
            $sql = 'select count(*) as total from flat where block_id = :block_id';
            $result = \DB::selectOne($sql,['block_id'=>$id]);
            
            if($result->total){
                return ['msg'=>'Block error','block_error'=>'Flats are assigned to this block, please delete those flat first. ','success'=>false];
            }else{
                $block = Block::find($id);
                $block->delete();
                return ['msg'=>'block deleted successfully','success'=>true];
            }
        }
        
        public function getAllBlock()
        {
            $oauthToken = OauthToken::find(\Input::get('access_token'));
            $society_id = $oauthToken->society_id;
            $data = \DB::select("select * from block where society_id = :society_id ORDER BY block",['society_id'=>$society_id]);
            $count = \DB::selectOne("select count(id) total from block where society_id = :society_id",['society_id'=>$society_id]);
            return array(
                'data'=>$data,
                'total'=>$count->total,
            );
        }
}