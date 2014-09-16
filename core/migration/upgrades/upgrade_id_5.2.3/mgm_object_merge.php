<?php
/** 
 * Objects merge/update
 * Add New capability: mgm_widget_dashboard_statistics to admin role
 */ 

$obj_role = new mgm_roles();
$obj_role->update_capability_role('administrator', 'mgm_widget_dashboard_statistics', true);
 // end file