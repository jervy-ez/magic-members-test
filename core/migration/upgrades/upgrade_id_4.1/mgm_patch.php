<?php
//readjust user roles/membership types:
$settings = mgm_get_class('system')->get_setting();
//as the patch is for resetting user roles and is related to Multiple Membership feature,
//enable the patch only if multiple membership feature is ON
if(isset($settings['enable_multiple_level_purchase']) && $settings['enable_multiple_level_purchase'] == 'Y' ) {
	global $wpdb;
	
	$limit = 100;
	$start = 0;
	$count  = $wpdb->get_var('SELECT count(*) from ' . $wpdb->users . ' WHERE ID <> 1');
	if($count) {
		for( $i = $start; $i < $count; $i = $i + $limit ) {
			if(!mgm_patch_get_users($i, $limit))
				break;
			//just give a break			
			usleep(100000);// 0.1 second
		}
	}
}
function mgm_patch_get_users($start, $limit) {
	global $wpdb;
	$qry = 'SELECT ID from ' . $wpdb->users . ' WHERE ID <> 1 ORDER BY ID LIMIT '. $start.','.$limit;
	$uids  = $wpdb->get_col($qry);
	
	if(!count($uids))
		return false;
		
	foreach ($uids as $user_id) {	
		mgm_remove_excess_user_roles($user_id, true);
		$member = mgm_get_member($user_id);
		$pack = mgm_get_option('subscription_packs')->get_pack($member->pack_id);
		if(!empty($pack['membership_type']) && $pack['membership_type'] != $member->membership_type && $member->status == MGM_STATUS_ACTIVE) {
			$member->membership_type = $pack['membership_type']; 
			// update_user_option($user_id, 'mgm_member', $member, true);	
			$member->save();		
		}
	}
	
	return true;
}