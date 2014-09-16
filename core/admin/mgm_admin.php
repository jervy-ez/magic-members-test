<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin base module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin extends mgm_controller{
 	
	// construct
	function __construct(){
		// php4
		$this->mgm_admin();
	}
	
	// construct php4
	function mgm_admin(){		
		// load parent
		parent::__construct();
	}	
	
	// index
	function index(){
		// data
		$data = array();
		// api 2.0
		mgm_check_auto_upgrader_api();
		// load template view
		$this->load->template('admin/admin', array('data'=>$data));		
	}

	// index
	function activation_index(){
		// data
		$data = array();		
		// load template view
		$this->load->template('admin/activation/index', array('data'=>$data));		
	}
	
	// activate
	function activation_activate(){
		global $wpdb;
		// 	local
		extract($_POST);
		// post
		if(isset($btn_activate)){
			// default
			$status = 'error';
			// check
			if(!empty($email)){							
				// validate
				$message = mgm_get_class('auth')->validate_subscription($email);	
				// check
				if($message===true){
					$status  = 'success';
					$message = __('Your account has been activated.','mgm');
				}
			}else{			
				$message = __('Email is not provided.','mgm');
			}				
			// return response
			echo json_encode(array('status'=>$status, 'message'=>$message));exit();
		}
		
		// data
		$data = array();				
		// load template view
		$this->load->template('admin/activation/activate', array('data'=>$data));	
	}

	// dashboard index
	function dashboard_index(){
		// data
		$data = array();		
		// load template view
		$this->load->template('admin/dashboard/index', array('data'=>$data));		
	}
	
	// dashboard widgets
	function dashboard_widgets(){	
		// data
		$data = array();				
		// load template view
		$this->load->template('admin/dashboard/widgets', array('data'=>$data));	
	}

	// dashboard widget subscription status
	function dashboard_widget_plugin_subscription_status(){
		echo mgm_get_subscription_status();
	}

	// dashboard widget check version
	function dashboard_widget_plugin_check_version(){
		echo mgm_check_version();
	}
	
	// dashboard widget plugin messages
	function dashboard_widget_plugin_messages(){				
		echo mgm_get_messages();
	}

	// dashboard widget site news
	function dashboard_widget_site_news(){				
		echo mgm_site_rss_news();
	}

	// dashboard widget site blog
	function dashboard_widget_site_blog(){				
		echo mgm_site_rss_blog();
	}
	
	// dashboard widget posts purchased
	function dashboard_widget_posts_purchased(){				
		echo mgm_render_posts_purchased(5);
	}

	// dashboard widget member statistics
	function dashboard_widget_member_statistics(){				
		echo mgm_member_level_statistics();
	}

	// wp dashboard widget
	function wp_dashboard_widget_statistics(){
		// data
		$data = array();
		// set
		$data['level_statistics'] = mgm_member_level_statistics(true);
		// set
		$data['status_statistics'] = mgm_member_status_statistics(true);
		// load template view
		$this->load->template('admin/wp_dashboard/widgets/statistics', array('data'=>$data));	
	}
	
	// membership details, profile
	function membership_details(){
		// data
		$data = array();		
		// load template view
		$this->load->template('admin/membership_details', array('data'=>$data));		
	}	

	// membership contents
	function membership_contents(){		
		// data
		$data = array();
		// membership level
		$data['membership_level'] = mgm_get_user_membership_type();		
		// load template view
		$this->load->template('admin/membership_contents', array('data'=>$data));		
	}

	
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin.php 