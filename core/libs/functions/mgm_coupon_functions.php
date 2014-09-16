<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Get register coupon pack 
 *
 * @param object $member
 * @param array $pack
 * @return void
 */
function mgm_get_register_coupon_pack($member, &$pack){
	// coupon as array
	$member->coupon = (array) $member->coupon; 	
	// check			
	if(isset($member->coupon['id'])){				
		// main 		
		if($pack && isset($member->coupon['cost'])){
			// original
			$pack['original_cost'] = $pack['cost'];
			// payable
			$pack['cost'] = $member->coupon['cost'];
		}	
		
		// product map on coupon 		
		if($pack && isset($member->coupon['product'])){
			// original
			$pack['original_product'] = $pack['product'];
			// payable
			$pack['product'] = $member->coupon['product'];
		}	
		
		if($pack && isset($member->coupon['duration']))
			$pack['duration'] = $member->coupon['duration'];
		if($pack && isset($member->coupon['duration_type']))
			$pack['duration_type'] = $member->coupon['duration_type'];
		if($pack && isset($member->coupon['membership_type']))
			$pack['membership_type'] = $member->coupon['membership_type'];
		//issue#: 478/ add billing cycles.	
		if($pack && isset($member->coupon['num_cycles']))
			$pack['num_cycles'] = $member->coupon['num_cycles'];	
		
		// trial	
		if($pack && isset($member->coupon['trial_on']))
			$pack['trial_on'] = $member->coupon['trial_on'];
		if($pack && isset($member->coupon['trial_cost']))
			$pack['trial_cost'] = $member->coupon['trial_cost'];
		if($pack && isset($member->coupon['trial_duration_type']))
			$pack['trial_duration_type'] = $member->coupon['trial_duration_type'];
		if($pack && isset($member->coupon['trial_duration']))
			$pack['trial_duration'] = $member->coupon['trial_duration'];	
		if($pack && isset($member->coupon['trial_num_cycles']))
			$pack['trial_num_cycles'] = $member->coupon['trial_num_cycles'];	
			
		// mark pack as coupon applied
		$pack['coupon_id'] = $member->coupon['id'];					
	}
}

/**
 * Get upgrade or complete payment coupon pack
 *
 * @param object $member
 * @param array $pack
 * @param string $type
 * @return void
 */
function mgm_get_upgrade_coupon_pack($member, &$pack, $type='upgrade'){
	// is using a coupon ? reset prices	
	if($pack !== false) {
		// type
		switch($type) {
			case 'upgrade':
				if(isset($member->upgrade) && is_array($member->upgrade) && isset($member->upgrade['coupon']['id'])){		
					// cost			
					if( isset($member->upgrade['coupon']['cost']) ){
						// original
						$pack['original_cost'] = $pack['cost'];
						// payable
						$pack['cost'] = $member->upgrade['coupon']['cost'];
					}	
					// product map on coupon 		
					if( isset($member->upgrade['coupon']['product']) ){
						// original
						$pack['original_product'] = $pack['product'];
						// payable
						$pack['product'] = $member->upgrade['coupon']['product'];
					}
					// duration
					if( isset($member->upgrade['coupon']['duration']) )
						$pack['duration'] = $member->upgrade['coupon']['duration'];
					// duration type	
					if( isset($member->upgrade['coupon']['duration_type']) )
						$pack['duration_type'] = $member->upgrade['coupon']['duration_type'];
					// membership type	
					if( isset($member->upgrade['coupon']['membership_type']) )
						$pack['membership_type'] = $member->upgrade['coupon']['membership_type'];	
					// billing cycles. issue#478	
					if( isset($member->upgrade['coupon']['num_cycles']) )
						$pack['num_cycles'] = $member->upgrade['coupon']['num_cycles'];	
						
					// trial on	
					if( isset($member->upgrade['coupon']['trial_on']) )
						$pack['trial_on'] = $member->upgrade['coupon']['trial_on'];
					// trial cost	
					if( isset($member->upgrade['coupon']['trial_cost']) )
						$pack['trial_cost'] = $member->upgrade['coupon']['trial_cost'];
					// trial duration type	
					if( isset($member->upgrade['coupon']['trial_duration_type']) )
						$pack['trial_duration_type'] = $member->upgrade['coupon']['trial_duration_type'];
					// trial duration	
					if( isset($member->upgrade['coupon']['trial_duration']) )
						$pack['trial_duration'] = $member->upgrade['coupon']['trial_duration'];
					// trial billing cycles		
					if( isset($member->upgrade['coupon']['trial_num_cycles']) )
						$pack['trial_num_cycles'] = $member->upgrade['coupon']['trial_num_cycles'];		
						
					// mark pack as coupon applied				
					$pack['coupon_id'] = $member->upgrade['coupon']['id'];		
				}				
			break;
			// consider on complete as well. issue#: 802	
			case 'complete_payment';
				if(isset($member->coupon['id'])){			
					// cost			
					if( isset($member->coupon['cost']) ){
						// original
						$pack['original_cost'] = $pack['cost'];
						// payable
						$pack['cost'] = $member->coupon['cost'];
					}	
					// product map on coupon 		
					if( isset($member->coupon['product']) ){
						// original
						$pack['original_product'] = $pack['product'];
						// payable
						$pack['product'] = $member->coupon['product'];
					}
					// duration
					if( isset($member->coupon['duration']) )
						$pack['duration'] = $member->coupon['duration'];
					// duration type	
					if( isset($member->coupon['duration_type']) )
						$pack['duration_type'] = $member->coupon['duration_type'];
					// membership type	
					if( isset($member->coupon['membership_type']) )
						$pack['membership_type'] = $member->coupon['membership_type'];	
					// billing cycles. issue#478	
					if( isset($member->coupon['num_cycles']) )
						$pack['num_cycles'] = $member->coupon['num_cycles'];	
						
					// trial on	
					if( isset($member->coupon['trial_on']) )
						$pack['trial_on'] = $member->coupon['trial_on'];
					// trial cost	
					if( isset($member->coupon['trial_cost']) )
						$pack['trial_cost'] = $member->coupon['trial_cost'];
					// trial duration type	
					if( isset($member->coupon['trial_duration_type']) )
						$pack['trial_duration_type'] = $member->coupon['trial_duration_type'];
					// trial duration	
					if( isset($member->coupon['trial_duration']) )
						$pack['trial_duration'] = $member->coupon['trial_duration'];
					// trial billing cycles		
					if( isset($member->coupon['trial_num_cycles']) )
						$pack['trial_num_cycles'] = $member->coupon['trial_num_cycles'];		
						
					// mark pack as coupon applied				
					$pack['coupon_id'] = $member->coupon['id'];		
				}
			break;
		}
	}
}

