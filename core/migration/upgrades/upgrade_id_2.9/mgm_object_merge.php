<?php
/** 
 * Objects merge/update
 */ 
 
 // system object updates
 $system_obj = new mgm_system();
 
 // saved object
 $system_obj_cached = mgm_get_option('system');
 
 // set new vars
 // membership_details_url
 if(!isset($system_obj_cached->setting['membership_details_url'])){
 	$system_obj_cached->setting['membership_details_url'] = $system_obj->setting['membership_details_url'];
 }
  
 // update
 update_option('mgm_system', $system_obj_cached);
 
 // ends