<?php
/** 
 * Objects merge/update
 * Add New setting: disable_registration_email_bp
 * This is to block registration notification email if Buddypress is enabled
 */ 
// read  
$system_obj = mgm_get_class('system');
// check
if (!isset($system_obj->setting['disable_registration_email_bp'])) { 
	// default to 'YES'
	$system_obj->setting['disable_registration_email_bp'] = 'Y';
	// save
	$system_obj->save();
}