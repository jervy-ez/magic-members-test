<?php
/** 
 * Objects merge/update
 */ 
 // read  
 //Update saved custom fields with user public profile field
 // system object updates
 $member_custom_fields_cached = mgm_get_class('member_custom_fields');
 $arr_len = count($member_custom_fields_cached->custom_fields);
 $new_id = $arr_len+1;  
 $new_field = 	array('id'         => $new_id,
					  'name'       => 'show_public_profile',
					  'label'      =>  __('I would like to show public profile.','mgm'),
					  'type'       => 'checkbox',
					  'system'     => true, 
					  'value'      => 'Y',
					  'options'    => 'Y,N',
					  'display'    => array('on_register'=> true,'on_profile'=> true,'on_payment'=> false,'on_public_profile' => false),					  				  
					  'attributes' => array('required'=> false,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '')
					  );
 
 if(!$member_custom_fields_cached->get_field_by_name($new_field['name'],'bool')) {  	
 	$member_custom_fields_cached->custom_fields = array_merge($member_custom_fields_cached->custom_fields, array($arr_len=>$new_field) ); 	
 	$member_custom_fields_cached->next_id = $new_id+1;  	
 	$member_custom_fields_cached->save();
 }