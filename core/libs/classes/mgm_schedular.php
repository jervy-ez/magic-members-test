<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members schedular class
 * extends object to save options to database
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_schedular extends mgm_object{
	// var
	var $events    = array();
	var $schedules = array();
	
	// construct
	function __construct(){
		// php4
		$this->mgm_schedular();
	}
	
	// construct
	function mgm_schedular(){
		// parent
		parent::__construct(); 
		// defaults
		$this->_set_defaults();			
		// read vars from db
		$this->read();// read and sync		
	}
	
	/**
	 * defaults
	 */
	function _set_defaults(){
		// code
		$this->code        = __CLASS__;
		// name
		$this->name        = 'Schedular Lib';
		// description
		$this->description = 'Schedular Lib';
		// init
		$this->events = array();
		// schedules
		$this->schedules = array( 'hourly' => array(), 'every2ndhourly'=>array(), 
		                          'daily' => array(), 'twicedaily' => array() );
		/* 
		'twicedaily' => array(),'every5minute' => array(), 'every10minute' => array(), 
		'every15minute' => array(), 'every30minute' => array()
		*/
	}
	
	/**
	 * get defined schedules
	 */
	function get_defined_schedules(){
	 	// wp event name => mgm event name
		return array('hourly' => 'mgm_hourly_schedule', 'every2ndhourly' => 'mgm_every2ndhourly_schedule', 
		             'daily' => 'mgm_daily_schedule', 'twicedaily' => 'mgm_twicedaily_schedule' );
		/* 
		'every5minute' => 'mgm_every5minute_schedule','every10minute' => 'mgm_every10minute_schedule', 
		'every15minute' => 'mgm_every15minute_schedule', 'every30minute' => 'mgm_every30minute_schedule'
		*/
	}
	
	/**
	 * run all 
	 */
	function run($recurrence='daily'){						
		// loop
		foreach($this->schedules[$recurrence] as $callback){
			// event_callback
			$event_callback = $recurrence . '_' . $callback; // daily_[callback]				
			// trigger
			if(method_exists($this, $event_callback)){ 										
				// run
				call_user_func(array($this, $event_callback));
				// log executed
				$current_date = mgm_get_current_datetime('Y-m-d H:i:s');// with time part #1023 issue
				// last run set in events
				$this->events[$recurrence][$callback] = $current_date['timestamp'];						
			}		
		}		
		// update option 
		$this->save();
	}
	
	/**
	 * add schedule
	 */
	function add_schedule($recurrence='daily', $callback){			
		// set array if not set
		if(!isset($this->schedules[$recurrence]) || (isset($this->schedules[$recurrence]) && !is_array($this->schedules[$recurrence]))) {			
			$this->schedules[$recurrence] = array();
		}
			
		// push
		if(!in_array($callback, $this->schedules[$recurrence])) {			
			array_push($this->schedules[$recurrence], $callback);
		}
	}
	
	/**
	 * EVENT CALLBACKS 
	 */
	 
	/** 
	 * reminder mail (runs daily)
	 * as per setting, sends out account expiring email to member
	 *
	 */
	function daily_reminder_mailer(){				
		mgm_check_expiring_memberships();	
	}
	
	/**
	 * get response confirmation users mail to campaign admin
	 * 
	 * @deprecated
	 */
	function daily_getresponse_confirmed_users() {
		// send
		mgm_get_module('getresponse', 'autoresponder')->send_confirmation();
	}

	/**
	 * Run rebill status check (runs twicedaily)
	 * 
	 */
	function twicedaily_rebill_status_check(){
		mgm_check_membership_rebill_status();
	}
	
	/**
	 * check ongoing memberships and extend or expire (runs hourly)
	 *
	 */
	function hourly_ongoing_membership_extend(){		
		mgm_check_ongoing_memberships();
	}
	
	/**
	 * Check and update dataplus transactions (runs hourly)
	 *
	 */
	function hourly_epoch_dataplus_transactions() {
		mgm_epoch_update_dataplus_transactions();	
	}

	/**
	 * Calculate and update widget data (runs hourly)
	 * 
	 * @deprecated, overuse of cron, utilizing wp transient cache
	 */
	function hourly_update_widget_data() {
		// mgm_update_dashboard_widget_data();
	}
	
	/**
     * update  missing transaction date for Authorize.Net and other module
	 */
	function hourly_update_transaction_data(){
		mgm_update_transaction_data();
	}

	/**
	 * check limited memberships and extend or expire (runs every 2nd hourly)
	 *
	 */
	function every2ndhourly_limited_membership_extend(){		
		mgm_check_limited_memberships();
	}
	
	/**
	 * private helpers 
	 */
		
	/** 
	 * fix object data
	 */
	function apply_fix($old_obj){
		// to be copied vars
		$vars = array('events','schedules');
		// set
		foreach($vars as $var){
			// var
			$this->{$var} = (isset( $old_obj->{$var} ) ) ? $old_obj->{$var} : '';
		}				
		// save
		$this->save();	
	}
	
	/**
	 * prepare save, define the object vars to be saved
	 * internally called by object->save()
	 */
	function _prepare(){		
		// init array
		$this->options = array();
		// to be saved vars
		$vars = array('events','schedules');
		// set
		foreach($vars as $var){
			// var
			$this->options[$var] = $this->{$var};
		}	
	}
}
// core/libs/classes/mgm_schedular.php