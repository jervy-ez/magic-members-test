<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * Free (Virtual) Payment Module
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */
class mgm_free extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_free();
	}
	
	// construct
	function mgm_free(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Free';
		// logo
		$this->logo = $this->module_url( 'assets/free.gif' );
		// virtual payment		
		$this->virtual_payment = 'Y';	
		// description
		$this->description = __('Allow users to signup for a free account, this Payment Gateway must '.
			                    'be enabled for Pay Per Post to work along with Paypal Gateway.', 'mgm');
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
				// $this->status      = $_POST['status'];
				// logo if uploaded
				if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
					$this->logo = $_POST['logo_new_'.$this->code];
				}				
				// save
				$this->save();
				// message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));
			break;
		}		
	}
	
	// return process api hook
	function process_return() {	
		// log
		// mgm_log('process_return free REQUEST : '.print_r($_REQUEST,true));
		//mgm_pr($_REQUEST); die;
		// only save once success, there may be multiple try
		if(isset($_REQUEST['custom']) && !empty($_REQUEST['custom'])){
			// id
			$transid = mgm_decode_id( mgm_get_var('transid', '', true) );
			// process 
			$this->process_notify($transid);
			// query arg
			$query_arg = array('status'=>'success');
			// is a post redirect?
			$post_redirect = $this->_get_post_redirect($_REQUEST['custom']);
			// set post redirect
			if($post_redirect !== false){
				$query_arg['post_redirect'] = $post_redirect;
			}			
			// log 
			mgm_log( $query_arg, __FUNCTION__);
			// login autoredirection			
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
			// log 
			mgm_log( $query_arg, __FUNCTION__);
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
			$trans_ref = is_numeric($transid) ? $transid : $_REQUEST['custom'];
			$payment_type = $this->_get_payment_type($trans_ref);
			// check
			switch($payment_type){				
				// buypost
				case 'post_purchase': 
				case 'buypost':
					$this->_buy_post(); //run the code to process a purchased post/page	
				break;	
				case 'subscription':
				default:					
					$this->_buy_membership($transid); //run the code to process a new/extended membership						
				break;												
			}			
		}
	}
	
	// process cancel api hook: 
	function process_cancel(){		
		// redirect to cancel page	
		mgm_redirect(add_query_arg(array('status'=>'cancel'), $this->_get_thankyou_url()));	
	}
	
	// buy post: if cost == 0
	function _buy_post() {
		global $wpdb;
		// system
		$system_obj = mgm_get_class('system');
		$dge    = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne   = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($_REQUEST['custom']);
		// local var
		extract($custom);
		
		// find user
		if($user_id) $user = get_userdata($user_id);
		// blog
		$blogname = get_option('blogname');

		//getting purchase post title and & price - issue #981				
		$post_obj = mgm_get_post($post_id);
		$purchase_cost     = mgm_convert_to_currency($post_obj->purchase_cost);
		$post    = get_post($post_id);
		$post_title   = $post->post_title;		
		
		// tran
		$tran_success = false;		
		// status success
		$tran_success = true;
		// status
		$status_str = __('Last payment was successful','mgm');
		// emails not for guest
		if($user_id){
			// subject
			$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
			// body
			$message = $system_obj->get_template('payment_success_email_template_body', 
									array('blogname'=>$blogname, 'name'=>$user->display_name,
										  'post_title'=>$post_title,'purchase_cost'=>$purchase_cost,  
										  'email'=>$user->user_email, 
										  'admin_email'=>$system_obj->setting['admin_email']), true);
			//update coupon usage - issue #1421
			do_action('mgm_update_coupon_usage', array('user_id' => $user_id));
										  
		}		
		// transation id
		$transaction_id = $this->_get_transaction_id('custom', $_REQUEST);
		// hook args
		$args = array('post_id'=>$post_id, 'transaction_id'=>$transaction_id);
		// user purchase
		if(isset($user_id) && (int)$user_id > 0){
			$args['user_id'] = $user_id;
		}else{
		// guest purchase	
			$args['guest_token'] = $guest_token;
		}					  
		// after succesful payment hook
		do_action('mgm_buy_post_transaction_success', $args);// backward compatibility
		do_action('mgm_post_purchase_payment_success', $args);// new organized name
		
		// do action
		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new, individual
		do_action('mgm_return_post_purchase_payment', array('post_id' => $post_id));// new, global 
		
		// notify user		
		if(!$dpne) {
			// mail
			if($user_id && $this->send_payment_email($_REQUEST['custom'])) {	
				//issue #862
				$subject = mgm_replace_email_tags($subject,$user_id);
				$message = mgm_replace_email_tags($message,$user_id);
				
				// mail			
				mgm_mail($user->user_email, $subject, $message); //send an email to the buyer
				//update as email sent 
				$this->update_paymentemail_sent($_REQUEST['custom']);	
			}
		}		
		
		// mark as purchased
		if(isset($guest_token)){
			// issue #1421
			if(isset($coupon_id) && isset($coupon_code)) {
				do_action('mgm_update_coupon_usage', array('guest_token' => $guest_token,'coupon_id' => $coupon_id));
				$this->_set_purchased(NULL, $post_id, $guest_token, $_REQUEST['custom'],$coupon_code);
			}else {
				$this->_set_purchased(NULL, $post_id, $guest_token, $_REQUEST['custom']);				
			}
		}else{
			$this->_set_purchased($user_id, $post_id, NULL, $_REQUEST['custom']);
		}
		// status
		$status = __('The post was purchased successfully', 'mgm');
		// transaction status
		mgm_update_transaction_status($_REQUEST['custom'], $status, $status_str);		
		// notify admin, only if gateway emails on 
		if (!$dge) {
			// not for guest
			if($user_id){
				// subject
				$subject = "[" . $blogname . "] Admin Notification: " . $user->user_email . " purchased post " . $post_id;
				// message
				$message = "User display name: {$user->display_name}<br />
							User email: {$user->user_email}<br />
							User ID: {$user->ID}<br />Status: " . $status . "<br />
							Action: Purchase post:" . $subject . "<br /><br />" . 
							$message;
			}else{
				$subject = "[" . $blogname . "] Admin Notification: Guest[IP: ".mgm_get_client_ip_address()."] purchased post " . $post_id;
				$message = "Guest Purchase";
			}			
			// mail
			mgm_mail($system_obj->setting['admin_email'], $subject, $message);
		}
	}
	
	// buy membership
	function _buy_membership($transid = null) {	
		// system	
		$system_obj  = mgm_get_class('system');
		$s_packs = mgm_get_class('subscription_packs');
		
		// get details	
		//if transaction id is available:
		if(is_numeric($transid)) {	
			$custom = $this->_get_transaction_passthrough($transid);
			extract($custom);		
			// mgm_log($custom);	
		}else {
			//Purchase Another Membership Level problem : issue #: 752				
			$is_another_membership_purchase = 'N';
			$parts = explode('_', $_REQUEST['custom']);
			$params = array('user_id'=>0, 'duration'=>'', 'duration_type'=>'', 'pack_id'=>0, 
				            'is_another_membership_purchase'=>'N', 'membership_type'=>'') ;
			$i = 0;
			foreach( $params as $param => $default ){
				if(isset( $parts[$i] )){
					${$param} = $parts[$i];
				}else{
					${$param} = $default;
				}

				$i++;
			}			
		}		
		
		// get pack
		$pack = $s_packs->get_pack($pack_id);		
		// membership_type -issue #1005
		if(empty($membership_type))
			$membership_type = $pack['membership_type'];	
		// user			
		if ($user = get_userdata($user_id)) {
			//Purchase Another Membership Level problem : issue #: 752				
			if($is_another_membership_purchase =='Y'){
				// another_subscription modification 
				//issue #1073
				$member = mgm_get_member_another_purchase($user_id, $membership_type);
			}else {
				// get member
				$member = mgm_get_member($user_id);
			}
			//pack currency over rides genral setting currency - issue #1602
			if(isset($pack['currency']) && !empty($pack['currency'])){
				$currency =$pack['currency'];
			}else {
				$currency =$system_obj->setting['currency'];
			}
			// check
			//uncommented the below line as it is not updating in upgrade subscription
			//if (!$member->duration) {
				$member->duration        = (($duration) ? $duration : 1); // one year
				$member->duration_type   = (($duration_type) ? $duration_type : 'y');
				$member->amount          = 0.00;
				//$member->currency        = 'USD';//not sure y hardcoded
				$member->currency        = $currency;
				$member->membership_type = $membership_type;
			//}	
			// set pack	
			$member->pack_id       = $pack_id;	
			$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $pack['num_cycles']; 
			// set status
			$member->status        = MGM_STATUS_ACTIVE;
			$member->account_desc  = __('Free Account','mgm');				
			$member->last_pay_date = '';		
			
			//reset payment_info if already set:
			if(isset($member->payment_info))
				unset($member->payment_info);
			//unset rebill:	
			if(isset($member->rebilled))
				unset($member->rebilled);
			//unset transaction_id:	
			if(isset($member->transaction_id))
				unset($member->transaction_id);			
			
			// join date			
			$time = time();
			// set
			if(!isset($member->join_date) || (isset($member->join_date) && empty($member->join_date))) 
				$member->join_date = $time;		

			// old content hide - issue #1227
			if(isset($hide_old_content))
				$member->hide_old_content = $hide_old_content; 
						
			// type expanded
			$duration_exprs = $s_packs->get_duration_exprs();
			// if not lifetime/date range
			if(in_array($member->duration_type, array_keys($duration_exprs))) {// take only date exprs
				// @TODO, time should be last expire date #773, 3 use cases must be tracked
				// expect new param in tran subscription_type: new, upgrade, downgrade, extend
				$expire_date_ts = (!$member->expire_date) ? $time : strtotime($member->expire_date);
				// time
				$expire_date_ts = strtotime("+{$member->duration} {$duration_exprs[$member->duration_type]}", $expire_date_ts);							
				// formatted
				$expire_date = date('Y-m-d', $expire_date_ts);				
				// date extended				
				if (!$member->expire_date || $expire_date_ts > strtotime($member->expire_date)) {
					$member->expire_date = $expire_date;										
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
			
			//Purchase Another Membership Level problem : issue #: 752				
			if( bool_from_yn($is_another_membership_purchase) ){
				$custom = array('is_another_membership_purchase'=>'Y');
			}
		
			// old status
			$old_status = $member->status;				
			// set new status
			$member->status = $new_status = MGM_STATUS_ACTIVE;
						
			// whether to subscriber the user to Autoresponder - This should happen only once
			//issue #1073
			if(!empty($transid) && $transid!=null ) {
				$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $transid);
			} else {
				$acknowledge_ar = mgm_subscribe_to_autoresponder($member, null);
			}
			
			// update
			if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {							
				// get object - issue #1227
				$obj_sp = mgm_get_class('subscription_packs')->get($member->pack_id);							
				if($obj_sp['hide_old_content'])
					$member->hide_old_content = $obj_sp['hide_old_content'];
				
				mgm_save_another_membership_fields($member, $user_id);	
				// Multiple membership upgrade: first time
				if ($transid && isset($custom['multiple_upgrade_prev_packid']) && is_numeric($custom['multiple_upgrade_prev_packid'])) {
					mgm_multiple_upgrade_save_memberobject($custom, $transid);	
				}						
			}else {
				// update
				$member->save();
			}
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
			// status change event
			do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);	
			
			//update coupon usage
			do_action('mgm_update_coupon_usage', array('user_id' => $user_id));
	
			// role
			if(isset($role)){					
				$obj_role = new mgm_roles();				
				$obj_role->add_user_role($user_id, $role);
			}
			
			// update pack/transaction
			if(is_numeric($transid))
				mgm_update_transaction(array('module'=>$this->module, 'status_text' => __('Success','mgm')), $transid);
			
			// return action
			do_action('mgm_return_'.$this->module, array('user_id' => $user_id));// backward compatibility
			do_action('mgm_return_subscription_payment_'.$this->module, array('user_id' => $user_id));// new , individual	
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