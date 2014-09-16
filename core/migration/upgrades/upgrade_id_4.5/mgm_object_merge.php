<?php
/** 
 * Objects merge/update
 */ 
 // read  
$obj_packs = mgm_get_option('subscription_packs');

if(!empty($obj_packs->duration_str) && !in_array(__('Lifetime', 'mgm'), $obj_packs->duration_str )) {
	// set
	$obj_packs->duration_str['l'] = __('Lifetime', 'mgm');
	// update
	update_option('mgm_subscription_packs', $obj_packs);
}
 // end file