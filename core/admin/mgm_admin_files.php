<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin upload/download module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_files extends mgm_controller{
 	
	// construct
	function __construct(){
		// php4
		$this->mgm_admin_files();
	}
	
	// construct php4
	function mgm_admin_files()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){	
		// clean file
		$file = str_replace(array("\\","/"), DIRECTORY_SEPARATOR, urldecode($_GET['file']));		
		// print_r($_GET);
		// type
		$type= strip_tags($_GET['type']);
		// get type
		switch($type){
			case 'download':
				// buffer
				@ob_end_clean();
				// download				
				mgm_force_download($file);
				// flush
				@ob_end_flush();exit();
			break;
			case 'upload':
				echo 'upload';
				exit;
			break;
		}
	}
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_files.php 