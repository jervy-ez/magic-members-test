<?php
/** 
 * Objects merge/update
 */  
	//Restoring user coupon usage to user meta

	//user ids
	$arr_users = mgm_get_all_userids();
	//check
	if(!empty($arr_users)) {
		//loop
		foreach ($arr_users as $user_id) {			
			//member 
			$member = mgm_get_member($user_id);	
			// othe membership level coupon usage purchase not in meta, also mark this in save as status changes occur often
			if(isset($member->other_membership_types) && !empty($member->other_membership_types)) {
				//loop
				foreach ($member->other_membership_types as $other_membership_type) {
					//filter for empty values
					$other_membership_type = array_filter($other_membership_type);
					//check
					if( !empty($other_membership_type)) {					
						//other members upgrade
						if(isset($other_membership_type['upgrade']['coupon']['id']) && !empty($other_membership_type['upgrade']['coupon']['id']) && ( mgm_get_user_option($coupon_id.'_mgm_user_upgrade_coupon', $user_id) === FALSE )){
							
							$coupon_id = (int)$other_membership_type['upgrade']['coupon']['id'];
							//check
							//if( mgm_get_user_option($coupon_id.'_mgm_user_upgrade_coupon', $user_id) === FALSE ){
								// update
								update_user_option($user_id, $coupon_id.'_mgm_user_upgrade_coupon', $coupon_id, true);
							//}
						}
						//other members extend
						if(isset($other_membership_type['extend']['coupon']['id']) && !empty($other_membership_type['extend']['coupon']['id']) && ( mgm_get_user_option($coupon_id.'_mgm_user_extend_coupon', $user_id) === FALSE )){
							
							$coupon_id = (int)$other_membership_type['extend']['coupon']['id'];
							//check
							//if( mgm_get_user_option($coupon_id.'_mgm_user_extend_coupon', $user_id) === FALSE ){
								// update
								update_user_option($user_id, $coupon_id.'_mgm_user_extend_coupon', $coupon_id, true);
							//}
						}
					}
				}		
			}
			unset($member);
			unset($user_id);
		}
	}
?>