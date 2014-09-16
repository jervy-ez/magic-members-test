<?php
/** 
 * Patch for Membership Level's rename
 */  	
						
	// get object
	$membership_types_obj = mgm_get_class('membership_types');
	
	// ge all users
	$users = mgm_get_all_userids(array('ID'), 'get_results');
	
	// loop types
	foreach($membership_types_obj->get_membership_types() as $type_code=>$type_name) {
	
		$new_type_code = $membership_types_obj->get_type_code($type_name);

		//check
		if($new_type_code != $type_code) {		
			
			// get object
			$obj_sp = mgm_get_class('subscription_packs');
			
			//update new 
			foreach ($obj_sp->packs as $key => $pack) {	
				
				if($obj_sp->packs[$key]['membership_type'] == $type_code ){
					
					$obj_sp->packs[$key]['membership_type'] = $new_type_code;
					
					$obj_sp->save();
				}
				
			}

			// loop
			foreach($users as $user){
				// get
				$member = mgm_get_member($user->ID);
				// if users with same membershiptype as that of selected
				if(isset($member->membership_type) && $member->membership_type == $type_code) {							
					// set
					$member->membership_type = $new_type_code;
					// save
					$member->save();						
				}
				// check if any multiple levels exist:
				if(isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {								
					// loop
					foreach ($member->other_membership_types as $key => $memtypes) {
						// make sure its an object:
						$memtypes = mgm_convert_array_to_memberobj($memtypes, $user->ID);
						// verify
						if($memtypes->membership_type == $type_code) {
							// set
							$memtypes->membership_type = $new_type_code;	
							// save
							mgm_save_another_membership_fields($memtypes, $user->ID, $key);
						}
					}
				}
				// unset
				unset($member);
			}							
			//unset 
			$membership_types_obj->unset_membership_type($type_code);
			// set
			$membership_types_obj->set_name($type_name, $new_type_code);

		} else {
			$membership_types_obj->set_name($type_name, $type_code);
			
		}
		$membership_types_obj->save();
	}
// end patch	
 