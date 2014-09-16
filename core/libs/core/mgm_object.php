<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members object class, saves object vars/instance in option table
 * parent for all classes that saves itself/options in wordpress options table
 *
 * @package MagicMembers
 * @since 2.5
 */ 
 class mgm_object{ 	 
    private static $instance; // instance

 	var $code        = 'mgm_object'; // the code identifies each object
	var $name        = '';           // the name 
	var $description = '';           // some descriptions
	var $setting     = array();      // internal settings
	var $options     = NULL;         // only vars set in options will be saved
	var $saving      = true;         // flag for saving @todo not properly used
	
	// construct
	public function __construct(){
		// setup
		$this->setup();	
	}
	
	// destruct
	public function __destruct(){
		// make own
	}
	
	// get instance
	public static function get_instance() {
		// check	
		if (!isset(self::$instance)) {	
			// class name	
			$cls = __CLASS__;
			// create instance
			self::$instance = new $cls;
		}
		// return
		return self::$instance;
	}

	// setup
	public function setup(){
		// code
		$this->code        = __CLASS__;
		// name
		$this->name        = 'Object';
		// description
		$this->description = 'Object';
		// setting
		$this->setting     = array();
	}
		
	// save settings to database for later capture as class member variables
	// only defined member variables in _prepare callback will be saved and retrieved
	public function save(){		
		// check saving
		// if(!$this->saving) return; @todo test 
			
		// prepare variables to save
		$this->_prepare();
		// save, only when options set
		if($this->options){
			// key 
			$options_key = $this->_get_options_key();			
			// update
			update_option($options_key, $this->options);
			// after save sync agin so that vars are immediately available on the calling object
			return $this->_sync();
		}
		// error
		return false;	
	}
			
	// read settings from database and synchoronizes as class member variables
	public function read(){
		// check saving
		// if(!$this->saving) return; @todo test 
		
		// get options, only read and merge when not in the object itself !important
		if(!$this->options){
			// key 
			$options_key = $this->_get_options_key();
			// read
			$this->options = get_option($options_key);										
			// sync saved vars with class vars		
			return $this->_sync();
		}
		// error
		return false;	
	}
	
	// internal
	// prepare save, must override to declare options/vars to save
	public function _prepare(){	
		// init array
		$this->options = array();	
		// to be saved
		$vars = array('code','name','description','setting');		
		// loop
		foreach($vars as $var){
			// set
			$this->options[$var] = @$this->{$var};
		}
	}
	
	// sync read vars, override is optional, in certain cases, by default only syncs options var(s)
	public function _sync(){
		// check
		if( $class_vars = get_object_vars( $this ) ){
			// has options
			if(is_array($this->options)){
				// loop
				foreach($this->options as $option_name => $option_value){		
					// check
					if(in_array($option_name, $class_vars)){						
						// array, needs merge 
						if(is_array($this->{$option_name})){							
							// callback, to extend merging scenario for some optionswhich needs to overwrite default option vars
							if(method_exists($this,'_option_merge_callback')){
							// invoke, name, current value, new value
								$this->_option_merge_callback($option_name,$this->{$option_name},$option_value);
							}else{
							// default, merge current value and new value
								$this->{$option_name} = mgm_array_merge_recursive_unique($this->{$option_name},$option_value);
							}															
						}elseif(is_object($this->{$option_name})){
						// object, convert to array and merge. @TODO, needs test
							$this->{$option_name} = (object)(array_merge((array)mgm_object2array($this->{$option_name}),(array)$option_value));						
						}else{		
						// string, current value reset with new value													
							// set
							$this->{$option_name} = $option_value;						
						}				
					}
				}
				// return
				return $this->options;
			}
		}
		// return
		return false;
	}
	
	// options key
	public function _get_options_key(){
		// get key
		return sprintf('%s_options',$this->code);
	}
	
	// apply fix
	public function apply_fix($old_obj){
		// dump
		// mgm_pr($old_obj);
	}
	
	// magic methods	
	// get 
	public function __get($name){
		// get
		return @$this->{$name};
	}
	
	// set 
	public function __set($name, $value){
		// set
		@$this->{$name} = $value;
	}
 	
	// before serialization, return class vars to serialize 
	public function __sleep(){
		// save 
		return $this->save();
	}
	
	// after serialization, read class vars from db
	public function __wakeup(){
		// read 
		return $this->read();
	} 
	
	// for serialize
	public function __toString(){
		// return
		return serialize($this);
	}
	
	// call
	public function __call($method, $data){
		// check		
		die(sprintf('No method: %s::%s, file: %s, line: %d',get_class($this), $method, __FILE__, __LINE__));
	}
	
	// disable cloning
	/*
	public function __clone(){
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}	
	*/
 }
 // end of file core/libs/core/mgm_object.php