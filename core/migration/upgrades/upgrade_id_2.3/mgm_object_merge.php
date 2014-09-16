<?php
/** 
 * Objects merge/update
 */ 
 
 // system object updates
 $system_obj = new mgm_system();
 
 // saved object
 $system_obj_cached = mgm_get_option('system');
 
 // set new vars
 // register url
 if(!isset($system_obj_cached->setting['register_url'])){
 	$system_obj_cached->setting['register_url'] = $system_obj->setting['register_url'];
 }
 // profile url
 if(!isset($system_obj_cached->setting['profile_url'])){
 	$system_obj_cached->setting['profile_url'] = $system_obj->setting['profile_url'];
 }
 // transactions url
 if(!isset($system_obj_cached->setting['transactions_url'])){
 	$system_obj_cached->setting['transactions_url'] = $system_obj->setting['transactions_url'];
 } 
  
 // update
 update_option('mgm_system', $system_obj_cached);
 
 // read 