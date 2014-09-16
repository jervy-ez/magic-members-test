<?php
/** 
 * Objects merge/update
 * Enable comments protection settings
 */ 
 // read  
$system_obj = mgm_get_class('system');

if(isset($system_obj->setting['enable_comments_protection'])) {
	$system_obj->setting['enable_comments_protection'] 	= 'N';	
	$system_obj->save();
}
 // end file