<?php
/** 
 * Objects merge/update
 * If wordpress version 3.0 or above default jquery version is 1.8.16.
 */ 

// check
if ( mgm_compare_wp_version( '3.0', '>=' ) ){
	$jqueryui_version = '1.8.16';		
	update_option('mgm_jqueryui_version', $jqueryui_version); // and update		 
}
