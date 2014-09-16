<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members loader class
 * loads file/template
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_loader{
	// private attributes	
	private $_tmpl_dir;	
	private $_ajax;
	// construct
	function __construct($tmpl_path=''){
		// php4 construct
		$this->mgm_loader($tmpl_path);
	}
	
	// php4 construct
	function mgm_loader($tmpl_path=''){
		// set default template path, if not false
		if($tmpl_path !== FALSE)
			$this->set_tmpl_path($tmpl_path);
				
		// ajax object
		$this->ajax = new mgm_ajax();
	}	
		
	// load template file
	function template($name, $data='', $return=false){
		// make data available
		if(is_array($data)) extract($data);
		
		// set template	
		$template = $this->get_tmpl_path() . $name . '.php';	
		
		// file exists
		if(is_file($template)){
			// output start
			$this->ajax->start_output();
			// html
			if($return){
				return $this->_get_include($template, $data);
			}else{
				@include($template);				
			}
			// output end
			$this->ajax->end_output();			
		}else{
			// error
			trigger_error("Template Html missing [$template]", E_USER_ERROR);
		}	
	}
	
	// ste template path	
	function set_tmpl_path($tmpl_path=''){	
		// set tpl dir	or take default		
		$this->_tmpl_dir = (!empty($tmpl_path) ? $tmpl_path : (MGM_CORE_DIR . 'admin' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR));			
	}	
	
	// get template path
	function get_tmpl_path(){
		// set if not done 
		if(!$this->_tmpl_dir)
			$this->set_tmpl_path();
			
		// return	
		return $this->_tmpl_dir;
	}
		
	// get include
	function _get_include($template, $data=false){
		// make data available
		if(is_array($data)) extract($data);
		
		// buffer start
		@ob_start();
		// include		
		@include($template);
		// return
		return @ob_get_clean();	
		// end	
	}
}
// end of file core/libs/core/mgm_loader.php