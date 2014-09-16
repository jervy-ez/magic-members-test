<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin contents module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_custom_fields extends mgm_controller{
 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_custom_fields();
	}
	
	// construct php4
	function mgm_admin_custom_fields()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){		
		global $wpdb;	
		// data
		$data = array();				
		// load template view
		$this->load->template('custom_fields/index', array('data'=>$data));
	}
	
	// lists
	function lists(){
		global $wpdb;	
		// data
		$data = array();	
		// coupons		
	    $data['cf_obj'] = mgm_get_class('member_custom_fields');	
		// $data['cf_obj']->update_field_id('favorite_sports', 27);	
		// mgm_log($data['cf_obj']);
		// load template view
		$this->load->template('custom_fields/lists', array('data'=>$data));		
	}
	
	// add
	function add(){	
		global $wpdb;	
		// extract
		extract($_POST);
		// get object
		$cf_obj = mgm_get_class('member_custom_fields');	
		// save
		if(isset($save_fields) && !empty($save_fields)){
			// init
			$custom_field = array();			
			// set new
			$custom_field['id'] = $cf_obj->next_id;
			// name
			$custom_field['name'] = (isset($name) && !empty($name)) ? mgm_create_slug($name, 50) : mgm_create_slug($label,50); 

			//duplicate check:			
			if( $cf_obj->is_duplicate(strtolower($custom_field['name'])) ) {
				// messgae
				$message = __('Sorry, the custom field name should be unique, please try a different name', 'mgm');
				$status  = 'error';
			}else {
				      
				// label
				$custom_field['label']          						= isset($label) ? __($label,'mgm') : sprintf(__('User Field %d','mgm'), $id);
				// type
				$custom_field['type']           						= isset($type) ? $type : 'text';
				// system defined
				$custom_field['system']         						= false;// custom added
				// value
				$custom_field['value']          						= (isset($value))? $value : '';
				// has options
				$custom_field['options']        						= (isset($options)) ? $options : false;
				
				// display
				$display                        						= array();			
				// on register page
				$display['on_register']         						= (isset($on_register)) ? $on_register : false;	
				// on login page
				$display['on_login']       	    						= (isset($on_login)) ? $on_login : false;
				// on login widget page
				$display['on_login_widget']     						= (isset($on_login_widget)) ? $on_login_widget : false;	
				// on profile page
				$display['on_profile']          						= (isset($on_profile)) ? $on_profile : false;	
				// on payment page
				$display['on_payment']          						= (isset($on_payment)) ? $on_payment : false;	
				// on public profile page
				$display['on_public_profile']   						= (isset($on_public_profile)) ? $on_public_profile : false;	
				// on another purchase
				// $display['on_another_purchase'] 						= (isset($on_another_purchase)) ? $on_another_purchase : false;				
				
				//on upgrade - issue #1285
				$display['on_upgrade']       							= (isset($on_upgrade)) ? $on_upgrade : false;			
				// on multiple membership level purchase page - issue #860
				$display['on_multiple_membership_level_purchase'] = (isset($on_multiple_membership_level_purchase)) ? $on_multiple_membership_level_purchase : false;							
				// set 
				$custom_field['display']           						= $display;
				
				// attributes
				$attributes                        						= array();	
				// required field
				$attributes['required']           						= (isset($required)) ? $required : false;
				// read only
				$attributes['readonly']            						= (isset($readonly)) ? $readonly : false;	
				// hide label
				$attributes['hide_label']          						= (isset($hide_label)) ? $hide_label : false;	
				// to autoresponder
				$attributes['to_autoresponder']                      	= (isset($to_autoresponder)) ? $to_autoresponder : false;	
				//capture only
				$attributes['capture_only']                          	= (isset($capture_only)) ? $capture_only : false;	
				//capture field alias
				$attributes['capture_field_alias']                   	= (isset($capture_field_alias)) ? $capture_field_alias : '';				
				
				//profile by membership types - issue#1573
				$attributes['profile_by_membership_types']          	= (isset($profile_by_membership_types)) ? $profile_by_membership_types : false;	
				//profile by membership types field alias
				$attributes['profile_membership_types_field_alias']  	= (isset($profile_membership_types_field_alias)) ? $profile_membership_types_field_alias : '';
				//register by membership types
				$attributes['register_by_membership_types']          	= (isset($register_by_membership_types)) ? $register_by_membership_types : false;	
				//register by membership types field alias
				$attributes['register_membership_types_field_alias'] 	= (isset($register_membership_types_field_alias)) ? $register_membership_types_field_alias : '';
				
				//password min length -issue #973
				$attributes['password_min_length'] 						= (isset($password_min_length)) ? $password_min_length : false;
				
				//default value 
				if (isset($password_min_length_field_alias) && 
					empty($password_min_length_field_alias) || 
					trim($password_min_length_field_alias) == 0 ){
						$password_min_length_field_alias =6;	
				}
				//password min length field alias
				$attributes['password_min_length_field_alias'] 			= (isset($password_min_length_field_alias)) ? $password_min_length_field_alias : '';		
				//password max length - issue #973
				$attributes['password_max_length']        				= (isset($password_max_length)) ? $password_max_length : false;
				//default value 
				if (isset($password_max_length_field_alias) && 
					empty($password_max_length_field_alias) || 
					trim($password_max_length_field_alias) == 0 ){
						$password_max_length_field_alias =13;	
				}				
				//password max length field alias
				$attributes['password_max_length_field_alias'] 			= (isset($password_max_length_field_alias)) ? $password_max_length_field_alias : '';					
				
				// set 
				$custom_field['attributes'] = $attributes;

				// save fields
				$success = $cf_obj->set_custom_field($custom_field);					 								 
				// saved
				if ($success) {
					// save
					$cf_obj->save();			
					// message
					$message =  sprintf(__('Successfully created new custom field: <b>%s</b>', 'mgm'), mgm_stripslashes_deep($label) );
					$status  = 'success';
				}else{
					// messgae
					$message = sprintf(__( 'Error while creating new custom field: <b>%s</b>', 'mgm'), mgm_stripslashes_deep($label));
					$status  = 'error';
				}
			}
			// return response			
			echo json_encode(array('status'=>$status, 'message'=>$message));				
			exit();
		}	
		
		// data
		$data = array();
		// types
		$data['input_types'] = $cf_obj->get_input_types();		
		// load template view
		$this->load->template('custom_fields/add', array('data'=>$data));		
	}
	
	// edit
	function edit(){	
		global $wpdb;	
		extract($_POST);
		// get object
		$cf_obj = mgm_get_class('member_custom_fields');
		// save
		if(isset($save_fields) && !empty($save_fields)){											
			// init
			$custom_field = $cf_obj->get_field($id);
			// name
			$custom_field['name'] 										= (isset($name) && !empty($name)) ? mgm_create_slug($name, 50) : mgm_create_slug($label,50);   
			// duplicate check:	
			if ($cf_obj->is_duplicate(strtolower($custom_field['name']), $id)) {
				// messgae
				$message = __('Sorry, the field name should be unique, please try a different name', 'mgm');
				$status  = 'error';
			} else {				     
				// label
				$custom_field['label']          						= isset($label) ? __($label,'mgm') : sprintf(__('User Field %d','mgm'), $id);
				
				// type
				if((int)$system == 0){               
					$custom_field['type']       						= isset($type) ? $type : 'text';
				}
				//issue #1343
				if($name == 'subscription_options' || $custom_field['name'] =='subscription_options'){
					$on_register = true;
					$required = true;
					$custom_field['type']       						= isset($type) ? $type : 'radio';		
				}
				// value
				$custom_field['value']          						= isset($value) ? $value : '';
				// has options
				$custom_field['options']        						= isset($options) ? $options : false;
							
				// display
				$display                        						= array();			
				// on register page
				$display['on_register']         						= (isset($on_register)) ? $on_register : false;	
				// on login page
				$display['on_login']            						= (isset($on_login)) ? $on_login : false;	
				// on login widget page
				$display['on_login_widget']     						= (isset($on_login_widget)) ? $on_login_widget : false;	
				// on profile page
				$display['on_profile']          						= (isset($on_profile)) ? $on_profile : false;	
				// on payment page
				$display['on_payment']          						= (isset($on_payment)) ? $on_payment : false;	
				// on public profile page
				$display['on_public_profile']   						= (isset($on_public_profile)) ? $on_public_profile : false;	
				// on another purchase
				// $display['on_another_purchase'] 						= (isset($on_another_purchase)) ? $on_another_purchase : false;	
				// coupon issue #1285
				if( in_array($name, array('coupon','payment_gateways','autoresponder')) ){
					// on extend page
					$display['on_extend']        						= (isset($on_extend)) ? $on_extend : false;	
					// on postpurchase page
					$display['on_postpurchase']  						= (isset($on_postpurchase)) ? $on_postpurchase : false;	
				}				
				
				// on upgrade page
				$display['on_upgrade']       							= (isset($on_upgrade)) ? $on_upgrade : false;	
				
				// on multiple membership level purchase page - issue #860
				$display['on_multiple_membership_level_purchase'] 		= (isset($on_multiple_membership_level_purchase)) ? $on_multiple_membership_level_purchase : false;											
				
				// set 
				$custom_field['display'] 								= $display;		

				// attributes
				$attributes                       	 					= array();	
				// required field
				$attributes['required']            						= (isset($required)) ? $required : false;
				// read only
				$attributes['readonly']            						= (isset($readonly)) ? $readonly : false;	
				// hide label
				$attributes['hide_label']          						= (isset($hide_label)) ? $hide_label : false;	
				// to autoresponder
				$attributes['to_autoresponder']    						= (isset($to_autoresponder)) ? $to_autoresponder : false;	
				//capture only
				$attributes['capture_only']        						= (isset($capture_only)) ? $capture_only : false;	
				//capture field alias
				$attributes['capture_field_alias'] 						= (isset($capture_field_alias)) ? $capture_field_alias : '';		
				
				//profile by membership types - issue#1573
				$attributes['profile_by_membership_types']          	= (isset($profile_by_membership_types)) ? $profile_by_membership_types : false;	
				//profile by membership types field alias
				$attributes['profile_membership_types_field_alias']  	= (isset($profile_membership_types_field_alias)) ? $profile_membership_types_field_alias : '';
				//register by membership types
				$attributes['register_by_membership_types']          	= (isset($register_by_membership_types)) ? $register_by_membership_types : false;	
				//register by membership types field alias
				$attributes['register_membership_types_field_alias'] 	= (isset($register_membership_types_field_alias)) ? $register_membership_types_field_alias : '';
				
				//password min length -issue #973
				$attributes['password_min_length'] 						= (isset($password_min_length)) ? $password_min_length : false;	
				
				//default value 
				if (isset($password_min_length_field_alias) && 
					empty($password_min_length_field_alias) || 
					trim($password_min_length_field_alias) == 0 ){
						$password_min_length_field_alias = 6;	
				}				
				//password min length field alias
				$attributes['password_min_length_field_alias'] 			= (isset($password_min_length_field_alias)) ? $password_min_length_field_alias : '';		
				
				//password max length - issue #973
				$attributes['password_max_length'] 						= (isset($password_max_length)) ? $password_max_length : false;	
				//default value 
				if (isset($password_max_length_field_alias) && 
					empty($password_max_length_field_alias) || 
					trim($password_max_length_field_alias) == 0 ){
						$password_max_length_field_alias = 13;	
				}				
				//password max length field alias
				$attributes['password_max_length_field_alias'] 			= (isset($password_max_length_field_alias)) ? $password_max_length_field_alias : '';		
				
				// auto_checked field
				$attributes['auto_checked'] 							= (isset($auto_checked)) ? $auto_checked : false;
				// email_confirm field
				$attributes['email_confirm'] 							= (isset($email_confirm)) ? $email_confirm : false;
				
				// birthdate
				if( in_array($name, array('birthdate')) ){
				// verify_age
					$attributes['verify_age'] 							= (isset($verify_age)) ? $verify_age : '';	
					$attributes['verify_age_unit'] 						= (isset($verify_age_unit)) ? $verify_age_unit : '';
					$attributes['verify_age_period'] 					= (isset($verify_age_period)) ? $verify_age_period : '';
				}

				// set 
				$custom_field['attributes'] = $attributes;

				// save
				$success = $cf_obj->set_custom_field($custom_field, $id);					 								 
				// saved
				if ($success) {
					// update on success
					$cf_obj->save();	
					// also update template, default subscription_introduction
					if($name == 'subscription_introduction'){
						// value
						if(isset($value)){
							mgm_get_class('system')->set_template('subs_intro', $value);	
						}
					}elseif($name == 'terms_conditions'){
					// default terms_conditions	
						// value
						if(isset($value)){
							mgm_get_class('system')->set_template('tos', $value);	
						}
					}													
					// message
					$message = sprintf(__('Successfully updated custom field: <b>%s</b>', 'mgm'),  mgm_stripslashes_deep($label));
					$status  = 'success';
				}else{
					// messgae
					$message = sprintf(__('Error while updating custom field: <b>%s</b>', 'mgm'), mgm_stripslashes_deep($label));
					$status  = 'error';
				}
			}
			// return response			
			echo json_encode(array('status'=>$status, 'message'=>$message));				
			exit();
		}	
		
		// data
		$data = array();
		// types
		$data['input_types'] = $cf_obj->get_input_types();		
		// get field
		$data['custom_field'] = $cf_obj->get_field($id);	
		
		// default subscription_introduction
		if($data['custom_field']['name'] == 'subscription_introduction'){
			// no value
			if(empty($data['custom_field']['value'])){
				$data['custom_field']['value'] = mgm_print_template_content('subs_intro');
			}
		}
		// default terms_conditions
		if($data['custom_field']['name'] == 'terms_conditions'){
			// no value
			if(empty($data['custom_field']['value'])){
				$data['custom_field']['value'] = mgm_print_template_content('tos');
			}
		}									 
		// load template view
		$this->load->template('custom_fields/edit', array('data'=>$data));		
	}
	
	// status change
	function status_change(){
		extract($_POST);
		// get object
		$cf_obj = mgm_get_class('member_custom_fields');
		// update
		if( bool_from_yn($active) ){
			$success = $cf_obj->set_sort_order($id);
			$w = 'activated';			
		}else{
			$success = $cf_obj->unset_sort_order($id);
			$w = 'deactivated';
		}		
		// label
		$label = $cf_obj->get_field_attr($id,'label');
		// send status
		if ($success) {
			// update on success
			$cf_obj->save();
			// message
			$message = sprintf(__('Successfully %s custom field: <b>%s</b>', 'mgm'), $w, mgm_stripslashes_deep($label));
			$status  = 'success';
		}else{
			// message
			$message = sprintf(__('Error while %s custom field: <b>%s</b>', 'mgm'), str_replace('ed','ing',$w), mgm_stripslashes_deep($label));
			$status  = 'error';
		}
		// return response		
		echo json_encode(array('status'=>$status, 'message'=>$message));			
		exit();	
	}
	
	// lists sort
	function lists_sort(){
		extract($_POST);
		// parse
		parse_str($sort_order, $sort);
		// new
		$new_sort_orders = $sort['active_custom_field_row'];		
		// object
		$cf_obj = mgm_get_class('member_custom_fields');
		// set sort
		$cf_obj->set_sort_orders($new_sort_orders);
		// update
		$cf_obj->save();
		// check
		$message = __('Successfully sorted custom fields', 'mgm');
		$status  = 'success';
		// return response		
		echo json_encode(array('status'=>$status, 'message'=>$message));			
		exit();	
	}
	
	// delete
	function delete(){
		extract($_POST);		
		// object
		$cf_obj = mgm_get_class('member_custom_fields');
		// label
		$label = $cf_obj->get_field_attr($id,'label');
		// set sort
		$success = $cf_obj->unset_custom_field($id);
		// success
		if($success) {
			// update on success
			$cf_obj->save();
			// message
			$message = sprintf(__('Successfully removed custom field: <b>%s</b>', 'mgm'), mgm_stripslashes_deep($label));
			$status  = 'success';
		}else{
			// message
			$message = sprintf(__('Error while removing custom field: <b>%s</b>', 'mgm'), mgm_stripslashes_deep($label));
			$status  = 'error';
		}			
		// return response		
		echo json_encode(array('status'=>$status, 'message'=>$message));			
		exit();	
	}	
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_custom_fields.php 