<?php
/** 
 * Objects merge/update
 */  
	//Restoring user billing num cycles based on membership level in user meta.
	//user ids
	$arr_users = mgm_get_all_userids();
	//check
	if(!empty($arr_users)) {
		//loop
		foreach ($arr_users as $user_id) {			
			//member 
			$member = mgm_get_member($user_id);	
			//check
			if((mgm_get_user_option(sprintf('mgm_user_billing_num_cycles_%d',$member->pack_id), $user_id) === FALSE) && !empty($member) && is_object($member)){			
				
				if(isset($member->pack_id) && (int)$member->pack_id > 0 ){	
					// init
					$user_billing_num_cycles = 'ongoing';// ongoing
					// check limited
					if( isset($member->active_num_cycles) && (int)$member->active_num_cycles >= 1 ){
						// set			
						$user_billing_num_cycles = (int)$member->active_num_cycles;	
					}else{
						// check pack
						if( isset($member->pack_id) && (int)$member->pack_id> 0 ){
							// set		
							if( $pack = mgm_get_class('subscription_packs')->get_pack($member->pack_id) ){
								if ( isset($pack['num_cycles']) && (int)$pack['num_cycles'] >= 1 ){
									$user_billing_num_cycles = (int)$pack['num_cycles'];				
								}		
							}
						}
					}
					// update
					update_user_option($user_id,sprintf('mgm_user_billing_num_cycles_%d',$member->pack_id), $user_billing_num_cycles, true);	
				}
			}			
			
			// othe membership level
			if(isset($member->other_membership_types) && !empty($member->other_membership_types) && is_object($member)) {
				//loop
				foreach ($member->other_membership_types as $key => $other_membership_type) {
					//filter for empty values
					$other_membership_type = array_filter($other_membership_type);
					//check
					if( !empty($other_membership_type)) {					
						// convet
						if(is_array($other_membership_type)) $other_membership_type = mgm_convert_array_to_memberobj($other_membership_type,$user_id);				
						//check
						if(mgm_get_user_option(sprintf('mgm_user_billing_num_cycles_%d',$other_membership_type->pack_id), $user_id) === FALSE){					
							//othermembership level wise user meta - issue #1681		
							if(isset($other_membership_type->pack_id) && (int)$other_membership_type->pack_id > 0 ){
								// init
								$user_billing_num_cycles = 'ongoing';// ongoing
								// check limited
								if( isset($other_membership_type->active_num_cycles) && (int)$other_membership_type->active_num_cycles >= 1 ){
									// set			
									$user_billing_num_cycles = (int)$other_membership_type->active_num_cycles;	
								}else{
									// check pack
									if( isset($other_membership_type->pack_id) && (int)$other_membership_type->pack_id> 0 ){
										// set		
										if( $pack = mgm_get_class('subscription_packs')->get_pack($other_membership_type->pack_id) ){
											if ( isset($pack['num_cycles']) && (int)$pack['num_cycles'] >= 1 ){
												$user_billing_num_cycles = (int)$pack['num_cycles'];				
											}		
										}
									}
								}
								// update
								update_user_option($user_id,sprintf('mgm_user_billing_num_cycles_%d',$other_membership_type->pack_id), $user_billing_num_cycles, true);	
							}							
						}
					}									
				}
			}
		}
	}
?>