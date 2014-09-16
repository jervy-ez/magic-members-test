<?php
/** 
 * Objects merge/update
 */ 
 // saved object
$system_obj_cached = mgm_get_option('system');
 
// set new vars
if(!isset($system_obj_cached->setting['recaptcha_public_key'])){
	$system_obj_cached->setting['recaptcha_public_key'] = ''; 	
}
if(!isset($system_obj_cached->setting['recaptcha_private_key'])){
	$system_obj_cached->setting['recaptcha_private_key'] = ''; 	
}
if(!isset($system_obj_cached->setting['recaptcha_api_server'])){
	$system_obj_cached->setting['recaptcha_api_server'] = 'http://www.google.com/recaptcha/api'; 	
}
if(!isset($system_obj_cached->setting['recaptcha_api_secure_server'])){
	$system_obj_cached->setting['recaptcha_api_secure_server'] = 'https://www.google.com/recaptcha/api'; 	
}
if(!isset($system_obj_cached->setting['recaptcha_verify_server'])){
	$system_obj_cached->setting['recaptcha_verify_server'] = 'www.google.com'; 	
}	
 // update
 update_option('mgm_system', $system_obj_cached);