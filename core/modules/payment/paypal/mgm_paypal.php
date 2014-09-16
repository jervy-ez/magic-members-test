<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * PayPal Standard Payment Module, integrates paypal standard / html form method
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */ 
class mgm_paypal extends mgm_payment{	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_paypal();
	}
	
	// php4 construct
	function mgm_paypal(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Paypal Standard';// product name
		// label
		$this->label = 'PayPal';	
		// logo
		$this->logo = $this->module_url( 'assets/paypal.jpg' );
		// description
		$this->description = __('PayPal is an e-money service allowing payments and money transfers to be made through the Internet. '.
		                        'PayPal operates with credit cards, debit cards, bank accounts and PayPal balance to make safe purchases online, '.
								'without disclosing your credit card number or financial information.', 'mgm');
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
		// if supports rebill status check via API, paypal standard does not support API status check yet	
		// $this->supports_rebill_status_check = 'N';
		// endpoints
		$this->_setup_endpoints();		
		// button message
		$this->button_message = 'Make payments with PayPal - it\'s fast, free and secure!';
		// default settings
		$this->_default_setting();
		// set path
		parent::set_tmpl_path();
		// read settings
		$this->read();	
	}
	
	// MODULE SETTINGS API CALLBACKS ------------------------------------------------------------------- 
	
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
							// set logo
							$this->logo = $_POST['logo_new_'.$this->code];
							// save object options
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
				// paypal specific
				$this->setting['business_email'] = $_POST['setting']['business_email'];
				$this->setting['currency']       = $_POST['setting']['currency'];								
				$this->setting['locale']         = $_POST['setting']['locale'];
				$this->setting['username'] 		 = $_POST['setting']['username'];
				$this->setting['password']  	 = $_POST['setting']['password'];
				$this->setting['signature'] 	 = $_POST['setting']['signature'];	
				$this->setting['return_method']  = $_POST['setting']['return_method'];
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
				// fix old data
				$this->hosted_payment = 'Y';	
				// setup callback messages				
				$this->_setup_callback_messages($_POST['setting']);
				// re setup callback urls
				$this->_setup_callback_urls($_POST['setting']);
				// re setup endpoints
				$this->_setup_endpoints();						
				// save object options
				$this->save();
				// message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));
			break;
		}		
	}
	
	// MODULE PROCESSING API CALLBACKS ------------------------------------------------------------------- 
	
	// return process api hook, link back to site after payment is made
	function process_return() {
		// NOTE: as of ISSUE ID 152, no post data is sent back to server even if rm =2 is set
		// this works ok on paypal sandbox but not on live, treat success always for now
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// check and show message
		// if((isset($_POST['custom']) && !empty($_POST['custom'])) || (isset($_POST['auth']) && !empty($_POST['auth']))){
		if( isset($alt_tran_id) && !empty($alt_tran_id) ){
			// query arg
			$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($alt_tran_id));
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
		//this is to confirm module for IPN POST
		//fix for issue#: 528
		if(!$this->_confirm_notify()) {
			return;
		}		
		// custom var
		$alt_tran_id = $this->_get_alternate_transaction_id();	
		/*// check invoice
		if(!empty($_POST['rp_invoice_id']) ) $_POST['custom'] = $_POST['rp_invoice_id'];	*/
		// verify 		
		if($this->_verify_callback()){ // verify paypal payment data				
			// log data before validate
			$tran_id = $this->_log_transaction();																	
			// for test mode which automatically marks all ipns as paid
			if (isset($_POST['test_ipn']) && isset($_POST['txn_type']) && !in_array($_POST['txn_type'], array('subscr_signup','subscr_failed'))) {
				$_POST['payment_status'] = 'Processed';
			}						
			// exit
			$exit_statuses = array('In-Progress', 'Partially-Refunded');
			// handle cases that the system must ignore
			if (isset($_POST['payment_status']) && in_array($_POST['payment_status'], $exit_statuses)) {
				exit;
			}	
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
					// txn type
					$txn_type = isset($_POST['txn_type']) ? $_POST['txn_type'] : '';
					// after capturing txn type, if need to handle other cases		
					do_action('mgm_notify_pre_subscription_process_'.$this->module, array('tran_id'=>$tran_id, 'custom'=>$custom, 'txn_type'=>$txn_type));
					// switch
					switch($txn_type){
						case 'subscr_cancel':						
						case 'recurring_payment_profile_cancel':
						case 'recurring_payment_suspended':							
						// cancellation
							$this->_cancel_membership($custom['user_id']); //run the code to process a membership cancellation
						break;
						case 'subscr_eot':
							$this->_expire_membership($custom['user_id']);
						break;
						case 'subscr_signup':
						case 'subscr_payment':
						// one-time payment		
						case 'web_accept':			
							// new subscription 						
							$this->_buy_membership(); //run the code to process a new/extended membership
							// subscr_payment is not sent when trial is set as 0, #287		
						break;			
						default:
						// all other notifications
							$this->process_status_notify();						
						break;
					}						
				break;							
			}
			// after process		
			do_action('mgm_notify_post_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));				
		}else {
			//Note: Keep the below log: This is to log posts from IPN as there are issues related to recurring IPN POST
			mgm_log('FROM PAYPAL process_notify: VERIFY Failed', $this->module);	
		}
		// after process unverified		
		do_action('mgm_notify_post_process_unverified_'.$this->module);		

		// 200 OK to gateway, only external		
		if( ! headers_sent() ){
			@header('HTTP/1.1 200 OK');
			exit('OK');
		}	
	}	
	
	// status notify process api hook, background INS url, 
	// can not use for paypal standard as notify url once set via payment form, being used always
	function process_status_notify(){
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );		
		
		// custom var
		$alt_tran_id = $this->_get_alternate_transaction_id();	
		
		// custom
		$custom = $this->_get_transaction_passthrough($alt_tran_id);
		extract($custom);
			
		// process rebill,treat invoked by paypal
		$this->process_rebill_status($user_id); 
		
		// 200 OK to gateway, only external		
		if( ! headers_sent() ){
			@header('HTTP/1.1 200 OK');
			exit('OK');
		}
	}
	
	// rebill status check
	function process_rebill_status($user_id, $member=NULL){
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		
		// member 
		if(!$member) $member = mgm_get_member($user_id);
		
		// return
		// return $this->api_status_check($member);
		return false;
	}
	
	// process cancel api hook 
	function process_cancel(){
		// redirect to cancel page
		mgm_redirect(add_query_arg(array('status'=>'cancel'), $this->_get_thankyou_url()));
	}	
	
	// unsubscribe process, post process for unsubscribe 
	function process_unsubscribe() {
		// get user id
		$user_id = $_POST['user_id'];
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// get user
		$user = get_userdata($user_id); 
		// multiple membesrhip level update:
		$member = mgm_get_member($user_id);
		// check multiple membership
		if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type'])
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);	
		
		// init			
		$cancel_account = true;
		// check					
		if(isset($member->payment_info->module) && $member->payment_info->module == $this->code) {// self check
			$subscr_id = null;
			$skip_cancelreq = false;				
			if(!empty($member->payment_info->subscr_id))
				$subscr_id = $member->payment_info->subscr_id;
			elseif (!empty($member->pack_id)) {	
				//check the pack is recurring
				$s_packs = mgm_get_class('subscription_packs');				
				$sel_pack = $s_packs->get_pack($member->pack_id);										
				// if onetime skip sending cancellation request				
				if($sel_pack['num_cycles'] == 1)
					$skip_cancelreq = true;
				else	 
					$subscr_id = 0;// 0 stands for a lost subscription id
			}
			
			// cancel at paypal
			if ( ! $skip_cancelreq ) {
				$cancel_account = $this->cancel_recurring_subscription(null, $user_id, $subscr_id);				
			}
		}
		
		// cancel in MGM
		if($cancel_account === true) {
			$this->_cancel_membership($user_id, true);// redirected
		}

		// message
		$message = isset($cancel_account['L_SHORTMESSAGE0']) ? sprintf('Paypal %s: %s',$cancel_account['L_SHORTMESSAGE0'], $cancel_account['L_LONGMESSAGE0']) :  __('Error while cancelling subscription', 'mgm') ;					
		// issue #1521
		if( $is_admin ){
			mgm_redirect( add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php')) );
		}
		// redirect to custom url:
		mgm_redirect(mgm_get_custom_url('membership_details', false,array('unsubscribe_errors'=>urlencode($message))));		
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
		$button_code = $this->_get_button_code($tran['data'], $tran_id);
		// extra code
		$additional_code = do_action('mgm_additional_code');
		// the html
		$html='<form action="'. $this->_get_endpoint() .'" method="post" class="mgm_form" name="' . $this->code . '_redirect_form" id="' . $this->code . '_redirect_form">
					'. $button_code .'					
					'. $additional_code .'						
					<img src="'.MGM_ASSETS_URL.'images/ajax/ajax-loader.gif"/><br>
					<b>'.sprintf(__('Please wait, you are being redirected to %s...','mgm'), $this->label).'</b>												
			  </form>				
			  <script language="javascript">document.' . $this->code . '_redirect_form.submit();</script>';
		
		// return 	  
		return $html;					
	}	
	
	// MODULE BUTTONS API CALLBACKS ------------------------------------------------------------------- 
		
	// subscribe button api hook
	function get_button_subscribe($options=array()){	
		// if payment initiaed from sidebar widget, do not use permalink : the current url		
		$include_permalink = (isset($options['widget'])) ? false : true;	
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect',$include_permalink) .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
				   <input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
				   <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
				   <input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->button_message . '">
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
					<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
					<input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->button_message . '">
					<div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
			   </form>';				
		// return or print
		if ($return) return $html;
		
		// print
		echo $html;		
	}
		
	// unsubscribe button api hook
	function get_button_unsubscribe($options=array()){		
		// if API credentials are given, use curl method to cancel asubscription
		if(	isset($this->setting['username']) && !empty($this->setting['username']) && 
				!empty($this->setting['password']) && !empty($this->setting['signature'])) 
		{
			$form_method_action = 'method="post" action="'. add_query_arg(array('module'=>$this->code,'method'=>'payment_unsubscribe'), mgm_home_url('payments')) .'"';
		}else {
			// else send the user to PAYPAL		
			$form_method_action = 'method="get" action="'. $this->_get_endpoint().'?cmd=_subscr-find&alias='. urlencode($this->setting['business_email']).'"';
		}
		// message
		$message = sprintf(__('You have subscribed to <b>%s</b> via <b>%s</b>, if you wish to unsubscribe, please click the following link. <br>','mgm'), get_option('blogname'), $this->name);						
		
		// html
		$html='<div class="mgm_margin_bottom_10px">
					<h4>'.__('Unsubscribe','mgm').'</h4>
					<div class="mgm_margin_bottom_10px">' . $message . '</div>
			   </div>
			   <form name="mgm_unsubscribe_form" id="mgm_unsubscribe_form" ' . $form_method_action . ' >			   		
			   		<input type="hidden" name="user_id" value="' . $options['user_id'] . '"/>
					<input type="hidden" name="membership_type" value="' . $options['membership_type'] . '"/>
					<input type="button" name="btn_unsubscribe" value="' . __('Unsubscribe','mgm') . '" onclick="confirm_unsubscribe(this)" class="button" />	
			   </form>';	
		// return
		return $html;		
	}								
	
	// MODULE INFO API CALLBACKS ------------------------------------------------------------------- 
	
	// get module transaction info
	function get_transaction_info($member, $date_format){				
		// data
		$subscription_id = $member->payment_info->subscr_id;
		$transaction_id  = $member->payment_info->txn_id;		
		// info
		$info = sprintf('<b>%s:</b><br>%s: %s<br>%s: %s', __('PAYPAL INFO','mgm'), __('SUBSCRIPTION ID','mgm'), $subscription_id, 
						__('TRANSACTION ID','mgm'), $transaction_id);		
		// set
		$transaction_info = sprintf('<div class="overline">%s</div>', $info);
		
		// return 
		return $transaction_info;
	}
	
	/**
	 * get gateway tracking fields for sync
	 *
	 * @todo process another subscription
	 */
	function get_tracking_fields_html(){
		// html
		$html = sprintf('<p>%s: <input type="text" size="20" name="paypal[subscriber_id]"/></p>
				 		 <p>%s: <input type="text" size="20" name="paypal[transaction_id]"/></p>', 
						 __('Subscription ID','mgm'), __('Transaction ID','mgm'));
		
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
		$data = $post_data['paypal'];
	 	// return
	 	return $this->_save_tracking_fields($fields, $member, $data); 			
	 }	
	 
	// MODULE API COMMON PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// get button code	
	function _get_button_code($pack, $tran_id=NULL) {
		// get data
		$data = $this->_get_button_data($pack, $tran_id);
		// strip 
		$data = mgm_stripslashes_deep($data);		
		// init
		$html = '';			
		// create return
		foreach ($data as $key => $value) {
			$html .= '<input type="hidden" name="'. $key .'" value="'. esc_html($value) .'" />' . "\n";
		}	
		// return
		return $html;
	}

	// get button data
	function _get_button_data($pack, $tran_id=NULL) {
		// system setting
		$system_obj = mgm_get_class('system');	
		$user_id = $pack['user_id'];	
		$user = get_userdata($user_id); 
		
		//pack currency over rides genral setting currency - issue #1602
		if(!isset($pack['currency']) || empty($pack['currency'])){
			$pack['currency']=$this->setting['currency'];
		}
			
		// item 		
		$item = $this->get_pack_item($pack);		
		// setup data array		
		$data = array(			
			'business'      => $this->setting['business_email'],
			'invoice'       => $tran_id,
			'item_name'     => $item['name'],
			'no_shipping'   => 1,
			'no_note'       => 1,
			'currency_code' => $pack['currency'],
			'lc'            => $this->setting['locale'],
			'notify_url'    => $this->setting['notify_url'],
			'cancel_return' => $this->setting['cancel_url'],
			'rm'            => (int)$this->setting['return_method'], // 0: GET, 1: GET, 2: POST
			'cbt'           => sprintf('Return to %s', get_option('blogname')),					
			'email'         => $user->user_email
		);
				
		// address fields
		$this->_set_address_fields($user, $data);			
		
		// subscription purchase with ongoing/limited
		if( !isset($pack['buypost']) && isset($pack['duration_type']) && $pack['num_cycles'] != 1){ // does not support one-time recurring
		// if ($pack['num_cycles'] != 1 && $pack['duration_type']) { // old style
			// command
			$data['cmd'] = '_xclick-subscriptions';
			// subs
			$data['a3'] = $pack['cost'];
			$data['p3'] = $pack['duration'];
			$data['t3'] = strtoupper($pack['duration_type']);
			$data['src'] = 1;
			$data['sra'] = 1;
			// greater than 0
			if ($pack['num_cycles']) {
				$data['srt'] = $pack['num_cycles'];
			}
			// trial
			if ($pack['trial_on']) {
				$data['a1'] = $pack['trial_cost'];
				$data['p1'] = $pack['trial_duration'];
				$data['t1'] = strtoupper($pack['trial_duration_type']);
			}			
		} else {
		// post purchase or one time billiing
			$data['cmd']    = '_xclick';
			$data['bn']     = 'PP-BuyNowBF';
			$data['amount'] = $pack['cost'];
			
			// Purchase Post Specifix
			if (isset($pack['buypost']) && $pack['buypost'] == 1 ) {				
				$data['src'] = 0;
				$data['sra'] = 0;												
			} else { // One time payment				
				$data['src'] = 1;
				$data['sra'] = 1;
			}
			
			// apply addons
			$this->_apply_addons($pack, $data, array('amount'=>'amount','description'=>'item_name'));// known field => module $data field
		}
		
		// custom passthrough
		$data['custom'] = $tran_id; 
		
		// set custom on request so that it can be tracked for post purchase
		$data['return'] = add_query_arg(array('custom'=>$data['custom']), $this->setting['return_url']);
		
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
		
		// custom var
		$alt_tran_id = $this->_get_alternate_transaction_id();	
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($alt_tran_id);
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
		switch ($_POST['payment_status']) {
			case 'Completed':
			case 'Processed':
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';											  
				
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

			case 'Reversed':
			case 'Refunded':
			case 'Denied':
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
				$status_str = sprintf(__('Last payment status: %s','mgm'),$_POST['payment_status']);
				// purchase status
				$purchase_status = 'Unknown';	

				// error
				$errors[] = $status_str;																						  
			break;
		}
		
		// do action
		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new, individual
		do_action('mgm_return_post_purchase_payment', array('post_id' => $post_id));// new, global 		
		
		// status
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
		mgm_update_transaction_status($alt_tran_id, $status, $status_str);
		
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
		
		// custom var
		$alt_tran_id = $this->_get_alternate_transaction_id();	
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($alt_tran_id);
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
		if (!$join_date = $member->join_date) $member->join_date = time(); // Set current AC join date\
		
		// if there is no duration set in the user object then run the following code
		if (empty($duration_type)) {
			// if there is no duration type then use Months
			$duration_type = 'm';
		}
		// membership type default
		if (empty($membership_type)) {
			// if there is no account type in the custom string then use the existing type
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
		// exit flag
		$exitif_subscr_signup = true;
		
		// if trial on	
		if (isset($custom['trial_on']) && $custom['trial_on'] == 1) {
			$member->trial_on            = $custom['trial_on'];
			$member->trial_cost          = $custom['trial_cost'];
			$member->trial_duration      = $custom['trial_duration'];
			$member->trial_duration_type = $custom['trial_duration_type'];
			$member->trial_num_cycles    = $custom['trial_num_cycles'];
			
			// 0 cost trial does not send payment_status, make check here
			// this should be causing trouble for MGA #969, mis firing payment success for failed payment, add support
			if($member->trial_cost == 0 && !isset($_POST['payment_status'])){				
				// when subscr_id present, treat it as Processed , #287 issue, with trial cost is 0, only subscr_signup is sent without payment_status
				// with trial cost > 0, subscr_signup and subscr_payment sent with payment_status
				if(isset($_POST['subscr_id'])){
					$_POST['payment_status'] = 'Processed';
					$exitif_subscr_signup = false;
				}		
			}
		}
		// check this later:(will need to be commented if it is being saved in transactions data)	
		elseif ($subs_pack['trial_on']) {
			$member->trial_on            = $subs_pack['trial_on'];
			$member->trial_cost          = $subs_pack['trial_cost'];
			$member->trial_duration      = $subs_pack['trial_duration'];
			$member->trial_duration_type = $subs_pack['trial_duration_type'];
			$member->trial_num_cycles    = $subs_pack['trial_num_cycles'];
			
			// 0 cost trial does not send payment_status, make check here
			// this should be causing trouble for MGA #969, mis firing payment success for failed payment, add support
			if($member->trial_cost == 0 && !isset($_POST['payment_status'])){				
				// when subscr_id present, treat it as Processed , #287 issue, with trial cost is 0, only subscr_signup is sent without payment_status
				// with trial cost > 0, subscr_signup and subscr_payment sent with payment_status
				if(isset($_POST['subscr_id'])){
					$_POST['payment_status'] = 'Processed';
					$exitif_subscr_signup = false;
				}		
			}
		}			
		
		// exit scenarios
		if(!isset($_POST['payment_status']) || ($exitif_subscr_signup && isset($_POST['txn_type']) && $_POST['txn_type'] == 'subscr_signup')) {			
			exit;
		}
		
		// double check txn type for MGA#969, subscr_failed triggers successful payment for trial packs
		if(isset($_POST['txn_type']) && $_POST['txn_type'] == 'subscr_failed'){
			exit;
		}
		//pack currency over rides genral setting currency - issue #1602
		if(isset($subs_pack['currency']) && $subs_pack['currency'] != $currency){
			$currency =$subs_pack['currency'];
		}
		// member fields
		$member->duration        = $duration;
		$member->duration_type   = strtolower($duration_type);
		$member->amount          = $amount;
		$member->currency        = $currency;
		$member->membership_type = $membership_type;		
		$member->pack_id         = $pack_id;
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		
		//one time pack subscription id option become an issue #1507
		if(isset($subs_pack['num_cycles']) == 1 && !isset($_POST['subscr_id'])){
			$_POST['subscr_id'] = 'ONE-TIME SUBSCRIPTION';
		}				
		// tracking fields module_field => post_field, will be used to unsubscribe
		$tracking_fields = array('txn_type'=>'txn_type', 'subscr_id'=>'subscr_id', 'txn_id'=>'txn_id');
		// save tracking fields 
		$this->_save_tracking_fields($tracking_fields, $member);
		// check here: ->module is absent in payment_info, its is _save_tracking_fields
		// if (!isset($member->payment_info->module)) $member->payment_info->module = $this->code;		
		// set parent transaction id
		$member->transaction_id = $alt_tran_id;
		
		// process PayPal response
		$new_status = $update_role = false;
		// status
		switch ($_POST['payment_status']) {
			case 'Completed':
			case 'Processed':
				// status
				$new_status = MGM_STATUS_ACTIVE;
				$member->status_str = __('Last payment was successful','mgm');					
				
				// old type match
				$old_membership_type = mgm_get_user_membership_type($user_id, 'code');
				// set
				if ($old_membership_type != $membership_type) {
					$member->join_date = time(); // type join date as different var
				}
				// old content hide
				$member->hide_old_content = $hide_old_content; 
				
				$time = time();
				$last_pay_date = isset($member->last_pay_date) ? $member->last_pay_date : null;			
				// last pay
				$member->last_pay_date = date('Y-m-d', $time);				
				
				// as per version 1.0, there was chance of double process, with new separation logic for rebill, this is safe
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
					// consider trial duration if trial period is applicable
					if( isset($trial_on) && $trial_on == 1 && (!isset($member->trial_used) || (int)$member->trial_used < (int)$member->trial_num_cycles) ) {// is it the root of #1150 issue
						// Do it only once
						if(!isset($member->rebilled) && isset($member->active_num_cycles) && $member->active_num_cycles != 1 ) {							
							// set
							$time = strtotime("+{$trial_duration} {$duration_exprs[$trial_duration_type]}", $time);		
							// increment trial used, each IPN should increement this and extend
							$member->trial_used = (!$member->trial_used) ? 1 : ((int)$member->trial_used+1);												
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
							
				// update rebill: issue #: 489				
				if($member->active_num_cycles != 1 && (int)$member->rebilled < (int)$member->active_num_cycles) {
					// rebill
					$member->rebilled = (!$member->rebilled) ? 1 : ((int)$member->rebilled+1);	
				}	
				
				// cancel previous subscription:
				// issue#: 565				
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
			case 'Reversed':
			case 'Refunded':
			case 'Denied':
				// status
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				break;

			case 'Pending':
				// status
				$new_status = MGM_STATUS_PENDING;
				$reason = 'Unknown';
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				break;

			default:
				// status
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
		$acknowledge_user = $this->send_payment_email($alt_tran_id);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $_POST['custom']);
		
		// another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
			//issue #1227
			if($subs_pack['hide_old_content'])
				$member->hide_old_content = $subs_pack['hide_old_content']; 

			// save			
			mgm_save_another_membership_fields($member, $user_id);	

			// Multiple membership upgrade: first time
			if (isset($custom['multiple_upgrade_prev_packid']) && is_numeric($custom['multiple_upgrade_prev_packid'])) {
				mgm_multiple_upgrade_save_memberobject($custom, $member->transaction_id);	
			}
		}else {
			// save	
			$member->save();			
		}	
		
		// status change event
		do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);		
		
		//update coupon usage
		do_action('mgm_update_coupon_usage', array('user_id' => $user_id));
			
		// role update
		if ($update_role) {						
			$obj_role = new mgm_roles();				
			$obj_role->add_user_role($user_id, $role);
		}		
		
		// return action
		do_action('mgm_return_'.$this->module, array('user_id' => $user_id, 'acknowledge_user' => $acknowledge_user));// backward compatibility
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
		
		// for paypal only
		if( in_array($_POST['txn_type'], array( 'subscr_payment', 'subscr_signup','web_accept')) && 
			in_array($_POST['payment_status'], array( 'Processed', 'Completed')) ){ 
			$acknowledge_user = true;
		}else{
			$acknowledge_user = false;
		}

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
	function _cancel_membership($user_id = NULL, $redirect = false){
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));	
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// custom var
		$alt_tran_id = $this->_get_alternate_transaction_id();	
		
		// get form custom
		if( ! $user_id ) {
			// get passthrough, stop further process if fails to parse
			$custom = $this->_get_transaction_passthrough($alt_tran_id);
			// local var
			extract($custom);
		}
				
		// find user
		$user = get_userdata($user_id);
		$member = mgm_get_member($user_id);
		// multiple membesrhip level update:	
		$multiple_update = false;		
		// check
		if((isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']) || (isset($is_another_membership_purchase) && $is_another_membership_purchase == 'Y' )) {
			$multiple_update = true;	
			$multi_memtype = (isset($_POST['membership_type'])) ? $_POST['membership_type'] : $membership_type;
			$member = mgm_get_member_another_purchase($user_id, $multi_memtype);	
		}
				
		// Don't save if it is cancel request with an upgrade:
		if(isset($_POST['subscr_id']) && isset($member->payment_info->subscr_id) && $_POST['subscr_id'] != $member->payment_info->subscr_id) {			
			return;
		}
			
		// get pack
		if($member->pack_id){
			$subs_pack = $s_packs->get_pack($member->pack_id);
		}else{
			$subs_pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
		}
		
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'txn_type', 'subscr_id'=>'subscr_id', 'txn_id'=>'txn_id');
		// save tracking fields
		$this->_save_tracking_fields($tracking_fields, $member);	
		
		// types
		$duration_exprs = $s_packs->get_duration_exprs();
						
		// default expire date				
		$expire_date = $member->expire_date;	
		// life time
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
			// expire			
			$member->expire_date = date('Y-m-d');																																				
			// reassign expiry membership pack if exists: issue#: 535			
			$member = apply_filters('mgm_reassign_member_subscription', $user_id, $member, 'CANCEL', true);			
		}else{		
			// format
			$date_format = mgm_get_date_format('date_format');
			// status
			$new_status     = MGM_STATUS_AWAITING_CANCEL;	
			$new_status_str = sprintf(__('Subscription awaiting cancellation on %s','mgm'), date($date_format, strtotime($expire_date)));	
			// set
			$member->status      = $new_status;
			$member->status_str  = $new_status_str;				
			// set reset info
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
		
		// redirect
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
					// log					
					// mgm_log('RECALLing '. $member->payment_info->module .': cancel_recurring_subscription FROM: ' . $this->code, $this->module);
					// return
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}
				
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;
				
			}else 
				return false;
		}
				
		// check		
		if( $subscr_id ) {
			// user
			$user = get_userdata($user_id);
			// format
			$date_format = mgm_get_date_format('date_format');
			// API call to cancel subscription				
			// compose post body 
			$post_data = array(
				'USER'      => $this->setting['username'],	
				'PWD'       => $this->setting['password'],		
				'SIGNATURE' => $this->setting['signature'],
				'VERSION'   => '64.0',//'52.0'
				'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
				'PROFILEID' => $subscr_id,
				'ACTION'    => 'cancel', 
				'NOTE'      => sprintf('Cancellation selected by member on UPGRADE: "%s", ID: %d on: %s', $user->user_email, $user->ID, date($date_format))
			);		
			// url
			$endpoint = $this->_get_endpoint($this->status . '_nvp');				
			//issue #1508
			$url_parsed = parse_url($endpoint);  			
			// domain/host
			$domain = $url_parsed['host'];
			// headers
			$http_headers = array ('POST /cgi-bin/webscr HTTP/1.1\r\n',
								'Content-Type: application/x-www-form-urlencoded\r\n',
								'Host: '.$domain.'\r\n',
								'Connection: close\r\n\r\n');			
			// post				
			$http_response = mgm_remote_post($endpoint, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
			// sleep		
			sleep(1);
			// parse
			$response = array();		
			// parse	
			parse_str($http_response, $response);								
			// cancel							
			return ((isset($response['ACK']) && $response['ACK'] == 'Success') ? true : $response);
		}elseif(is_null($subscr_id) || $subscr_id === 0 || 
				(isset($this->setting['username']) && 
				(empty($this->setting['username']) || 
				empty($this->setting['password']) || 
				empty($this->setting['signature'])))
			) {
			//send reminder mail to admin:
			$system_obj = mgm_get_class('system');
			$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
			//send email only if setting enabled
			if( ! $dge ) {
				// blog
				$blogname = get_option('blogname');
				// user
				$user = get_userdata($user_id);
				// notify admin
				mgm_notify_admin_membership_cancellation_manual_removal_required($blogname, $user, $member);				
			}
			return true;	
		}			
		// return	
		return true;
	}

	// default setting
	function _default_setting(){
		// paypal specific
		$this->setting['business_email']  = get_option('admin_email');	
		$this->setting['locale']          = 'US';
		$this->setting['currency']        = mgm_get_class('system')->setting['currency'];
		$this->setting['username'] 		  = '';
		$this->setting['password']  	  = '';
		$this->setting['signature'] 	  = '';	
		$this->setting['return_method']   = 2;// return method 
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
		// custom var
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// check
		if($this->_is_transaction($alt_tran_id)){	
			// tran id
			$tran_id = (int)$alt_tran_id;			
			// return data				
			if(isset($_POST['txn_type'])){
				$option_name = $this->module.'_'.strtolower($_POST['txn_type']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// check
			if($tran_id>0){
				// set
				mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_POST)));
				
				// options 
				$options = array('txn_type','subscr_id','txn_id');
				// loop
				foreach($options as $option){
					// save
					if(isset($_POST[$option])){
						mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>strtolower($this->module.'_'.$option),'option_value'=>$_POST[$option]));
					}
				}
			}	
			// return transaction id
			return $tran_id;
		}
		// error
		return false;		
	}
	
	// setup endpoints
	function _setup_endpoints($end_points = array()){
		// define defaults
		$defaults = array('test'     => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
						  'live'     => 'https://www.paypal.com/cgi-bin/webscr',
						  'test_nvp' => 'https://api-3t.sandbox.paypal.com/nvp',
						  'live_nvp' => 'https://api-3t.paypal.com/nvp');	
		// merge
		$end_points = (is_array($end_points)) ? array_merge($defaults, $end_points) : $defaults;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// set address fields
	function _set_address_fields($user, &$data){
		// mappings
		$mappings= array('first_name'=>'first_name','last_name'=>'last_name','address'=>'address1',
		                 'city'=>'city','state'=>'state','zip'=>'zip','country'=>'country');
						 
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter'));				 
	}
	
	// filter address fields
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
	
	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// verify callback 
	function _verify_callback(){	
		
		//issue #1129
		if(isset($_POST)) {  
	    	foreach ($_POST as $key => $value){
	    		$value = stripslashes($value);
				$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);// IPN fix			
				$_POST[$key] = $value;
	    	}		
	    }
		
		// needed post data		
		if (isset($_POST['test_ipn']) || isset($_POST['payment_status']) || isset($_POST['custom'])) {	// reversed for #298 logic gate failure	
			$error = false;	
		}else{
			$error = true;
			// error
			$error_string = sprintf('Error in IPN. Missing payment_status or custom. Request from IP: %s', mgm_get_client_ip_address());			
			// log
			mgm_log('PayPal IPN Error : '.$error_string.' post data:'.print_r($_POST, true), $this->module .'_' . __FUNCTION__);
			// error
			return false;
		} 
		// return 
		$return = false;
		// check
		if(!$error){
			// post data
			$post_data = array_merge($_POST, array('cmd'=>'_notify-validate'));	
			// endpoint
			$endpoint  = $this->_get_endpoint();
			//issue #1508
			$url_parsed = parse_url($endpoint);  			
			// domain/host
			$domain = $url_parsed['host'];
			// headers
			$http_headers = array ('POST /cgi-bin/webscr HTTP/1.1\r\n',
								'Content-Type: application/x-www-form-urlencoded\r\n',
								'Host: '.$domain.'\r\n',
								'Connection: close\r\n\r\n');
			// post				
			$http_response = mgm_remote_post($endpoint, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
			// response
			$return = $this->_verify_response($http_response, $endpoint);
			// $return = $this->_verify_postback()
			// mgm_log($http_response, $this->module .'_' . __FUNCTION__);
		}
		// error
		return $return;	
	}
	
	// verify response
	function _verify_response($response, $endpoint){
		// system
		$system_obj = mgm_get_class('system');								
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		// check
		if (!preg_match('/VERIFIED/i',$response)) {
			// notify admin, only if gateway emails on
			if(!$dge){
				// message
				$message = sprintf("sent a request to host: '%s'. \n\n <br />response was: %s. \n\n <br />
									post vars: <pre>%s</pre>", parse_url($endpoint, PHP_URL_HOST), $response, print_r($_POST, true));
				// mail
				mgm_notify_admin( 'callback failed', $messgae );
			}else{
				// log
				mgm_log('PAYPAL verification failed', $this->module);
			}			
			// error
			return false;
		}
		// valid
		return true;
	}
	
	// postback
	// deprecated
	function _verify_postback(){
		// system
		$system_obj = mgm_get_class('system');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		// parse the paypal URL
		$url_parsed = parse_url($this->_get_endpoint());  
		// domain/host
		$domain = $url_parsed['host']; // str_replace('https://', '', $this->_get_endpoint());
		// post vars
		$request = 'cmd=_notify-validate';
		// loop post
		foreach ($_POST as $key=>$value) {
			// strip
			//issue#: 552(verification fails if get_magic_quotes_gpc() turned off and doesn't strip slashes )
			/*if (get_magic_quotes_gpc()) {
				$value = stripslashes($value);
			}*/
			$value = mgm_stripslashes_deep($value);
			// request
			$request .= '&' . $key . '=' .urlencode($value);
		}
		
		// get conn
		@set_time_limit(60);
		// fosockopen
		if ($conn = @fsockopen("ssl://".$domain, 443, $errno, $errstr, 60)) {
			fputs($conn, "POST /cgi-bin/webscr HTTP/1.1\r\n");
			fputs($conn, "Host: " . $domain . "\r\n");
			fputs($conn, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($conn, "Content-length: ".strlen($request)."\r\n");
			fputs($conn, "Connection: close\r\n\r\n");
			fputs($conn, $request . "\r\n\r\n");
			// get response
			$response = '';
			while(!feof($conn)) {
				$response .= fgets($conn, 1024);
			}
			// close
			fclose($conn); // close connection		
						
			// check
			if (!preg_match('/VERIFIED/i',$response)) {
				// notify admin, only if gateway emails on
				if( ! $dge ){
					$message = sprintf("sent a request to host: '%s'. \n\n <br />response was: \n\n <br />
						                %s \n\n <br />post vars: <pre>%s</pre><br />\n", $domain, $response, print_r($_POST, true)); 							
					mgm_notify_admin( 'callback failed', $message );
				}else{
					// log
					mgm_log('PAYPAL verification failed(fsockopen): paypal', $this->module);
				}
				
				// error
				return false;
			}
			// valid
			return true;
		} elseif (extension_loaded('curl')) {
			// open
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL            , $this->_get_endpoint());
			curl_setopt($ch, CURLOPT_USERAGENT      , 'Magic Members Membership Software');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER     , array("Content-Type: application/x-www-form-urlencoded"));	
			curl_setopt($ch, CURLOPT_POSTFIELDS     , $request);
			curl_setopt($ch, CURLOPT_POST           , 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , 0);
			curl_setopt($ch, CURLOPT_NOPROGRESS     , 1); 
			curl_setopt($ch, CURLOPT_VERBOSE        , 1); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION , 0); 
			curl_setopt($ch, CURLOPT_TIMEOUT        , 30); 
			curl_setopt($ch, CURLOPT_REFERER        , get_option('siteurl')); 
			curl_setopt($ch, CURLOPT_HEADER         , 0);				
			
			$response = curl_exec($ch); 
			
			// check
			if (!preg_match('/VERIFIED/i',$response)) {
				// notify admin, only if gateway emails on
				if( ! $dge ){
					$message = sprintf("sent a request to host: '%s'. \n\n <br />response was: \n\n <br />
						               %s\n\n <br />post vars: <pre>%s</pre>", $domain, $response, print_r($_POST, true));
					mgm_notify_admin( 'callback failed', $message );
				}else{
					// log
					mgm_log('PAYPAL verification failed(curl): paypal', $this->module);
				}				
				// error
				return false;
			}
			// valid
			return true;	
		} else {						
			// notify admin, only if gateway emails on
			if( ! $dge ){
				mgm_admin_mail( 'callback failed', sprintf('fsockopen/curl to %s failed. Would have sent: %s', $domain, $request) );
			}else{
				// log
				mgm_log('PAYPAL verification failed(NOT SENT): paypal', $this->module);
			}
			
			// error
			return false;
		}	
	}
	
	/************************
	// curl post
	function _curl_post($url, $post, $http_header=array(), $set_response = true){
		if($set_response)
			$this->response = array();
		// create curl post		
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url); 		
		// when set
		if(is_array($http_header)){	
			curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);	
		}			
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
		curl_setopt($ch, CURLOPT_NOPROGRESS, 1); 
		curl_setopt($ch, CURLOPT_VERBOSE, 1); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,0); 
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 180); 
		curl_setopt($ch, CURLOPT_USERAGENT, 'Magic Members Membership Software'); 
		curl_setopt($ch, CURLOPT_REFERER, get_option('siteurl')); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
		// buffer
		$buffer = curl_exec($ch);
		curl_close($ch);		
		// parse response
		if($set_response) parse_str($buffer, $this->response);		
		// return
		return $buffer;	
	}
	************************/
	
	
	
	/**
	 * Specifically check recurring status of each rebill for an expiry date
	 * Along with IPN post mechanism for rebills, the module will need to specifically request for the rebill status
	 * @param int $user_id
	 * @param object $member
	 * @return boolean
	 * @deprecated as not supported by standard	 
	 */
	function query_rebill_status($user_id, $member=NULL) {	
		// return
		return false;
		// $this->process_rebill_status($user_id, $member);
		// $this->status_check_api($member)
	}
	
	// via api status check
	function status_check_api($member){
		// check	
		if (isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id)) {			
			// post data
			$post_data = array();			
			// add internal vars
			$secure =array(
				'USER'         => $this->setting['username'],	
				'PWD'          => $this->setting['password'],		
				'SIGNATURE'    => $this->setting['signature'],
				'VERSION'      => '64.0',
				'IPADDRESS'    => mgm_get_client_ip_address(),
				'CURRENCYCODE' => $this->setting['currency']);
			// merge
			$post_data = array_merge($post_data, $secure); // overwrite post data array with secure params		
			// method
			$post_data['METHOD']     = 'GetRecurringPaymentsProfileDetails';
			$post_data['PROFILEID']  = $member->payment_info->subscr_id;			
			// endpoint	
			$endpoint = $this->_get_endpoint($this->status . '_nvp');					
			//issue #1508
			$url_parsed = parse_url($endpoint);  			
			// domain/host
			$domain = $url_parsed['host'];
			// headers
			$http_headers = array ('POST /cgi-bin/webscr HTTP/1.1\r\n',
								'Content-Type: application/x-www-form-urlencoded\r\n',
								'Host: '.$domain.'\r\n',
								'Connection: close\r\n\r\n');
			// post
			$http_response = mgm_remote_post($endpoint, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
			
			// post string
			// $post_string = mgm_http_build_query($post_data);
			// create curl post				
			// $response = $this->_curl_post($endpoint, $post_string);
						
			// parse
			parse_str($http_response, $this->response);
			
			// log
			// mgm_log($this->response, __FUNCTION__);
			
			// check		
			if (isset($this->response['STATUS'])) {
				// old status
				$old_status = $member->status;	
				// set status
				switch($this->response['STATUS']){
					case 'Active':
						// set new status
						$member->status = $new_status = MGM_STATUS_ACTIVE;
						// status string
						$member->status_str = __('Last payment cycle processed successfully','mgm');	
						// last pay date
						$member->last_pay_date = (isset($this->response['LASTPAYMENTDATE'])) ? date('Y-m-d', strtotime($this->response['LASTPAYMENTDATE'])) : date('Y-m-d');	
						// expire date
						if(isset($this->response['LASTPAYMENTDATE']) && !empty($member->expire_date)){							
							// date to add
						 	$date_add = mgm_get_pack_cycle_date((int)$member->pack_id, $member);		
							// check 
							if($date_add !== false){
								// new expire date should be later than current expire date, #1223
								$new_expire_date = date('Y-m-d', strtotime($date_add, strtotime($member->last_pay_date)));
								// apply on last pay date so the calc always treat last pay date form gateway
								if(strtotime($new_expire_date) > strtotime($member->expire_date)){
									$member->expire_date = $new_expire_date;
								}
							}else{
							// set last pay date if greater than expire date
								if(strtotime($member->last_pay_date) > strtotime($member->expire_date)){
									$member->expire_date = $member->last_pay_date;
								}
							}				
						} 
						// save
						$member->save();
					break;
					case 'Cancelled':					
						// if expire date in future, let as awaiting
						if(!empty($member->expire_date) && strtotime($member->expire_date) > time()){
							// date format
							$date_format = mgm_get_date_format('date_format');				
							// status				
							$member->status = $new_status = MGM_STATUS_AWAITING_CANCEL;	
							// status string	
							$member->status_str = sprintf(__('Subscription awaiting cancellation on %s','mgm'), date($date_format, strtotime($member->expire_date)));							
							// set reset date				
							$member->status_reset_on = $member->expire_date;
							// reset as
							$member->status_reset_as = MGM_STATUS_CANCELLED;
						}else{
						// set cancelled
							// status
							$member->status = $new_status = MGM_STATUS_CANCELLED;
							// status string
							$member->status_str = __('Last payment cycle cancelled','mgm');	
						}
						// save
						$member->save();
					break;	
					case 'Expired':
						// set new status
						$member->status = $new_status = MGM_STATUS_EXPIRED;
						// status string
						$member->status_str = __('Last payment cycle expired','mgm');	
						// save
						$member->save();
					break;
				}			
				// action
				if( isset($new_status) ){
					do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);	
				}					
				// return as a successful rebill
				return true;
			}			
		}
		// return
		return false;//default to false to skip normal modules
	}
	
	// expire
	function _expire_membership($user_id){
		// member 
		$member = mgm_get_member($user_id);
		// old status
		$old_status = $member->status;	
		// set new status
		$member->status = $new_status = MGM_STATUS_EXPIRED;
		// status string
		$member->status_str = __('Last payment cycle expired','mgm');	
		// reassign expiry membership pack if exists: issue#: 1676		
		$member = apply_filters('mgm_reassign_member_subscription', $user_id, $member, 'EXPIRE', true);			
		// save
		$member->save();
		// action
		do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);
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
		}elseif(isset($_POST['rp_invoice_id']) && !empty($_POST['rp_invoice_id'])){
			$alt_tran_id = $_POST['rp_invoice_id'];
		}  				
		// return 		
		return $alt_tran_id;
	}
}

// end file