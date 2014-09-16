<?php
/** 
 * Objects merge/update
 * Add New content protection setting: using_the_excerpt_in_theme
 * This is to prevent executing content protection callback if the_excerpt is being used in themes
 */ 
// read  
$system_obj = mgm_get_class('system');
// check
if (!isset($system_obj->setting['using_the_excerpt_in_theme'])) { 
	// default to 'NO'
	$system_obj->setting['using_the_excerpt_in_theme'] = 'N';
	// save
	$system_obj->save();	
}