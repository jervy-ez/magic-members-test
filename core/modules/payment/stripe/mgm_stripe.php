<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Stripe payment module, integrates Subscription and Charge API
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */
class mgm_stripe extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_stripe();
	}
	
	// construct
	function mgm_stripe(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__;
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Stripe';		
		// logo
		$this->logo = $this->module_url( 'assets/stripe.png' );
		// description
		$this->description = __('Stripe. Recurring payments and One Off Purchase.', 'mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');
		// trial support available ?
		$this->supports_trial= 'Y';	
		// cancellation support available ?
		$this->supports_cancellation= 'Y';	
		// do we depend on product mapping	
		$this->requires_product_mapping = 'Y'; 
		// type of integration
		$this->hosted_payment = 'N';// credit card process onsite
		// if supports rebill status check	
		$this->supports_rebill_status_check = 'Y';		
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
				// stripe specific				
				$this->setting['secretkey']  = $_POST['setting']['secretkey'];
				$this->setting['publishable_key'] = $_POST['setting']['publishable_key'];	
				$this->setting['currency']  = $_POST['setting']['currency'];	
				// csutom end points flag
				if( isset($_POST['setting']['end_points']) ){
					$this->setting['end_points'] = $_POST['setting']['end_points'];		
				}
				// update supported card types
				if( isset($_POST['card_types']) && !empty($_POST['card_types']) ){
					$this->setting['supported_card_types'] = $_POST['card_types'];
				}else{
					$this->setting['supported_card_types'] = array();	
				}
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
				$this->hosted_payment = 'N';	
				$this->requires_product_mapping = 'Y'; 
				// setup callback messages				
				$this->_setup_callback_messages($_POST['setting']);
				// re setup callback urls
				$this->_setup_callback_urls($_POST['setting']);
				// re setup endpoints
				$end_points = (isset($_POST['end_points'])) ? $_POST['end_points'] : array(); 
				// update
				$this->_setup_endpoints($end_points);												
				// save
				$this->save();
				// message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));
			break;
		}		
	}
	
	// hook for subscription package setting
	function settings_subscription_package($data=NULL){
		// plan_id
		$plan_id = isset($data['pack']['product']['stripe_plan_id']) ? $data['pack']['product']['stripe_plan_id'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data['pack']['modules']) && in_array($this->code,(array)$data['pack']['modules'])){
			$display = 'class="displayblock"';
		}
		// html
		$html = '<div id="settings_subscription_package_' . $this->module. '" ' . $display . '>
					<div class="row">
						<div class="cell"><div class="subscription-heading">'.__('Stripe Settings','mgm').'</div></div>
					</div>
					<div class="row">
						<div class="cell">
							<div class="marginleft10px">	
								<p class="fontweightbold">' . __('Plan ID','mgm') . '</p>
								<input type="text" name="packs['.($data['pack_ctr']-1).'][product][stripe_plan_id]" value="'.esc_html($plan_id).'" />
								<div class="tips width95">' . __('Plan ID from Stripe.','mgm') . '</div>
							</div>
						</div>
					 </div>
				 </div>';
		// return
		return $html;
	}
	

	// return process api hook, link back to site after payment is made
	function process_return(){
		// check and show message		
		if( isset($this->response->id) ){// id		
			// caller
			$this->webhook_called_by = 'self';				
			// process notify, internally called
			$this->process_notify();
			// redirect as success if not already redirected
			$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($_POST['custom']));
			// is a post redirect?
			$post_redirect = $this->_get_post_redirect($_POST['custom']);
			// set post redirect
			if($post_redirect !== false){
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
			// error
			$error = isset($this->response->error->message) ? $this->response->error->message : 'Unknown error';
			// error
			mgm_redirect(add_query_arg(array('status'=>'error','errors'=>urlencode($error)), $this->_get_thankyou_url()));
		}		
	}
	
	// notify process api hook, background IPN url 
	// used as proxy IPN for this module
	function process_notify(){		
		// notify to post
		$this->_notify2post();	
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );			
		// verify			
		if ($this->_verify_callback()){	
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
				// buypost
				case 'post_purchase': 
				case 'buypost':
					$this->_buy_post(); //run the code to process a purchased post/page
				break;
				// subscription	
				case 'subscription':
					// txn type
					$notify_type = isset($_POST['notify_type']) ? $_POST['notify_type'] : '';
					// switch
					switch($notify_type){
						case 'subscr_canceled':						
						// cancellation
							$this->_cancel_membership($custom['user_id']); //run the code to process a membership cancellation
						break;
						case 'subscr_expired':
							$this->_expire_membership($custom['user_id']);
						break;
						default:		
							// new subscription 						
							$this->_buy_membership(); //run the code to process a new/extended membership								
						break;	
					}					
				break;	
				// other		
				default:
					// error
					// $error = 'error in payment type : '.$payment_type;
					// redirect to error
					// mgm_redirect(add_query_arg(array('status'=>'error','errors'=>$error), $this->_get_thankyou_url()));				
				break;							
			}
			// after process		
			do_action('mgm_notify_post_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));
		}		
		// after process unverified		
		do_action('mgm_notify_post_process_unverified_'.$this->module);	
		
		// 200 OK to gateway, only when called externally by Merchant i.e. Push Notify	
		if( $this->is_webhook_called_by('merchant') ){	
			if( ! headers_sent() ){
			  	@header('HTTP/1.1 200 OK');
			 	exit('OK');
			} 
		}	
	}
	
	// process cancel api hook 
	function process_cancel(){
		// redirect to cancel page
		mgm_redirect(add_query_arg(array('status'=>'cancel'), $this->_get_thankyou_url()));
	}
	
	// unsubscribe process, proxy for unsubscribe
	function process_unsubscribe() {				
		// get user id
		$user_id = (int)$_POST['user_id'];		
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// get user
		$user = get_userdata($user_id);	
		$member = mgm_get_member($user_id);
		$cancel_account = true;	
		
		// multiple membesrhip level update:
		if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']){
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);				
		}				
		// check
		if(isset($member->payment_info->module)) {
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
			// cancel 
			$cancel_account = $this->cancel_recurring_subscription(null, $user_id, $subscr_id);						
		}	
			
		// verify
		if($cancel_account === true){
			$this->_cancel_membership($user_id);
		}else{
			// message
			$message = isset($this->response->error->message) ? $this->response->error->message : __('Error while cancelling subscription', 'mgm') ;
			//issue #1521
			if($is_admin){
				$url = add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php'));
				mgm_redirect($url);
			}			
			// force full url, bypass custom rewrite bug
			mgm_redirect(mgm_get_custom_url('membership_details', false,array('unsubscribe_errors'=>urlencode($message))));
		}
	}
	
	// process credit_card, proxy for credit_card processing
	function process_credit_card(){			
		// read tran id
		if(!$tran_id = $this->_read_transaction_id()){		
			return $this->throw_cc_error(__('Transaction Id invalid','mgm'));
		}	
		
		// get trans
		if(!$tran = mgm_get_transaction($tran_id)){
			return $this->throw_cc_error(__('Transaction invalid','mgm'));
		}		

		// Check user id is set if subscription_purchase. issue #1049
		if ($tran['payment_type'] == 'subscription_purchase' && 
			(!isset($tran['data']['user_id']) || (isset($tran['data']['user_id']) && (int) $tran['data']['user_id']  < 1))) {
			return $this->throw_cc_error(__('Transaction invalid . User id field is empty','mgm'));		
		}
		// default
		$error_string = 'Unknown Error occured';
		// system
		$system_obj = mgm_get_class('system');	
		// get data		
		$data = $this->_get_button_data($tran['data'], $tran_id);
		// merge
		$post_data = array_merge($_POST, $data); 		
		// set email
		$this->_set_default_email($post_data, 'email');		
		// action
		$action = ( isset($data['plan']) ? 'create_customer' : 'create_charge' );// action
		// check if upgrade
		// we can check $tran['data']['subscription_option'] == 'upgrade'
		$subscr_id = null;
		if( $user_id = $tran['data']['user_id']){
			if( $member = mgm_get_member($user_id) ){
				if( isset($member->payment_info->module) && $member->payment_info->module == $this->module ){
					if( $subscr_id = $member->payment_info->subscr_id ){
						// check		
						if ( $customer = $this->_get_api_notify('get_customer', null, $subscr_id) ) {
							// log
							mgm_log($customer, $this->module . '_' .__FUNCTION__);
							// customer exists
							if( isset($customer->id) ){		
								$action = 'upgrade_subscription';
							}
						}							
					}
				}
			}
		}
		// merge
		$post_data = $this->_filter_postdata($action, $post_data); // overwrite post data array with secure params				
		// log
		mgm_log($post_data, __FUNCTION__);
		// to object
		if( $this->response = $this->_get_api_notify($action, $post_data, $subscr_id) ){// 
			// dumo
			mgm_log( $this->response, __FUNCTION__ );
			// ok
			if( isset($this->response->id) ){// check id				
				// store custom
				$_POST['custom'] = !empty($post_data['custom']) ? $post_data['custom'] : $tran_id;
				// treat as return
				$this->process_return(); exit;		
			}else{
				// wp error 
				if ( is_wp_error( $this->response ) ){
					$error_string = $this->response->get_error_message();  
				}  
			}			
		}		
		
		// stripe error
		if ( isset($this->response->error) ) {
			// return to credit card form
			$error_string = $this->response->error->message;	
		}

		// return error
		return $this->throw_cc_error($error_string);			
	}	
	
	

	// process html_redirect, proxy for form submit
	//The credit card form will get submitted to the same function, then validate the card and if everything is clear
	//() will be called internally
	function process_html_redirect(){	
		// read tran id
		if(!$tran_id = $this->_read_transaction_id()){		
			return __('Transaction Id invalid','mgm');
		}
		
		// get trans
		if(!$tran = mgm_get_transaction($tran_id)){
			return __('Transaction invalid','mgm');
		}
		// Check user id is set if subscription_purchase. issue #1049	
		if ($tran['payment_type'] == 'subscription_purchase' && 
			(!isset($tran['data']['user_id']) || (isset($tran['data']['user_id']) && (int) $tran['data']['user_id']  < 1))) {
			return __('Transaction invalid . User id field is empty','mgm');		
		}
		// get user
		$user_id = $tran['data']['user_id'];
		$user    = get_userdata($user_id);		
		
		// update pack/transaction: this is to confirm the module code if it is different
		mgm_update_transaction(array('module'=>$this->module), $tran_id);
				
		// cc field
		$cc_fields = $this->_get_ccfields($user, $tran);
		
		// validate card: This will validate card and reload the form with errors
		// if validated process_credit_card() method will be called internally			
		$html = $this->validate_cc_fields_process(__FUNCTION__);		
		// the html
		$html .='<form action="'. $this->_get_endpoint('html_redirect') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					<input type="hidden" name="tran_id" value="'.$tran_id.'">
					<input type="hidden" name="submit_from" value="'.__FUNCTION__.'">
					'. $cc_fields .'
			   </form>';
		// return 	  
		return $html;					
	}	
	
	// subscribe button api hook
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
	
	// buypost button api hook
	function get_button_buypost($options=array(), $return = false) {
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
	
	// dependency_check
	function dependency_check(){		
		return false;			
	}
	
	// get module transaction info
	function get_transaction_info($member, $date_format){		
		// data
		$subscription_id = $member->payment_info->subscr_id;
		$transaction_id  = $member->payment_info->txn_id;	
		
		// info
		$info = sprintf('<b>%s:</b><br>%s: %s<br>%s: %s', __('STRIPE INFO','mgm'), __('SUBSCRIPTION ID','mgm'), $subscription_id, 
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
		$html = sprintf('<p>%s: <input type="text" size="20" name="stripe[subscriber_id]"/></p>
				 		 <p>%s: <input type="text" size="20" name="stripe[transaction_id]"/></p>', 
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
		$data = $post_data['stripe'];
	 	// return
	 	return $this->_save_tracking_fields($fields, $member, $data); 			
	 }
						
	// MODULE API COMMON PRIVATE HELPERS /////////////////////////////////////////////////////////////////	
	
	// get button data
	function _get_button_data($pack, $tran_id=NULL) {
		// system
		$system_obj = mgm_get_class('system');	
		$user_id = $pack['user_id'];
		$user = get_userdata($user_id);		
		// item 		
		$item = $this->get_pack_item($pack);
		// set data
		$data = array(			
			'invoice_num' => $tran_id, 					
			'description' => $item['name'],			
			'email'       => $user->user_email,
			'currency'    => strtolower($this->setting['currency'])	
		);		
		
		// additional fields,see parent for all fields, only different given here	
		$this->_set_address_fields($user, $data);		

		// product based	
		if(isset($pack['product']['stripe_plan_id'])){
			$data['plan']     = $pack['product']['stripe_plan_id'];	
			$data['quantity'] = 1;
		}else{
		// use total
			$data['amount'] = mgm_convert_to_cents($pack['cost']);
		}	
		
		// custom passthrough
		$data['custom'] = $tran_id;

		// update currency
		if($pack['currency'] != $this->setting['currency']){
			$pack['currency'] = $this->setting['currency'];
		}
		
		// strip
		$data = mgm_stripslashes_deep($data);
		
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
		
		// passthrough
		$custom_pt_var = $this->_get_custom_pt_var();
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($custom_pt_var);
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

		// response code
		$response_code = ($this->response->paid == true) ? 'Approved' : 'Declined';
		// process on response code
		switch ($response_code) {
			case 'Approved':
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';				
										  
				// after succesful payment hook
				do_action('mgm_buy_post_transaction_success', array('post_id' => $post_id));// backward compatibility
				do_action('mgm_post_purchase_payment_success', array('post_id' => $post_id));// new organized name
			break;

			case 'Declined':
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
			case 'Held for Review':
				// reason
				// $reason = $this->response['message_text'];
				// status
				$status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $response_code);				
				// purchase status
				$purchase_status = 'Pending';	
										  
				// error
				$errors[] = $status_str;
			break;

			default:
				// status
				$status_str = sprintf(__('Last payment status: %s','mgm'),$response_code);
				// purchase status
				$purchase_status = 'Unknown';					
																							  
				// error
				$errors[] = $status_str;
		}
		
		// do action
		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new, individual
		do_action('mgm_return_post_purchase_payment', array('post_id' => $post_id));// new, global 	
		
		// STATUS
		$status = __('Failed join', 'mgm'); // overridden on a successful payment
		// check status
		if ( $purchase_status == 'Success' ) {
			// mark as purchased
			if( isset($user->ID) ){	// purchased by user	
				// call coupon action
				do_action('mgm_update_coupon_usage', array('user_id' => $user_id));		
				// set as purchased	
				$this->_set_purchased($user_id, $post_id, NULL, $custom_pt_var);
			}else{
				// purchased by guest
				if( isset($guest_token) ){
					// issue #1421, used coupon
					if(isset($coupon_id) && isset($coupon_code)) {
						// call coupon action
						do_action('mgm_update_coupon_usage', array('guest_token' => $guest_token,'coupon_id' => $coupon_id));
						// set as purchased
						$this->_set_purchased(NULL, $post_id, $guest_token, $custom_pt_var, $coupon_code);
					}else {
						$this->_set_purchased(NULL, $post_id, $guest_token, $custom_pt_var);				
					}
				}
			}	

			// status
			$status = __('The post was purchased successfully', 'mgm');
		}
		
		// transaction status
		mgm_update_transaction_status($custom_pt_var, $status, $status_str);
		
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
				if( $this->send_payment_email($custom_pt_var) ) {	
				// check
					if( mgm_notify_user_post_purchase($blogname, $user, $post, $purchase_status, $system_obj, $post_obj, $status_str) ){
					// update as email sent 
						$this->update_paymentemail_sent($custom_pt_var);
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
		
		// passthrough
		$custom_pt_var = $this->_get_custom_pt_var();
				
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($custom_pt_var);		
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
		// duration
		$member->duration        = $duration;
		$member->duration_type   = strtolower($duration_type);
		$member->amount          = $amount;
		$member->currency        = $currency;
		$member->membership_type = $membership_type;		
		$member->pack_id         = $pack_id;
		// $member->payment_type = 'subscription';		
		//save num_cycles in mgm_member object:(issue#: 478)
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		// payment info for unsubscribe		
		if(!isset($member->payment_info)) $member->payment_info = new stdClass;
		// module
		$member->payment_info->module = $this->code;		
		// transaction type
		if(isset($this->response->object)){	
			$member->payment_info->txn_type = $this->response->object;	
		}
		// subscription id
		if(isset($this->response->id)){
			// set
			$member->payment_info->subscr_id = $this->response->id;
			// reset rebilled count
			if(isset($member->rebilled)) unset($member->rebilled);		
		}
		// transaction	
		if(isset($this->response->invoice)){	
			$member->payment_info->txn_id = $this->response->invoice;	
		}
		// mgm transaction id
		$member->transaction_id = $custom_pt_var;
		// process response
		$new_status = $update_role = false;
		// errors
		$errors = array();	
		// response code
		// $response_code = ($this->response->subscription->status == 'active') ? 'Approved' : 'Declined';
		// check
		$subscription_status = 'unknown';
		if( isset($this->response->subscription->status) && ! empty($this->response->subscription->status) ){
			$subscription_status = strtolower($this->response->subscription->status);
		}
		// log
		mgm_log('subscription_status: '.$subscription_status, $this->module . '_' . __FUNCTION__);	
		// status
		switch ($subscription_status) {
			case 'approved':
			case 'active':
			case 'trialing':
				// status
				$new_status = MGM_STATUS_ACTIVE;
				// $member->status_str = __('Last payment was successful','mgm');	
				$member->status_str = sprintf(__('Last %s was successful','mgm'), ($subscription_status == 'trialing' ? 'trial' : 'payment') );
										
				// current time
				$time = time();
				$last_pay_date = isset($member->last_pay_date) ? $member->last_pay_date : null;			
				// last pay date			
				$member->last_pay_date = date('Y-m-d', $time);	
				
				// default expire_date_ts to calculate next cycle expire date
				$expire_date_ts = $time;
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
							$expire_date_ts = $time;
						break;
						case 'downgrade':
						// expire date will be based on expire_date if exists, current time other wise					
						case 'extend':
						// expire date will be based on expire_date if exists, current time other wise
							$expire_date_ts = $time;
							// extend/expire date
							//if (!empty($member->expire_date) && $member->last_pay_date != date('Y-m-d', $expire_date_ts)) {
							// calc expiry	- issue #1226
							// membership extend functionality broken if we try to extend the same day so removed && $last_pay_date != date('Y-m-d', $time) check	
							if (!empty($member->expire_date) ) {
								// expiry
								$expire_date_ts2 = strtotime($member->expire_date);
								// valid
								// valid && expiry date is greater than today
								if ($expire_date_ts2 > 0 && $expire_date_ts2 > $expire_date_ts) {
									// set it for next calc
									$expire_date_ts = $expire_date_ts2;
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
					if(isset($trial_on) && $trial_on == 1 ) {
						// Do it only once
						if(!isset($member->rebilled) && isset($member->active_num_cycles) && $member->active_num_cycles != 1 ) {							
							$expire_date_ts = strtotime('+' . $trial_duration . ' ' . $duration_exprs[$trial_duration_type], $expire_date_ts);								
						}					
					}else {
						// recalc - issue #1068
						$expire_date_ts = strtotime('+' . $member->duration . ' ' . $duration_exprs[$member->duration_type], $expire_date_ts);										
					}
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
					
				// update rebill: issue #: 489				
				if($member->active_num_cycles != 1){
					// check			
					if(!isset($member->rebilled)){
						$member->rebilled = 1;
					}else if((int)$member->rebilled < (int)$member->active_num_cycles) { // 100 
						// rebill
						$member->rebilled = ((int)$member->rebilled + 1);	
					}	
				}
				
				//clear cancellation status if already cancelled:
				if(isset($member->status_reset_on)) unset($member->status_reset_on);
				if(isset($member->status_reset_as)) unset($member->status_reset_as);				
				
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

			case 'declined':
			case 'refunded':
			case 'denied':
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				// error
				$errors[] = $member->status_str;
			break;
			
			case 'pending':
			case 'held for review':
				$new_status = MGM_STATUS_PENDING;
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $subscription_status);				
				// error
				$errors[] = $member->status_str;
			break;

			default:
				$new_status = MGM_STATUS_ERROR;
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $subscription_status);
				// error
				$errors[] = $member->status_str;
			break;
		}
		
		// old status
		$old_status = $member->status;	
		// set new status
		$member->status = $new_status;			
				
		// whether to acknowledge the user by email - This should happen only once
		$acknowledge_user = $this->send_payment_email($custom_pt_var);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $custom_pt_var);
		
		// update member
		// another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {// issue #1227
			// hide old content
			if($subs_pack['hide_old_content']) $member->hide_old_content = $subs_pack['hide_old_content']; 
			
			// save
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
					$this->update_paymentemail_sent($custom_pt_var);	
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
		// error condition redirect
		if(count($errors)>0){			
			mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
		}
	}
	
	// cancel membership
	function _cancel_membership($user_id){
		// system	
		$system_obj  = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge     = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne    = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));	
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
		$member->payment_info->txn_type = 'subscription_cancel';
		
		// types
		$duration_exprs = $s_packs->get_duration_exprs();
						
		// default expire date				
		$expire_date = $member->expire_date;
		// if lifetime:
		if($member->duration_type == 'l') $expire_date = date('Y-m-d');	
							
		// if trial on 
		if ($subs_pack['trial_on'] && isset($duration_exprs[$subs_pack['trial_duration_type']])) {			
			// if cancel data is before trial end, set cancel on trial expire_date
			$trial_expire_date = strtotime('+' . $subs_pack['trial_duration'] . ' ' . $duration_exprs[$subs_pack['trial_duration_type']], $member->join_date);
			
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
			$member->status      = $new_status;
			$member->status_str  = $new_status_str;		
			// set reset date
			$member->status_reset_on = $expire_date;
			$member->status_reset_as = MGM_STATUS_CANCELLED;
		}
						
		// multiple memberhip level update:	
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
		
		// message
		$lformat = mgm_get_date_format('date_format_long');
		$message = sprintf(__("You have successfully unsubscribed. Your account has been marked for cancellation on %s", "mgm"), 
		                  ($expire_date == date('Y-m-d') ? 'Today' : date($lformat, strtotime($expire_date))));		
		//issue #1521
		if($is_admin){
			$url = add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php'));
			mgm_redirect($url);
		}		
		// redirect 		
		mgm_redirect(mgm_get_custom_url('membership_details', false,array('unsubscribed'=>'true','unsubscribe_errors'=>urlencode($message))));
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
				if(isset($member->payment_info->subscr_id)) {
					$subscr_id = $member->payment_info->subscr_id; 
				}else {
					//check pack is recurring:
					$pid = $pack_id ? $pack_id : $member->pack_id;
					
					if($pid) {
						$s_packs = mgm_get_class('subscription_packs');
						$sel_pack = $s_packs->get_pack($pid);												
						if($sel_pack['num_cycles'] != 1)
							$subscr_id = 0;
					}										
				}
				
												
				//check for same module: if not call the same function of the applicale module.
				if(str_replace('mgm_','' , $member->payment_info->module) != str_replace( 'mgm_','' , $this->code ) ) {
					// log					
					// mgm_log('RECALLing '. $member->payment_info->module .': cancel_recurring_subscription FROM: ' . $this->code);
					// return
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}				
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;
				
			}else 
				return false;
		}
		
		//only for subscription_purchase		
		if($subscr_id) {				
			if ( $this->response = $this->_get_api_notify('cancel_subscription', null, $subscr_id) ){
			// check		
				if(isset($this->response->status) && $this->response->status == 'canceled'){	
					return true;
				}
			}
		}elseif($subscr_id === 0) {			
			//send email to admin if subscription Id is absent		
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
			// return			
			return true;
		}		
		// return
		return false;
	}

	/**
	 * Specifically check recurring status of each rebill for an expiry date
	 * ALong with IPN post mechanism for rebills, the module will need to specifically request for the rebill status
	 * @param int $user_id
	 * @param object $member
	 * @return boolean
	 */
	function query_rebill_status($user_id, $member=NULL) {	
		// check	
		if (isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id)) {					
			// id
			$subscr_id = $member->payment_info->subscr_id;			
			// check		
			if ( $this->response = $this->_get_api_notify('get_customer', null, $subscr_id) ) {
				// log
				mgm_log($this->response, $this->module . '_' .__FUNCTION__);
				// old status
				$old_status = $member->status;	
				// check
				$subscription_status = 'not found';
				if( isset($this->response->subscription->status) && ! empty($this->response->subscription->status) ){
					$subscription_status = strtolower($this->response->subscription->status);
				}
				// log
				mgm_log('subscription_status: '.$subscription_status, $this->module . '_' . __FUNCTION__);
				// set status
				switch($subscription_status){
					case 'active':
					case 'trialing':
						// set new status
						$member->status = $new_status = MGM_STATUS_ACTIVE;
						// status string
						$member->status_str = __('Last payment cycle processed successfully','mgm');
						// start date
						$current_period_start = $this->response->subscription->current_period_start;
						// trial fix
						if('trialing' == $subscription_status){
							// expire
							if(empty($member->expire_date)){
								$member->expire_date = date('Y-m-d', $this->response->subscription->trial_end);
							}
							// start date
							$current_period_start = $this->response->subscription->trial_start;
							// status string
							$member->status_str = __('Last trial cycle processed successfully','mgm');
						}
						// last pay date
						$member->last_pay_date = (isset($current_period_start) && !empty($current_period_start)) ? date('Y-m-d', $current_period_start) : date('Y-m-d');	
						// expire date
						if(isset($current_period_start) && !empty($current_period_start) && !empty($member->expire_date)){													
							// date to add
						 	$date_add = mgm_get_pack_cycle_date((int)$member->pack_id, $member);		
							// check 
							if($date_add !== false){
								// new expire date should be later than current expire date, #1223
								$new_expire_date = date('Y-m-d', strtotime($date_add, strtotime($member->last_pay_date)));
								// apply on last pay date so the calc always treat last pay date form gateway, 
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

						// only run in cron, other wise too many tracking will be added
						// if( defined('DOING_QUERY_REBILL_STATUS') && DOING_QUERY_REBILL_STATUS != 'manual' ){
						// transaction_id
						$transaction_id = $member->transaction_id;
						// hook args
						$args = array('user_id' => $user_id, 'transaction_id' => $transaction_id);
						// after succesful payment hook
						do_action('mgm_membership_transaction_success', $args);// backward compatibility				
						do_action('mgm_subscription_purchase_payment_success', $args);// new organized name	
						// }											
					break;
					case 'canceled':
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
							// set reset date				
							$member->cancel_date = date('Y-m-d',$this->response->subscription->canceled_at);// $member->expire_date;
							// status string
							$member->status_str = __('Last payment cycle cancelled','mgm');	
						}
						// save
						$member->save();

						// only run in cron, other wise too many tracking will be added
						// if( defined('DOING_QUERY_REBILL_STATUS') && DOING_QUERY_REBILL_STATUS != 'manual' ){
						// after cancellation hook
						do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));	
						// }
					break;					
					case 'suspended':
					case 'terminated':
					case 'expired':		
					case 'error':
					case 'not found':		
					case 'past_due':
					default:						
						// set new statis
						$member->status = $new_status = MGM_STATUS_EXPIRED;
						// status string
						$member->status_str = sprintf(__('Last payment cycle expired, subscription: %s.','mgm'), $subscription_status );
						// save
						$member->save();						
					break;
				}					
				// action
				if( isset($new_status) ){
					// user status change
					do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);	
					// rebill status change
					do_action('mgm_rebill_status_change', $user_id, $new_status, $old_status, 'query');// query or notify
				}		
				// return as a successful rebill
				return true;
			}			
		}
		// return
		return false;//default to false to skip normal modules
	}
				
	// default setting
	function _default_setting(){
		// authorize.net specific
		$this->setting['secretkey']  = '';
		$this->setting['publishable_key'] = '';	
		$this->setting['currency']  = mgm_get_class('system')->setting['currency'];
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
		if($this->_is_transaction($_POST['custom'])){	
			// tran id
			$tran_id = (int)$_POST['custom'];			
			// return data				
			if( isset($this->response->object) ){
				$option_name = $this->module.'_'.strtolower($this->response->object).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($this->response)));
			
			// options 
			$options = array('object','id','created','invoice');
			// loop
			foreach($options as $option){
				if( isset($this->response->$option) ){
					// value
					$option_value = $this->response->$option;
					// id
					if( $option == 'id') 
						$option = $this->response->object.'_'.$option;// customer_id, charge_id
					
					// add
					mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>strtolower($this->module.'_'.$option),'option_value'=>$option_value));
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
		// validate
		if($this->_is_transaction($_POST['custom'])){	
			// tran id
			return $tran_id = (int)$_POST['custom'];
		}
		// return 
		return 0;	
	}
	
	// setup endpoints
	function _setup_endpoints($end_points = array()){
		// define defaults
		$end_points_default = array('api' => 'https://api.stripe.com/v1');	
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// set 
	function _set_address_fields($user, &$data){
		// mappings
		$mappings= array('first_name'=>'first_name','last_name'=>'last_name','address'=>array('address_line1','address_line2'),
		                 'city'=>'city','state'=>'address_state','zip'=>'address_zip','country'=>'address_country',
						 'phone'=>'phone');
						 
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
	
	// verify callback 
	function _verify_callback(){	
		// keep it simple		
		return (isset($_POST['custom']) && !empty($_POST['custom'])) ? true : false;
	}
	
	// custom pt var
	function _get_custom_pt_var(){
		// var
		$custom_pt_var = '';
		// post
		if(isset($_POST['custom']) && !empty($_POST['custom'])){
			$custom_pt_var = $_POST['custom'];
		}elseif(isset($_GET['custom']) && !empty($_GET['custom'])){
			$custom_pt_var = $_GET['custom'];
		}		
		// return 
		return $custom_pt_var;
	}

	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	// filter postdata
	function _filter_postdata($action, $post_data, $join=false){
		// card holder name
		// list($ch_first_name, $ch_last_name) = explode(' ', $post_data['mgm_card_holder_name']);	
		// init
		$filtered = array();				
		
		// action
		switch( $action ){
			case 'create_customer':
				// desc
				$filtered['description'] = $post_data['description'];
				$filtered['plan'] = $post_data['plan'];
				$filtered['email'] = $post_data['email']; 
			break;
			case 'create_charge':
				// desc
				$filtered['description'] = $post_data['description'];
				$filtered['amount'] = $post_data['amount'];
				$filtered['currency'] = $post_data['currency'];
			break;
			case 'upgrade_subscription':
				$filtered['plan'] = $post_data['plan'];
			break;
		}
		
		// quantity
		if( isset( $post_data['quantity']) ){		
			$filtered['quantity'] = $post_data['quantity'];
		}	

		// trial end
		if( isset( $post_data['trial_end']) ){		
			$filtered['trial_end'] = $post_data['trial_end'];
		}
		
		$filtered['card']['number']    = $post_data['mgm_card_number'];
		$filtered['card']['exp_month'] = $post_data['mgm_card_expiry_month'];
		$filtered['card']['exp_year']  = $post_data['mgm_card_expiry_year'];
		$filtered['card']['cvc']       = $post_data['mgm_card_code'];		
		$filtered['card']['name']      = $post_data['mgm_card_holder_name'];				
		// street
		if(isset($post_data['address_line1'])){
			$filtered['card']['address_line1'] = $post_data['address_line1'];
		}
		if(isset($post_data['address_line2'])){
			$filtered['card']['address_line2'] = $post_data['address_line2'];
		}
		// zip
		if(isset($post_data['address_zip'])){
			$filtered['card']['address_zip'] = $post_data['address_zip'];
		}
		// state
		if(isset($post_data['address_state'])){
			$filtered['card']['address_state'] = $post_data['address_state'];
		}
		// country
		if(isset($post_data['address_country'])){
			$filtered['card']['address_country'] = $post_data['address_country'];
		}
		// send filtered
		return ($join) ? mgm_http_build_query($filtered) : $filtered;
	}
	
	/**
	 * fetch remote data via http POST
	 *
	 * @param string $url
	 * @param array $data to post
	 * @param array $options
	 * @param bool $on_error_message (CONNECT_ERROR|WP_ERROR)
	 * @return string $response
	 */
	function _remote_post($request_url, $data=array(), $options=array(), $on_error_message='CONNECT_ERROR'){
		// args
		$args = array('body' => $data);
		
		// merge		
		if(is_array($options)) $args = array_merge($args, $options);	
		
		// log
		mgm_log($request_url, __FUNCTION__);

		// log
		mgm_log($args, __FUNCTION__);

	 	// request
		$request = wp_remote_post($request_url, $args);

		// log
		mgm_log($request, __FUNCTION__);
		
		// validate, 200 and 302, WP permalink cacuses 302 Found/Temp Redirect often
		if ( is_wp_error( $request ) /*|| !in_array(wp_remote_retrieve_response_code( $request ), array( 200, 302))*/ )  
			return $request->get_error_message();  
			
		// return
		return wp_remote_retrieve_body( $request ); 
	}

	function _remote_delete($request_url, $args = array()) {
		// fetch
		$objFetchSite = _wp_http_get_object();
		
		$defaults = array('method' => 'DELETE');
		$r = wp_parse_args( $args, $defaults );

		// log
		mgm_log($request_url, __FUNCTION__);

		// log
		mgm_log($args, __FUNCTION__);

		// request
		$request = $objFetchSite->request($request_url, $r);
		
		// log
		mgm_log($request, __FUNCTION__);

		// validate, 200 and 302, WP permalink cacuses 302 Found/Temp Redirect often
		if ( is_wp_error( $request ) /*|| !in_array(wp_remote_retrieve_response_code( $request ), array( 200, 302))*/ )  
			return $request->get_error_message();  
		
		// return
		return wp_remote_retrieve_body( $request ); 
	}

	function _get_notify(){
		if( $notify_json = @file_get_contents("php://input") ){
			return $notify = json_decode($notify_json);	
		}
		return false;		
	}

	function _notify2post(){
		// parse
		if( $notify_response = $this->_get_notify() ){
			// log
			mgm_log($notify_response, $this->module . __FUNCTION__);
			// only if canceled
			if( $notify_response->type == 'customer.subscription.deleted'){
				// get email
				$customer_id = $notify_response->data->object->customer;
				// get transaction
				if( $tran = mgm_get_transaction_by_option('stripe_customer_id', $customer_id) ){
					// check
					if( $member = mgm_get_member($transaction['data']['user_id']) ){
						// proces only when not canceled
						if( !in_array($member->status, array( MGM_STATUS_AWAITING_CANCEL, MGM_STATUS_CANCELLED)) ){
							// set post fields
							$_POST['custom'] = $tran['id']; 
							$_POST['notify_type'] = 'subscr_canceled';
							//$_POST['notify_type'] = 'subscr_expired'
							// set notify
							$this->response = $notify_response;
							// log
							mgm_log('Cancelling from Notify Post' . mgm_pr($tran, true), __FUNCTION__);
						}						
					}					
				}
			}
		}
	}

	function _get_api_notify($action, $post_data=null, $id=null){
		// headers	
		$http_headers = array('Authorization' => 'Basic ' . base64_encode( $this->setting['secretkey'] . ':' ));// just in case	
		// base 
		$api_url = trailingslashit( $this->_get_endpoint('api') );
		// action
		switch(  $action ) {
			case 'create_customer':
			case 'create_charge':
				// endpoint
				$api_url .= ($action == 'create_customer') ? 'customers' : 'charges';
				// post
				$http_response = $this->_remote_post($api_url, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));	
				// log
				mgm_log($http_response, __FUNCTION__);
				// return
				if( $notify = json_decode($http_response) ){
					return $notify;
				}
			break;		
			case 'upgrade_subscription':
				// endpoint
				$api_url .= sprintf('customers/%s/subscription', $id) ;
				// return
				$http_response = $this->_remote_post($api_url, $post_data, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false));
				// log
				mgm_log($http_response, __FUNCTION__);
				// return
				if( $notify = json_decode($http_response) ){
					return $notify;
				}
			break;				
			case 'cancel_subscription':
				// endpoint
				$api_url .= sprintf('customers/%s/subscription', $id) ;
				// return
				$http_response = $this->_remote_delete($api_url, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false));
				// log
				mgm_log($http_response, __FUNCTION__);
				// return
				if( $notify = json_decode($http_response) ){
					return $notify;
				}
			break;			
			case 'get_customer':
				// endpoint
				$api_url .= sprintf('customers/%s', $id) ;
				// return
				$http_response = mgm_remote_get($api_url, null, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false));
				// log
				mgm_log($http_response, __FUNCTION__);
				// return
				if( $notify = json_decode($http_response) ){
					return $notify;
				}
			break;
			case 'get_invoice':
				// endpoint
				$api_url .= sprintf('invoices/%s', $id) ;
				// return
				$http_response = mgm_remote_get($api_url, null, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false));
				// log
				mgm_log($http_response, __FUNCTION__);
				// return
				if( $notify = json_decode($http_response) ){
					return $notify;
				}
			break;
		}
		// return
		return false;		
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
		// save
		$member->save();
		// action
		do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);
	}
}

// end file