<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * ManualPay (Virtual) Payment Module
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */
class mgm_manualpay extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_manualpay();
	}
	
	// construct
	function mgm_manualpay(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Manual Pay / eCheck';
		// logo
		$this->logo = $this->module_url( 'assets/manual.jpg' );	
		// virtual payment		
		$this->virtual_payment = 'Y';		
		// description
		$this->description = __('Allow users to signup and pay via eCheck/Wire Transfer. ' .
			                    'Registration will be active once payment is complete.', 'mgm');
		// supported buttons types
		$this->supported_buttons = array('subscription');
		// trial support available ?
		$this->supports_trial= 'Y';		
		// cancellation support available ?
		$this->supports_cancellation= 'Y';		
		// do we depend on product mapping	
		$this->requires_product_mapping = 'N';
		// type of integration
		$this->hosted_payment = 'Y';// html redirect
		// supported buttons types
	 	$this->supported_buttons = array('subscription');
		// default settings
		$this->_default_setting();
		// set path
		parent::set_tmpl_path();
		// read settings
		$this->read();	
	}
	
	// MODULE API COMMON HOOKABLE CALLBACKS  //////////////////////////////////////////////////////////////////
	
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
	
	// return process api hook, link back to site after payment is made
	function process_return() {				
		// only save once success, there may be multiple try
		if(isset($_POST['custom']) && !empty($_POST['custom'])){
			// process 
			$this->process_notify();
			// query arg
			//trans_ref: mgm trans reference
			$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($_POST['custom']));
			// is a post redirect?
			$post_redirect =$this->_get_post_redirect($_POST['custom']);
			// set post redirect
			if($post_redirect !==false){
				$query_arg['post_redirect'] = $post_redirect;
			}
			// is a register redirect?
			$register_redirect = $this->_auto_login($_POST['custom']);
			// set register redirect
			if($register_redirect !== false){
				$query_arg['register_redirect'] = $register_redirect;
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
	
	// notify process api hook, background IPN url
	function process_notify() {		
		// verify 
		if($this->_verify_callback()){	
			// log data before validate
			$tran_id = $this->_log_transaction();	
			// payment type
			$payment_type = $this->_get_payment_type($_POST['custom']);
			// custom
			$custom = $this->_get_transaction_passthrough($_POST['custom']);
			// hook for pre process
			do_action('mgm_notify_pre_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));
			// check
			switch($payment_type){				
				// subscription	
				case 'subscription':					
					$this->_buy_membership(); //run the code to process a new/extended membership						
				break;	
				// other		
				default:									
					// error									
				break;							
			}	
			// after process		
			do_action('mgm_notify_post_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));			
		}
		// after process unverified		
		do_action('mgm_notify_post_process_unverified_'.$this->module);	
	}	
	
	// process cancel api hook 
	function process_cancel(){
		// redirect to cancel page
		mgm_redirect(add_query_arg(array('status'=>'cancel'), $this->_get_thankyou_url()));
	}	
	
	// unsubscribe process, IPN for unsubscribe 
	function process_unsubscribe(){
		// overwrite this		
	}
	
	// process html_redirect, proxy  for cc processing
	//no longer using: will be bypassed to process_return
	function process_html_redirect(){	
		// read tran id
		if(!$tran_id = $this->_read_transaction_id()){		
			return __('Transaction Id invalid','mgm');
		}	
		
		// get trans
		if(!$tran = mgm_get_transaction($tran_id)){
			return __('Transaction invalid','mgm');
		}		
		
		// update pack/transaction: this is to confirm the module code if it is different
		mgm_update_transaction(array('module'=>$this->module), $tran_id);		
		// Check user id is set if subscription_purchase. issue #1049
		if ($tran['payment_type'] == 'subscription_purchase' && 
			(!isset($tran['data']['user_id']) || (isset($tran['data']['user_id']) && (int) $tran['data']['user_id']  < 1))) {
			return __('Transaction invalid . User id field is empty','mgm');		
		}
		// generate
		$button_code = $this->_get_button_code($tran['data'],$tran_id);
		// extra code
		$additional_code = do_action('mgm_additional_code');
		// the html
		$html='<form action="'. $this->_get_endpoint('return') .'" method="post" class="mgm_form" name="' . $this->code . '_redirect_form" id="' . $this->code . '_redirect_form">
					'. $button_code .'					
					'. $additional_code .'						
					<img src="'.MGM_ASSETS_URL.'images/ajax/ajax-loader.gif"/><br>
					<b>'.__('Please wait, your transaction is being processed...','mgm').'...</b>				
			  </form>				
			  <script language="javascript">document.' . $this->code . '_redirect_form.submit();</script>';
		// return 	  
		return $html;		
	}
		
	// subscribe button api hook
	/*	
	function get_button_subscribe($options=array()){
		$include_permalink = (isset($options['widget'])) ? false : true;
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect',$include_permalink) .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					<input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
					<input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">	
					<div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>				
				</form>';
		// return			
		return $html;		
	}
	*/	
	// subscribe button api hook
	//get_button_subscribe function has been modified as below to bypass process_html_redirect.
	//So the request will be immediately processes upon clicking: manual pay button/image(issue#:514)	
	function get_button_subscribe($options=array()){
		// perm link
		$include_permalink = (isset($options['widget'])) ? false : true;
		
		// get trans id
		$tran_id = (int)$options['tran_id'];	
		
		// get trans
		if(!$tran = mgm_get_transaction($tran_id)){
			return __('Transaction invalid','mgm');
		}		
			
		// generate
		$button_code = $this->_get_button_code($tran['data'],$tran_id);
		// extra code
		$additional_code = do_action('mgm_additional_code');
		
		// get html
		//modified the form to bypass process_html_redirect(issue#:514)
		//$html='<form action="'. $this->_get_endpoint('html_redirect',$include_permalink) .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
		$html='<form action="'. $this->_get_endpoint('return') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					'. $button_code .'					
					'. $additional_code .'						
					<input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">	
					<div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>				
				</form>';
		
		// return			
		return $html;		
	}
	
	// MODULE API COMMON PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// get button code	
	function _get_button_code($pack, $tran_id=NULL) {
		// get data
		$data   = $this->_get_button_data($pack, $tran_id);		
		// strip 
		$data = mgm_stripslashes_deep($data);
		// init
		$return = '';	
		// create return
		foreach ($data as $key => $value) {
			$return .= '<input type="hidden" name="'. $key .'" value="'. esc_html($value) .'" />' . "\n";
		}	
		// return
		return $return;
	}

	// get button data
	function _get_button_data($pack, $tran_id=NULL) {
		// system setting
		$system_obj  = mgm_get_class('system');	
		$user_id = $pack['user_id'];	
		$user    = get_userdata($user_id); 	
		//pack currency over rides genral setting currency - issue #1602
		if(!isset($pack['currency']) || empty($pack['currency'])){
			$pack['currency']=$this->setting['currency'];
		}		
		// setup data array		
		$data = array(			
			'ip'       => $_SERVER["REMOTE_ADDR"],
			'currency' => $pack['currency'],			
			'email'    => $user->user_email,
		);		
		
		// custom passthrough
		$data['custom'] = $tran_id;
		
		// add filter @todo test
		$data = apply_filters('mgm_payment_button_data', $data, $tran_id, $this->module, $pack);
		
		// update pack/transaction
		mgm_update_transaction(array('data'=>json_encode($pack),'module'=>$this->module), $tran_id);
		
		// return data
		return $data;
	}		
	
	// buy membership
	function _buy_membership() {		
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($_POST['custom']);
		// local var
		extract($custom);	
		
		// currency
		if (!$currency) $currency = $system_obj->get_setting('currency');
		
		// find user
		$user = get_userdata($user_id);
		//another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
			$member = mgm_get_member_another_purchase($user_id, $custom['membership_type']);			
		}else {
			$member = mgm_get_member($user_id);			
		}
		
		// Get the current AC join date
		if (!$join_date = $member->join_date) $member->join_date = time(); // Set current AC join date		

		// if there is no duration set in the user object then run the following code
		if (empty($duration_type)) {
			//if there is no duration type then use Months
			$duration_type = 'm';
		}
		// membership type default
		if (empty($membership_type)) {
			//if there is no account type in the custom string then use the existing type
			$membership_type = md5($member->membership_type);
		}
		// validate parent method
		$membership_type_verified = $this->_validate_membership_type($membership_type, 'md5|plain');
		// verified
		if (!$membership_type_verified) {
			if (strtolower($member->membership_type) != 'free') {
				// notify admin, only if gateway emails on
				if( ! $dge ) mgm_notify_admin_membership_verification_failed( $this->name );				
				// abort
				return;
			} else {
				$membership_type_verified = $member->membership_type;
			}
		}
		// set
		$membership_type = $membership_type_verified;
		// sub pack
		$subs_pack = $s_packs->get_pack($pack_id);
		// if trial on		
		if ($subs_pack['trial_on']) {
			$member->trial_on            = $subs_pack['trial_on'];
			$member->trial_cost          = $subs_pack['trial_cost'];
			$member->trial_duration      = $subs_pack['trial_duration'];
			$member->trial_duration_type = $subs_pack['trial_duration_type'];
			$member->trial_num_cycles    = $subs_pack['trial_num_cycles'];
		}
		//pack currency over rides genral setting currency - issue #1602
		if(isset($subs_pack['currency']) && $subs_pack['currency'] != $currency){
			$currency =$subs_pack['currency'];
		}		
		// duration
		$member->duration        = $duration;
		$member->duration_type   = strtolower($duration_type);
		$member->amount          = $amount;
		$member->currency        = $currency;
		$member->membership_type = $membership_type;	
		$member->pack_id         = $pack_id;	
		// $member->payment_type = 'subscription';
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		// payment info for unsubscribe		
		$member->payment_info    = new stdClass;
		$member->payment_info->module = $this->code;		
		// transaction type
		$member->payment_info->txn_type = 'subscription';
		// subscriber id
		$member->payment_info->subscr_id = '';	
		// refer id
		$member->payment_info->txn_id = '';
		
		// mgm transaction id
		$member->transaction_id = $_POST['custom'];
		
		// process response
		$new_status = $update_role = false;
		// process
		$process = 'Success';
		// status
		switch ($process) {
			case 'Success':		
				// set pending status				
				$new_status = MGM_STATUS_PENDING;	
							
				// starus string
				$member->status_str = __('Last payment is pending','mgm');					
				
				$time = time();
				$last_pay_date = isset($member->last_pay_date) ? $member->last_pay_date : null;			
				$member->last_pay_date = date('Y-m-d', $time);		
				
				// for upgrading
				if($member->status == MGM_STATUS_ACTIVE){
					// mark for later status reset 
					$member->status_reset_on = date('Y-m-d',strtotime($member->expire_date));
				}		
						// check subscription_option
				if(isset($subscription_option)){
					// on option
					switch($subscription_option){
						// @ToDo, apply expire date login
						case 'create':
						// expire date will be based on current time					
						case 'upgrade':
						// expire date will be based on current time
							// already on top
						break;
						case 'downgrade':
						// expire date will be based on expire_date if exists, current time other wise					
						case 'extend':
							// expire date will be based on expire_date if exists, current time other wise
							// extend/expire date
							// calc expiry	- issue #1226
							// membership extend functionality broken if we try to extend the same day so removed && $last_pay_date != date('Y-m-d', $time) check	
							if (!empty($member->expire_date) ) {
								$expiry = strtotime($member->expire_date);
								if ($expiry > 0 && $expiry > $time) {
									$time = $expiry;
								}
							}
						break;
					}
				}
				
				// type expanded
				$duration_exprs = $s_packs->get_duration_exprs();
				// if not lifetime/date range
				if(in_array($member->duration_type, array_keys($duration_exprs))) {// take only date exprs
					//consider trial duration if trial period is applicable
					if(isset($trial_on) && $trial_on == 1 ) {
						//Do it only once
						if(!isset($member->rebilled) && isset($member->active_num_cycles) && $member->active_num_cycles != 1 ) {							
							$time = strtotime("+{$trial_duration} {$duration_exprs[$trial_duration_type]}", $time);								
						}					
					} else {
						// time - issue #1068
						$time = strtotime("+{$member->duration} {$duration_exprs[$member->duration_type]}", $time);							
					}
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
									
				//update rebill: issue #: 489				
				if($member->active_num_cycles != 1 && (int)$member->rebilled < (int)$member->active_num_cycles) {
					// rebill
					$member->rebilled = (!$member->rebilled) ? 1 : ((int)$member->rebilled+1);
				}
							
				// role update
				if ($role) $update_role = true;		
				
				// transaction_id
				$transaction_id = $this->_get_transaction_id();
				// hook args
				$args = array('user_id' => $user_id, 'transaction_id'=>$transaction_id);
				// another membership
				if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
					$args['another_membership'] = $custom['membership_type'];
				}
				// after succesful payment hook
				do_action('mgm_membership_transaction_success', $args);// backward compatibility				
				do_action('mgm_subscription_purchase_payment_success', $args);// new organized name
				do_action('mgm_return_subscription_payment_'.$this->module, $args);// autoresponder	

			break;
			case 'Denied':
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				break;

			case 'Pending':
				$new_status = MGM_STATUS_PENDING;
				$reason = 'Unknown';
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				break;

			default:
				$new_status = MGM_STATUS_ERROR;
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $_POST['payment_status']);
				break;
		}
				
		// old status
		$old_status = $member->status;				
		// set new status
		$member->status = $new_status;
		
		// whether to acknowledge the user - This should happen only once
		$acknowledge_user = $this->send_payment_email($_POST['custom']);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $_POST['custom']);
		
		//another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {				//issue #1227
			if($subs_pack['hide_old_content'])
				$member->hide_old_content = $subs_pack['hide_old_content']; 
		
			mgm_save_another_membership_fields($member, $user_id);	

			// Multiple membership upgrade: first time
			if (isset($member->transaction_id) && isset($custom['multiple_upgrade_prev_packid']) && is_numeric($custom['multiple_upgrade_prev_packid'])) {
				mgm_multiple_upgrade_save_memberobject($custom, $member->transaction_id);	
			}
		}else {
			$member->save();
		}			
		
		// status change event
		do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);	
		
		//update coupon usage
		do_action('mgm_update_coupon_usage', array('user_id' => $user_id));
		
		// role
		if ($update_role) {						
			$obj_role = new mgm_roles();				
			$obj_role->add_user_role($user_id, $role);
		}
		
		// update transaction
		if(is_numeric($member->transaction_id))
			mgm_update_transaction(array('module'=>$this->module,'status' => $member->status ,'status_text' => $member->status_str ), $member->transaction_id);
		// return action
		do_action('mgm_return_'.$this->module, array('user_id' => $user_id));// backward compatibility
		do_action('mgm_return_subscription_payment_'.$this->module, array('user_id' => $user_id));// new , individual	
		do_action('mgm_return_subscription_payment', array('user_id' => $user_id, 'acknowledge_ar' => $acknowledge_ar, 'mgm_member' => $member));// new, global 
		
		// read member again for internal updates if any
		// another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
			$member = mgm_get_member_another_purchase($user_id, $custom['membership_type']);				
		}else {
			$member = mgm_get_member($user_id);
		}
		
		// send email notification to client
		$blogname = get_option('blogname');
		
		// notify
		if( $acknowledge_user ) {
			// notify user, only if gateway emails on 
			if ( ! $dpne ) {			
				// notify
				if( mgm_notify_user_membership_purchase($blogname, $user, $member, $custom, $subs_pack, $s_packs, $system_obj) ){						
					// update as email sent 
					$this->update_paymentemail_sent($alt_tran_id);	
				}				
			}
			// notify admin, only if gateway emails on 
			if ( ! $dge ) {
				// pack duration
				$pack_duration = $s_packs->get_pack_duration($subs_pack);
				// notify admin,
				mgm_notify_admin_membership_purchase($blogname, $user, $member, $pack_duration);
			}
		}
	}
	
	// default setting
	function _default_setting(){				
		// callback messages				
		$this->_setup_callback_messages();
		// callback urls
		$this->_setup_callback_urls();	
	}
	
	// log transaction
	function _log_transaction(){
		// check
		if($this->_is_transaction($_POST['custom'])){	
			// tran id
			$tran_id = (int)$_POST['custom'];			
			// return data				
			if(isset($_POST['txn_type'])){
				$option_name = $this->module.'_'.strtolower($_POST['txn_type']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_POST)));
			
			// options 
			$options = array('txn_type','subscr_id','txn_id');
			// loop
			foreach($options as $option){
				if(isset($_POST[$option])){
					mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>strtolower($this->module.'_'.$option),'option_value'=>$_POST[$option]));
				}
			}
			// return transaction id
			return $tran_id;
		}	
		// error
		return false;	
	}
	
	
	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// verify callback 
	function _verify_callback(){	
		// keep it simple		
		return (isset($_POST['custom'])) ? true : false;		
	}
	
	/**
	 * get gateway tracking fields for sync
	 *
	 * @todo process another subscription
	 */
	function get_tracking_fields_html(){
		// html
		$html = sprintf('<p align="top">%s: <textarea size="50" name="manualpay[purchase_notes]"></textarea></p>', 
						__('Manualpay Notes','mgm'));
		
		// return			
		return $html;				
	}
	
	/**
      * update and sync gateway tracking fields
	  *
	  * @param array $data
	  * @param object $member	  
	  * @return boolean 
	  * @uses _save_tracking_fields()
	  */
	 function update_tracking_fields($post_data, &$member){
	 	// validate
		if(isset($member->payment_info->module) && $member->payment_info->module != $this->code) return false;
		
	 	// fields, module_field => post_field
		$fields = array('notes'=>'purchase_notes');
		// data
		$data = $post_data['manualpay'];
	 	// return
	 	return $this->_save_tracking_fields($fields, $member, $data); 			
	 }
	
	// get module transaction info
	function get_transaction_info($member, $date_format){				
		// data
		$purchase_notes = $member->payment_info->notes;	
		// info
		$info = sprintf('<b>%s:</b><br>%s:<br>%s', __('MANUALPAY INFO','mgm'), __('NOTES ','mgm'), $purchase_notes);		
		// set
		$transaction_info = sprintf('<div class="overline">%s</div>', $info);		
		// return 
		return $transaction_info;
	}	
}

// end file