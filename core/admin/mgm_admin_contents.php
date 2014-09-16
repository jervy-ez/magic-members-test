<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin contents module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_contents extends mgm_controller{
 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_contents();
	}
	
	// construct php4
	function mgm_admin_contents()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){
		// data
		$data = array();
		// load template view
		$this->load->template('contents/index', array('data'=>$data));			
	}			
																				
	// protections
	function protections(){				
		global $wpdb;	
		extract($_POST);
		
		// set 
		if(isset($update) && !empty($update)){
			// get system object	
			$system_obj = mgm_get_class('system');
			// update if set
			foreach($system_obj->setting as $k => $v){				
				// set var
				if(isset($_POST[$k])){
					$system_obj->setting[$k] = addslashes($_POST[$k]);		
				}
			}			
			// update
			$system_obj->save();
			// update
			$message = __('Content protection settings successfully updated.', 'mgm');
			$status  = 'success';
			// return response			
			echo json_encode(array('status'=>$status, 'message'=>$message));	
			// exit			
			exit();
		}
		
		// data
		$data = array();	
		// system
		$data['system_obj'] = mgm_get_class('system');	
		// load template view
		$this->load->template('contents/protections', array('data'=>$data));
	}
		
	// pages -----------------------------------------------
	function pages(){		
		global $wpdb;	
		extract($_POST);
		// set 
		if(isset($update) && !empty($update)){
			// get system object	
			$system_obj = mgm_get_class('system');
			// update			
			$system_obj->setting['excluded_pages'] = $_POST['excluded_pages'];								
			// save
			$system_obj->save();
			// update
			$message = __('Page exclusion settings successfully updated.', 'mgm');
			$status  = 'success';
			// return response			
			echo json_encode(array('status'=>$status, 'message'=>$message));				
			exit();
		}
		// data
		$data = array();	
		// all pages
		$data['pages'] = mgm_field_values( $wpdb->posts, 'ID', 'post_title', "AND post_status = 'publish' AND post_type IN ('page')", 'post_title' );	
		// excluded pages		
		$data['pages_excluded'] = mgm_get_class('system')->get_setting('excluded_pages', array());
		// load template view
		$this->load->template('contents/pages', array('data'=>$data));
	}		
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_contents.php 