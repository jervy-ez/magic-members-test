<?php
/** 
 * Objects merge/update
 * fix autoresponder key
 */ 
// read  
$system_obj = mgm_get_class('system');
// check
if(!preg_match('/^mgm_/', $system_obj->active_modules['autoresponder'])) {
	// add proper prefix
	$system_obj->active_modules['autoresponder'] = 'mgm_' . $system_obj->active_modules['autoresponder'];	
}
// make unique
$system_obj->active_plugins = array_unique( $system_obj->active_plugins ); 
// make unique
$system_obj->active_modules['payment'] = array_unique( $system_obj->active_modules['payment'] ); 
// save
$system_obj->save();
 // end file