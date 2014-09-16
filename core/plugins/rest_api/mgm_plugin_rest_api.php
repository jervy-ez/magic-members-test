<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/** 
 * generate api suuport
 * @deprecated, developing in core
 */
class mgm_plugin_rest_api extends mgm_plugin{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_plugin_rest_api();
	}
	
	// mgm_plugin_rest_api
	function mgm_plugin_rest_api(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set name
		$this->name = 'Rest API';		
		// description
		$this->description = __('Provides REST API support.', 'mgm');		
		// default settings
		// $this->_default_setting();
		// set path
		parent::set_tmpl_path();		
	}
	
	// settings_box
	function settings_box(){		
		return "settings";
	}
	
	// test
	function show(){
		echo 'yuk';
	}
	
}

// end file	