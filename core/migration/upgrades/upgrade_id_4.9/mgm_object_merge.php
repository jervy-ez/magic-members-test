<?php
/** 
 * Objects merge/update
 */ 
 // read  
 //Update saved custom fields with payment_gateways field
 // system object updates
 $member_custom_fields_cached = mgm_get_class('member_custom_fields');
 $arr_len = count($member_custom_fields_cached->custom_fields);
 $new_id = $arr_len+1;  
 $new_field = 		array(   'id'     	  => $new_id,
							 'name'       => 'payment_gateways',
							 'label'      => __('Payment Gateways','mgm'),
							 'type'       => 'label',
							 'system'     => true, 
							 'value'      => '',
							 'options'    => false,
							 'display'    => array('on_register'=> true,'on_profile'=> false,'on_payment'=> false,'on_public_profile' => false),	
							 'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false)
							 );		 

 
 if(!$member_custom_fields_cached->get_field_by_name($new_field['name'],'bool')) {  	
 	$member_custom_fields_cached->custom_fields = array_merge($member_custom_fields_cached->custom_fields, array($arr_len=>$new_field) ); 	
 	$member_custom_fields_cached->next_id = $new_id+1;  	
 	$member_custom_fields_cached->save();
 }
 
 // end file