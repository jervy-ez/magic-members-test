<?php
/** 
 * Objects merge/update
 */  
	//Restoring custom fileds to user meta
	
	$arr_users = mgm_get_all_userids();
	
	if(!empty($arr_users)) {
		
		foreach ($arr_users as $user_id) {			
			//member 
			$member = mgm_get_member($user_id);			
			// save
			if(isset($member->custom_fields)){			
				foreach ($member->custom_fields as $key => $value) {
					// key 
					$options_key = '_mgm_cf_'.$key;
					// verify
					if($user_id){
						// update
						update_user_option($user_id, $options_key, $value, true);	
					}				
				}
			}			
		}			
	}
 // end file