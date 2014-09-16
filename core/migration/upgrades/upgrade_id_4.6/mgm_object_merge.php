<?php
/** 
 * Objects merge/update
 */ 
 // read  
 
$obj_packs = mgm_get_option('subscription_packs');
$arr_packs = array();
foreach ($obj_packs->packs as $pack) {
	$active = $pack['active'];
	if(!is_array($active)) {
		$pack['active'] = array();
		foreach ($obj_packs->get_active_options() as $option => $val) {
			$pack['active'][$option] = ($active) ? 1 : ( ($option != 'register') ? 1 : 0 ); 
		}
	}
	$arr_packs[] = $pack;	
}

if(!empty($arr_packs)) {
	$obj_packs->packs = $arr_packs;
	update_option('mgm_subscription_packs', $obj_packs);
}

//update nested shortcode parsing:
$obj_settings = mgm_get_option('system');
if(!isset($obj_settings->setting['enable_nested_shortcode_parsing'])) {
	$obj_settings->setting['enable_nested_shortcode_parsing'] = 'Y';
	update_option('mgm_system', $obj_settings);
}
 // end file