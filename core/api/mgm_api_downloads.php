<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members api downloads controller
 *
 * @package MagicMembers
 * @version 1.0
 * @since 2.6.0
 */
 class mgm_api_downloads extends mgm_api_controller{
 	// construct
	public function __construct(){
		// php4
		$this->mgm_api_downloads();
	}
	
	// php4
	public function mgm_api_downloads(){
		// parent
		parent::__construct();
	}
	
	/** 
	 * get downloads	
	 *
	 * @param int $id
	 * @return downloads 
	 * @verb GET
	 * @action list 	
	 * @url <site>/mgmapi/downloads.<format>
	 * @url <site>/mgmapi/downloads/:id.<format>
	 * @since 1.0
	 */
	public function index_get($id=false){
		global $wpdb;
		
		// status		
		$status = 'success';
		$total_rows = 0;
		
		// fetch
		if($id){
			// get one
			$download = mgm_get_download($id);
			// total
			$total_rows = ($download === FALSE) ? 0 : 1;
		}else{
			// get all
			$downloads = mgm_get_all_download();
			// total
			$total_rows = count($downloads);
		}
		
		// base
		$data = array('total_rows'=>$total_rows);
		// data
		if((int)$id>0){
			// message
			$message = sprintf(__('Get download by id - %d response','mgm'), $id, $total_rows);
			// data
			if($total_rows > 0) $data = $data + array('download'=>$download);
		}else{
			// message
			$message = sprintf(__('Get downloads response - %d download(s) found','mgm'), $total_rows);
			// data
			if($total_rows > 0) $data = $data + array('downloads'=>$downloads);
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
	 * create download
	 *
	 * @param none
	 * @return download 
	 * @verb POST
	 * @action create 	
	 * @url <site>/mgmapi/downloads/create.<format>
	 * @since 1.0
	 */	
	public function create_post(){
		// post vars
		$post_vars = $this->request->data['post'];		
		
		// status	
		$status = 'error';  	
		$data   = array();
		
		// message
		$message = __('Download create failed, ','mgm');
		
		// save		
		if(!$errors = $this->_validate_data('create', $post_vars)){	
			// trim
			$data['title'] = trim($post_vars['title']);// required
			// file url
			if(isset($post_vars['file_url'])){
				// name
				$data['filename'] = $post_vars['file_url'];
				// real name
				$data['real_filename'] = basename($post_vars['file_url']);
			}else{
				// try upload
				if($file_info = mgm_save_file_for_download('file_name')){
					// name
					$data['filename'] = $file_info['file_url'];
					// real name
					$data['real_filename'] = $file_info['real_name'];
				}
			}
			// expire date		
			if(isset($post_vars['expire_dt'])){
				$data['expire_dt'] = $post_vars['expire_dt'];
			}	
			// members_only
			if(isset($post_vars['members_only'])){
				$data['members_only'] = $post_vars['members_only'];
			}
			// user_id
			if(isset($post_vars['user_id'])){
				$data['user_id'] = $post_vars['user_id'];
			}
			// posts					
			$posts = (isset($post_vars['posts'])) ? $post_vars['posts'] : NULL;
			
			// create
			if($download = mgm_add_download($data, $posts)){					
				// status	
				$status = 'success';  		
				// message
				$message = __('Download created successfully','mgm');
				// data
				$data = array('download'=>$download);
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
	 * update download
	 *
	 * @param none
	 * @return download 
	 * @verb POST
	 * @action update 	
	 * @url <site>/mgmapi/downloads/update.<format>
	 * @since 1.0
	 */	
	public function update_post(){
		// post vars
		$post_vars = $this->request->data['post'];
		
		// status	
		$status = 'error';  	
		$data   = array();
		
		// message
		$message = __('Download update failed, ','mgm');
		
		// save
		if(!$errors = $this->_validate_data('update', $post_vars)){	
			// id			
			$id = (int)$post_vars['id'];
			// title
			if(isset($post_vars['title'])){
				$data['title'] = trim($post_vars['title']);
			}
			// file url
			if(isset($post_vars['file_url'])){
				// name
				$data['filename'] = $post_vars['file_url'];
				// real name
				$data['real_filename'] = basename($post_vars['file_url']);
			}else{
				// try upload
				if($file_info = mgm_save_file_for_download('file_name')){
					// name
					$data['filename'] = $file_info['file_url'];
					// real name
					$data['real_filename'] = $file_info['real_name'];
				}
			}
			// expire date	
			if(isset($post_vars['expire_dt'])){
				$data['expire_dt'] = $post_vars['expire_dt'];
			}	
			// members_only
			if(isset($post_vars['members_only'])){
				$data['members_only'] = $post_vars['members_only'];
			}
			// user_id
			if(isset($post_vars['user_id'])){
				$data['user_id'] = $post_vars['user_id'];
			}
			// posts					
			$posts = (isset($post_vars['posts']) && is_array($post_vars['posts'])) ? $post_vars['posts'] : NULL;
			
			// update
			if($download = mgm_update_download($id, $data, $posts)){					
				// status	
				$status = 'success';  		
				// message
				$message = __('Download updated successfully','mgm');
				// data
				$data = array('download'=>$download);
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
	 * delete download
	 *
	 * @param none
	 * @return download 
	 * @verb DELETE
	 * @action delete 	
	 * @url <site>/mgmapi/downloads/delete.<format>
	 * @since 1.0
	 */	
	public function delete_delete(){
		// delete vars
		$delete_vars = $this->request->data['delete'];		
		
		// status	
		$status = 'error';  	
		$data   = array();
		
		// message
		$message = __('Download delete failed, ','mgm');		
		
		// delete
		if(!$errors = $this->_validate_data('delete', $delete_vars)){	
			// id			
			$id = $delete_vars['id'];
			// delete
			if( mgm_delete_download($id) ){					
				// status	
				$status   = 'success';  		
				// message
				$message = __('Download deleted successfully','mgm');
				// data
				$data    = array('download'=>array('id'=>$id));
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
	 * delete all download
	 *
	 * @param none
	 * @return download 
	 * @verb DELETE
	 * @action delete 	
	 * @url <site>/mgmapi/downloads/delete_all.<format>
	 * @since 1.0
	 */	
	public function delete_all_delete(){			
		// status	
		$status = 'error';  	
		$data   = array();
		// message
		$message = __('Downloads delete failed, ','mgm');		
		// delete
		if( mgm_delete_all_download() ){					
			// status	
			$status   = 'success';  		
			// message
			$message = __('Downloads deleted successfully','mgm');
			// data
			$data    = array();
		}else{
			$message .= __('Downloads delete failed','mgm');
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
				$req_flds = array('title');
				// loop
				foreach($req_flds as $req_fld){
					// check
					if(!isset($data[$req_fld]) || isset($data[$req_fld]) && empty($data[$req_fld])){
						$errors[$req_fld . '_required'] = sprintf(__('%s is a required parameter', 'mgm'), $req_fld);
					}
				}
				// check if title already validated
				if(!in_array('title_required', array_keys($errors))){
					// size overflow
					if(strlen($data['title']) > 150){
						$errors['title_overflow'] = __('Download title should not exceed 150 characters','mgm');
					}
					// duplicate
					if(mgm_is_duplicate(TBL_MGM_DOWNLOAD, array('title'), '', $data)){
						$errors['title_duplicate'] = __('Duplicate download title provided, please try with a different title','mgm');
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
				// check if title already validated
				if(!in_array('title_required', array_keys($errors))){
					// size overflow
					if(isset($data['title']) && strlen($data['title']) > 150){
						$errors['title_overflow'] = __('Download title should not exceed 150 characters','mgm');
					}
					// duplicate
					if(mgm_is_duplicate(TBL_MGM_DOWNLOAD, array('title'), '`id`<>'.$data['id'], $data)){
						$errors['title_duplicate'] = __('Duplicate download title provided, please try with a different title','mgm');
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
// end file /core/api/mgm_api_downloads.php			