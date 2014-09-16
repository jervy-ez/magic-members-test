<?php
/** 
 * Objects merge/update
 * Add New setting: share_registration_url_with_bp
 * Whether using the same registration URL for both MGM and Buddypress
 */ 
// read  
$system_obj = mgm_get_class('system');
// check
if (!isset($system_obj->setting['share_registration_url_with_bp'])) { 
	// default to 'NO'
	$system_obj->setting['share_registration_url_with_bp'] = 'N';
	// save
	$system_obj->save();
}