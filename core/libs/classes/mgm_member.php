<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members member class
 * extends object to save options to database
 * saves itself/options in usermeta table as mgm_member/mgm_member_options
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_member extends mgm_object{	
	// user id
	var $id                     = NULL;
	// custom fields
	var $custom_fields          = NULL;// object
	// other membership types purchased
	var $other_membership_types = NULL;	// array	
	// payment info
	var $payment_info           = NULL;// object	
	// coupon
	var $coupon                 = NULL;// object
	// upgrade
	var $upgrade                = NULL;// object
	// extend
	var $extend                 = NULL;// object
	
	// construct
	function __construct($id=0, $fields=false){
		// php4
		$this->mgm_member($id, $fields);
	}
	
    // construct
	function mgm_member($id=0, $fields=false){
		// parent
		parent::__construct(); 
		// defaults
		$this->_set_defaults($id, $fields);
		// read vars from db
		$this->read();// read and sync
		// fix expire date
		$this->fix_expire_date();
	}
	
	// defaults
	function _set_defaults($id=0, $fields=false){
		// code
		$this->code        = __CLASS__;
		// name
		$this->name        = 'Member Lib';
		// description
		$this->description = 'Member Lib';
		// set id
		$this->set_field('id', $id);
		// custom fields
		$this->custom_fields = new stdClass;
		// payment info
		$this->payment_info  = new stdClass;
		// coupon
		$this->coupon  = array();
		// upgrade
		$this->upgrade  = array();
		// extend
		$this->extend  = array();
		// other membership types:
		$this->other_membership_types = array();
		// not set from argument
		if(!is_array($fields)) $fields = $this->_default_fields();
		// set
		$this->set_fields($fields);	
	}
	
	// set multiple: array
	function set_fields($fields) {
		// check
		if(is_array($fields)){
			// loop
			foreach($fields as $name=>$value){
				// set
				$this->{$name} = $value;
			}
		}
	}
	
	// set single: key=>value
	function set_field($name, $value) {		
		// set
		$this->{$name} = $value;
	}
	
	// set inner object vars
	function set_custom_fields($custom_fields) {
		// check
		if(is_array($custom_fields)){
			// loop
			foreach($custom_fields as $name => $value){
				// set
				$this->custom_fields->{$name} = $value;
			}
		}
	} 
	
	// set inner object vars
	function set_payment_info($payment_info) {
		// check
		if(is_array($payment_info)){
			// loop
			foreach($payment_info as $name => $value){
				// set
				$this->payment_info->{$name} = $value;
			}
		}
	} 
	
	// set single: key=>value
	function set_custom_field($name, $value) {		
		// merge
		$this->custom_fields->{$name} = $value;
	}
	
	// copy mgm_member object fields to user object 
	function merge_fields($user){
		global $wpdb;
		// create
		if(!is_object($user)){
			$user = new stdClass();
		}		
		

		// merge defaults
		foreach($this->_default_fields() as $field=>$value){ 
			// if not set
			if(!isset($user->$field)){
				// set
				$user->{$field} = $this->{$field};			
			}
		}
		
		// merge custom fields
		// take acive fields, this will eradicate possibility of different set of custom fields active at different times
		// default
		$custom_fields = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true,'on_profile'=>true)));
		// loop
		foreach($custom_fields as $field){ 
			// if not set in main object
			if(!isset($user->$field['name'])){
				// if set in object
				if(isset($this->custom_fields->$field['name'])){
					$user->$field['name'] = $this->custom_fields->$field['name'];
				}else{
				// default
					$user->$field['name'] = 'N/A';
				}
			}
		}

		// from meta
		$profile_fields = mgm_get_config('default_profile_fields', array());
		//mgm_log('default_profile_fields : '.mgm_array_dump($profile_fields,true));
		// loop rest
		foreach(get_object_vars($this) as $field=>$value){
			// skip some 
			if(in_array($field, array('id','code','name','description','saving'))) continue;			
			
			// loop
			foreach($profile_fields as $p_field=>$p_field_options){
				
				if (method_exists($user, 'exists')) {
					
					if ( ! $user->exists() )
		                  continue;					
		            if ( $user->has_prop( $wpdb->prefix . $p_field ) ) // Blog specific
						$result = $user->get( $wpdb->prefix . $p_field );
					elseif ( $user->has_prop( $p_field ) ) // User specific and cross-blog
						$result = $user->get( $p_field );
					else
						$result = false;

					if($result){
						$user->$p_field = $result;	
					}
				}
				// option
				//if($p_field_value = get_user_option($p_field, $user->ID)){
					//$user->$p_field = $p_field_value;
					
				//}
			}
			
			// if not set
			if(!isset($user->$field) && $field != 'custom_fields'){
				// string value
				if(is_string($value)){
					// strip _mgm				
					$value = str_replace('mgm_', '', $value);				
					// set
					$user->{$field} = $value;	
				}else if(is_object($value)){
				// object value
					// loop
					foreach(get_object_vars($value) as $field2=>$value2){
						// only take first level
						if(is_string($value2)){
							// strip _mgm		
							$value2 = str_replace('mgm_', '', $value2);
							$field2 = $field . '_' . $field2;						
							// set
							$user->{$field2} = $value2;	
						}	
					}
				}
			}	
		}
		
		// return
		return $user;
	}
	
	// expire date fix, add time part to it
	function fix_expire_date(){
	// fix
		mgm_fix_member_expire_date($this);
	}
	
	// default fields
	function _default_fields(){
		// return
		return array('trial_on' => 0, 'trial_cost' => 0.00, 'trial_duration' => 0, 'trial_duration_type' => 'd', 'trial_num_cycles'=>0, 
					 'duration' => 0, 'duration_type' => 'm', 'amount' => 0, 'currency' => 'USD', 'join_date' => '', 'last_pay_date' => '', 
					 'expire_date' => '', 'membership_type' => 'guest', 'status' => 'Inactive', 'payment_type' => '', 'autoresponder' => '', 
					 'subscribed' => '', 'autoresponder_notified' => '');
	}
		
	// serialize
	function __toString(){
		return serialize($this);
	}	
		
	// save settings to database for later capture as class member variables
	// only defined member variables in _prepare callback will be saved and retrieved
	function save(){			
		// prepare variables to save
		$this->_prepare();
		// save
		if($this->options){
			// key 
			$options_key = 'mgm_member_options';			
			// verify
			if($this->id){
				// update
				update_user_option($this->id, $options_key, $this->options, true);	
				// update custom fields
				$this->_update_custom_fields();				
				// allow hook
				do_action('mgm_user_options_save', $this->options, $this->id);		
				// after save sync agin so that vars are immediately available on the calling object
				return $this->_sync();
			}
		}
		// error
		return false;	
	}
	//save custom fields in user meta
	function _update_custom_fields(){	
		// save
		if($this->options){			
			foreach ($this->options['custom_fields'] as $key => $value) {
				// key 
				$options_key = '_mgm_cf_'.$key;
				// verify
				if($this->id){
					// update
					update_user_option($this->id, $options_key, $value, true);	
				}				
			}
		}
	}			
	// read settings from database and synchoronizes as class member variables
	function read(){
		// get vars
		if(!$this->options){
			// key 
			$options_key = 'mgm_member_options';
			// verify
			if($this->id){
				// read				
				$this->options = mgm_get_user_option($options_key, $this->id);
				// allow hook
				do_action('mgm_user_options_read', $this->options, $this->id);								
				// sync saved vars with class vars		
				return $this->_sync();
			}
		}
		// error
		return false;	
	}
	
	// fix
	function apply_fix($old_obj){		
		// to be copied vars
		$vars = get_object_vars($this);		
		// set
		foreach($vars as $name=>$var){
			// object
			if(is_object($var)){
				// merge
				$this->{$name} = (object) array_merge((array) $this->{$name}, (array) $old_obj->{$name});
			}else{
				// var
				if ( isset( $old_obj->{$name} ) ){
					$this->{$name} = $old_obj->{$name} ;
				}
			}	
		}				
		// save
		$this->save();	
	}
	
	// prepare save, define the object vars to be saved
	// internally called by object->save()
	function _prepare(){	
		// init array
		$this->options = array();
		$obj_name_array = array('coupon');	
		// to be saved vars
		$vars = get_object_vars($this);				
		// set
		foreach($vars as $name=>$var){
			// skip error
			if('options' == $name) continue;
			// var
			if(is_object($var)){					
				$this->options[$name] = (array) $this->{$name};
			}else{
				$this->options[$name] = $this->{$name};
			}
			//3rd level update:
			foreach ($obj_name_array as $name_level3) {
				if(isset($this->options[$name][$name_level3]) && is_object($this->options[$name][$name_level3])) {					
					$this->options[$name][$name_level3] = (array) $this->options[$name][$name_level3];					
				}
			}
		}		
	}
}
// core/libs/classes/mgm_member.php