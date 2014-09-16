<?php
/** 
 * Objects merge/update
 */  
 // system object updates
 $mgm_system = new mgm_system();
 
 // saved object
 $mgm_system_cached = mgm_get_option('system');
 
 // set new vars
 // aws_enable_s3
 if(!isset($mgm_system_cached->setting['aws_enable_s3'])){
 	$mgm_system_cached->setting['aws_enable_s3'] = $mgm_system->setting['aws_enable_s3'];
 }
 // aws_key
 if(!isset($mgm_system_cached->setting['aws_key'])){
 	$mgm_system_cached->setting['aws_key'] = $mgm_system->setting['aws_key'];
 }
 // aws_secret_key
 if(!isset($mgm_system_cached->setting['aws_secret_key'])){
 	$mgm_system_cached->setting['aws_secret_key'] = $mgm_system->setting['aws_secret_key'];
 } 
  
 // update
 update_option('mgm_system', $mgm_system_cached);
 
 // read =======
 // saved object
 // include logout_redirects array
 $mgm_mem_type_cached = mgm_get_option('membership_types');

 if(!isset($mgm_mem_type_cached->logout_redirects)) {
	$mgm_mem_type_cached->logout_redirects = array();
	update_option('mgm_membership_types', $mgm_mem_type_cached);
 }

 // include logout redirect url:
 $mgm_system_cached =  mgm_get_option('system');

 if(!isset($mgm_system_cached->setting['logout_redirect_url'])) {
	$mgm_system_cached->setting['logout_redirect_url'] = '';
	update_option('mgm_system', $mgm_system_cached);
 }
//end
