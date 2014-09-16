<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members sidebar widget class
 * extends object to save options to database
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_sidebar_widget extends mgm_object{
	// var
	var $default_text    = array();
	var $login_widget    = array();
	var $register_widget = array();
	var $status_widget   = array();
	var $text_widget     = array();	
	
	// construct
	function __construct(){
		// php4
		$this->mgm_sidebar_widget();
	}
	
	// construct
	function mgm_sidebar_widget(){
		// parent
		parent::__construct(); 
		// defaults
		$this->_set_defaults();
		// read vars from db
		$this->read();// read and sync	
	}
	
	// defaults
	function _set_defaults(){
		// code
		$this->code        = __CLASS__;
		// name
		$this->name        = 'Sidebar Widget Lib';
		// description
		$this->description = 'Sidebar Widget Lib';
		
		// default texts
		$this->default_texts();
	}
	
	// default texts
	function default_texts(){
		// inactive_intro
		$this->default_text['inactive_intro'] = '<p>You can subscribe to this blog using the buttons below.</p><p>You will be taken to a payment gateway and then returned to the site as a subscribed member.</p><p style="font-weight:bold;">Choose From:</p>';		
		
		// active_intro
		$this->default_text['active_intro'] = '<p>You are a subscribed member.</p><div>Subscription Level: [membership_type]</div><div style="margin-bottom: 5px;">Expiry Date: [expiry_date]</div>';
		
		// logged_out_intro		
		$this->default_text['logged_out_intro'] =	'<p>You need to be logged in to be able to subscribe to this blog or purchase any of its posts.</p><p>Use the link below to login or register.</p>';
		
		// logged_in_template		
		$this->default_text['logged_in_template'] = '<p>Welcome [display_name]</p><ul><li>[membership_details_link]</li><li>[logout_link]</li></ul>';
	}
	
	// fix
	function apply_fix($old_obj){
		// to be copied vars
		$vars = array('default_text','login_widget','register_widget','status_widget','text_widget');
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
		$vars = array('default_text','login_widget','register_widget','status_widget','text_widget');
		// set
		foreach($vars as $var){
			// var
			$this->options[$var] = $this->{$var};
		}	
	}
}
// core/libs/classes/mgm_sidebar_widget.php