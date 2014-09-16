<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members post class
 * extends object to save options to database
 * saves itself/options in postmeta table as _mgm_post/_mgm_post_options
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_post extends mgm_object{	
	// post id
	var $id = NULL;

	// construct
	function __construct($id=0, $fields=false){
		// php4
		$this->mgm_post($id, $fields);
	}
	
	// construct
	function mgm_post($id=0, $fields=false){
		// parent
		parent::__construct(); 
		// defaults
		$this->_set_defaults($id, $fields);
		// read vars from db
		$this->read();// read and sync				
	}
	
	// defaults
	function _set_defaults($id=0, $fields=false){
		// code
		$this->code        = __CLASS__;
		// name
		$this->name        = 'Post Lib';
		// description
		$this->description = 'Post Lib';		
		// set id
		$this->set_field('id', $id);
		// set from argument
		if(!is_array($fields)){			
			// defaults
			$fields = array('purchasable'=>'N', 'purchase_cost'=>0.00, 'purchase_expiry'=>'', 'purchase_duration'=>0, // actually access_duration 
			                'product'=>array(), 'access_membership_types'=>array(), 'access_delay'=>array(), 'access_duration'=>0,
							'access_view_limit'=>0, 'addons'=>array(), 'allowed_modules'=>array());
		}	
		// set
		$this->set_fields($fields);	
	}
	
	// set multiple: array
	function set_fields($fields) {
		// merge
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
		// merge
		$this->{$name} = $value;
	}
	
	// get single by key
	function get_field($name) {		
		// merge
		if(isset($this->{$name}))
			return $this->{$name};
		
		// error
		return false;	
	}
	
	// get product 
	function get_product() {		
		// merge
		if(isset($this->product))
			return (array)$this->product;
		
		// error
		return array();	
	}
	
	// get access membership types
	function get_access_membership_types() {		
		// merge
		if(isset($this->access_membership_types))
			return (array)$this->access_membership_types;
		
		// error
		return array();	
	}
	
	// get single by name
	function get_access_delay() {		
		// merge
		if(isset($this->access_delay))
			return (array)$this->access_delay;
		
		// error
		return array();	
	}
	
	// get single by name
	function get_access_duration() {		
		// merge
		if(isset($this->access_duration))
			return $this->access_duration;
		elseif(isset($this->purchase_duration))
			return $this->purchase_duration;
		// error
		return 0;	
	}
	
	// get view limit
	function get_access_view_limit(){
		// merge
		if(isset($this->access_view_limit))
			return $this->access_view_limit;		
		// error
		return 0;
	}
	
	// get addons
	function get_addons(){
		// merge
		if(isset($this->addons))
			return (array)$this->addons;
		
		// error
		return array();	
	}
	

	// get allowed modules
	function get_allowed_modules(){
		// merge
		if(isset($this->allowed_modules))
			return (array)$this->allowed_modules;
		
		// error
		return array();	
	}

	// check purchasable
	function is_purchasable(){
		// return 
		return bool_from_yn($this->get_field('purchasable'));
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
			$options_key = '_mgm_post_options';			
			// verify
			if($this->id){
				// update options
				update_post_meta($this->id, $options_key, $this->options);	
				// allow hook
				do_action('mgm_post_options_save', $this->options, $this->id);	
				// after save sync agin so that vars are immediately available on the calling object
				return $this->_sync();
			}
		}
		// error
		return false;	
	}
			
	// read settings from database and synchoronizes as class member variables
	function read(){
		// get vars
		if(!$this->options){
			// key 
			$options_key = '_mgm_post_options';
			// verify
			if($this->id){
				// read
				$this->options = get_post_meta($this->id, $options_key, true);	// single					
				// allow hook
				do_action('mgm_post_options_read', $this->options, $this->id);	
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
		$vars = array('purchasable', 'purchase_cost', 'purchase_expiry', 'purchase_duration',  
			          'product', 'access_membership_types', 'access_delay', 'access_duration',
					  'access_view_limit');		
		// set
		foreach($vars as $var){
			// var
			if ( isset( $old_obj->{$var} ) ){
				$this->{$var} = $old_obj->{$var};
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
		// to be saved vars
		$vars = array('purchasable', 'purchase_cost', 'purchase_expiry', 'purchase_duration',  
			          'product', 'access_membership_types', 'access_delay', 'access_duration',
					  'access_view_limit', 'addons', 'allowed_modules');	
		// loop
		foreach($vars as $var){			
			// set
			$this->options[$var] = $this->{$var};				
		}
	}	
}
// core/libs/classes/mgm_post.php