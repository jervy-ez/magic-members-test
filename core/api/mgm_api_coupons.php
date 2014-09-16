<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members api coupons controller
 *
 * @package MagicMembers
 * @version 1.0
 * @since 2.6.0
 */
 class mgm_api_coupons extends mgm_api_controller{
 	// construct
	public function __construct(){
		// php4
		$this->mgm_api_coupons();
	}
	
	// php4
	public function mgm_api_coupons(){
		// parent
		parent::__construct();
	}
	
	/** 
	 * get coupons	
	 *
	 * @param int $id
	 * @return coupons 
	 * @verb GET
	 * @action list 	
	 * @url <site>/mgmapi/coupons.<format>
	 * @url <site>/mgmapi/coupons/:id.<format>
	 * @since 1.0
	 */
	public function index_get($id=false){
		global $wpdb;
		
		// response status		
		$status = 'success';
		$total_rows = 0;
		
		// fetch
		if($id){
			// get one
			$coupon = mgm_get_coupon($id);
			// total
			$total_rows = ($coupon === FALSE) ? 0 : 1;
		}else{
			// get all
			$coupons = mgm_get_all_coupon();
			// total
			$total_rows = count($coupons);
		}
		
		// base
		$data = array('total_rows'=>$total_rows);	
		// data
		if((int)$id>0){
			// message
			$message = sprintf(__('Get coupon by id#%d response','mgm'), $id, $total_rows);
			// data
			if($total_rows > 0) $data = $data + array('coupon'=>$coupon);
		}else{
			// message
			$message = sprintf(__('Get coupons response - %d coupon(s) found','mgm'), $total_rows);
			// data
			if($total_rows > 0) $data = $data + array('coupons'=>$coupons);
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
	 * create coupon
	 *
	 * @param none
	 * @return coupon 
	 * @verb POST
	 * @action create 	
	 * @url <site>/mgmapi/coupons/create.<format>
	 * @since 1.0
	 */	
	public function create_post(){
		// post vars
		$post_vars = $this->request->data['post'];	
			
		// status	
		$status = 'error';  	
		$data   = array();		
		
		// message
		$message = __('Coupon create failed, ','mgm');
		
		// save
		if(!$errors = $this->_validate_data('create', $post_vars)){				
			// trim
			$data['name']  = trim($post_vars['name']);	// required
			$data['value'] = trim($post_vars['value']); // required
			$data['description'] = trim($post_vars['description']); // required	
			// use limit, optional
			if(isset($post_vars['use_limit'])){
				$data['use_limit'] = $post_vars['use_limit'];
			}
			// product mapping, optional
			if(isset($post_vars['product'])){
				$data['product'] = is_array($post_vars['product'])? json_encode($post_vars['product']) : $post_vars['product'];
			}			
			// expire date, optional		
			if(isset($post_vars['expire_dt'])){
				$data['expire_dt'] = $post_vars['expire_dt'];
			}
			// create
			if($coupon = mgm_add_coupon($data)){					
				// status	
				$status = 'success';  		
				// message
				$message = __('Coupon created successfully','mgm');
				// data
				$data = array('coupon'=>$coupon);
			}else{
				$message .= __('Database error','mgm');
			}				
		}	
			
		// response
		$response = array('status'  => $status, 
		                  'message' => $message, 
		                  'data'    => $data);
		// errors
		if($errors !== FALSE)  $response = $response + array('errors'=>$errors);
						  
		// return
		return array($response, 200);
	}
			
	/** 
	 * update coupon
	 *
	 * @param none
	 * @return coupon 
	 * @verb POST
	 * @action update 	
	 * @url <site>/mgmapi/coupons/update.<format>
	 * @since 1.0
	 */	
	public function update_post(){
		// post vars
		$post_vars = $this->request->data['post'];
		
		// status	
		$status = 'error';  	
		$data   = array();
		
		// message
		$message = __('Coupon update failed, ','mgm');
		
		// save
		if(!$errors = $this->_validate_data('update', $post_vars)){	
			// id			
			$id = (int)$post_vars['id'];			
			// name
			if(isset($post_vars['name'])){
				$data['name'] = trim($post_vars['name']);
			}
			// value
			if(isset($post_vars['value'])){
				$data['value'] = trim($post_vars['value']);
			}
			// description
			if(isset($post_vars['description'])){
				$data['description'] = trim($post_vars['description']);
			}
			// use limit, optional
			if(isset($post_vars['use_limit'])){
				$data['use_limit'] = $post_vars['use_limit'];
			}
			// product mapping, optional
			if(isset($post_vars['product'])){
				$data['product'] = is_array($post_vars['product'])? json_encode($post_vars['product']) : $post_vars['product'];
			}			
			// expire date, optional		
			if(isset($post_vars['expire_dt'])){
				$data['expire_dt'] = $post_vars['expire_dt'];
			}
			
			// create
			if($coupon = mgm_update_coupon($id, $data)){					
				// status	
				$status = 'success';  		
				// message
				$message = __('Coupon updated successfully','mgm');
				// data
				$data = array('coupon'=>$coupon);
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
	 * delete coupon
	 *
	 * @param none
	 * @return coupon 
	 * @verb DELETE
	 * @action delete 	
	 * @url <site>/mgmapi/coupons/delete.<format>
	 * @since 1.0
	 */	
	public function delete_delete(){
		// delete vars
		$delete_vars = $this->request->data['delete'];	
			
		// status	
		$status = 'error';  	
		$data   = array();
		
		// message
		$message = __('Coupon delete failed, ','mgm');		
		
		// delete
		if(!$errors = $this->_validate_data('delete', $delete_vars)){	
			// id			
			$id = $delete_vars['id'];
			// delete
			if( mgm_delete_coupon($id) ){					
				// status	
				$status  = 'success';  		
				// message
				$message = __('Coupon deleted successfully','mgm');
				// data
				$data    = array('coupon'=>array('id'=>$id));
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
	 * delete all coupon
	 *
	 * @param none
	 * @return coupon 
	 * @verb DELETE
	 * @action delete 	
	 * @url <site>/mgmapi/coupons/delete_all.<format>
	 * @since 1.0
	 */	
	public function delete_all_delete(){			
		// status	
		$status = 'error';  	
		$data   = array();
		// message
		$message = __('Coupons delete failed, ','mgm');		
		// delete
		if( mgm_delete_all_coupon() ){					
			// status	
			$status   = 'success';  		
			// message
			$message = __('Coupons deleted successfully','mgm');			
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
				$req_flds = array('name', 'value', 'description');
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
					if(strlen($data['name']) > 150){
						$errors['name_overflow'] = __('Coupon name should not exceed 150 characters','mgm');
					}
					// duplicate
					if(mgm_is_duplicate(TBL_MGM_COUPON, array('name'), '', $data)){
						$errors['name_duplicate'] = __('Duplicate coupon name provided, please try with a different name','mgm');
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
				// check if name already validated
				if(!in_array('name_required', array_keys($errors))){
					// size overflow
					if(isset($data['name']) && strlen($data['name']) > 150){
						$errors['name_overflow'] = __('Coupon name should not exceed 150 characters','mgm');
					}
					// duplicate
					if(mgm_is_duplicate(TBL_MGM_COUPON, array('name'), '`id`<>'.$data['id'], $data)){
						$errors['name_duplicate'] = __('Duplicate coupon name provided, please try with a different name','mgm');
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
// end file /core/api/mgm_api_coupons.php			