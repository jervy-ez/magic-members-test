<?php
	
/** 
 * Patch for updating ccbill last payment date & expiration date from gateway (issue #1520)
 */  
	
	$arr_users = mgm_get_all_userids();
	
	if(!empty($arr_users)) {
		
		foreach ($arr_users as $user_id) {		
			//member 
			$member = mgm_get_member($user_id);	
			
			if(!isset($member->last_payment_check) || (isset($member->last_payment_check) && $member->last_payment_check != 'disabled')){
				// check	
				if(!empty($member->payment_info->module) && ($module_obj = mgm_is_valid_module($member->payment_info->module, 'payment', 'object')) && trim($member->payment_info->module) =='mgm_ccbill') {	
					// check is supported
					if($module_obj->is_rebill_status_check_supported()){
						//call
						if($module_obj->query_rebill_status($user_id, $member)) {								
							// return 
							$return = true;
						}
					}
				}
			}		
		}
	}
// end file