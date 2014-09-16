<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin payments module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_payments extends mgm_controller{
 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_payments();
	}
	
	// construct php4
	function mgm_admin_payments()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){
		// data
		$data = array();
		// get active modules
		$data['payment_modules'] = mgm_get_class('system')->get_active_modules('payment');						
		// mgm_log($data['payment_modules'], __FUNCTION__);
		// load template view
		$this->load->template('payments/index', array('data'=>$data));		
	}
	
	// module_settings
	function module_settings(){		
		// make local
		extract($_REQUEST);				
		// get module
		$module_class = mgm_get_module($module, 'payment');	
		// update
		if(isset($update) && $update=='true'){
			// settings update
			$module_class->settings_update();
		}else{		
			// load settings form
			$module_class->settings();
		}				
	}
	
	// module_setting_box
	function module_setting_box(){		
		// make local
		extract($_REQUEST);
		// get module
		$module_class = mgm_get_module($module, 'payment');	
		// load settings box
		echo $module_class->settings_box();		
	}
	
	// module_file_upload
	function module_file_upload(){						
		// name of module
		$module= mgm_request_var('module', '', true);
		// file
		$file_element = 'logo_'.$module;
		// init
		$logo = array();
		// init messages
		$status  = 'error';	
		$message = __('Logo upload failed.','mgm');
		// upload check
		if (is_uploaded_file($_FILES[$file_element]['tmp_name'])) {
			// random filename
			$uniquename = substr(microtime(),2,8);
			// paths
			$oldname    = strtolower($_FILES[$file_element]['name']);
			$newname    = preg_replace('/(.*)\.(png|jpg|jpeg|gif)$/i', $uniquename.'.$2', $oldname);
			$filepath   = MGM_FILES_MODULE_DIR . $newname;
			// upload
			if(move_uploaded_file($_FILES[$file_element]['tmp_name'], $filepath)){
				// get thumb
				$thumb = image_make_intermediate_size(MGM_FILES_MODULE_DIR . $newname, 100, 100);				
				// set logo
				if($thumb){
					$logo  = array('image_name' => $thumb['file'], 'image_url' => MGM_FILES_MODULE_URL . $thumb['file']);
					// remove main file, we dont need it					
					mgm_delete_file($filepath); 
				}else{
					$logo  = array('image_name' => $newname, 'image_url' => MGM_FILES_MODULE_URL . $newname);				
				}
				// status
				$status  = 'success';	
				$message = __('logo uploaded successfully, it will be attached when you update the settings.','mgm');
			}
		}		
		// send ouput		
		@ob_end_clean();	
		// PRINT
		echo json_encode(array('status'=>$status,'message'=>$message, 'logo'=>$logo));
		// end out put			
		@ob_flush();
		exit();
	}
	
	// payment modules
	function payment_modules(){		
		global $wpdb;
		// data
		$data = array();
		// get available modules
		$data['payment_modules'] = mgm_get_modules('payment');	
		// log
		// mgm_log($data['payment_modules'], __FUNCTION__);	
		// get object			
		// $module_object = mgm_get_module('mgm_' . $data['payment_modules'][0], 'payment');	
		// load
		/*foreach($data['payment_modules'] as $module){// @todo change on request loading
			// get object			
			$module_object = mgm_get_module('mgm_' . $module, 'payment');			
			// check
			if(is_object($module_object)){
				// get box settings 
				$data['modules'][$module]['html'] = $module_object->settings_box();	
				// get code
				$data['modules'][$module]['code'] = $module_object->code;				
			}	
		}	*/	
		// load template view
		$this->load->template('payments/modules', array('data'=>$data));
	}		
 }
// return name of class
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_payments.php