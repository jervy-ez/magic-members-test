<?php
/** 
 * Objects merge/update
 */ 
 
 // saved object
 $member_custom_fields = mgm_get_option('member_custom_fields');
 // new 
 $custom_fields = array();
 // loop
 foreach($member_custom_fields->custom_fields as $custom_field){
 	// check display
	if(!isset($custom_field['display'])){
		// loop
		foreach(array('on_register','on_profile','on_payment','on_public_profile','on_upgrade','on_extend') as $property){
			// skip for coupon
			if($custom_field['name'] !='coupon' && in_array($property, array('on_upgrade','on_extend'))) continue;
			
			// if set
			if(isset($custom_field[$property])){
				$property_value = $custom_field[$property];
			}else{
				$property_value = false;
			}
			// set
			$custom_field['display'][$property] = $property_value; 
		}	
	}
	
	// check attributes
	if(!isset($custom_field['attributes'])){
		// loop
		foreach(array('required','readonly','hide_label') as $property){			
			// if set
			if(isset($custom_field[$property])){
				$property_value = $custom_field[$property];
			}else{
				$property_value = false;
			}
			// set
			$custom_field['attributes'][$property] = $property_value; 
		}	
	}
	// set
	$custom_fields[] = $custom_field;
 }
 
 // set 
 $member_custom_fields->set_custom_fields($custom_fields);
  
 // update
 update_option('mgm_member_custom_fields', $member_custom_fields);
 
 // read 