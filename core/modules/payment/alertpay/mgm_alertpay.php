<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Alertpay (Payza) Payment Module
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */ 
class mgm_alertpay extends mgm_payment{	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_alertpay();
	}
	
	// php4 construct
	function mgm_alertpay(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Payza';
		// logo
		$this->logo = $this->module_url( 'assets/payza.png' );
		// description
		$this->description = __('A comprehensive all-in-one solution for your payment needs. Payza is an account-based payment processor '.
								'allowing just about anyone with an email address to securely send and receive money with their credit card '.
								'or bank account.', 'mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');
		// trial support available ?
		$this->supports_trial= 'Y';		
		// cancellation support available ?
		$this->supports_cancellation= 'Y';	
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
							// set
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
				// alertpay specific
				$this->setting['ap_merchant']     = $_POST['setting']['ap_merchant'];
				$this->setting['ap_securitycode'] = $_POST['setting']['ap_securitycode'];
				$this->setting['ap_apipassword']  = $_POST['setting']['ap_apipassword'];
				$this->setting['currency']        = $_POST['setting']['currency'];												
				// purchase price
				if(isset($_POST['setting']['purchase_price'])){
					$this->setting['purchase_price']  = $_POST['setting']['purchase_price'];
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
				//  message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));
			break;
		}		
	}
	
	// return process api hook, link back to site after payment is made
	function process_return() {		
		// check and show message
		if(isset($_REQUEST['apc_1'])){
			// query arg
			$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($_REQUEST['apc_1']));
			// is a post redirect?
			if(isset($_REQUEST['apc_1'])){				
				// is a post redirect?
				$post_redirect = $this->_get_post_redirect($_REQUEST['apc_1']);
				// set post redirect
				if($post_redirect !== false){
					$query_arg['post_redirect'] = $post_redirect;
				}	
				// is a register redirect?				
				$register_redirect = $this->_auto_login($_REQUEST['apc_1']);	
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
			// set success for test
			if( isset($_POST['ap_test']) && $_POST['ap_test'] == 1 ){
				$_POST['ap_status'] = 'Success';
			}						
			// log data before validate
			$tran_id = $this->_log_transaction();						
			// payment type
			$payment_type = $this->_get_payment_type($_POST['apc_1']);
			// custom
			$custom = $this->_get_transaction_passthrough($_POST['apc_1']);
			// hook for capture
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
	
	// unsubscribe process, proxy for unsubscribe
	function process_unsubscribe(){
		// get user id
		$user_id = $_POST['user_id'];
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// get user
		$user = get_userdata($user_id); 		
		//multiple membesrhip level update:
		$member = mgm_get_member($user_id);
		// check
		if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type'])
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);	
		
		// init	
		$cancel_account = true;		
		// check
		if(isset($member->payment_info->module) && $member->payment_info->module == $this->code) {// self check
			$subscr_id = null;				
			if(!empty($member->payment_info->subscr_id))
				$subscr_id = $member->payment_info->subscr_id;
			elseif (!empty($member->pack_id)) {	
				//check the pack is recurring
				$s_packs = mgm_get_class('subscription_packs');				
				$sel_pack = $s_packs->get_pack($member->pack_id);										
				if($sel_pack['num_cycles'] != 1) 
					$subscr_id = 0;// 0 stands for a lost subscription id
			}

			// cancel at alertpay
			$cancel_account = $this->cancel_recurring_subscription(null, $user_id, $subscr_id);	
		}
			
		// cancel in MGM
		if($cancel_account === true){
			$this->_cancel_membership($user_id, true);// redirected
		}

		// message
		$message = is_string($cancel_account) ? $cancel_account : __('Error while cancelling subscription', 'mgm') ;
		// force full url, bypass custom rewrite bug
		// mgm_redirect(add_query_arg(array('unsubscribe_errors'=>urlencode($message)),site_url('wp-admin/profile.php?page=mgm/profile')));	
		// issue #1521
		if( $is_admin ){
			mgm_redirect( add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php')) );
		}	
		// redirect		
		mgm_redirect(mgm_get_custom_url('membership_details', false, array('unsubscribe_errors'=>urlencode($message))));			
	}
	
	// process html_redirect, proxy for form submit
	function process_html_redirect() {
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
		$button_code     = $this->_get_button_code($tran['data'],$tran_id);
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
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					<input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
					<input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">
					<div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
			   </form>';				
		// return or print
		if ($return) return $html;
		
		// print
		echo $html;		
	}
	
	// unsubscribe button api hook
	function get_button_unsubscribe($options=array()){
		// action
		$action = add_query_arg(array('module'=>$this->code,'method'=>'payment_unsubscribe'), mgm_home_url('payments'));
		// message
		$message = sprintf(__('You have subscribed to <b>%s</b> via <b>%s</b>, if you wish to unsubscribe, please click the following link. <br>','mgm'), get_option('blogname'), $this->name);
		// html
		$html='<div class="mgm_margin_bottom_10px">
					<h4>'.__('Unsubscribe','mgm').'</h4>
					<div class="mgm_margin_bottom_10px">' . $message . '</div>
			   </div>
			   <form name="mgm_unsubscribe_form" id="mgm_unsubscribe_form" method="post" action="' . $action . '">
					<input type="hidden" name="user_id" value="' . $options['user_id'] . '"/>
					<input type="hidden" name="membership_type" value="' . $options['membership_type'] . '"/>
					<input type="button" name="btn_unsubscribe" value="' . __('Unsubscribe','mgm') . '" onclick="confirm_unsubscribe(this)" class="button" />	
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
			$return .= '	<input type="hidden" name="'. $key .'" value="'. esc_html($value) .'" />' . "\n";
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
		// set num_cycles from pack obejct if $pack doesn't contain num_cycles value
		if( !isset($pack['num_cycles']) && isset($pack['pack_id']) ) {
			$subs_pack          = mgm_get_class('subscription_packs')->get_pack($pack['pack_id']);
			$pack['num_cycles'] = $subs_pack['num_cycles']; 
		}
		//pack currency over rides genral setting currency - issue #1602
		if(!isset($pack['currency']) || empty($pack['currency'])){
			$pack['currency']=$this->setting['currency'];
		}		
		// setup data array		
		$data = array(
			'ap_merchant'     => $this->setting['ap_merchant'],
			'ap_itemcode'     => $tran_id,
			'ap_itemname'     => $item['name'],		
			'ap_description'  => $item['name'],			
			'ap_currency'     => $pack['currency'],					
			'ap_cancelurl'    => $this->setting['cancel_url'],	
			'ap_amount'	      => $pack['cost']	,			
			'ap_contactemail' => $user->user_email,
		);
				
		// address fields,see parent for all fields, only different given here
		$this->_set_address_fields($user, $data);		
		
		// subscription purchase with ongoing/limited
		if( !isset($pack['buypost']) && isset($pack['duration_type']) && $pack['num_cycles'] != 1 ){ // does not support one-time recurring
		// if ($pack['num_cycles'] != 1 && $pack['duration_type']) { // old style
			// type
			$data['ap_purchasetype'] = 'subscription';
     		// unit types
			$unit_types = array('d'=>'Day', 'w'=>'Week', 'm'=>'Month', 'y'=>'Year');
			// interval
			$data['ap_timeunit']     = $unit_types[$pack['duration_type']]; // day|month|year
			$data['ap_periodlength'] = $pack['duration'];
			
			// greater than 0, limited
			if ( (int)$pack['num_cycles'] > 0) {
				$data['ap_periodcount'] = $pack['num_cycles'];
			}
			// trial
			if ($pack['trial_on']) {
				$data['ap_trialamount']       = $pack['trial_cost'];
				$data['ap_trialperiodlength'] = $pack['trial_duration'];
				$data['ap_trialtimeunit']     = $unit_types[$pack['trial_duration_type']];				
			}			
		}else{
		// post purchase/one time billing

			$data['ap_purchasetype'] = 'item';
			// apply addons
			$this->_apply_addons($pack, $data, array('amount'=>'ap_amount','description'=>'ap_description'));			
		}  
		
		// custom passthrough
		$data['apc_1'] = $tran_id;
		
		// set apc on request so that it can be tracked for post purchase
		$data['ap_returnurl'] = add_query_arg(array('apc_1'=>$data['apc_1']), $this->setting['return_url']);
		
		// update currency - issue #1602
/*		if($pack['currency'] != $this->setting['currency']){
			$pack['currency'] = $this->setting['currency'];
		}*/		
		
		// add filter @todo test
		$data = apply_filters('mgm_payment_button_data', $data, $tran_id, $this->module, $pack);
		
		// update pack/transaction
		mgm_update_transaction(array('data'=>json_encode($pack),'module'=>$this->module), $tran_id);
		
		// return data
		return $data;
	}	
	
	// buy post
	function _buy_post() {
		global $wpdb;
		// system
		$system_obj = mgm_get_class('system');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($_POST['apc_1']);
		// local var
		extract($custom);
		
		// find user
		$user = null;
		// check
		if(isset($user_id) && (int)$user_id > 0) $user = get_userdata($user_id);
		
		// errors
		$errors = array();
		// purchase status
		$purchase_status = 'Error';
		
		// status
		switch ($_POST['ap_status']) {
			case 'Success':
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';
					
				// transation id
				$transaction_id = $this->_get_transaction_id('apc_1');
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

			case 'Expired':
			case 'Failed':
			case 'Canceled':
				// status
				$status_str = __('Last payment was refunded or denied','mgm');
				// purchase status
				$purchase_status = 'Failure';
				
				// error
				$errors[] = $status_str;												  
			break;

			case 'Pending':
				// status
				$status_str = __('Last payment is pending. Reason: Unknown','mgm');
				// purchase status
				$purchase_status = 'Pending';	

				// error
				$errors[] = $status_str;				
			break;

			default:
				// status
				$status_str = sprintf(__('Last payment status: %s','mgm'),$_POST['ap_status']);		
				// purchase status
				$purchase_status = 'Unknown';
				
				// error
				$errors[] = $status_str;
																							  
		}
		
		// do action
		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new, individual
		do_action('mgm_return_post_purchase_payment', array('post_id' => $post_id));// new, global 
			
		// init
		$status = __('Failed join', 'mgm'); //overridden on a successful payment
		// check status
		if ( $purchase_status == 'Success' ) {
			// mark as purchased
			if( isset($user->ID) ){	// purchased by user	
				// call coupon action
				do_action('mgm_update_coupon_usage', array('user_id' => $user_id));		
				// set as purchased	
				$this->_set_purchased($user_id, $post_id, NULL, $alt_tran_id);
			}else{
				// purchased by guest
				if( isset($guest_token) ){
					// issue #1421, used coupon
					if(isset($coupon_id) && isset($coupon_code)) {
						// call coupon action
						do_action('mgm_update_coupon_usage', array('guest_token' => $guest_token,'coupon_id' => $coupon_id));
						// set as purchased
						$this->_set_purchased(NULL, $post_id, $guest_token, $alt_tran_id, $coupon_code);
					}else {
						$this->_set_purchased(NULL, $post_id, $guest_token, $alt_tran_id);				
					}
				}
			}	

			// status
			$status = __('The post was purchased successfully', 'mgm');
		}

		// transaction status
		mgm_update_transaction_status($_POST['custom'], $status, $status_str);
		
		// blog
		$blogname = get_option('blogname');			
		// post being purchased			
		$post = get_post($post_id);

		// notify user and admin, only if gateway emails on	
		if ( ! $dpne ) {			
			// notify user
			if( isset($user->ID) ){
				// mgm post setup object
				$post_obj = mgm_get_post($post_id);
				// check
				if( $this->send_payment_email($alt_tran_id) ) {	
				// check
					if( mgm_notify_user_post_purchase($blogname, $user, $post, $purchase_status, $system_obj, $post_obj, $status_str) ){
					// update as email sent 
						$this->update_paymentemail_sent($alt_tran_id);
					}	
				}					
			}			
		}
		
		// notify admin, only if gateway emails on
		if ( ! $dge ) {
			// notify admin, 
			mgm_notify_admin_post_purchase($blogname, $user, $post, $status);
		}
		
		// error condition redirect
		if(count($errors)>0){
			mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
		}		
	}
	
	// buy membership
	function _buy_membership() {	
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($_POST['apc_1']);
		// local var
		extract($custom);
		
		// currency
		if (!$currency) $currency = $system_obj->get_setting('currency');
		
		// find user
		$user = get_userdata($user_id);
		// another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
			$member = mgm_get_member_another_purchase($user_id, $custom['membership_type']);			
		}else {
			$member = mgm_get_member($user_id);			
		}
		
		// Get the current AC join date		
		if (!$join_date = $member->join_date) $member->join_date = time(); // Set current AC join date		

		//if there is no duration set in the user object then run the following code
		if (empty($duration_type)) {
			// if there is no duration type then use Months
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
		// $member->payment_type    = ($_POST['ap_purchasetype']=='subscription' ) ? 'subscription' : 'one-time';
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		// payment info for unsubscribe		
		if(!isset($member->payment_info)) $member->payment_info = new stdClass;
		// module
		$member->payment_info->module = $this->code;
		// set txn_type
		if(isset($_POST['ap_purchasetype'])){
			$member->payment_info->txn_type = $_POST['ap_purchasetype'];
		}	
		// set subscr_id
		if(isset($_POST['ap_subscriptionreferencenumber'])){
			$member->payment_info->subscr_id = $_POST['ap_subscriptionreferencenumber'];
		}	
		// txn_id
		if(isset($_POST['ap_referencenumber'])){
			$member->payment_info->txn_id = $_POST['ap_referencenumber'];
		}	
		// mgm transaction id
		$member->transaction_id = $_POST['apc_1'];
		// process PayPal response
		$new_status = $update_role = false;
		// status
		switch ($_POST['ap_status']) {
			case 'Success':
			case 'Subscription-Payment-Success':
				$new_status = MGM_STATUS_ACTIVE;
				$member->status_str = __('Last payment was successful','mgm');					
				
				// old type match
				$old_membership_type = mgm_get_user_membership_type($user_id, 'code');
				if ($old_membership_type != $membership_type) {
					$member->join_date = time(); // type join date as different var
				}
				// old content hide
				$member->hide_old_content = $hide_old_content; 
				
				// current
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
							// update expire
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
					}else {
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
				
				//cancel previous subscription:	issue#: 565	
				$this->cancel_recurring_subscription($_POST['apc_1'], null, null, $pack_id);
				
				//clear cancellation status if already cancelled:
				if(isset($member->status_reset_on)) unset($member->status_reset_on);
				if(isset($member->status_reset_as)) unset($member->status_reset_as);
						
				// role
				if ($role) $update_role = true;									
				
				// transaction_id
				$transaction_id = $this->_get_transaction_id('apc_1');
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
			case 'Subscription-Expired':
			case 'Subscription-Payment-Failed':
			case 'Subscription-Canceled':
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				break;

			case 'Subscription-Payment-Rescheduled':
				$new_status = MGM_STATUS_PENDING;

				$reason = 'Unknown';
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				break;

			default:
				$new_status = MGM_STATUS_ERROR;
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $_POST['payment_status']);
				break;
		}

		// handle exceptions from the subscription specific fields
		if ($new_status == MGM_STATUS_ACTIVE && in_array($_POST['txn_type'], array('subscr_failed', 'subscr_eot'))) {
			$new_status = MGM_STATUS_NULL;
			$member->status_str = __('The subscription is not active','mgm');
		}
		
		// old status
		$old_status = $member->status;	
		// set new status
		$member->status = $new_status;
		
		// whether to acknowledge the user - This should happen only once
		$acknowledge_user = $this->send_payment_email($_POST['apc_1']);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $_POST['apc_1']);
		
		// another_subscription modification
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
		
		// update role
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
		
		// transaction status
		mgm_update_transaction_status($member->transaction_id, $member->status, $member->status_str);
			
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
	
	// cancel membership
	function _cancel_membership($user_id, $redirect = false){
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// find user
		$user = get_userdata($user_id);	
		$member = mgm_get_member($user_id);		
		// multiple membesrhip level update:	
		$multiple_update = false;		
		// check
		if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']){
			$multiple_update = true;	
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);
		} 
						
		// get pack
		if($member->pack_id){
			$subs_pack = $s_packs->get_pack($member->pack_id);
		}else{
			$subs_pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
		}
				
		// reset payment info
		$data['TXN_TYPE'] = 'subscription_cancel';		
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'TXN_TYPE', 'txn_id'=>'REFERENCENUMBER');
		// save tracking fields
		$this->_save_tracking_fields($tracking_fields, $member, $data);	
		
		// types
		$duration_exprs = $s_packs->get_duration_exprs();
						
		// default expire date				
		$expire_date = $member->expire_date;
		// if lifetime:
		if($member->duration_type == 'l') $expire_date = date('Y-m-d');				
			
		// if trial on 
		if ($subs_pack['trial_on'] && isset($duration_exprs[$subs_pack['trial_duration_type']])) {			
			// if cancel data is before trial end, set cancel on trial expire_date
			$trial_expire_date = strtotime("+{$subs_pack['trial_duration']} {$duration_exprs[$subs_pack['trial_duration_type']]}", $member->join_date);
			
			// if lower
			if(time() < $trial_expire_date){
				$expire_date = date('Y-m-d',$trial_expire_date);
			}
		}	
		
		// transaction_id	
		$trans_id = $member->transaction_id;
		// if today 
		if($expire_date == date('Y-m-d')){
			// status
			$new_status          = MGM_STATUS_CANCELLED;
			$new_status_str      = __('Subscription cancelled','mgm');
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
			$member->status     = $new_status;
			$member->status_str = $new_status_str;		
			// set reset date
			$member->status_reset_on = $expire_date;
			$member->status_reset_as = MGM_STATUS_CANCELLED;
		}

		// multiple membesrhip level update:	
		if($multiple_update){
			mgm_save_another_membership_fields($member, $user_id);		
		}else{			
			$member->save();
		}	
		
		// transaction status
		mgm_update_transaction_status($trans_id, $new_status, $new_status_str);
			
		// send email notification to client
		$blogname = get_option('blogname');
		
		// notify user
		if( ! $dpne ) {
			// notify user
			mgm_notify_user_membership_cancellation($blogname, $user, $member, $system_obj, $new_status, $membership_type);			
		}
		// notify admin
		if ( ! $dge ) {
			// notify admin	
			mgm_notify_admin_membership_cancellation($blogname, $user, $member);
		}
		
		// after cancellation hook
		do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));	
		
		// redirect only internal
		if( $redirect ) {		
			// message
			$lformat = mgm_get_date_format('date_format_long');
			$message = sprintf(__("You have successfully unsubscribed. Your account has been marked for cancellation on %s", "mgm"), 
			                     ($expire_date == date('Y-m-d') ? 'Today' : date($lformat, strtotime($expire_date))));
			//issue #1521
			if( $is_admin ){
				mgm_redirect( add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php')) );
			}		
			// redirect 
			mgm_redirect(mgm_get_custom_url('membership_details', false,array('unsubscribed'=>'true','unsubscribe_errors'=>urlencode($message))));
		}	
	}
	
	/**
	 * Cancel Recurring Subscription
	 * This is not a private function
	 * @param int/string $trans_ref	
	 * @param int $user_id	
	 * @param int/string $subscr_id	
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
				$subscr_id = null;				
				if(!empty($member->payment_info->subscr_id))
					$subscr_id = $member->payment_info->subscr_id;
				elseif (!empty($member->pack_id)) {	
					//check the pack is recurring
					$s_packs = mgm_get_class('subscription_packs');				
					$sel_pack = $s_packs->get_pack($member->pack_id);										
					if($sel_pack['num_cycles'] != 1) 
						$subscr_id = 0;// 0 stands for a lost subscription id
				}
												
				//check for same module: if not call the same function of the applicale module.
				if(str_replace('mgm_','' , $member->payment_info->module) != str_replace( 'mgm_','' , $this->code ) ) {					
					// mgm_log('RECALLing '. $member->payment_info->module .': cancel_recurring_subscription FROM: ' . $this->code);
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;
				
			}else 
				return false;
		}
		
		// when a subscription found
		if($subscr_id) {
			$user = get_userdata($user_id);
			$date_format = mgm_get_date_format('date_format');
			// post data
			$post_data = array('USER'=>$this->setting['ap_merchant'],'PASSWORD'=>$this->setting['ap_apipassword'],
							   'SUBSCRIPTIONREFERENCE'=>$subscr_id,
							   'NOTE'=>sprintf('Cancellation selected by member: "%s", ID: %d on: %s', $user->user_email, $user->ID, date($date_format)), 
							   'TESTMODE'=>( $this->status == 'test' ?  '1' : '0'));		
			// url
			$unsubscribe_url =  $this->_get_endpoint('unsubscribe');
			// headers
			$http_headers = array();			
			// create curl post				
			$http_response = mgm_remote_post($unsubscribe_url, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
			// parse
			@parse_str($http_response, $data);			
			// verify
			if(isset($data['REFERENCENUMBER']) && $data['RETURNCODE'] == 100){
				return true;
			}elseif (isset($data['DESCRIPTION']))
				//error string
				return $data['DESCRIPTION'];
		}elseif ( is_null($subscr_id) || $subscr_id === 0 ) {
			//send email to admin if subscription Id is absent		
			$system_obj = mgm_get_class('system');
			$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
			// check
			if( ! $dge ) {
				// blog
				$blogname = get_option('blogname');
				// user
				$user = get_userdata($user_id);
				// notify admin
				mgm_notify_admin_membership_cancellation_manual_removal_required($blogname, $user, $member);				
			}	
			// return	
			return true;
		}
		// return	
		return false;
	}
	
	// default setting
	function _default_setting(){
		// alertpay specific
		$this->setting['ap_merchant']      = get_option('admin_email');
		$this->setting['ap_securitycode']  = '';
		$this->setting['ap_apipassword']   = '';
		$this->setting['currency']         = mgm_get_class('system')->setting['currency'];
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
		// check
		if($this->_is_transaction($_POST['apc_1'])){	
			// tran id
			$tran_id = (int)$_POST['apc_1'];			
			// return data				
			if(isset($_POST['ap_purchasetype'])){
				$option_name = $this->module.'_'.strtolower($_POST['ap_purchasetype']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_POST)));
			
			// options 
			$options = array('ap_purchasetype','ap_subscriptionreferencenumber','ap_referencenumber');
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
	/*
	function _get_transaction_id(){
		// validate
		if($this->_is_transaction($_POST['apc_1'])){	
			// tran id
			return $tran_id = (int)$_POST['apc_1'];
		}
		// return 
		return 0;	
	}
	*/

	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	/*
	// curl post
	function _curl_post($url, $post, $http_header=array()){
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url); 	
		// when set
		if(is_array($http_header)){	
			curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);	
		}		
		curl_setopt($ch, CURLOPT_HEADER, 0);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
		curl_setopt($ch, CURLOPT_NOPROGRESS, 1); 
		curl_setopt($ch, CURLOPT_VERBOSE, 1); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); 
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
		curl_setopt($ch, CURLOPT_USERAGENT, 'Magic Members Membership Software'); 
		curl_setopt($ch, CURLOPT_REFERER, get_option('siteurl')); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
	    $buffer = curl_exec($ch);	
		curl_close($ch);		
		// return
		return $buffer;
	}*/
	
	// verify callback 
	function _verify_callback(){	
		// system
		$system_obj = mgm_get_class('system');				
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		// ip ranges
		$ip_range = array('174.142.185.131','174.142.185.132','108.163.136.234','108.163.136.235');
	    // check
		if (isset($_POST['ap_securitycode']) && $_POST['ap_securitycode'] == $this->setting['ap_securitycode']) {
       		// valid
			return true;
	  	}
		// error 
		return false;	
	}	
	
	// setup
	function _setup_endpoints($end_points = array()){		
		// define defaults
		$end_points_default = array('test'        => 'https://sandbox.payza.com/sandbox/payprocess.aspx',
									'live'        => 'https://secure.payza.com/checkout',
									'unsubscribe' => 'https://api.payza.com/svc/api.svc/CancelSubscription'); // cancel subscription	
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// set 
	function _set_address_fields($user, &$data){
		// mappings
		$mappings= array('first_name'=>'ap_fname','last_name'=>'ap_lname','address'=>array('ap_addressline1','ap_addressline2'),
		                 'city'=>'ap_city','state'=>'ap_stateprovince', 'zip'=>'ap_zippostalcode','country'=>'ap_country',
						 'phone'=>'ap_contactphone');
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter'));				 
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
	
	
}

// end file