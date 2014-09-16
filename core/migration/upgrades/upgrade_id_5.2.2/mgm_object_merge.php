<?php
/** 
 * Objects merge/update
 * Add New System Setting: enable_role_based_menu_loading
 * Populate Custom MGM capabilities for 'administrator' role
 */ 
// read  
$system_obj = mgm_get_class('system');
if (!isset($system_obj->setting['enable_role_based_menu_loading'])) { 
	// default to 'NO'
	$system_obj->setting['enable_role_based_menu_loading'] = 'N';
	// save
	$system_obj->save();	
}

//default MGM custom capabilities to true for administrator role:
$obj_role = new mgm_roles();
$custom_caps = $obj_role->get_custom_capabilities();
foreach ($custom_caps as $cap)
	$obj_role->update_capability_role('administrator', $cap, true);
 // end file