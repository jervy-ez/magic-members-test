<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members autoresponders admin module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_autoresponders extends mgm_controller{
 	
	// construct
	function __construct(){
		// php4
		$this->mgm_admin_autoresponders();
	}
	
	// construct php4
	function mgm_admin_autoresponders()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){
		// data
		$data = array();		
		// load template view
		$this->load->template('autoresponders/index', array('data'=>$data));		
	}		
	
	// modules
	function autoresponder_modules(){
		// data
		$data = array();		
		// get modules
		$data['autoresponder_modules'] = mgm_get_modules('autoresponder');		
		// autoresponders
		foreach($data['autoresponder_modules'] as $module){				
			// get module
			$module_object = mgm_get_module('mgm_' . $module, 'autoresponder');
			// check	
			if(is_object($module_object)){										
				// get html
				$data['modules'][$module]['html'] = $module_object->settings();
				// get code
				$data['modules'][$module]['code'] = $module_object->code;	
				// get name
				$data['modules'][$module]['name'] = $module_object->name;	
			}
		}		
		// membership types
		$data['membership_types'] = mgm_get_class('membership_types')->membership_types;		
		// active
		$data['active_module']    = mgm_get_class('system')->active_modules['autoresponder'];
		// load template view
		$this->load->template('autoresponders/modules', array('data'=>$data));	
	}
	
	// module settings
	function module_settings(){		
		// make local
		extract($_REQUEST);				
		// get module
		$module_class = mgm_get_module($module, 'autoresponder');	
		// update
		if(isset($update) && $update=='true'){
			// settings update
			echo $module_class->settings_update();
		}else{		
			// load settings form
			$module_class->settings();
		}				
	}
		
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_autoresponders.php