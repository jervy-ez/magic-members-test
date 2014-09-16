<?php
/** 
 * Objects merge/update
 */  
 // system object updates
 $system_obj = new mgm_system();
 
 // saved object
 $system_obj_cached = mgm_get_option('system');
 
 // set new vars
 // aws_enable_s3
 if(!isset($system_obj_cached->setting['aws_enable_s3'])){
 	$system_obj_cached->setting['aws_enable_s3'] = $system_obj->setting['aws_enable_s3'];
 }
 // aws_key
 if(!isset($system_obj_cached->setting['aws_key'])){
 	$system_obj_cached->setting['aws_key'] = $system_obj->setting['aws_key'];
 }
 // aws_secret_key
 if(!isset($system_obj_cached->setting['aws_secret_key'])){
 	$system_obj_cached->setting['aws_secret_key'] = $system_obj->setting['aws_secret_key'];
 } 
  
 // update
 update_option('mgm_system', $system_obj_cached);
 
 // read =======
 // saved object
 // include logout_redirects array
 $mgm_mem_type_cached = mgm_get_option('membership_types');

 if(!isset($mgm_mem_type_cached->logout_redirects)) {
	$mgm_mem_type_cached->logout_redirects = array();
	update_option('mgm_membership_types', $mgm_mem_type_cached);
 }

 // include logout redirect url:
 $system_obj_cached =  mgm_get_option('system');

 if(!isset($system_obj_cached->setting['logout_redirect_url'])) {
	$system_obj_cached->setting['logout_redirect_url'] = '';
	update_option('mgm_system', $system_obj_cached);
 }
//end
