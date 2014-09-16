<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members ajax utility class
 *
 * @package MagicMembers
 * @since 2.5
 */
class mgm_ajax{
	// ajax_request
	var $ajax_request = false; 
	
	// construct
	function __construct(){
		// php4 
		$this->mgm_ajax();
	}
	
	// php4 construct
	function mgm_ajax(){
		// check if ajax
		$this->ajax_request = (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"]  == 'XMLHttpRequest') ? TRUE : FALSE;
	}	
	
	// check if ajax
	function is_ajax_request(){
		// return 
		return $this->ajax_request;
	}	
	
	// header
	function send_header(){
		// when not sent
		if(!headers_sent()){
			// default
			$content_type = 'text/html';
			// accept_types
			$accept_types = array('application/json','text/javascript','text/plain');
			// loop
			foreach($accept_types as $accept_type){
				// match
				if(preg_match('/' . (str_replace('/','\/', $accept_type)) . '/i', $_SERVER['HTTP_ACCEPT'])){
					$content_type = $accept_type; break;
				}
			}
			// headers
			@header('Content-type:' . $content_type);			
		}
	}
	
	// start output
	function start_output($header=true){
		// check if ajax
		if($this->is_ajax_request()){
			// start
			@ob_end_clean();
			// to restart output buffering: issue#577	
			if(!ini_get('output_buffering')){			
				@ob_start();		
			}
			// header
			$this->send_header();	
			// return		
			return true;
		}
	}
	
	// end output
	function end_output(){
		// is ajax
		if($this->is_ajax_request()){
			// flush
			@ob_end_flush();
			// exut
			exit();
		}
	}	
}
// core/libs/utilities/mgm_ajax.php