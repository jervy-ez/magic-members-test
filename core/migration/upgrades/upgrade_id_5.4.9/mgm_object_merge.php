<?php
/** 
 * Objects merge/update
 * If wordpress version 3.5 default jquery version is 1.9.2.
 */ 

// check
if (version_compare(get_bloginfo('version'), '3.5', '==')){
	$jqueryui_version = '1.9.2';		
	update_option('mgm_jqueryui_version', $jqueryui_version); // and update		 
}
