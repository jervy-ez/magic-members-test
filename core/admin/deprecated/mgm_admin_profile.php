<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin profile module
 *
 * @package MagicMembers
 * @since 2.0
 * @deprecated
 */
 class mgm_admin_profile extends mgm_controller{
 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_profile();
	}
	
	// construct php4
	function mgm_admin_profile()
	{		
		// load parent
		parent::__construct();
	}	
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_profile.php