/**
 * get extend coupon pack 
 *
 * @param object $member
 * @param array $pack
 * @return void
 */
function mgm_get_extend_coupon_pack($member, &$pack){
	// check
	if($pack !== false && isset($member->extend['coupon']['id'])){		
		// cost				
		if( isset($member->extend['coupon']['cost']) ){
			// original
			$pack['original_cost'] = $pack['cost'];
			// payable
			$pack['cost'] = $member->extend['coupon']['cost'];
		}
		// product map on coupon 		
		if( isset($member->extend['coupon']['product']) ){
			// original
			$pack['original_product'] = $pack['product'];
			// payable
			$pack['product'] = $member->extend['coupon']['product'];
		}
		// duration	
		if( isset($member->extend['coupon']['duration']) )
			$pack['duration'] = $member->extend['coupon']['duration'];
		// duration type	
		if( isset($member->extend['coupon']['duration_type']) )
			$pack['duration_type'] = $member->extend['coupon']['duration_type'];
		// membership type	
		if( isset($member->extend['coupon']['membership_type']) )
			$pack['membership_type'] = $member->extend['coupon']['membership_type'];
		// billing cycles, issue#478	
		if( isset($member->extend['coupon']['num_cycles']) )
			$pack['num_cycles'] = $member->extend['coupon']['num_cycles'];		
			
		// trial on	
		if( isset($member->extend['coupon']['trial_on']) )
			$pack['trial_on'] = $member->extend['coupon']['trial_on'];
		// trial cost	
		if( isset($member->extend['coupon']['trial_cost']) )
			$pack['trial_cost'] = $member->extend['coupon']['trial_cost'];
		// trial duration type	
		if( isset($member->extend['coupon']['trial_duration_type']) )
			$pack['trial_duration_type'] = $member->extend['coupon']['trial_duration_type'];
		// trial duration	
		if( isset($member->extend['coupon']['trial_duration']) )
			$pack['trial_duration'] = $member->extend['coupon']['trial_duration'];	
		// trial billing cycles	
		if( isset($member->extend['coupon']['trial_num_cycles']) )
			$pack['trial_num_cycles'] = $member->extend['coupon']['trial_num_cycles'];		
			
		// mark pack as coupon applied
		$pack['coupon_id'] = $member->extend['coupon']['id'];				
	}// end coupon
}

/**
 * Purchase another subscription coupon pack
 *
 * @param object $purchase_another_coupon
 * @param array $pack
 * @return void
 */
