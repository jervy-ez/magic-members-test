<?php
/** 
 * Objects merge/update
 * Add disable_payment_notify_emails settings
 */ 
 // read  
$system_obj = mgm_get_class('system');

if(empty($system_obj->setting['disable_payment_notify_emails'])) {
	$system_obj->setting['disable_payment_notify_emails'] 	= 'N';	
	$system_obj->save();
}
 // end file