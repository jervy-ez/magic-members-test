<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin home module
 *
 * @package MagicMembers
 * @since 2.0
 * @deprecated
 */
 class mgm_admin_home extends mgm_controller{
 	
	// construct
	function __construct(){
		// php4
		$this->mgm_admin_home();
	}
	
	// construct php4
	function mgm_admin_home()
	{		
		// load parent
		parent::__construct();
	}	
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_home.php