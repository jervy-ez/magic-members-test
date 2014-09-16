<?php
/** 
 * Objects merge/update
 */ 
 // saved object
 $system_obj_cached = mgm_get_option('system');
 
 // set new vars
 // enable_autologin
 if(!isset($system_obj_cached->setting['enable_multiple_level_purchase'])){
 	$system_obj_cached->setting['enable_multiple_level_purchase'] = 'N'; 	
 }  	
 // update
 update_option('mgm_system', $system_obj_cached);
 
 // ends