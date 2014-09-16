<?php
/** 
 * $Upgrade 5.2.8
 *
 * Patch for updating sub_pack_trial coupon code cost position
 */  
 global $wpdb; 
 // current upgrade
 $current_upgrade_name = 'upgrade_id_5.2.8';
 // get all coupons
 $coupons = $wpdb->get_results("SELECT `id`, `value` FROM ".TBL_MGM_COUPON." WHERE `value` LIKE 'sub_pack_trial%'", ARRAY_A);
 // check
 if($coupons){ 	
 	// loop
 	foreach($coupons as $coupon){
		// value
		$value = $coupon['value'];
		// check
		$value = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace('sub_pack_trial#', '', $coupon['value']));
		// list, duration, duration_type, cost, cycles
		list($new_duration, $new_duration_type, $new_cost, $new_num_cycles) = explode('_', $value, 4);	// 4 only
		// set
		$value = 'sub_pack_trial#' . implode('_', array($new_cost, $new_duration, $new_duration_type, $new_num_cycles));
		// update
		$wpdb->update(TBL_MGM_COUPON, array('value'=>$value), array('id'=>$coupon['id']));
	}
 }
 // skip
 // $skip_upgrade_tracking[str_replace('upgrade_id_', '', $current_upgrade_name)] = true;
 