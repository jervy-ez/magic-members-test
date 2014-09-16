<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin support docs module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_support_docs extends mgm_controller{
 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_support_docs();
	}
	
	// construct php4
	function mgm_admin_support_docs()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){
		// data
		$data = array();
		// load template view
		$this->load->template('support_docs/support_docs', array('data'=>$data));		
	}
	
	// generalinfo
	function generalinfo(){		
		// data
		$data = array();		
		// load template view
		$this->load->template('support_docs/generalinfo', array('data'=>$data));			
	}
	
	// troubleshooting
	function troubleshooting(){		
		// data
		$data = array();		
		// load template view
		$this->load->template('support_docs/troubleshooting', array('data'=>$data));		
	}
	
	// tutorials
	function tutorials(){		
		// data
		$data = array();		
		// load template view
		$this->load->template('support_docs/tutorials', array('data'=>$data));	
	}
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_support_docs.php