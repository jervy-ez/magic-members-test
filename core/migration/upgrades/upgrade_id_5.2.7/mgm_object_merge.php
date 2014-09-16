<?php
/** 
 * Objects merge/update
 * Add New content protection setting: enable_excerpt_protection
 */ 
// read  
$system_obj = mgm_get_class('system');
// check
if (!isset($system_obj->setting['enable_excerpt_protection'])) { 
	// default to 'NO'
	$system_obj->setting['enable_excerpt_protection'] = 'Y';
	// save
	$system_obj->save();	
}