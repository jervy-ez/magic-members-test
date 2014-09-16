<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members api subscription packages controller
 *
 * @package MagicMembers
 * @version 1.0
 * @since 2.6.0
 */
 class mgm_api_subscription_packages extends mgm_api_controller{
 	// construct
	public function __construct(){
		// php4
		$this->mgm_api_subscription_packages();
	}
	
	// php4
	public function mgm_api_subscription_packages(){
		// parent
		parent::__construct();
	}
	
	/** 
	 * get subscription packages	
	 *
	 * @param int optional subscription id
	 * @return subscription packages 
	 * @verb GET
	 * @action list 	
	 * @url <site>/mgmapi/subscription_packages.<format>
	 * @url <site>/mgmapi/subscription_packages/:id.<format>
	 * @since 1.0
	 */
	public function index_get($id=false){
		global $wpdb;
		
		// init		
		$status = 'success'; 
		$total_rows = 0;
		$subscription_packages = array();

		// loop
		if( $packs = mgm_get_all_subscription_package() ){
			// loop
			foreach($packs as $pack){
				// code check
				if($id) if($id != $pack['id']) continue;
				
				// set
				$subscription_packages[] = $pack;
			}			
			// total
			$total_rows = count($subscription_packages);
		} 	
		
		// base
		$data = array('total_rows'=>$total_rows);
		// data
		if((int)$id>0){
			// message
			$message = sprintf(__('Get subscription package by id#%d response','mgm'), $id);
			// data
			if($total_rows > 0) $data = $data + array('subscription_package'=>array_shift($subscription_packages));
		}else{
			// message
			$message = sprintf(__('Get subscription packages response - %d subscription package(s) found','mgm'), $total_rows);
			// data
			if($total_rows > 0) $data = $data + array('subscription_packages'=>$subscription_packages);
		}
		
		// response
		$response = array('status'  => $status, 
		                  'message' => $message, 
		                  'data'    => $data
						 );
						 
		// return
		return array($response, 200);	
	}
	
	/** 
	 * create subscription package
	 *
	 * @param none
	 * @return subscription package
	 * @verb POST
	 * @action create 	
	 * @url <site>/mgmapi/subscription_packages/create.<format>
	 * @since 1.0
	 */	
	public function create_post(){
		// post vars
		$post_vars = $this->request->data['post'];	
			
		// status	
		$status = 'error';  	
		$data   = array();
		
		// message
		$message = __('Subscription Package create failed, ','mgm');
		
		// save
		if(!$errors = $this->_validate_data('create', $post_vars)){			
			// trim
			$membership_type = trim($post_vars['membership_type']);// required
			// pack
			$pack = (isset($post_vars['pack'])) ? $post_vars['pack'] : array();					
			// create
			if($n_pack = mgm_add_subscription_package($membership_type, $pack)){					
				// status	
				$status = 'success';  		
				// message
				$message = __('Subscription package created successfully','mgm');
				// data
				$data = array('membership_type'=>$membership_type, 'pack'=>$n_pack);
			}else{
				$message .= __('Database error','mgm');
			}				
		}	
			
		// response
		$response = array('status'  => $status, 
		                  'message' => $message, 
		                  'data'    => $data
						 );
						 
		// errors
		if($errors !== FALSE)  $response = $response + array('errors'=>$errors);
						 
		// return
		return array($response, 200);
	}
		
	/** 
	 * update subscription package
	 *
	 * @param none
	 * @return subscription package
	 * @verb POST
	 * @action update 	
	 * @url <site>/mgmapi/subscription_packages/update.<format>
	 * @since 1.0
	 */	
	public function update_post(){
		// post vars
		$post_vars = $this->request->data['post'];	
			
		// status	
		$status = 'error';  	
		$data   = array();
		
		// message
		$message = __('Subscription package update failed, ','mgm');
		
		// save
		if(!$errors = $this->_validate_data('update', $post_vars)){
			// trim
			$id = (int)$post_vars['id'];
			// pack
			$pack = (isset($post_vars['pack'])) ? $post_vars['pack'] : array();		
			// update
			if( mgm_update_subscription_package($id, $pack) ){					
				// status	
				$status   = 'success';  		
				// message
				$message = __('Subscription package updated successfully','mgm');
				// data
				$data    = array('pack'=>mgm_get_subscription_package($id));
			}else{
				$message .= __('Database error','mgm');
			}				
		}	
			
		// response
		$response = array('status'  => $status, 
		                  'message' => $message, 
		                  'data'    => $data
						 );
		
		// errors
		if($errors !== FALSE)  $response = $response + array('errors'=>$errors);
						 
		// return
		return array($response, 200);
	}
	
	/** 
	 * delete subscription package
	 *
	 * @param none
	 * @return subscription package 
	 * @verb DELETE
	 * @action delete 	
	 * @url <site>/mgmapi/subscription_package/delete.<format>
	 * @since 1.0
	 */	
	public function delete_delete(){
		// delete vars
		$delete_vars = $this->request->data['delete'];	
		
		// status	
		$status = 'error';  	
		$data   = array();
		
		// message
		$message = __('Subscription package delete failed, ','mgm');		
		
		// delete
		if(!$errors = $this->_validate_data('delete', $delete_vars)){	
			// code			
			$id = $delete_vars['id'];
			// delete
			if( mgm_delete_subscription_package($id) ){					
				// status	
				$status  = 'success';  		
				// message
				$message = __('Subscription package deleted successfully','mgm');
				// data
				$data    = array('subscription_package'=>array('id'=>$id));
			}else{
				$message .= __('Database error','mgm');
			}				
		}	
			
		// response
		$response = array('status'  => $status, 
		                  'message' => $message, 
		                  'data'    => $data
						 );
		
		// errors
		if($errors !== FALSE)  $response = $response + array('errors'=>$errors);
						 
		// return
		return array($response, 200);
	}
	
	/** 
	 * delete all subscription package
	 *
	 * @param none
	 * @return subscription package 
	 * @verb DELETE
	 * @action delete 	
	 * @url <site>/mgmapi/subscription_package/delete_all.<format>
	 * @since 1.0
	 */	
	public function delete_all_delete(){			
		// status	
		$status = 'error';  	
		$data   = array();
		// message
		$message = __('Subscription Packages delete failed, ','mgm');			
		// delete
		if( mgm_delete_all_subscription_package() ){					
			// status	
			$status   = 'success';  		
			// message
			$message = __('Subscription packages deleted successfully','mgm');			
		}	
		
		// response
		$response = array('status'  => $status, 
		                  'message' => $message, 
		                  'data'    => $data
						 );
						 
		// return
		return array($response, 200);
	}
	
	/** 
	 * get subscription packages	
	 *
	 * @param int optional subscription id
	 * @return subscription packages 
	 * @verb GET
	 * @action list 	
	 * @url <site>/mgmapi/subscription_packages.<format>
	 * @url <site>/mgmapi/subscription_packages/:id.<format>
	 * @since 1.0
	 */
	public function members_get($id=NULL){
		global $wpdb;
		
		// init
		$subscription_packages = array();
		$total_rows = 0;
		$status = 'success';
						
		// loop
		if( $packs = mgm_get_all_subscription_package() ){
			// loop
			foreach($packs as $pack){
				// id flter
				if($id) if($id != $pack['id']) continue;
				// membership type
				$membership_type = $pack['membership_type'];				
				// description
				$description = mgm_stripslashes_deep($pack['description']);
				$members = mgm_get_members_with('membership_type', $pack['membership_type'], array('pack_id' => $pack['id']), 'count');
				// set
				$subscription_packages[] = array('id'=>$pack['id'], 'membership_type'=>$membership_type, 'description'=>$description, 'members'=>$members);
			}
			// total
			$total_rows = count($subscription_packages);
		}
		
		// base
		$data = array('total_rows'=>$total_rows);				
		// data
		if((int)$id>0){
			// message
			$message = sprintf(__('Get members of subscription package by id#%d response','mgm'), $id);			
			// data
			if($total_rows > 0) $data = $data + array('subscription_packages'=>array_shift($subscription_packages));
		}else{
			// message
			$message = sprintf(__('Get members of subscription packages response - %d subscription package(s) found','mgm'), $total_rows);
			// data
			if($total_rows > 0) $data = $data + array('subscription_packages'=>$subscription_packages);
		}
		
		// response
		$response = array('status'  => 'success', 
		                  'message' => $message, 
		                  'data'    => $data
						 );
						 
		// return
		return array($response, 200);	
	}
	
	// private ------------------------------------------------------------------------
	
	// validate
	function _validate_data($action, $data){
		// errors
		$errors = array();
		// action
		switch($action){
			case 'create':
				// required fields
				$req_flds = array('membership_type');
				// loop
				foreach($req_flds as $req_fld){
					// check
					if(!isset($data[$req_fld]) || isset($data[$req_fld]) && empty($data[$req_fld])){
						$errors[$req_fld . '_required'] = sprintf(__('%s is a required parameter', 'mgm'), $req_fld);
					}
				}				
			break;
			case 'update':
				// required fields
				$req_flds = array('id');
				// loop
				foreach($req_flds as $req_fld){
					// check
					if(!isset($data[$req_fld]) || isset($data[$req_fld]) && empty($data[$req_fld])){
						$errors[$req_fld . '_required'] = sprintf(__('%s is a required parameter', 'mgm'), $req_fld);
					}
				}				
			break;
			case 'delete':
				// required fields
				$req_flds = array('id');
				// loop
				foreach($req_flds as $req_fld){
					// check
					if(!isset($data[$req_fld]) || isset($data[$req_fld]) && empty($data[$req_fld])){
						$errors[$req_fld . '_required'] = sprintf(__('%s is a required parameter', 'mgm'), $req_fld);
					}
				}
			break;	
		}
		
		// return
		return (count($errors) > 0) ? $errors : false;
	}
}
// return name of class 
 return basename(__FILE__,'.php');
// end file /core/api/mgm_api_subscription_packages.php			