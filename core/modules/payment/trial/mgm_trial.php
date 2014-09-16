<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * Trial (Virtual) Payment Module
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */ 
class mgm_trial extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_trial();
	}
	
	// construct
	function mgm_trial(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Trial';
		// logo
		$this->logo = $this->module_url( 'assets/trial.gif' );
		// virtual payment		
		$this->virtual_payment = 'Y';	
		// description
		$this->description = __('Allow users to signup for a free trial account which expires automatically after '.
								'a number of days. You can set the number of days/months/years before a trial account '.
								'expires on the Subscription Settings page. Simply set the Account '.
								'Type to "Trial" to use this gateway.', 'mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription');
		// default settings
		$this->_default_setting();
		// set path
		parent::set_tmpl_path();
		// read settings
		$this->read();	
	}
	
	// settings
	function settings(){
		global $wpdb;
		// data
		$data = array();		
		// set 
		$data['module'] = $this;	
		// load template view
		$this->load->template('settings', array('data'=>$data));
	}	
	
	// settings_box
	function settings_box(){
		global $wpdb;
		// data
		$data = array();	
		// set 
		$data['module'] = $this;	
		// load template view
		return $this->load->template('settings_box', array('data'=>$data), true);
	}
	
	// update
	function settings_update(){
		// form type 
		switch($_POST['setting_form']){
			case 'box':
			// from box	
				switch($_POST['act']){
					case 'logo_update':
						// logo if uploaded
						if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
							$this->logo = $_POST['logo_new_'.$this->code];
							// save
							$this->save();
						}
						// message
						$message = sprintf(__('%s logo updated', 'mgm'), $this->name);			
						$extra   = array();
					break;
					case 'status_update':
					default:
						// enable
						$enable_state = (isset($_POST['payment']) && $_POST['payment']['enable'] == 'Y') ? 'Y' : 'N';
						// enable
						if( bool_from_yn($enable_state) ){
							$this->install();
							$stat = ' enabled.';
						}else{
						// disable
							$this->uninstall();	
							$stat = ' disabled.';
						}	
						
						# message
						$message = sprintf(__('%s module has been %s', 'mgm'), $this->name, $stat);							
						$extra   = array('enable' => $enable_state);		
					break;
				}							
				// print message
				echo json_encode(array_merge(array('status'=>'success','message'=>$message,'module'=>array('name'=>$this->name,'code'=>$this->code,'tab'=>$this->settings_tab)), $extra));
			break;
			case 'main':
			default:
			// from main						
				// setup callback messages				
				$this->_setup_callback_messages($_POST['setting']);
				// re setup callback urls
				$this->_setup_callback_urls($_POST['setting']);
				// common
				$this->description = $_POST['description'];
				// $this->status   = $_POST['status'];
				// logo if uploaded
				if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
					$this->logo = $_POST['logo_new_'.$this->code];
				}				
				// save
				$this->save();
				// message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'),$this->name)));
			break;
		}		
	}
	
	// return process api hook
	function process_return() {
		// only save once success, there may be multiple try
		if(isset($_REQUEST['custom']) && !empty($_REQUEST['custom'])){
			// process 
			$transid = mgm_get_var('transid', '', true);
			$transid = mgm_decode_id($transid);
			$this->process_notify($transid);
			// query arg
			$query_arg = array('status'=>'success');
			// is a post redirect?
			$post_redirect =$this->_get_post_redirect($_REQUEST['custom']);
			// set post redirect
			if($post_redirect !==false){
				$query_arg['post_redirect'] = $post_redirect;
			}		
			
			//autoredirection to profile page			
			if(is_numeric($transid)) {
				// update transaction
				mgm_update_transaction_status($transid, MGM_STATUS_ACTIVE, '');
				// is a register redirect?
				$register_redirect = $this->_auto_login($transid);				
				// set register redirect
				if($register_redirect !== false){
					$query_arg['register_redirect'] = $register_redirect;
				}
			}
			// redirect
			mgm_redirect(add_query_arg($query_arg, $this->_get_thankyou_url()));			
		}else{
			// teat as error			
			$errors = 'error in processing your request'; 	
			// redirect			
			mgm_redirect(add_query_arg(array('status'=>'error','errors'=>$errors), $this->_get_thankyou_url()));
		}			
	}
	
	// notify process api hook, background IPN
	function process_notify($transid) {
		// check			
		if (isset($_REQUEST['custom'])){
			// payment type
			$payment_type = $this->_get_payment_type($_REQUEST['custom']);
			// check
			switch($payment_type){				
				// subscription	
				case 'subscription':	
				default:				
					$this->_buy_membership($transid); //run the code to process a new/extended membership						
				break;											
			}			
		}
	}
	
	// process cancel api hook: 
	function process_cancel(){
		// not used for this module
		mgm_redirect(add_query_arg(array('status'=>'cancel'), $this->_get_thankyou_url()));	
	}	
	
	// buy membership
	function _buy_membership($transid = null) {
		// packs
		$s_packs = mgm_get_class('subscription_packs');
		// get details
		//if transaction id is available:
		if(is_numeric($transid)) {				
			$custom = $this->_get_transaction_passthrough($transid);
			extract($custom);			
		}else{	
			list($user_id, $duration, $duration_type, $pack_id) = explode('_', $_REQUEST['custom']);
			//mgm_log($_REQUEST['custom']);
		}	
		// get pack
		$pack = $s_packs->get_pack($pack_id);
		// membership_type
		$membership_type = $pack['membership_type'];	
		// check
		if ($user = get_userdata($user_id)) {
			// get member
			$member = mgm_get_member($user_id);
			// check
			if (!$member->duration) {
				$member->duration        = (($duration) ? $duration : 1); // one year
				$member->duration_type   = (($duration_type) ? $duration_type : 'y');
				$member->amount          = 0.00;
				$member->currency        = (isset($pack['currency']) && !empty($pack['currency']))?$pack['currency'] : 'USD';
				$member->membership_type = $membership_type;
			}
			// set pack	
			$member->pack_id       = $pack_id;	
			$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 			
			// set status
			$member->status        = MGM_STATUS_ACTIVE;
			$member->account_desc  = __('Trial Account','mgm');				
			$member->last_pay_date = '';
			
			// join date
			$time = time();	
			// type expanded
			$duration_exprs = $s_packs->get_duration_exprs();
			// if not lifetime/date range
			if(in_array($member->duration_type, array_keys($duration_exprs))) {// take only date exprs
				// time
				$time = strtotime("+{$member->duration} {$duration_exprs[$member->duration_type]}", $time);							
				// formatted
				$time_str = date('Y-m-d', $time);				
				// date extended				
				if (!$member->expire_date || strtotime($time_str) > strtotime($member->expire_date)) {
					$member->expire_date = $time_str;										
				}		
			}else{
				//if lifetime:
				if($member->duration_type == 'l'){// el = lifetime
					$member->expire_date = '';
				}
				//issue #1096
				if($member->duration_type == 'dr'){// el = /date range
					$member->expire_date = $duration_range_end_dt;
				}												
					
			}	
						
			// whether to subscriber the user to Autoresponder - This should happen only once
			$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $transid);
			// update
			$member->save();
			// on status - issue #1468
			switch ($member->status) {
				case MGM_STATUS_ACTIVE:		
					//sending notification email to user
					if($notify_user && $is_registration =='Y'){
						$user_pass = mgm_decrypt_password($member->user_password, $user_id);
						do_action('mgm_register_user_notification', $user_id, $user_pass);
					}			
				break;
			}			
			// role
			if(isset($role)){					
				$obj_role = new mgm_roles();				
				$obj_role->add_user_role($user_id, $role);
			}
			
			// update pack/transaction
			if(is_numeric($transid))
				mgm_update_transaction(array('module'=>$this->module, 'status_text' => __('Success','mgm') ), $transid);
			
			// return action
			do_action('mgm_return_'.$this->module, array('user_id' => $user_id));	
			//issue#: 343
			do_action('mgm_unpaid_autoresponder', array('user_id' => $user_id));// autoresponder
			do_action('mgm_return_subscription_payment_'.$this->module, array('user_id' => $user_id));// 
			//issue#: 666
			do_action('mgm_return_subscription_payment', array('user_id' => $user_id, 'acknowledge_ar' => $acknowledge_ar, 'mgm_member' => $member));// new, global: pass mgm_member object to consider multiple level purchases as well. 	
		}				
	}
	
	// default setting
	function _default_setting(){				
		// callback messages				
		$this->_setup_callback_messages();
		// callback urls
		$this->_setup_callback_urls();	
	}
}

// end file