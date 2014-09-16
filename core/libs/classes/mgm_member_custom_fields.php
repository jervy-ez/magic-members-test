<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members custom fields class
 * extends object to save options to database
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_member_custom_fields extends mgm_object{
	// custom_fields
	var $custom_fields = array();
	// order
	var $sort_orders   = array();
	// next id
	var $next_id       = 28;
	
	// construct
	function __construct($custom_fields=false, $sort_orders=false){
		// php4
		$this->mgm_member_custom_fields($custom_fields, $sort_orders);
	}
	
	// construct
	function mgm_member_custom_fields($custom_fields=false, $sort_orders=false){
		// parent
		parent::__construct(); 
		// defaults
		$this->_set_defaults($custom_fields, $sort_orders);
		// read vars from db
		$this->read();// read and sync			
	}
	
	// defaults
	function _set_defaults($custom_fields=false, $sort_orders=false){
		// code
		$this->code        = __CLASS__;
		// name
		$this->name        = 'Member Custom Fields Lib';
		// description
		$this->description = 'Member Custom Fields Lib';
		
		// set from default
		if($custom_fields === false)
			$custom_fields = $this->base_custom_fields();					
		// set
		$this->set_custom_fields($custom_fields);	
		
		if($sort_orders === false)
			$sort_orders = $this->base_sort_orders();	
		// set
		$this->set_sort_orders($sort_orders);	
	}	
	
	// set multiple
	function set_custom_fields($custom_fields, $merge=false) {
		
		// check
		if(!is_array($custom_fields))
			return false;
		
		// set
		$this->custom_fields = ($merge) ? array_merge($this->custom_fields, $custom_fields) : $custom_fields;
				
		// update next id
		// $this->next_id = $this->custom_fields[(count($this->custom_fields)-1)]['id'];
		
		// return
		return true;
	}
	
	// set single
	function set_custom_field($custom_field, $id=NULL, $name=NULL) {		
		// check
		if(!isset($custom_field)) return false;
			
		// add at pos, update
		if($id || $name){
			// search and set
			$key = $id ? $this->get_field_index($id) : $this->get_field_index_by_name($name);
			// check
			if($key !== false){
				$this->custom_fields[$key] = $custom_field;
			}else{
				return false;
			}		
		}else{
		// end, new	
			array_push($this->custom_fields, $custom_field);	
			// update next 
			$this->next_id++;
		}
		// return
		return true;
	}
	
	// set multiple
	function set_sort_orders($sort_orders, $merge=false) {
		// check
		if(!is_array($sort_orders))
			return false;
			
		$this->sort_orders = ($merge) ? array_merge($this->sort_orders, $sort_orders) : $sort_orders;
		// eliminate duplicates
		$this->sort_orders = array_unique($this->sort_orders); 
		// return
		return true;
	}
	
	// set single
	function set_sort_order($sort_order) {		
		// check
		if(intval($sort_order) == 0)
			return false;
		// add
		array_push($this->sort_orders, $sort_order);	
		// make unique
		$this->sort_orders = array_unique($this->sort_orders);
		// return
		return true;
	}
	
	// unset single custom field
	function unset_custom_field($id) {
		// get key
		$key = $this->get_field_index($id);
		// remove if found
		if($key !== false){
			// unset
			unset($this->custom_fields[$key]);
			// treat success
			return true;
		}
		// trteat error
		return false;
	}	
	
	// unset single sort order
	function unset_sort_order($sort_order) {
		// get key
		$key = array_search($sort_order, $this->sort_orders);
		// remove if found
		if($key !== false){
			// unset
			unset($this->sort_orders[$key]);
			// treat success
			return true;
		}
		// trteat error
		return false;
	}	
	 
	// base custom_fields
	function base_custom_fields() {
		// options
		return array( 
				array('id'         => 1,
					  'name'       => 'terms_conditions',
					  'label'      => __('Terms and Conditions','mgm'),
					  'type'       => 'html',
					  'system'     => true, 
					  'value'      => 'Terms and Conditions',
					  'options'    => false,		
					  'display'    => array('on_register'=> true,'on_profile'=> false,'on_payment'=> false,'on_public_profile'=> false),
					  'attributes' => array('required'=> false,'readonly'=> true,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
				array('id'         => 2,
					  'name'       => 'subscription_introduction',
					  'label'      => __('Subscription Introduction','mgm'),
					  'type'       => 'html',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,
					  'display'    => array('on_register'=> true,'on_profile'=> false,'on_payment'=> false,'on_public_profile' => false),					  				  
					  'attributes' => array('required'=> false,'readonly'=> true,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
			    array('id'         => 3,
					  'name'       => 'subscription_options',
					  'label'      => __('Subscription Options','mgm'),
					  'type'       => 'radio',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,	
					  'display'    => array('on_register'=> true,'on_profile'=> false,'on_payment'=> false,'on_public_profile' => false),
					  'attributes' => array('required'=> true,'readonly'=> true,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
		  		array('id'         => 4,
					  'name'       => 'birthdate',
					  'label'      => __('Birthdate','mgm'),
					  'type'       => 'text',	
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,
					  'display'    => array('on_register'=> true,'on_profile'=> true,'on_payment'=> false,'on_public_profile' => true),	
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
		 		array('id'         => 5,
					  'name'       => 'country',
					  'label'      => __('Country','mgm'),
					  'type'       => 'select',
					  'system'     => true, 
					  'value'      => 'US',
					  'options'    => false,
					  'display'    => array('on_register'=> true,'on_profile'=> true,'on_payment'=> true,'on_public_profile' => true),
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
			  	array('id'         => 6,
					  'name'       => 'coupon',
					  'label'      => __('Coupon','mgm'),
					  'type'       => 'text',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,
					  'display'    => array('on_register'=> true,'on_profile'=> false,'on_payment'=> false, 'on_public_profile' => false, 
					  						'on_upgrade'=> false,'on_extend'=> false,'on_postpurchase'=>false,
					  						'on_multiple_membership_level_purchase'=>true),
					  'attributes' => array('required'=> false,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
				array('id'         => 7,
					  'name'       => 'autoresponder',
					  'label'      =>  __('I would like to receive updates.','mgm'),
					  'type'       => 'checkbox',
					  'system'     => true, 
					  'value'      => 'Y',
					  'options'    => 'Y,N',
					  'display'    => array('on_register'=> true,'on_profile'=> false,'on_payment'=> false,'on_public_profile' => false),					  				  
					  'attributes' => array('required'=> false,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
				array('id'         => 8,
					  'name'       => 'first_name',
					  'label'      => __('First Name','mgm'),
					  'type'       => 'text',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,
					  'display'    => array('on_register'=> true,'on_profile'=> true,'on_payment'=> true,'on_public_profile' => true),		
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> true,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
				array('id'         => 9,
					  'name'       => 'last_name',
					  'label'      => __('Last Name','mgm'),
					  'type'       => 'text',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,
					  'display'    => array('on_register'=> true,'on_profile'=> true,'on_payment'=> true,'on_public_profile' => true),					  					  
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> true,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
				array('id'         => 10,
					  'name'       => 'address',
					  'label'      => __('Address','mgm'),
					  'type'       => 'textarea',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,
					  'display'    => array('on_register' => true,'on_profile'=> true,'on_payment'=> true,'on_public_profile' => true),					  					  
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
				array('id'         => 11,
					  'name'       => 'city',
					  'label'      => __('City','mgm'),
					  'type'       => 'text',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,	
					  'display'    => array('on_register' => true,'on_profile'  => true,'on_payment'  => true,'on_public_profile' => true),					  				  
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
				array('id'         => 12,
					  'name'       => 'state',
					  'label'      => __('State','mgm'),
					  'type'       => 'text',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,
					  'display'    => array('on_register' => true,'on_profile'  => true,'on_payment'  => true,'on_public_profile' => true),					  				  
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
				array('id'         => 13,
					  'name'       => 'zip',
					  'label'      => __('Zip','mgm'),
					  'type'       => 'text',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,	
					  'display'    => array('on_register' => true,'on_profile'  => true,'on_payment'  => true,'on_public_profile' => true),					  				  
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
				array('id'         => 14,
					  'name'       => 'password',
					  'label'      => __('Password','mgm'),
					  'type'       => 'password',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,	
					  'display'    => array('on_register' => true,'on_profile'  => false,'on_payment'  => false,'on_public_profile' => false),							  			      
				      'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
				      						'capture_only'=> false,'capture_field_alias'=> '','password_min_length'=> false,'password_min_length_field_alias'=> '',
				      						'password_max_length'=> false,'password_max_length_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
				array('id'         => 15,
					  'name'       => 'privacy_policy',
					  'label'      => __('Privacy Policy','mgm'),
					  'type'       => 'html',
					  'system'     => true, 
					  'value'      => 'Privacy Policy',
					  'options'    => false,	
					  'display'    => array('on_register' => true,'on_profile'  => false,'on_payment'  => false,'on_public_profile' => false),		
					  'attributes' => array('required'=> false,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
				array('id'         => 16,
					  'name'       => 'phone',
					  'label'      => __('Phone','mgm'),
					  'type'       => 'text',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,		
					  'display'    => array('on_register' => true,'on_profile'  => true,'on_payment'  => true,'on_public_profile' => true),
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),	
					  
				array('id'         => 17,
					  'name'       => 'username',
					  'label'      => __('Username','mgm'),
					  'type'       => 'text',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,	
					  'display'    => array('on_register' => true,'on_profile'  => true,'on_payment'  => false,'on_public_profile' => true),
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),	
					  
				array('id'         => 18,
					  'name'       => 'email',
					  'label'      => __('E-mail','mgm'),
					  'type'       => 'text',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,	
					  'display'    => array( 'on_register' => true,'on_profile'  => false,'on_payment'=> false,'on_public_profile' => true),
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> true,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),
					  
			   array('id'         => 19,
					 'name'       => 'photo',
					 'label'      => __('Photo','mgm'),
					 'type'       => 'image',
					 'system'     => true, 
					 'value'      => '',
					 'options'    => false,
					 'display'    => array('on_register'=> true,'on_profile'=> true,'on_payment'=> false,'on_public_profile' => true),	
					 'attributes' => array('required'=> false,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					 					   'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					 ),
				
			   array('id'         => 20,
					 'name'       => 'url',
					 'label'      => __('Website','mgm'),
					 'type'       => 'text',
					 'system'     => true, 
					 'value'      => '',
					 'options'    => false,
					 'display'    => array('on_register'=> false,'on_profile'=> true,'on_payment'=> false,'on_public_profile' => true),	
					 'attributes' => array('required'=> false,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					 					   'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					 ),
					 
			   array('id'         => 21,
					 'name'       => 'display_name',
					 'label'      => __('Display Name','mgm'),
					 'type'       => 'select',
					 'system'     => true, 
					 'value'      => 'AUTO',
					 'options'    => false,
					 'display'    => array('on_register'=> false,'on_profile'=> true,'on_payment'=> false,'on_public_profile' => true),
					 'attributes' => array('required'=> false,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					 					   'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					 ),
				
			   array('id'         => 22,
					 'name'       => 'description',
					 'label'      => __('Description','mgm'),
					 'type'       => 'textarea',
					 'system'     => true, 
					 'value'      => '',
					 'options'    => false,
					 'display'    => array('on_register'=> false,'on_profile'=> true,'on_payment'=> false,'on_public_profile'=> true),
					 'attributes' => array('required'=> false,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					 					   'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					 ),
					 
			   array('id'         => 23,
					 'name'       => 'nickname',
					 'label'      => __('Nickname','mgm'),
					 'type'       => 'text',
					 'system'     => true, 
					 'value'      => '',
					 'options'    => false,
					 'display'    => array('on_register'=> false,'on_profile'=> true,'on_payment'=> false,'on_public_profile' => true),	
					 'attributes' => array('required'=> false,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					 					   'capture_only'=>false,'capture_field_alias'=>'','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					 ),	
				array('id'         => 24,
					 'name'       => 'captcha',
					 'label'      => __('Captcha','mgm'),
					 'type'       => 'captcha',
					 'system'     => true, 
					 'value'      => '',
					 'options'    => false,
					 'display'    => array('on_register'=> true,'on_profile'=> false,'on_payment'=> false,'on_public_profile' => false),	
					 'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					 					   'capture_only'=>false,'capture_field_alias'=>'','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					 ),	 	
				array('id'         => 25,
					 'name'       => 'payment_gateways',
					 'label'      => __('Payment Gateways','mgm'),
					 'type'       => 'label',
					 'system'     => true, 
					 'value'      => '',
					 'options'    => false,
					 'display'    => array('on_register'=> true,'on_profile'=> false,'on_payment'=> false,'on_public_profile' => false,
					                       'on_multiple_membership_level_purchase'=>true),	
					 'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					 					   'capture_only'=>false,'capture_field_alias'=>'','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					 ),					  
			  	array('id'         => 26,
					  'name'       => 'addon',
					  'label'      => __('Addon','mgm'),
					  'type'       => 'checkbox',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,
					  'display'    => array('on_register'=> true,'on_profile'=> false,'on_payment'=> false, 'on_public_profile' => false, 
					  						'on_upgrade'=> false,'on_extend'=> false,'on_postpurchase'=>false,
					  						'on_multiple_membership_level_purchase'=>true),
					  'attributes' => array('required'=> false,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  ),

				array('id'         => 27,
					  'name'       => 'show_public_profile',
					  'label'      =>  __('I would like to show public profile.','mgm'),
					  'type'       => 'checkbox',
					  'system'     => true, 
					  'value'      => 'Y',
					  'options'    => 'Y,N',
					  'display'    => array('on_register'=> true,'on_profile'=> true,'on_payment'=> false,'on_public_profile' => false),					  				  
					  'attributes' => array('required'=> false,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> false,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '')
					  )
					  
					  	 			 
			);	
	}
	
	// base sort_orders,used to track active fields
	function base_sort_orders() {
		// options
		//return array(2, 3, 7);
		//issue #989
		return array(3, 7);
		
	}	
	
	// get attr of field
	function get_field_attr($id, $attr='name'){
		// default
		$field_attr = '';
		// check
		foreach($this->custom_fields as $custom_field){
			// id match
			if(intval($custom_field['id']) == $id){
				// set
				$field_attr = $custom_field[$attr] ;
				// no more process
				break;
			}
		}
		// return
		return $field_attr;
	}
	
	// get index
	function get_field_index($id){
		// default
		$field_index = false;		
		// check
		foreach($this->custom_fields as $index=>$custom_field){
			// id match
			if( (int)$custom_field['id'] == $id ){
				// set
				$field_index = $index;
				// no more process
				break;
			}			
		}
		// return
		return $field_index;
	}
	
	// get index
	function get_field_index_by_name($name){
		// default
		$field_index = false;		
		// check
		foreach($this->custom_fields as $index=>$custom_field){
			// id match
			if( $custom_field['name'] == $name ){
				// set
				$field_index = $index;
				// no more process
				break;
			}			
		}
		// return
		return $field_index;
	}
	
	// get field by id
	function get_field($id){
		// default
		$field = array();
		// check
		foreach($this->custom_fields as $custom_field){
			// id match
			if((int)$custom_field['id'] == $id){
				// set
				$field = $custom_field;
				// no more process
				break;
			}
		}
		// return
		return $field;
	}
	
	// get field by name
	// @return : array|bool
	function get_field_by_name($name, $return='array'){
		// default
		$field = array();
		// check
		foreach($this->custom_fields as $custom_field){
			// id match
			if($custom_field['name'] == $name){
				// set
				$field = $custom_field;
				// no more process
				break;
			}
		}
		// return
		switch($return){			
			case 'bool':
				return (isset($field['id'])) ? true : false;
			break;
			case 'array':
			default;
				return $field;
			break;
		}	
		
		// return error
		return false;	
	}
	
	// get field where
	function get_fields_where($where){
		// default
		$fields = $_fields = array();
		
		// property
		if(isset($where['display'])){
			$property = 'display';// display
			$where    = $where['display'];// reset
		}elseif(isset($where['attributes'])){
			$property = 'attributes';// attributes
			$where    = $where['attributes'];// reset
		}else{
			$property = 'self';
		}	
		
		// check
		foreach($this->custom_fields as $custom_field){
			// all
			if(is_string($where) && $where == ':any'){
				// set
				$_fields[] = $custom_field;		
			}else if(is_array($where)){
			// array
				// loop key value
				foreach($where as $key=>$value){
					// check
					switch($property){
						case 'display':
						case 'attributes':
							// if match
							if(isset($custom_field[$property][$key]) && $custom_field[$property][$key] == $value){
								// set
								$_fields[] = $custom_field;				
							}
						break;
						default;
							// if match
							if(isset($custom_field[$key]) && $custom_field[$key] == $value){
								// set
								$_fields[] = $custom_field;				
							}
						break;
					}
				}	
			}
		}		
		
		// order	
		foreach($this->sort_orders as $id){
			// loop
			foreach($_fields as $field){			
				// order match
				if($field['id'] == $id){
					// set
					$fields[$id] = $field;	// keep unique
					// password included
					if($field['name']=='password'){
						// add password conf internally
						$password_conf = $this->get_password_conf_field();
						// reset hide label here 
						$password_conf['attributes']['hide_label'] = $field['attributes']['hide_label'];
						// set in sorted array
						$fields[$password_conf['id']] = $password_conf;
					}
					// email included  - issue #1315
					if($field['name']=='email' && isset($field['attributes']['email_confirm'])){
						// add password conf internally
						$email_conf = $this->get_email_conf_field();
						// reset hide label here 
						$email_conf['attributes']['hide_label'] = $field['attributes']['hide_label'];
						// set in sorted array
						$fields[$email_conf['id']] = $email_conf;
					}
				}
			}	
		}
		// return
		return $fields;
	}
	
	//check duplicate name: nice
	function is_duplicate($name, $id = null) {
		// loop
		foreach($this->custom_fields as $custom_field){
			// id match
			if($custom_field['name'] == $name && $custom_field['id'] != $id ){				
				return true;
			}
		}
		return false;
	}
	
	// update value by name
	function update_field_value($name, $value){
		// get field
		$custom_field = $this->get_field_by_name($name);
		// found
		if(isset($custom_field['id'])){
			// set value
			$custom_field['value'] = $value;
			// set
			$this->set_custom_field($custom_field, $custom_field['id']);
			// update on success
			$this->save();
		}
	}
	
	// update value
	function update_field_id($name, $id){
		// get field
		$custom_field = $this->get_field_by_name($name);
		// found
		if(isset($custom_field['id'])){
			// set value
			$custom_field['id'] = $id;
			// set
			$this->set_custom_field($custom_field, false, $custom_field['name']);
			// update on success
			$this->save();
		}
	}
	
	// password conf field
	function get_password_conf_field(){
		// return
		return array('id'         => substr(time(),0,6),
					 'name'       => 'password_conf',
					 'label'      => __('Confirm Password','mgm'),
					 'type'       => 'password',
					 'system'     => true, 
					 'value'      => '',
					 'options'    => false,	
					 'display'    => array('on_register' => true,'on_profile' => true),							  			      
				     'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false)
					 );
	}
	
	// email conf field - issue #1315
	function get_email_conf_field(){
		// return
		return array('id'         => substr(time(),1,7),
					 'name'       => 'email_conf',
					 'label'      => __('Confirm E-mail','mgm'),
					 'type'       => 'text',
					 'system'     => true, 
					 'value'      => '',
					 'options'    => false,	
					 'display'    => array('on_register' => true,'on_profile' => true),							  			      
				     'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false)
					 );
	}
	
	// export helper
	function export(){
		// loop
		$export = array('sort_order'=>json_encode($this->sort_orders));	
	}
	
	// get input types
	function get_input_types(){
		return array('text'       => __('Text Field (Single Line)','mgm'),
		             'textarea'   => __('Text Area (Multi-Line)','mgm'),
					 'html'       => __('Html','mgm'),
					 'password'   => __('Password','mgm'),
					 'select'     => __('Select (Drop down box)','mgm'),
					 'selectm'    => __('Multiple Select (Drop down box)','mgm'),
					 'checkbox'   => __('Checkbox','mgm'),
					 'checkboxg'  => __('Checkbox Group','mgm'),
					 'radio'      => __('Radio','mgm'),
					 'hidden'     => __('Hidden Field (Set value)','mgm'),
					 'label'      => __('Label','mgm'),
					 'image'      => __('Image','mgm'), 
					 'captcha'    => __('Captcha', 'mgm'),
					 'datepicker' => __('Date Picker', 'mgm'),
				 );	
	}
	
	// fix
	function apply_fix($old_obj){
		// to be copied vars
		$vars = array('custom_fields','sort_orders','next_id');
		// set
		foreach($vars as $var){
			// var
			$this->{$var} = (isset( $old_obj->{$var} ) ) ? $old_obj->{$var} : '';
		}				
		// save
		$this->save();	
	}
	
	// prepare save, define the object vars to be saved
	// internally called by object->save()
	function _prepare(){		
		// init array
		$this->options = array();
		// to be saved vars
		$vars = array('custom_fields','sort_orders','next_id');
		// set
		foreach($vars as $var){
			// var
			$this->options[$var] = $this->{$var};
		}	
	}
}
// core/libs/classes/mgm_member_custom_fields.php