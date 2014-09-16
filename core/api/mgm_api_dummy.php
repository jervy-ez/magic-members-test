<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members api dummy controller, test api purpose
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_api_dummy extends mgm_api_controller{
 	// construct
	function __construct(){
		// php4
		$this->mgm_api_dummy();
	}
	
	// php4
	function mgm_api_dummy(){
		// parent
		parent::__construct();
	}
	
	// action_verb : get verb 
	function test_get($id=0){
		// params		
		$params = array('id' => $id);		
		// return
		return array(array('status' => true, 'message'=>'GET Verb request', 'data' => $this->request->data['get'], 'params'=>$params), 200);				
	}
	
	// action_verb : post verb
	function test_post(){
		// return
		return array(array('status' => true, 'message'=>'POST Verb request', 'data' => $this->request->data['post']), 200);
	}
	
	// action_verb : put verb
	function test_put(){
		// return
		return array(array('status' => true, 'message'=>'PUT Verb request', 'data' => $this->request->data['put']), 200);
	}
	
	// action_verb : delete verb
	function test_delete(){
		// return
		return array(array('status' => true, 'message'=>'DELETE Verb request', 'data' => $this->request->data['delete']), 200);
	}
 }
 
 // return name of class 
 return basename(__FILE__,'.php');
// end file /core/api/mgm_api_dummy.php