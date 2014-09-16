<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members processor class
 *
 * @package MagicMembers
 * @since 2.5
 */
class mgm_processor{
	// method
	private $_method;	
	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_processor();
	}
	
	// php4 construct
	function mgm_processor(){
		// do something
		$this->_method = 'index';
		
		// ajax object
		$this->ajax = & new mgm_ajax();
	}
	
	// set instance to call
	function set_instance($instance){
		// set
		$this->instance = $instance;
	}
	
	// load method as called
	function call($method='', $default=''){
		// set default if set
		$this->set_default_method($default);
		
		// when method empty, take from request
		if(empty($method) || is_null($method)){
			// what method to call
			if(isset($_REQUEST['method']) && !empty($_REQUEST['method'])){
				$method = $_REQUEST['method'];
			}else{
			// take default
				$method = $this->_method;
			}
		}		
			
		// check and call		
		if(method_exists($this->instance, $method)){
			// ajax output start
			$this->ajax->start_output();
			// method
			$this->instance->$method();
			// ajax output end
			$this->ajax->end_output();
		}else{
			// error
			trigger_error("No such method {$this->instance}::{$method}", E_USER_ERROR);
		}
	}	
	
	// bypass and force call	
	function forward($method=''){
		// load method
		$this->call($method);
	}
	
	// set default method
	function set_default_method($method=null){
		// check
		if(empty($method) || is_null($method)) return;
		
		// set	
		$this->_method = $method;
	}	
}
// end of file core/libs/core/mgm_processor.php