<?php
/** 
 * Objects merge/update
 */ 
 // read  
 
 // system object updates
 $member_custom_fields = new mgm_member_custom_fields();
 
 // saved object
 $member_custom_fields_cached = mgm_get_option('member_custom_fields');
 
 // set new vars
 $new_fields = array_slice( $member_custom_fields->custom_fields, 7);
 
 // check
 if(count($new_fields)){
 	 // flag
	 $is_diff=false;
	 // loop and check
	 foreach($new_fields as $new_field){
		// check created
		$created = $member_custom_fields_cached->get_field_by_name($new_field['name'],'bool');
		// not created already
		if(!$created){
			// diff
			$is_diff = true;
			// create new id
			$new_field['id'] = $member_custom_fields_cached->next_id;
			// set 
			$member_custom_fields_cached->set_custom_field($new_field);
		}
	 }		

	 // update
	 if($is_diff){
		 update_option('mgm_member_custom_fields', $member_custom_fields_cached);
	 }	 
 }
 
 // end file