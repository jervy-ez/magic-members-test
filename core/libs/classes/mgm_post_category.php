<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members post category class
 * extends object to save options to database
 *
 * @package MagicMembers
 * @since 2.5
 */  
class mgm_post_category extends mgm_object{		
	
	// construct
	function __construct($fields=false){
		// php4
		$this->mgm_post_category($fields);
	}
	
	// construct
	function mgm_post_category($fields=false){
		// parent
		parent::__construct(); 
		// defaults
		$this->_set_defaults($fields);
		// read vars from db
		$this->read();// read and sync			
	}
	
	// defaults
	function _set_defaults($fields=false){
		// code
		$this->code        = __CLASS__;
		// name
		$this->name        = 'Post Category Lib';
		// description
		$this->description = 'Post Category Lib';
		
		// set from argument
		if(!is_array($fields)){			
			$fields = array('access_membership_types' => array());		
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
				$this->{$name} = $value;
			}
		}
	}
	
	// set single: key=>value
	function set_field($name, $value) {		
		// merge
		$this->$name = $value;
	}
	
	// get single by key
	function get_field($name) {		
		// merge
		if(isset($this->$name))
			return $this->$name;
		
		// error
		return false;	
	}
	
	// get single by name
	function get_access_membership_types() {		
		// merge
		if(isset($this->access_membership_types))
			return (array)$this->access_membership_types;
		
		// error
		return array();	
	}
	
	// serialize
	function __toString(){
		return serialize($this);
	}
	
	// fix
	function apply_fix($old_obj){
		// to be copied vars
		$vars = array('access_membership_types');
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
		$vars = array('access_membership_types');
		// set
		foreach($vars as $var){
			// var
			$this->options[$var] = $this->{$var};
		}	
	}
}
// core/libs/classes/mgm_object.php