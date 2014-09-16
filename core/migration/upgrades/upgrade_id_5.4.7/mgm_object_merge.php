<?php
/** 
 * Objects merge/update
 * Add New capability: mgm_home dashboard options to admin role
 */ 

$obj_role = new mgm_roles();
$obj_role->update_capability_role('administrator', 'mgm_home', true);
 // end file