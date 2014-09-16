<?php
/** 
 * Objects merge/update
 * Add google analytics settings
 */ 
 // read  
$system_obj = mgm_get_class('system');

if(empty($system_obj->setting['enable_googleanalytics'])) {
	$system_obj->setting['enable_googleanalytics'] 	= 'N';
	$system_obj->setting['googleanalytics_key'] 	= '';
	$system_obj->save();
}
 // end file