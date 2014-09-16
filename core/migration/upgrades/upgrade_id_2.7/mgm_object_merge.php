<?php
/** 
 * Objects merge/update
 */ 
 // new object
 $member_custom_fields = & new mgm_member_custom_fields();
 
 // saved object
 $member_custom_fields_cached = mgm_get_option('member_custom_fields');
 
 // before
 // mgm_log('before:'.print_r($member_custom_fields_cached,true));
 // updated
 $updated = 0; 
 // loop fresh
 foreach($member_custom_fields->custom_fields as $custom_field){
 	// check if exists
	if($member_custom_fields_cached->get_field_by_name($custom_field['name'], 'bool') == false){
		// add new
		$member_custom_fields_cached->set_custom_field($custom_field);
		// added
		$updated++;
	} 	
 } 
 // if updated
 if($updated){ 
 	// update
 	update_option('mgm_member_custom_fields', $member_custom_fields_cached);
 }
 // after
 // mgm_log('after:'.print_r($member_custom_fields_cached,true));
 
 // read 