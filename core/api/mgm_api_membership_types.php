<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members api membership types controller
 *
 * @package MagicMembers
 * @version 1.0
 * @since 2.6.0
 */
 class mgm_api_membership_types extends mgm_api_controller{
 	// construct
	public function __construct(){
		// php4
		$this->mgm_api_membership_types();
	}
	
	// php4
	public function mgm_api_membership_types(){
		// parent
		parent::__construct();
	}
	
	/** 
	 * get membership_types	
	 *
	 * @param string optional type code
	 * @return membership types 
	 * @verb GET
	 * @action list 	
	 * @url <site>/mgmapi/membership_types.<format>
	 * @url <site>/mgmapi/membership_types/:code.<format>
	 * @since 1.0
	 */
	public function index_get($code=false){
		global $wpdb;
		
		// init
		$status = 'success';
		$total_rows = 0;		
		$membership_types = array();
		
		// loop
		if( $types = mgm_get_all_membership_type() ){
			// loop
			foreach($types as $type){
				// code check
				if($code) if($code != $type['code']) continue;
				
				// set
				$membership_types[] = $type;
			}
			// total
			$total_rows = count($membership_types);
		} 
		
		// base
		$data = array('total_rows'=>$total_rows);
		// data
		if(!empty($code)){
			// message
			$message = sprintf(__('Get membership type by code - %s response','mgm'), $code, count($membership_types));
			// data
			if($total_rows > 0) $data = $data + array('membership_type'=>array_shift($membership_types));
		}else{
			// message
			$message = sprintf(__('Get membership types response - %d membership type(s) found','mgm'), $total_rows);
			// data
			if($total_rows > 0) $data = $data + array('membership_types'=>$membership_types);
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
	 * create membership type
	 *
	 * @param none
	 * @return membership type 
	 * @verb POST
	 * @action create 	
	 * @url <site>/mgmapi/membership_types/create.<format>
	 * @since 1.0
	 */	
	public function create_post(){
		// post vars
		$post_vars = $this->request->data['post'];
		
		// status	
		$status = 'error';  	
		$data   = array();
		
		// message
		$message = __('Membership type create failed, ','mgm');
		
		// save
		if(!$errors = $this->_validate_data('create', $post_vars)){				
			// trim
			$name = trim($post_vars['name']);
			// login_redirect
			$login_redirect = (isset($post_vars['login_redirect'])) ? $post_vars['login_redirect'] : '';
			// logout_redirect
			$logout_redirect = (isset($post_vars['logout_redirect'])) ? $post_vars['logout_redirect'] : '';			

			// create
			if($code = mgm_add_membership_type($name, $login_redirect, $logout_redirect)){					
				// status	
				$status = 'success';  		
				// message
				$message = __('Membership type created successfully','mgm');
				// data
				$data = array('membership_type'=>array('code'=>$code, 'name'=>$name, 'login_redirect'=>$login_redirect,'logout_redirect'=>$logout_redirect));
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
	 * update membership type
	 *
	 * @param none
	 * @return membership type 
	 * @verb POST
	 * @action update 	
	 * @url <site>/mgmapi/membership_types/update.<format>
	 * @since 1.0
	 */	
	public function update_post(){
		// post vars
		$post_vars = $this->request->data['post'];
		
		// status	
		$status = 'error';  	
		$data   = array();
		
		// message
		$message = __('Membership type update failed, ','mgm');
		
		// save		
		if(!$errors = $this->_validate_data('update', $post_vars)){
			// trim
			$code = $post_vars['code'];// required
			$name = trim($post_vars['name']);// required			
			// login_redirect
			$login_redirect = (isset($post_vars['login_redirect'])) ? $post_vars['login_redirect'] : '';
			// logout_redirect
			$logout_redirect = (isset($post_vars['logout_redirect'])) ? $post_vars['logout_redirect'] : '';	
			
			// update
			if( mgm_update_membership_type($code, $name, $login_redirect, $logout_redirect) ){					
				// status	
				$status   = 'success';  		
				// message
				$message = __('Membership type updated successfully','mgm');
				// data
				$data    = array('membership_type'=>array('code'=>$code, 'name'=>$name, 'login_redirect'=>$login_redirect,'logout_redirect'=>$logout_redirect));
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
	 * delete membership type
	 *
	 * @param none
	 * @return membership type 
	 * @verb DELETE
	 * @action delete 	
	 * @url <site>/mgmapi/membership_types/delete.<format>
	 * @since 1.0
	 */	
	public function delete_delete(){
		// delete vars
		$delete_vars = $this->request->data['delete'];
				
		// status	
		$status = 'error';  	
		$data   = array();
		
		// message
		$message = __('Membership type delete failed, ','mgm');		
		
		// delete
		if(!$errors = $this->_validate_data('delete', $delete_vars)){	
			// code			
			$code = $delete_vars['code'];// required
			// delete
			if( mgm_delete_membership_type($code) ){					
				// status	
				$status   = 'success';  		
				// message
				$message = __('Membership type deleted successfully','mgm');
				// data
				$data    = array('membership_type'=>array('code'=>$code));
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
	 * delete all membership type
	 *
	 * @param none
	 * @return membership type 
	 * @verb DELETE
	 * @action delete all	
	 * @url <site>/mgmapi/membership_types/delete_all.<format>
	 * @since 1.0
	 */	
	public function delete_all_delete(){			
		// status	
		$status = 'error';  	
		$data   = array();
		// message
		$message = __('Membership types delete failed, ','mgm');		
		// delete all
		if( mgm_delete_all_membership_type() ){					
			// status	
			$status   = 'success';  		
			// message
			$message = __('Membership types deleted successfully','mgm');
			// data
			$data    = array();
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
	 * get accessible posts by membership type and post type
	 *
	 * @param none
	 * @return posts
	 * @verb GET
	 * @action list 	
	 * @url <site>/mgmapi/membership_types/:membership_type/posts/:post_type.<format>
	 * @since 1.0
	 */		 
	public function posts_get($membership_type, $post_type){
		// status	
		$status = 'success';  	
		$data   = array('membership_type'=>$membership_type, 'post_type'=>$post_type);
		// message
		$message = __('Get posts by membership type response','mgm');		
		// get		
		$membership_contents = mgm_get_membership_contents($membership_type, 'accessible', NULL, $post_type);
		// set data
		$data = array_merge($data, array_slice($membership_contents,0,2));
		// response
		$response = array('status'  => $status, 
		                  'message' => $message, 
		                  'data'    => $data
						 );
		// return
		return array($response, 200);
	}
	
	/** 
	 * get taxonomies by membership type and taxonomy
	 *
	 * @param none
	 * @return post taxonomies 
	 * @verb GET
	 * @action list 	
	 * @url <site>/mgmapi/membership_types/:membership_type/taxonomies/:taxonomy.<format>
	 * @since 1.0
	 */		 
	public function taxonomies_get($membership_type, $taxonomy){
		// status	
		$status = 'success';  	
		$data   = array('membership_type'=>$membership_type, 'taxonomy'=>$taxonomy);
		// message
		$message = __('Get taxonomies by membership type response','mgm');		
		// get		
		$membership_taxonomies = mgm_get_membership_taxonomies($membership_type, NULL, $taxonomy);
		// set data
		$data = array_merge($data, array_slice($membership_taxonomies,0,2));
		// response
		$response = array('status'  => $status, 
		                  'message' => $message, 
		                  'data'    => $data
						 );
		// return
		return array($response, 200);
	}
	
	/** 
	 * get members by membership type
	 *
	 * @param none
	 * @return members
	 * @verb GET
	 * @action list 	
	 * @url <site>/mgmapi/membership_types/:membership_type/members.<format>
	 * @since 1.0
	 */		 
	public function members_get($code=NULL){
		// init		
		$status = 'success'; 
		$total_rows = 0;
		$membership_types = array();
		
		// loop
		if( $types = mgm_get_all_membership_type() ){
			// loop
			foreach($types as $type){
				// code check
				if($code) if($code != $type['code']) continue;
				
				// name
				$name    = mgm_stripslashes_deep($type['name']);
				$members = mgm_get_members_with('membership_type', $type['code'], NULL, 'count');
				// set
				$membership_types[] = array('code'=>$type['code'], 'name'=>$name, 'members'=>$members);
			}
			// total
			$total_rows = count($membership_types);
		} 		
		
		// base
		$data = array('total_rows'=>$total_rows);		
		// data
		if(!empty($code)){
			// message
			$message = sprintf(__('Get members by membership type - %s response','mgm'), $code, count($membership_types));
			// data
			if($total_rows > 0) $data = $data + array('membership_type'=>array_shift($membership_types));
		}else{
			// message
			$message = sprintf(__('Get members by membership types response - %d membership type(s) found','mgm'), $total_rows);
			// data
			if($total_rows > 0) $data = $data + array('membership_types'=>$membership_types);
		}
		
		// response
		$response = array('status'  => $status, 
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
				$req_flds = array('name');
				// loop
				foreach($req_flds as $req_fld){
					// check
					if(!isset($data[$req_fld]) || isset($data[$req_fld]) && empty($data[$req_fld])){
						$errors[$req_fld . '_required'] = sprintf(__('%s is a required parameter', 'mgm'), $req_fld);
					}
				}	
				// check if name already validated
				if(!in_array('name_required', array_keys($errors))){
					// size overflow
					if(strlen($data['name']) > 250){
						$errors['name_overflow'] = __('Type name should not exceed 250 characters','mgm');
					}
					// duplicate
					if(mgm_is_duplicate_membership_type($data['name'])){
						$errors['name_duplicate'] = __('Duplicate type name provided, please try with a different name','mgm');
					}
				}			
			break;
			case 'update':
				// required fields
				$req_flds = array('code', 'name');
				// loop
				foreach($req_flds as $req_fld){
					// check
					if(!isset($data[$req_fld]) || isset($data[$req_fld]) && empty($data[$req_fld])){
						$errors[$req_fld . '_required'] = sprintf(__('%s is a required parameter', 'mgm'), $req_fld);
					}
				}	
				// check if name already validated
				if(!in_array('name_required', array_keys($errors))){
					// size overflow
					if(strlen($data['name']) > 250){
						$errors['name_overflow'] = __('Type name should not exceed 250 characters','mgm');
					}
					// duplicate
					if(mgm_is_duplicate_membership_type($data['name'], $data['code'])){
						$errors['name_duplicate'] = __('Duplicate type name provided, please try with a different name','mgm');
					}
				}			
			break;
			case 'delete':
				// required fields
				$req_flds = array('code');
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
// end file /core/api/mgm_api_membership_types.php			