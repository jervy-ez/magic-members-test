<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * SagePay Payment Module
 *
 * @see issue# 361 for required fields
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */ 
class mgm_sagepay extends mgm_payment{	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_sagepay();
	}
	
	// php4 construct
	function mgm_sagepay(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Sagepay';
		// logo
		$this->logo = $this->module_url( 'assets/sagepay.jpeg' );
		// description
		$this->description = __('At Sagpay We process millions of secure payments each month for over 30,000 businesses.', 'mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription','buypost');
		// trial support available ?
		$this->supports_trial= 'N';		
		// do we depend on product mapping	
		$this->requires_product_mapping = 'N';
		// type of integration
		$this->hosted_payment = 'Y';// html redirect
		// endpoints
		$this->_setup_endpoints();			
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
	
	// settings box api hook
	function settings_box(){
		global $wpdb;
		// data
		$data = array();	
		// set 
		$data['module'] = $this;	
		// load template view
		return $this->load->template('settings_box', array('data'=>$data), true);		
	}
	
	// settings update
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
						// message
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
				// sagepay specific
				$this->setting['vendor']         = $_POST['setting']['vendor'];
				$this->setting['encryption_key'] = $_POST['setting']['encryption_key'];
				$this->setting['currency']       = $_POST['setting']['currency'];								
				// purchase price
				if(isset($_POST['setting']['purchase_price'])){
					$this->setting['purchase_price'] = $_POST['setting']['purchase_price'];
				}
				// common
				$this->description = $_POST['description'];
				$this->status      = $_POST['status'];
				// logo if uploaded
				if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
					$this->logo = $_POST['logo_new_'.$this->code];
				}	
				// setup callback messages				
				$this->_setup_callback_messages($_POST['setting']);
				// re setup callback urls
				$this->_setup_callback_urls($_POST['setting']);
				// re setup endpoints
				$this->_setup_endpoints();							
				// save
				$this->save();
				// message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));
			break;
		}		
	}
	
	// return process api hook, link back to site after payment is made
	function process_return() {		
		// check and show message
		if((isset($_REQUEST['crypt']))){
			// parse crypt
			$_POST = $this->_crypt($_REQUEST['crypt']);
			// custom
			$alt_tran_id = $this->_get_alternate_transaction_id();
			// process notify, internally called
			$this->process_notify();
			// query arg
			$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($alt_tran_id));
			// is a post redirect?
			if(isset($alt_tran_id) && !empty($alt_tran_id)){
				// is a post redirect?
				$post_redirect = $this->_get_post_redirect($alt_tran_id);
				// set post redirect
				if($post_redirect !== false){
					$query_arg['post_redirect'] = $post_redirect;
				}	
				// is a register redirect?
				$register_redirect = $this->_auto_login($alt_tran_id);	
				// set register redirect
				if($register_redirect !== false){
					$query_arg['register_redirect'] = $register_redirect;
				}	
			}
			// redirect
			mgm_redirect(add_query_arg($query_arg, $this->_get_thankyou_url()));
		}else{			
			mgm_redirect(add_query_arg(array('status'=>'error'), $this->_get_thankyou_url()));
		}
	}	
	
	// notify process api hook, background IPN url
	function process_notify() {	
		//record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );		
		// verify 
		if($this->_verify_callback()){	
			// custom
			$alt_tran_id = $this->_get_alternate_transaction_id();
			// log data before validate
			$tran_id = $this->_log_transaction();
			// payment type
			$payment_type = $this->_get_payment_type($alt_tran_id);
			// custom
			$custom = $this->_get_transaction_passthrough($alt_tran_id);
			// hook for pre process
			do_action('mgm_notify_pre_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));
			// check
			switch($payment_type){
				// buypost 
				case 'post_purchase':
				case 'buypost':
					$this->_buy_post(); //run the code to process a purchased post/page
				break;
				// subscription	
				case 'subscription':
					// cancellation
					/*if(isset($_POST['txn_type']) && $_POST['txn_type']=='subscr_cancel') {						
						$this->_cancel_membership(); //run the code to process a membership cancellation
					}else{*/
						$this->_buy_membership(); //run the code to process a new/extended membership
					//}	
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
	
	// process html_redirect, proxy for form submit
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
			(!isset($tran['data']['user_id']) || (isset($tran['data']['user_id']) && (int) $tran['data']['user_id'] < 1))) {
			return __('Transaction invalid . User id field is empty','mgm');		
		}
		// generate
		$button_code = $this->_get_button_code($tran['data'],$tran_id);
		// extra code
		$additional_code = do_action('mgm_additional_code');
		// the html			
		$html='<form action="'. $this->_get_endpoint() .'" method="post" class="mgm_form" name="' . $this->code . '_redirect_form" id="' . $this->code . '_redirect_form">
					'. $button_code .'					
					'. $additional_code .'						
					<img src="'.MGM_ASSETS_URL.'images/ajax/ajax-loader.gif"/><br>
					<b>'.sprintf(__('Please wait, you are being redirected to %s...','mgm'), $this->name).'</b>									
			  </form>				
			  <script language="javascript">document.' . $this->code . '_redirect_form.submit();</script>';
		// return 	  
		return $html;					
	}	
		
	// subscribe button api hook
	function get_button_subscribe($options=array()){	
		// sp depends on encryption_key, check for it before generating button
		if(!isset($this->setting['encryption_key']) || $this->setting['encryption_key']== ''){
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in SagePay settings : No encryption key set.','mgm').'</b>
					</div>';
			exit;
		}	
		$include_permalink = (isset($options['widget'])) ? false : true;
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect', $include_permalink) .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
				   <input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
				   <input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">
				   <div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
			   </form>';
		// return	   
		return $html;
	}
	
	// buypost button api hook
	function get_button_buypost($options=array(), $return = false) {
		// sp depends on encryption_key, check for it before generating button
		if(!isset($this->setting['encryption_key']) || $this->setting['encryption_key']== ''){
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in SagePay settings : No encryption key set.','mgm').'</b>
					</div>';
			exit;
		}	
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					<input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
					<input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">
					<div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
			   </form>';				
		// return or print
		if ($return) {
			return $html;
		} else {
			echo $html;
		}
	}	
	
	// get module transaction info
	function get_transaction_info($member, $date_format){		
		// return
		// return print_r($member->payment_info, 1);		
		// data
		$auth_no = $member->payment_info->subscr_id;
		$txn_id  = $member->payment_info->txn_id;		
		// info
		$info = sprintf('<b>%s:</b><br>%s: %s<br>%s: %s', __('SAGEPAY INFO','mgm'), __('AUTH NO','mgm'), $auth_no, 
						__('TXN ID','mgm'), $txn_id);					
		// set
		$transaction_info = sprintf('<div class="overline">%s</div>', $info);
		
		// return 
		return 	$transaction_info;
	}
	
	/**
	 * get gateway tracking fields for sync
	 *
	 * @todo process another subscription
	 */
	function get_tracking_fields_html(){
		//  
		// html
		$html = sprintf('<p>%s: <input type="text" size="20" name="sagepay[subscriber_id]"/></p>
						 <p>%s: <input type="text" size="20" name="sagepay[transaction_id]"/></p>', 
						 __('AUTH NO','mgm'), __('TXN ID','mgm'));
		
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
		$fields = array('subscr_id'=>'subscriber_id','txn_id'=>'transaction_id');
		// data
		$data = $post_data['sagepay'];
	 	// return
	 	return $this->_save_tracking_fields($fields, $member, $data); 			
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
		$system_obj = mgm_get_class('system');	
		$user_id = $pack['user_id'];
		$user = get_userdata($user_id);			
		// item 		
		$item = $this->get_pack_item($pack);
		//pack currency over rides genral setting currency - issue #1602
		if(!isset($pack['currency']) || empty($pack['currency'])){
			$pack['currency']=$this->setting['currency'];
		}		
		// setup data array	
		$data = array(
			'VPSProtocol'   => '2.23',
			'TxType'        => 'PAYMENT',
		  	'Vendor'        => $this->setting['vendor'],							
			'VendorTxCode'  => $tran_id,
			'Currency'      => $pack['currency'],			
			'Description'   => $item['name'],
			'Apply3DSecure' => 0,
			'ApplyAVSCV2'   => 0,
			'AllowGiftAid'  => 0,
			'FailureURL'    => $this->setting['return_url'],	
			'CustomerEMail' => $user->user_email,
			'Amount'        => number_format($pack['cost'], 2, '.', '')			
		);
		
		// additional fields
		$this->_set_address_fields($user, $data);
		
		// subscription purchase with ongoing/limited
		if( !isset($pack['buypost']) && isset($pack['duration_type']) && $pack['num_cycles'] != 1 ){ // does not support one-time recurring
		// if ($pack['num_cycles'] != 1 && $pack['duration_type']) { // old style
			// nothing for sagepay
		} 		
		
		// custom/passthrough
		$custom = $tran_id;
		
		// set in success
		$data['SuccessURL'] = add_query_arg(array('custom'=>$custom),$this->setting['return_url']);		
		
		// update currency - issue #1602
/*		if($pack['currency'] != $this->setting['currency']){
			$pack['currency'] = $this->setting['currency'];
		}*/
		
		// add filter @todo test
		$data = apply_filters('mgm_payment_button_data', $data, $tran_id, $this->module, $pack);
		
		// update pack/transaction
		mgm_update_transaction(array('data'=>json_encode($pack),'module'=>$this->module), $tran_id);
		
		// actual form data
		$form_data = array_slice($data,0,3);// pluck first 3, VPSProtocol,TxType and Vendor
		// generate crypt
		$form_data['Crypt'] = $this->_crypt($data);// with data create, with no data parse
		
		// return data, crypted
		return $form_data;
	}		
	
	// buy post
	function _buy_post() {
		global $wpdb;
		// system
		$system_obj = mgm_get_class('system');
		$dge    = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne    = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		// passthrough
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($alt_tran_id);
		// local var
		extract($custom);

		//getting purchase post title and & price - issue #981				
		$post_obj = mgm_get_post($post_id);
		$purchase_cost     = mgm_convert_to_currency($post_obj->purchase_cost);
		$post    = get_post($post_id);
		$post_title   = $post->post_title;		
		
		// find user
		//issue #504
		if($user_id) $user = get_userdata($user_id);

		// errors
		$errors = array();
		
		$blogname = get_option('blogname');
		$tran_success = false;
		$payment_status = $_POST['Status']; 
		switch ($payment_status) {
			case 'OK':
				// status success
				$tran_success = true;
				// status
				$status_str = __('Last payment was successful','mgm');
				// emails not for guest
				//issue #504
				if($user_id){
					
					// subject
					$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
					// body
					$message = $system_obj->get_template('payment_success_email_template_body', 
											array('blogname'=>$blogname, 'name'=>$user->display_name,
												  'post_title'=>$post_title,'purchase_cost'=>$purchase_cost, 
												  'email'=>$user->user_email, 
												  'admin_email'=>$system_obj->setting['admin_email']), true);
				}
				// transaction id
				$transaction_id = $this->_get_transaction_id();
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
			break;
						
			case 'NOTAUTHED':
			case 'INVALID':
			case 'REJECTED':
				// status
				$status_str = __('Last payment was refunded or denied','mgm');
				// emails not for guest
				//issue #504
				if($user_id){
					// subject
					$subject = $system_obj->get_template('payment_failed_email_template_subject', array('blogname'=>$blogname), true);
					 
					// body			
					$message = $system_obj->get_template('payment_failed_email_template_body', 
											array('blogname'=>$blogname, 'name'=>$user->display_name, 
											      'post_title'=>$post_title,'purchase_cost'=>$purchase_cost, 
												  'email'=>$user->user_email, 'payment_type'=>'post purchase payment','reason'=>$status_str,
												  'admin_email'=>$system_obj->setting['admin_email']), true);
				}											  
				// error
				$errors[] = $status_str;

				break;

			case 'ABORT':
				// status
				$reason = 'Unknown';
				$status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				// emails not for guest
				//issue #504
				if($user_id){
					// subject
					$subject = $system_obj->get_template('payment_pending_email_template_subject', array('blogname'=>$blogname), true);
					// body	
					$message = $system_obj->get_template('payment_pending_email_template_body', 
											array('blogname'=>$blogname, 'name'=>$user->display_name,
											      'post_title'=>$post_title,'purchase_cost'=>$purchase_cost,  
												  'email'=>$user->user_email, 'reason'=>$status_str,
												  'admin_email'=>$system_obj->setting['admin_email']), true);
				}												  
				// error
				$errors[] = $status_str;

				break;

			default:
				// status
				$status_str = sprintf(__('Last payment status: %s','mgm'),$payment_status);
				// emails not for guest
				//issue #504
				if($user_id){
					// subject
					$subject = $system_obj->get_template('payment_unknown_email_template_subject', array('blogname'=>$blogname), true);				
					// body	
					$message = $system_obj->get_template('payment_unknown_email_template_body', 
											array('blogname'=>$blogname, 'name'=>$user->display_name,
												  'post_title'=>$post_title,'purchase_cost'=>$purchase_cost,  
					                              'email'=>$user->user_email, 'reason'=>$status_str,
					                              'admin_email'=>$system_obj->setting['admin_email']), true);
				}
				// error
				$errors[] = $status_str;
																											  
		}
		
		// do action
		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new, individual
		do_action('mgm_return_post_purchase_payment', array('post_id' => $post_id));// new, global 
		
		// notify user
		if(!$dpne) {
			if($user_id && $this->send_payment_email($alt_tran_id)) {
				//issue #862
				$subject = mgm_replace_email_tags($subject,$user_id);
				$message = mgm_replace_email_tags($message,$user_id);
				
				mgm_mail($user->user_email, $subject, $message); //send an email to the buyer		
				//update as email sent 
				$this->update_paymentemail_sent($alt_tran_id);	
			}
		}

		$status = __('Failed join', 'mgm'); //overridden on a successful payment
		if ($tran_success) {
			
			//issue #1421
			if($user_id){				
				do_action('mgm_update_coupon_usage', array('user_id' => $user_id));				
			}			
			
			// mark as purchased
			if(isset($guest_token)){
				// issue #1421
				if(isset($coupon_id) && isset($coupon_code)) {
					do_action('mgm_update_coupon_usage', array('guest_token' => $guest_token,'coupon_id' => $coupon_id));
					$this->_set_purchased(NULL, $post_id, $guest_token, $alt_tran_id,$coupon_code);
				}else {
					$this->_set_purchased(NULL, $post_id, $guest_token, $alt_tran_id);				
				}
			}else{
				$this->_set_purchased($user_id, $post_id, NULL, $alt_tran_id);
			}
			// status
			$status = __('The post was purchased successfully', 'mgm');
		}

		// transaction status
		mgm_update_transaction_status($alt_tran_id, $status, $status_str);
		
		// notify admin, only if gateway emails on
		if (!$dge) {
			// not for guest
			if($user_id){
				$subject = "[" . $blogname . "] Admin Notification: " . $user->user_email . " purchased post " . $post_id;
				$message = "User display name: {$user->display_name}<br />User email: {$user->user_email}<br />User ID: {$user->ID}<br />Status: " . $status . "<br />Action: Purchase post:" . $subject . "<br /><br />" . $message . "<br /><br /><pre>" . print_r($_POST, true) . '</pre>';
			}else{
				$subject = "[" . $blogname . "] Admin Notification: Guest[IP: ".mgm_get_client_ip_address()."] purchased post " . $post_id;
				$message = "Guest Purchase";
			}
			mgm_mail($system_obj->setting['admin_email'], $subject, $message);
		}
		// error condition redirect
		if(count($errors)>0){
			mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
		}
		
	}
	
	// buy membership
	function _buy_membership() {	
		// system	
		$system_obj  = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge     = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne    = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		// passthrough
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($alt_tran_id);
		// local var
		extract($custom);
		
		// currency
		if (!$currency) $currency = $this->setting['currency'];
		
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

		//if there is no duration set in the user object then run the following code
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
				if(!$dge){
					$message = 'Could not read membership type in the following POST data. Please debug or contact magic members to fix the problem making sure to pass on the following data. <br /><br /><pre>' . "\n\n" . print_r($_POST, true) . '</pre>';
					mgm_mail($system_obj->setting['admin_email'], 'Error in sagepay membership verification', $message);
				}
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
		// $member->payment_type    = 'subscription' ;
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		// payment info for unsubscribe		
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'txn_type', 'subscr_id'=>'TxAuthNo', 'txn_id'=>'VPSTxId');
		// save tracking fields
		$this->_save_tracking_fields($tracking_fields, $member);
		/*if(!isset($member->payment_info))
			$member->payment_info    = new stdClass;
		$member->payment_info->module = $this->code;
		if(isset($_POST['txn_type'])){
			$member->payment_info->txn_type = $_POST['txn_type'];
		}	
		if(isset($_POST['TxAuthNo'])){
			$member->payment_info->subscr_id = $_POST['TxAuthNo'];		
		}	
		if(isset($_POST['VPSTxId'])){	
			$member->payment_info->txn_id = $_POST['VPSTxId'];	
		}	*/
		// mgm transaction id
		$member->transaction_id = $alt_tran_id;
		// process sagepay response
		$new_status = false;
		$update_role = false;
		// statusid to name
		$payment_status = $_POST['Status']; 
		// status
		switch ($payment_status) {
			case 'OK':			
				$new_status = MGM_STATUS_ACTIVE;
				$member->status_str = __('Last payment was successful','mgm');					
				
				// old type match
				$old_membership_type = mgm_get_user_membership_type($user_id, 'code');
				if ($old_membership_type != $membership_type) {
					$member->join_date = time(); // type join date as different var
				}
				// old content hide
				$member->hide_old_content = $hide_old_content; 
				
				$time = time();
				$last_pay_date = isset($member->last_pay_date) ? $member->last_pay_date : null;			
				$member->last_pay_date = date('Y-m-d', $time);
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
				
				//cancel previous subscription:
				//issue#: 565				
				$this->cancel_recurring_subscription($alt_tran_id, null, null, $pack_id);
					
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

			break;
			case 'NOTAUTHED':
			case 'INVALID':
			case 'REJECTED':
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				break;

			case 'ABORT':
				$new_status = MGM_STATUS_PENDING;

				$reason = $_POST['StatusDetail'];
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				break;

			default:
				$new_status = MGM_STATUS_ERROR;
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $_POST['StatusDetail']);
				break;
		}		
		
		// old status
		$old_status = $member->status;				
		// set new status
		$member->status = $new_status;
		
		// whether to acknowledge the user - This should happen only once
		$acknowledge_user = $this->send_payment_email($alt_tran_id);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $alt_tran_id);
		
		//another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {			//issue #1227
			if($subs_pack['hide_old_content'])
				$member->hide_old_content = $subs_pack['hide_old_content']; 
			
			mgm_save_another_membership_fields($member, $user_id);	
			// Multiple membership upgrade: first time
			if (isset($custom['multiple_upgrade_prev_packid']) && is_numeric($custom['multiple_upgrade_prev_packid'])) {
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
		// return action
		do_action('mgm_return_'.$this->module, array('user_id' => $user_id));// backward compatibility
		do_action('mgm_return_subscription_payment_'.$this->module, array('user_id' => $user_id));// new , individual	
		do_action('mgm_return_subscription_payment', array('user_id' => $user_id, 'acknowledge_ar' => $acknowledge_ar, 'mgm_member' => $member));// new, global: pass mgm_member object to consider multiple level purchases as well. 	

		// read member again for internal updates if any
		// another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
			$member = mgm_get_member_another_purchase($user_id, $custom['membership_type']);				
		}else {
			$member = mgm_get_member($user_id);
		}
		
		// send email notification to client
		$blogname = get_option('blogname');
		// on status
		switch ($member->status) {
			case MGM_STATUS_ACTIVE:
				//Sending notification email to user - issue #1468
				if($notify_user && $is_registration =='Y'){
					$user_pass = mgm_decrypt_password($member->user_password, $user_id);
					do_action('mgm_register_user_notification', $user_id, $user_pass);
				}
				//sending upgrade notifaction email to admin
				if(isset($subscription_option) && $subscription_option =='upgrade'){
					do_action('mgm_user_upgrade_notification', $user_id);
				}				
				// init
				$subscription = '';
				// add trial 
				if ($subs_pack['trial_on']) {
					// trial
					$subscription = sprintf('%1$s %2$s for the first %3$s %4$s,<br> then ',$member->trial_cost, $member->currency, ($member->trial_duration * $member->trial_num_cycles), $s_packs->get_pack_duration($subs_pack,true)); 
				}
				// subject
				$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
				// on type
				if ($member->payment_type == 'subscription') {
					$payment_type = 'recurring subscription';
					$subscription .= sprintf('%1$s %2$s for each %3$s %4$s, %5$s',$member->amount,$member->currency, 
					                                                              $member->duration,$s_packs->get_pack_duration($subs_pack),
																				  ((int)$member->active_num_cycles > 0 ? sprintf('for %d installments',(int)$member->active_num_cycles):'until cancelled'));
				} else {
					$payment_type = 'one-time payment';
					$subscription .= sprintf('%1$s %2$s for %3$s %4$s',$member->amount, $member->currency, $member->duration,$s_packs->get_pack_duration($subs_pack));					
				}
				// body
				$message = $system_obj->get_template('payment_success_subscription_email_template_body', 
													array('blogname'=>$blogname, 'name'=>$user->display_name, 
														  'email'=>$user->user_email, 'payment_type'=>$payment_type,
														  'subscription'=>$subscription,'admin_email'=>$system_obj->setting['admin_email']), true);
				break;

			case MGM_STATUS_NULL:
				// subject
				$subject = $system_obj->get_template('payment_failed_email_template_subject', array('blogname'=>$blogname), true);				
				// message
				$message = $system_obj->get_template('payment_failed_email_template_body', 
									array('blogname'=>$blogname, 'name'=>$user->display_name, 
										  'email'=>$user->user_email, 'payment_type'=>'subscription payment',
										  'reason'=>$member->status_str,
										  'admin_email'=>$system_obj->setting['admin_email']), true);
				break;

			case MGM_STATUS_PENDING:
				// subject
				$subject = $system_obj->get_template('payment_pending_email_template_subject', array('blogname'=>$blogname), true);
				// body
				$message = $system_obj->get_template('payment_pending_email_template_body', 
										array('blogname'=>$blogname, 'name'=>$user->display_name, 
											  'email'=>$user->user_email, 'reason'=>$member->status_str,
											  'admin_email'=>$system_obj->setting['admin_email']), true);
				break;

			case MGM_STATUS_ERROR:
				// subject
				$subject = $system_obj->get_template('payment_error_email_template_subject', array('blogname'=>$blogname), true);				
				// body	
				$message = $system_obj->get_template('payment_error_email_template_body', 
									array('blogname'=>$blogname, 'name'=>$user->display_name, 
										  'email'=>$user->user_email, 'reason'=>$member->status_str,
										  'admin_email'=>$system_obj->setting['admin_email']), true);
				break;
		}

		// notify user
		if(!$dpne) {
			if($acknowledge_user) {
				//issue #862
				$subject = mgm_replace_email_tags($subject,$user_id);
				$message = mgm_replace_email_tags($message,$user_id);
				
				mgm_mail($user->user_email, $subject, $message);		
				//update as email sent 
				$this->update_paymentemail_sent($alt_tran_id);	
			}
		}

		// send email notification to system administrator
		if (!$dge && $acknowledge_user) {
			$subject = "[$blogname] {$user->user_email} - {$member->status}";
			$message = "	User display name: {$user->display_name}\n\n<br />
					User email: {$user->user_email}\n\n<br />
					User ID: {$user->ID}\n\n<br />
					Membership Type: {$membership_type}\n\n<br />
					New status: {$member->status}\n\n<br />
					Status message: {$member->status_str}\n\n<br />
					Subscription period: {$member->duration} ". $s_packs->get_pack_duration($subs_pack) ."\n\n<br />
					Subscription amount: {$member->amount} {$member->currency}\n<br />
					Payment Mode: {$member->payment_type}\n\n<br />
					POST Data was: \n\n<br /><br /><pre>" . print_r($_POST, true) . '</pre>';
			mgm_mail($system_obj->setting['admin_email'], $subject, $message);
		}
	}	
	
	// cancel membership
	function _cancel_membership(){
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs  = mgm_get_class('subscription_packs');
		$dge    = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne   = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		// passthrough
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($alt_tran_id);
		// local var
		extract($custom);
		
		// currency
		if (!$currency) $currency = $this->setting['currency'];		
		
		// find user
		$user = get_userdata($user_id);
		$member = mgm_get_member($user_id);
		//multiple membesrhip level update:
		$multiple_update = false;	
		// check
		if((isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']) || (isset($is_another_membership_purchase) && $is_another_membership_purchase == 'Y' )) {
			$multiple_update = true;
			$multi_memtype = (isset($_POST['membership_type'])) ? $_POST['membership_type'] : $membership_type;	
			$member = mgm_get_member_another_purchase($user_id, $multi_memtype);		
		}	
		
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'txn_type', 'subscr_id'=>'TxAuthNo', 'txn_id'=>'VPSTxId');
		// save tracking fields
		$this->_save_tracking_fields($tracking_fields, $member);		
		
		// default expire date	
		$expire_date = $member->expire_date;
		// if lifetime:
		if($member->duration_type == 'l') $expire_date = date('Y-m-d');
		
		// transaction_id	
		$trans_id = $member->transaction_id;	
		// if today 
		if($expire_date == date('Y-m-d')){
			// status
			$new_status              = MGM_STATUS_CANCELLED;
			$new_status_str          = __('Subscription cancelled','mgm');
			// set
			$member->status      = $new_status;
			$member->status_str  = $new_status_str;					
			$member->expire_date = date('Y-m-d');
												
			// reassign expiry membership pack if exists: issue#: 535			
			$member = apply_filters('mgm_reassign_member_subscription', $user_id, $member, 'CANCEL', true);			
		}else{
			// date
			$date_format = mgm_get_date_format('date_format');
			// status
			$new_status     = MGM_STATUS_AWAITING_CANCEL;	
			$new_status_str = sprintf(__('Subscription awaiting cancellation on %s','mgm'), date($date_format, strtotime($expire_date)));			
			// set
			$member->status      = $new_status;
			$member->status_str  = $new_status_str;	
			// set reset date
			$member->status_reset_on = $expire_date;
			$member->status_reset_as = MGM_STATUS_CANCELLED;
		}

		// multiple membesrhip level update:	
		if($multiple_update) {
			mgm_save_another_membership_fields($member, $user_id);	
		}else{ 			
			$member->save();
		}	
		
		// transaction status
		mgm_update_transaction_status($trans_id, $new_status, $new_status_str);
			
		// send email notification to client
		$blogname = get_option('blogname');
		// subject
		$subject = $system_obj->get_template('subscription_cancelled_email_template_subject', array('blogname'=>$blogname), true);				
		// body	
		$message = $system_obj->get_template('subscription_cancelled_email_template_body', 
							array('blogname'=>$blogname, 'name'=>$user->display_name, 
								  'email'=>$user->user_email, 'admin_email'=>$system_obj->setting['admin_email']), true);
		// send email notification to user
		if(!$dpne) {
			//issue #862
			$subject = mgm_replace_email_tags($subject,$user_id);
			$message = mgm_replace_email_tags($message,$user_id);
			// mail
			mgm_mail($user->user_email, $subject, $message);		
		}

		// notify admin, only if gateway emails on
		if (!$dge) {
			$subject = "[$blogname] {$user->user_email} - {$new_status}";
			$message = "	User display name: {$user->display_name}\n\n<br />
					User email: {$user->user_email}\n\n<br />
					User ID: {$user->ID}\n\n<br />
					Membership Type: {$membership_type}\n\n<br />
					New status: {$new_status}\n\n<br />
					Status message: {$member->status_str}\n\n<br />					
					Payment Mode: Cancelled\n\n<br />
					POST Data was: \n\n<br /><br /><pre>" . print_r($_POST, true) . '</pre>';
			mgm_mail($system_obj->setting['admin_email'], $subject, $message);
		}
		
		// after cancellation hook
		do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));	
	}
	
	// default setting
	function _default_setting(){
		// sagepay specific
		$this->setting['vendor']         = '';
		$this->setting['encryption_key'] = '';		
		$this->setting['currency']       = mgm_get_class('system')->get_setting('currency');		
		// purchase price
		if(in_array('buypost', $this->supported_buttons)){
			$this->setting['purchase_price']  = 4.00;
		}		
		// callback messages				
		$this->_setup_callback_messages();
		// callback urls
		$this->_setup_callback_urls();		
	}		
	
	// log transaction
	function _log_transaction(){
		// custom
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// check
		if($this->_is_transaction($alt_tran_id)){	
			// tran id
			$tran_id = (int)$alt_tran_id;			
			// return data				
			/*if(isset($_REQUEST['txn_type'])){
				$option_name = $this->module.'_'.strtolower($_REQUEST['txn_type']).'_return_data';
			}else{*/
				$option_name = $this->module.'_return_data';
			//}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_POST)));
			
			// options 
			$options = array('TxAuthNo','VPSTxId');
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
	
	// get tran id
	function _get_transaction_id(){
		// custom
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// validate
		if($this->_is_transaction($alt_tran_id)){	
			// tran id
			return $tran_id = (int)$alt_tran_id;
		}
		// return 
		return 0;	
	}
	
	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// Filters unwanted characters out of an input string.  Useful for tidying up FORM field inputs.
	function _clean_input($strRawText,$strType) {
	
		if ($strType=="Number") {
			$strClean="0123456789.";
			$bolHighOrder=false;
		}
		else if ($strType=="VendorTxCode") {
			$strClean="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
			$bolHighOrder=false;
		}
		else {
			$strClean=" ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.,'/{}@():?-_&�$=%~<>*+\"";
			$bolHighOrder=true;
		}
	
		$strCleanedText="";
		$iCharPos = 0;
	
		do
			{
				// Only include valid characters
				$chrThisChar=substr($strRawText,$iCharPos,1);
	
				if (strspn($chrThisChar,$strClean,0,strlen($strClean))>0) {
					$strCleanedText=$strCleanedText . $chrThisChar;
				}
				else if ($bolHighOrder==true) {
					// Fix to allow accented characters and most high order bit chars which are harmless
					if (bin2hex($chrThisChar)>=191) {
						$strCleanedText=$strCleanedText . $chrThisChar;
					}
				}
	
			$iCharPos=$iCharPos+1;
			}
		while ($iCharPos<strlen($strRawText));
	
		$cleanInput = ltrim($strCleanedText);
		return $cleanInput;
	
	}

	/* Base 64 Encoding function **
	** PHP does it natively but just for consistency and ease of maintenance, let's declare our own function **/
	
	function _base64_encode($plain) {
	
	  // Initialise output variable
	  $output = "";
	
	  // Do encoding
	  $output = base64_encode($plain);
	
	  // Return the result
	  return $output;
	}

	/* Base 64 decoding function **
	** PHP does it natively but just for consistency and ease of maintenance, let's declare our own function **/
	
	function _base64_decode($scrambled) {
	  // Initialise output variable
	  $output = "";
	
	  // Fix plus to space conversion issue
	  $scrambled = str_replace(" ","+",$scrambled);
	
	  // Do encoding
	  $output = base64_decode($scrambled);
	
	  // Return the result
	  return $output;
	}


	/*  The SimpleXor encryption algorithm                                                                                **
	**  NOTE: This is a placeholder really.  Future releases of Form will use AES or TwoFish.  Proper encryption      **
	**  This simple function and the Base64 will deter script kiddies and prevent the "View Source" type tampering        **
	**  It won't stop a half decent hacker though, but the most they could do is change the amount field to something     **
	**  else, so provided the vendor checks the reports and compares amounts, there is no harm done.  It's still          **
	**  more secure than the other PSPs who don't both encrypting their forms at all                                      */
	
	function _simpleXor($InString, $Key) {
	  // no key
	  if($Key==''){
	  	return '';
	  	// die(__('No key for Sagepay','mgm'));
	  }	
	  // Initialise key array
	  $KeyList = array();
	  // Initialise out variable
	  $output = "";
		
	  // Convert $Key into array of ASCII values
	  for($i = 0; $i < strlen($Key); $i++){
		$KeyList[$i] = ord(substr($Key, $i, 1));
	  }
	
	  // Step through string a character at a time
	  for($i = 0; $i < strlen($InString); $i++) {
		// Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the two, get the character from the result
		// % is MOD (modulus), ^ is XOR
		$output.= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
	  }
	
	  // Return the result
	  return $output;
	}
	
	// Function to check validity of email address entered in form fields
	function _is_valid_email($email) {
	  $result = TRUE;
	  if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email)) {
		$result = FALSE;
	  }
	  return $result;
	}	
	
	// parse crypt
	function _get_token($thisString) {

	  // List the possible tokens
	  $Tokens = array("Status","StatusDetail","VendorTxCode","VPSTxId","TxAuthNo","Amount",
					  "AVSCV2","AddressResult","PostCodeResult","CV2Result","GiftAid","3DSecureStatus", 
					  "CAVV","AddressStatus","CardType","Last4Digits","PayerStatus","CardType");
		
	  // Initialise arrays
	  $output = array();
	  $resultArray = array();
	  
	  // Get the next token in the sequence
	  for ($i = count($Tokens)-1; $i >= 0 ; $i--){
		// Find the position in the string
		$start = strpos($thisString, $Tokens[$i]);
		// If it's present
		if ($start !== false){
		  // Record position and token name
		  $resultArray[$i]->start = $start;
		  $resultArray[$i]->token = $Tokens[$i];
		}
	  }
	  
	  // Sort in order of position
	  sort($resultArray);
		// Go through the result array, getting the token values
	  for ($i = 0; $i<count($resultArray); $i++){
		// Get the start point of the value
		$valueStart = $resultArray[$i]->start + strlen($resultArray[$i]->token) + 1;
		// Get the length of the value
		if ($i==(count($resultArray)-1)) {
		  $output[$resultArray[$i]->token] = substr($thisString, $valueStart);
		} else {
		  $valueLength = $resultArray[$i+1]->start - $resultArray[$i]->start - strlen($resultArray[$i]->token) - 2;
		  $output[$resultArray[$i]->token] = substr($thisString, $valueStart, $valueLength);
		}      
	
	  }
	
	  // Return the ouput array
	  return $output;
	}
	
	// generate/parse crypt
	function _crypt($data=NULL){
		// if array : encode
		if(is_array($data)){
			$str = mgm_http_build_query($data,false);		
			return $this->_base64_encode($this->_simpleXor($str,$this->setting['encryption_key']));
		}elseif(is_string($data)){
		// if string : decode
			$str_decoded = $this->_simpleXor($this->_base64_decode($data),$this->setting['encryption_key']);
			return $this->_get_token($str_decoded);
		}
	}
	
	// additional payment fields
	/*
	function _set_address_fields($user,&$data){
		$member = mgm_get_member($user->ID);
		// user fields on payment page
		$uf_on_paymentpage = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_payment'=>true)));
		// founf some
		if($uf_on_paymentpage){
			foreach($uf_on_paymentpage as $uf){
				if($uf_value = $member->custom_fields->$uf['name']){
					// set appropiate fields
					switch($uf['name']){
						case 'first_name':
							$data['CustomerName']       = $uf_value;
							$data['BillingFirstnames']  = $uf_value;	
							$data['DeliveryFirstnames'] = $uf_value;									
						break;
						case 'last_name':
							$data['CustomerName']    .= ' '.$uf_value;
							$data['BillingSurname']   = $uf_value;	
							$data['DeliverySurname']  = $uf_value;	
						break;
						case 'address':
							$data['BillingAddress1']  = $uf_value;
							$data['DeliveryAddress1'] = $uf_value;
						break;
						case 'city':
							$data['BillingCity']  = $uf_value;
							$data['DeliveryCity'] = $uf_value;
						break;
						case 'state':
							$data['BillingState']  = $uf_value;
							$data['DeliveryState'] = $uf_value;
						break;
						case 'zip':
							$data['BillingPostCode']  = $uf_value;
							$data['DeliveryPostCode'] = $uf_value;
						break;
						case 'country':
							$data['BillingCountry']  = $uf_value;
							$data['DeliveryCountry'] = $uf_value;
						break;
						default:
							$data[$uf['name']] = $uf_value;
						break;
					}
				}
			}
		}	
	}
	*/
	
	// setup
	function _setup_endpoints($end_points = array()){
		// define defaults
		$end_points_default = array('test' 		=> 'https://test.sagepay.com/gateway/service/vspform-register.vsp',
									'live' 		=> 'https://live.sagepay.com/gateway/service/vspform-register.vsp',
									'simulator' => 'https://test.sagepay.com/Simulator/VSPFormGateway.asp');	
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// set 
	function _set_address_fields($user, &$data){
				
		// mappings: Billing address
		$mappings= array('full_name'=>'CustomerName','first_name'=>'BillingFirstnames','last_name'=>'BillingSurname',
		                 'address'=>array('BillingAddress1','BillingAddress2'),'city'=>'BillingCity','state'=>'BillingState',/* : STATE CODE IS NEEDED HERE*/
						 'zip'=>'BillingPostCode','country'=>'BillingCountry','phone'=>'BillingPhone');
						 
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter'));	
		
		// mappings: delivery address
		$mappings= array('first_name'=>'DeliveryFirstnames','last_name'=>'DeliverySurname',
		                 'address'=>array('DeliveryAddress1','DeliveryAddress2'),'city'=>'DeliveryCity','state'=>'DeliveryState',/* : STATE CODE IS NEEDED HERE*/
						 'zip'=>'DeliveryPostCode','country'=>'DeliveryCountry','phone'=>'DeliveryPhone');
						 
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter')); 
		//reformat state: issue#:472
		if($data['BillingCountry'] == 'US') {
			$us_states = array('ALABAMA' => 'AL','ALASKA' => 'AK', 'AMERICAN SAMOA' => 'AS',
								'ARIZONA' => 'AZ', 'ARKANSAS' => 'AR', 'CALIFORNIA' => 'CA', 'COLORADO' => 'CO',
								'CONNECTICUT' => 'CT', 'DELAWARE' => 'DE', 'DISTRICT OF COLUMBIA' =>'DC',
								'FEDERATED STATES OF MICRONESIA' => 'FM','FLORIDA' => 'FL', 'GEORGIA' => 'GA',
								'GUAM' => 'GU', 'HAWAII' => 'HI', 'IDAHO' => 'ID', 'ILLINOIS' => 'IL','INDIANA' =>'IN',
								'IOWA' => 'IA', 'KANSAS' => 'KS', 'KENTUCKY' => 'KY', 'LOUISIANA' => 'LA', 'MAINE' => 'ME',
								'MARSHALL ISLANDS' => 'MH', 'MARYLAND' => 'MD', 'MASSACHUSETTS' => 'MA', 'MICHIGAN' => 'MI',
								'MINNESOTA' => 'MN', 'MISSISSIPPI' => 'MS', 'MISSOURI' => 'MO', 'MONTANA' => 'MT',
								'NEBRASKA' => 'NE', 'NEVADA' => 'NV', 'NEW HAMPSHIRE' => 'NH', 'NEW JERSEY' => 'NJ',
								'NEW MEXICO' => 'NM', 'NEW YORK' => 'NY', 'NORTH CAROLINA' => 'NC', 'NORTH DAKOTA' => 'ND',
								'NORTHERN MARIANA ISLANDS' => 'MP','OHIO' => 'OH', 'OKLAHOMA' => 'OK', 'OREGON' => 'OR',
								'PALAU' => 'PW', 'PENNSYLVANIA' => 'PA', 'PUERTO RICO' => 'PR', 'RHODE ISLAND' => 'RI',
								'SOUTH CAROLINA' => 'SC', 'SOUTH DAKOTA' => 'SD', 'TENNESSEE' => 'TN', 'TEXAS' => 'TX',
								'UTAH' =>'UT', 'VERMONT' => 'VT', 'VIRGIN ISLANDS' => 'VI', 'VIRGINIA' => 'VA', 'WASHINGTON' => 'WA',
								'WEST VIRGINIA' => 'WV', 'WISCONSIN' => 'WI', 'WYOMING' => 'WY' );
								
			if(isset($data['BillingState']))
				$data['BillingState'] = (isset($us_states[ strtoupper($data['BillingState']) ])) ? $us_states[ strtoupper($data['BillingState']) ] : strtoupper(substr($data['BillingState'],0,2));
			if(isset($data['DeliveryState']))
				$data['DeliveryState'] = (isset($us_states[ strtoupper($data['DeliveryState']) ])) ? $us_states[ strtoupper($data['DeliveryState']) ] : strtoupper(substr($data['DeliveryState'],0,2));	
		}else {
			unset($data['BillingState']);
			unset($data['DeliveryState']);
		}
	}
	
	// filter
	function _address_fields_filter($name, $value){
		// reuse parent filter unless needed
		switch($name){
			default:
				 $value = parent::_address_field_filter($name, $value);		
			break;
		}	
		// return 
		return $value;
	}	
	
	// verify callback 
	function _verify_callback(){	
		// Did not find expected POST variables. Possible access attempt from a non sagepay site.
		return (isset($_POST['VPSTxId']) && isset($_POST['Status'])) ? true : false;		
	}
	/**
	 * Cancel Recurring Subscription
	 * This is not a private function
	 * @param int/string $trans_ref	
	 * @param int $user_id	
	 * @param int/string $subscr_id	
	 * @param int $pack_id	
	 * @return boolean
	 */	
	function cancel_recurring_subscription($trans_ref = null, $user_id = null, $subscr_id = null, $pack_id = null) {
		//if coming form process return after a subscription payment
		if(!empty($trans_ref)) {
			$transdata = $this->_get_transaction_passthrough($trans_ref);
			if($transdata['payment_type'] != 'subscription_purchase')
				return false;				
					
			$user_id = $transdata['user_id'];
							
			if(isset($transdata['is_another_membership_purchase']) && $transdata['is_another_membership_purchase'] == 'Y') {
				$member = mgm_get_member_another_purchase($user_id, $transdata['membership_type']);			
			}else {
				$member = mgm_get_member($user_id);			
			}
			
			if(isset($member->payment_info->module) && !empty($member->payment_info->module)) {					
				if(isset($member->payment_info->subscr_id)) {
					$subscr_id = $member->payment_info->subscr_id; 
				}else {
					//check pack is recurring:
					$pid = $pack_id ? $pack_id : $member->pack_id;
					
					if($pid) {
						$s_packs = mgm_get_class('subscription_packs');
						$sel_pack = $s_packs->get_pack($pid);												
						if($sel_pack['num_cycles'] != 1)
							$subscr_id = 0;// 0 stands for a lost subscription id
					}										
				}
												
				//check for same module: if not call the same function of the applicale module.
				if(str_replace('mgm_','' , $member->payment_info->module) != str_replace( 'mgm_','' , $this->code ) ) {
					
					mgm_log('RECALLing '. $member->payment_info->module .': cancel_recurring_subscription FROM: ' . $this->code);
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}
				
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;
				
			}else 
				return false;
		}	
		
		$system_obj = mgm_get_class('system');
		$dge    = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		//send email only if setting enabled
		if((!empty($subscr_id) || $subscr_id === 0) && !$dge) {
			$user = get_userdata($user_id);
			//send notification email to admin:
			$message = (__('The User: ', 'mgm')). $user->user_email.' ('. $user_id .') '.(__('has upgraded/cancelled subscription.', 'mgm'));
			$message .= "<br/>" .__('Please unsubscribe the user from Gateway Merchant panel.', 'mgm');
			if($subscr_id)
				$message .= "<br/><br/>" .__('Subscription Id: ','mgm' ) . $subscr_id;	
			if(isset($member->transaction_id))			
				$message .= "<br/>" .__('MGM Transaction Id:' ,'mgm' ) . $member->transaction_id;		
			//admin email:
			if(!empty($system_obj->setting['admin_email']))
				@mgm_mail($system_obj->setting['admin_email'], sprintf(__('[%s] User Subscription Cancellation', 'mgm'), get_option('blogname')), $message);
		}
		
		return true;
	}	
	
	// get custom var from mulple sources
	function _get_alternate_transaction_id(){
		// custom
		$alt_tran_id = '';
		// post
		if(isset($_POST['custom']) && !empty($_POST['custom'])){
			$alt_tran_id = $_POST['custom'];
		}elseif(isset($_GET['custom']) && !empty($_GET['custom'])){
			$alt_tran_id = strip_tags($_GET['custom']);
		}elseif(isset($_POST['VendorTxCode']) && !empty($_POST['VendorTxCode'])){
			$alt_tran_id = $_POST['VendorTxCode'];
		} 		
		// return 
		return $alt_tran_id;
	}
}

// end file