function mgm_get_purchase_another_coupon_pack($purchase_another_coupon, &$pack){
	// is using a coupon ? reset prices
	if($purchase_another_coupon !== false){			
		// cost		
		if($pack && isset($purchase_another_coupon['cost'])){
			// original
			$pack['original_cost'] = $pack['cost'];
			// payable
			$pack['cost'] = $purchase_another_coupon['cost'];
		}	
		// product map on coupon 		
		if($pack && isset($purchase_another_coupon['product'])){
			// original
			$pack['original_product'] = $pack['product'];
			// payable
			$pack['product'] = $purchase_another_coupon['product'];
		}
		// duration
		if($pack && isset($purchase_another_coupon['duration']))
			$pack['duration'] = $purchase_another_coupon['duration'];
		// duration_type	
		if($pack && isset($purchase_another_coupon['duration_type']))
			$pack['duration_type'] = $purchase_another_coupon['duration_type'];
		// membership_type	
		if($pack && isset($purchase_another_coupon['membership_type']))
			$pack['membership_type'] = $purchase_another_coupon['membership_type'];
		// issue#: 478/ add billing cycles.	
		if($pack && isset($purchase_another_coupon['num_cycles']))
			$pack['num_cycles'] = $purchase_another_coupon['num_cycles'];					
		// trial	
		if($pack && isset($purchase_another_coupon['trial_on']))
			$pack['trial_on'] = $purchase_another_coupon['trial_on'];
		// trial_cost	
		if($pack && isset($purchase_another_coupon['trial_cost']))
			$pack['trial_cost'] = $purchase_another_coupon['trial_cost'];
		// trial_duration_type	
		if($pack && isset($purchase_another_coupon['trial_duration_type']))
			$pack['trial_duration_type'] = $purchase_another_coupon['trial_duration_type'];
		// trial_duration	
		if($pack && isset($purchase_another_coupon['trial_duration']))
			$pack['trial_duration'] = $purchase_another_coupon['trial_duration'];	
		// trial_num_cycles	
		if($pack && isset($purchase_another_coupon['trial_num_cycles']))
			$pack['trial_num_cycles'] = $purchase_another_coupon['trial_num_cycles'];
		// mark pack as coupon applied
		$pack['coupon_id'] = $purchase_another_coupon['id'];
	}
}

/**
 * Post purchase coupon pack
 *
 * @param object $member
 * @param array $pack
 * @return void
 */
function mgm_get_post_purchase_coupon_pack($post_purchase_coupon, &$pack){
	// coupon
	if($post_purchase_coupon !== false){			
		// main			
		if($pack && isset($post_purchase_coupon['cost'])){
			// original
			$pack['original_cost'] = $pack['cost'];
			// payable
			$pack['cost'] = $post_purchase_coupon['cost'];
		}	
		// product map on coupon 		
		if($pack && isset($post_purchase_coupon['product'])){
			// original
			$pack['original_product'] = $pack['product'];
			// payable
			$pack['product'] = $post_purchase_coupon['product'];
		}				
		// mark pack as coupon applied
		$pack['coupon_id'] = $post_purchase_coupon['id'];		
		// mark pack as coupon code- issue #1421
		$pack['coupon_code'] = $post_purchase_coupon['name'];				
	}
}

/**
 * Updating coupon usage for register coupon only
 *
 * @param array $args
 * @return void
 */
function mgm_update_coupon_usage_process($args =array()){
	global $wpdb;
	// check
	if(isset($args['user_id'])) {
		// user_id
		$user_id = $args['user_id'];				
		$member = mgm_get_member($user_id);

		// check
		if(isset($member->coupon['update_usage']) && $member->coupon['update_usage']) {			
			if(isset($member->coupon['coupon_usage_id']) && $member->coupon['coupon_usage_id']) {
				// id
				$coupon_id = $member->coupon['coupon_usage_id'];
				
				// update
				mgm_update_coupon_usage($coupon_id, 'after_payment');

				// once done reset the same since all type of coupon uses the same
				$member->coupon['coupon_usage_id'] = false;
				$member->coupon['update_usage'] = false;
				$member->save();
			}
		}
	}

	// check - issue #1421
	if(isset($args['guest_token'])) {			
		//coupon_id
		$coupon_id = isset($args['coupon_id']) ? $args['coupon_id'] : 0;
		// update
		mgm_update_coupon_usage($coupon_id, 'after_payment');		
	}
}

/**
 * Updating coupon usage
 *
 * @param int $id'
 * @param string $type
 * @return void
 */
function mgm_update_coupon_usage($id, $type='unknown'){
	global $wpdb;	
	// sql
	$sql = "UPDATE `".TBL_MGM_COUPON."` SET `used_count` = IF(`used_count` IS NULL, 1, `used_count`+1) WHERE id=%d";	
	// update
	$affected = $wpdb->query($wpdb->prepare($sql, $id));	
	// return
	return $affected;
}

// end of file