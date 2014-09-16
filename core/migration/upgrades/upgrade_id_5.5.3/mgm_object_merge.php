<?php
/** 
 * Objects merge/update
 * Update capability:  mgm_addons , mgm_post_purchases , mgm_addon_purchases , mgm_payment_history to admin role
 */ 

$obj_role = new mgm_roles();

$obj_role->update_capability_role('administrator', 'mgm_post_purchases', true);
$obj_role->update_capability_role('administrator', 'mgm_addons', true);
$obj_role->update_capability_role('administrator', 'mgm_addon_purchases', true);
$obj_role->update_capability_role('administrator', 'mgm_payment_history', true);

// end file