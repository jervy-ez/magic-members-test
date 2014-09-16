<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Clickbank Payment Module
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */
class mgm_clickbank extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_clickbank();
	}
	
	// construct
	function mgm_clickbank(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'ClickBank';
		// logo
		$this->logo = $this->module_url( 'assets/clickbank.jpg' );
		// desc
		$this->description = __('ClickBank is a secure online retail outlet for more than 10,000 digital product'.
								'vendors and 100,000 active affiliate marketers.','mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');
		// trial support available ?
		$this->supports_trial= 'Y';	
		// cancellation support available ?
		$this->supports_cancellation= 'Y';	
		// do we depend on product mapping	
		$this->requires_product_mapping = 'Y';
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
				// clickbank specific
				$this->setting['username']   = $_POST['setting']['username'];
				$this->setting['secret_key'] = $_POST['setting']['secret_key'];
				$this->setting['cbskin']     = $_POST['setting']['cbskin'];
				// purchase price
				if(isset($_POST['setting']['purchase_price'])){
					$this->setting['purchase_price']  = $_POST['setting']['purchase_price'];
				}
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
				// common
				$this->description = $_POST['description'];
				$this->status      = $_POST['status'];
				// re setup endpoints
				$this->_setup_endpoints();						
				// save
				$this->save();
				// message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));
			break;
		}		
	}	
	
	// hook for post purchase setting
	function settings_post_purchase($data=NULL){
		// product_id
		$product_id = isset($data->product['clickbank_product_id']) ? $data->product['clickbank_product_id'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data->allowed_modules) && in_array($this->code,(array)$data->allowed_modules)){
			$display = 'class="displayblock"';
		}
		// overwrite this
		$html = '<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '>
					<div class="row">
						<div class="cell"><div class="postpurhase-heading">'.__('Clickbank Settings','mgm').'</div></div>
					</div>
					<div class="row">
						<div class="cell width125px mgm-padding-tb"><b>'. __('Product ID','mgm') . ':</b></div>
					</div>	
					<div class="row">
						<div class="cell textalignleft">							
							<input type="text" name="mgm_post[product][clickbank_product_id]" class="mgm_text_width_payment" value="'.esc_html($product_id).'" />
						</div>
					 </div>
				 </div>';
		// html
		/*$html=' <li>
					<label>'.__('ClickBank Product ID','mgm').' <input type="text" class="mgm_text_width_payment" name="mgm_post[product][clickbank_product_id]" value="'. esc_html($product_id) .'" /></label>
				</li>';*/
		// return
		return $html;
	}
	
	// hook for post pack purchase setting
	function settings_postpack_purchase($data=NULL){
		// product_id
		$product_id = isset($data->product['clickbank_product_id']) ? $data->product['clickbank_product_id'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data->modules) && in_array($this->code,(array)$data->modules)){
			$display = 'class="displayblock"';
		}
		// overwrite this
		$html = '<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '>
					 <div class="row">
						<div class="cell"><div class="subscription-heading">'.__('Clickbank Settings','mgm').'</div></div>
					 </div>
					 <div class="row">
						<div class="cell width125px"><b>'. __('Product ID','mgm') . ':</b></div>
					 </div>	
					 <div class="row">	
						<div class="cell textalignleft">
							<input type="text" name="product[clickbank_product_id]" value="'.esc_html($product_id).'" />
						</div>
					 </div>
				 </div>';
		// return
		return $html;
	}
	
	// hook for subscription package setting
	function settings_subscription_package($data=NULL){
		// product_id
		$product_id = isset($data['pack']['product']['clickbank_product_id']) ? $data['pack']['product']['clickbank_product_id'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data['pack']['modules']) && in_array($this->code,(array)$data['pack']['modules'])){
			$display = 'class="displayblock"';
		}
		// html
		$html = '<div id="settings_subscription_package_' . $this->module. '" ' . $display . '>
					<div class="row">
						<div class="cell"><div class="subscription-heading">'.__('Clickbank Settings','mgm').'</div></div>
					</div>
					<div class="row">
						<div class="cell">
							<div class="marginleft10px">	
								<p class="fontweightbold">' . __('Product ID','mgm') . '</p>
								<input type="text" name="packs['.($data['pack_ctr']-1).'][product][clickbank_product_id]" value="'.esc_html($product_id).'" />
								<div class="tips width95">' . __('Product ID from ClickBank.','mgm') . '</div>
							</div>
						</div>
					 </div>
				 </div>';
		// return
		return $html;
	}
	
	// hook for coupon setting
	function settings_coupon($data=NULL){
		// product_id
		$product_id = isset($data['clickbank_product_id']) ? $data['clickbank_product_id'] : ''; 
		// overwrite this
		$html = '<div class="row">
					<div class="cell"><div class="subscription-heading">' . __('Clickbank Settings','mgm') . '</div></div>
			    </div>
			    <div class="row">
					<div class="cell width125px"><b>' . __('Product ID','mgm') . ':</b></div>
			    </div>	
			    <div class="row">
					<div class="cell textalignleft">
						<input type="text" name="product[clickbank_product_id]" value="' . esc_html($product_id) . '" />
					</div>
			    </div>';
		// return
		return $html;
	}
	
	// return process api hook, link back to site after payment is made
	function process_return(){	
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );	
		// check and show message
		if((isset($_REQUEST['cbreceipt']) && !empty($_REQUEST['cbreceipt'])) || (isset($_REQUEST['custom']) && !empty($_REQUEST['custom']))){						
			// redirect as success if not already redirected
			// query arg
			$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($_REQUEST['custom']));
			// is a post redirect?
			$post_redirect = $this->_get_post_redirect($_REQUEST['custom']);
			// set post redirect
			if($post_redirect !== false){
				$query_arg['post_redirect'] = $post_redirect;
			}		
			// is a register redirect?
			$register_redirect = $this->_auto_login($_REQUEST['custom']);	
			// set register redirect
			if($register_redirect !== false){
				$query_arg['register_redirect'] = $register_redirect;
			}
			// redirect
			mgm_redirect(add_query_arg($query_arg, $this->_get_thankyou_url()));
		}else{
			// error
			mgm_redirect(add_query_arg(array('status'=>'error','errors'=>urlencode('clickbank receipt error')), $this->_get_thankyou_url()));
		}
	}
	
	// notify process api hook, background IPN url 
	function process_notify(){
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );	
		// verify
		if ($this->_verify_callback()) {	
			// log data before validate
			$tran_id = $this->_log_transaction();				
			// get passthrough data
			$passthrough = $this->_parse_passthrough();					
			// payment type
			$payment_type = $this->_get_payment_type($passthrough['custom']);
			// custom
			$custom = $this->_get_transaction_passthrough($passthrough['custom']);
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
					if(isset($_POST['ctransaction']) && ($_POST['ctransaction']=='RFND' || $_POST['ctransaction']=='CANCEL-REBILL')) {						
						$this->_cancel_membership(); //run the code to process a membership cancellation
					}else{
						$this->_buy_membership(); //run the code to process a new/extended membership
					}					
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
		
		// 200 OK to clickbank, this is IMPORTANT, otherwise CB will keep on sending IPN .........
		if( ! headers_sent() ){
			@header('HTTP/1.1 200 OK');
			exit('OK');
		}
	}
	
	// process cancel api hook 
	function process_cancel(){
		// not used for this module
		// redirect to cancel page
		mgm_redirect(add_query_arg(array('status'=>'cancel'), $this->_get_thankyou_url()));
	}
	
	// unsubscribe process, IPN for unsubscribe 
	function process_unsubscribe(){
		// overwrite this
		// Not implemented for module
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
			(!isset($tran['data']['user_id']) || (isset($tran['data']['user_id']) && (int) $tran['data']['user_id']  < 1))) {
			return __('Transaction invalid . User id field is empty','mgm');		
		}
		// generate
		$button_code     = $this->_get_button_code($tran['data'],$tran_id);
		// extra code
		$additional_code = do_action('mgm_additional_code');
		// the html
		$html='<form action="'. $this->_get_endpoint($tran['data'], $tran_id) .'" method="post" class="mgm_form" name="' . $this->code . '_redirect_form" id="' . $this->code . '_redirect_form">
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
		// cb depends on product id, check for it before generating button
		if(!isset($options['pack']['product']['clickbank_product_id']) || empty($options['pack']['product']['clickbank_product_id'])){
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in ClickBank settings : No Product ID set.','mgm').'</b>
					</div>';
			exit;
		}	
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
		// cb depends on product id, check for it before generating button
		if(!isset($options['pack']['product']['clickbank_product_id']) || empty($options['pack']['product']['clickbank_product_id'])){
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in ClickBank settings : No Product ID set.','mgm').'</b>
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
		
	// MODULE API COMMON PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// get button code	
	function _get_button_code($pack, $tran_id=NULL) {
		// get data
		$data = $this->_get_button_data($pack, $tran_id);
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
		
		// set data
		$data = array(						
			'email' => $user->user_email
		);
		
		// address fields
		$this->_set_address_fields($user, $data);	
		
		// add filter @todo test
		$data = apply_filters('mgm_payment_button_data', $data, $tran_id, $this->module, $pack);
															  
		// update pack/transaction
		mgm_update_transaction(array('data'=>json_encode($pack),'module'=>$this->module), $tran_id);
		
		// data		
		return $data;
	}	
	
	// buy post
	function _buy_post() {
		global $wpdb;
		// get system settings
		$system_obj = mgm_get_class('system');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));

		// get passthrough data
		$passthrough = $this->_parse_passthrough();	
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($passthrough['custom']);
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

		// set test status		
		$ctransaction=($this->status=='test') ? preg_replace('/^TEST_/', '', $_POST['ctransaction']) : $_POST['ctransaction'];
		
		// check 
		switch (trim($ctransaction)) {
			case "SALE" :
			case "BILL" :
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';	

				// transation id
				$transaction_id = $this->_get_transaction_id('custom', $passthrough);
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

			case "RFND" :
			case "CGBK" :
			case "INSF" :
				// status
				$status_str = __('Last payment was refunded or denied','mgm');
				// purchase status
				$purchase_status = 'Failure';
																  
				// error
				$errors[] = $status_str;
			break;

			case "CANCEL-REBILL" :
			case "UNCANCEL-REBILL" :
				// status
				$status_str = __('Last payment is pending. Reason: Unknown','mgm');
				// purchase status
				$purchase_status = 'Pending';
																  
				// error
				$errors[] = $status_str;
			break;

			default:
				// status
				$status_str = sprintf(__('Last payment status: %s','mgm'), $ctransaction);
																												  
				// error
				$errors[] = $status_str;
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
		mgm_update_transaction_status($passthrough['custom'], $status, $status_str);
		
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

		// parse
		$passthrough = $this->_parse_passthrough();	
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($passthrough['custom']);
		// local var
		extract($custom);	
		
		// currency
		if (!$currency) $currency = $system_obj->get_setting('currency');
		
		// find user
		$user    = get_userdata($user_id);
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
		// $member->payment_type    = ($_POST['cprodtype']=='RECURRING') ?'subscription' : 'one-time';
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		// payment info for unsubscribe		
		if(!isset($member->payment_info))
			$member->payment_info    = new stdClass;
		$member->payment_info->module = $this->code;
		if(isset($_POST['ctransaction'])){
			$member->payment_info->txn_type = $_POST['ctransaction'];
		}	
		/*if(isset($_POST['subscr_id'])){
			$member->payment_info->subscr_id = $_POST['subscr_id'];		
		}*/	
		if(isset($_POST['ctransreceipt'])){	
			$member->payment_info->txn_id = $_POST['ctransreceipt'];	
		}
		// transaction
		$member->transaction_id = $passthrough['custom'];
		
		// process response
		$new_status = false;
		// errors
		$errors = array();
		// set test status		
		$ctransaction=($this->status=='test') ? preg_replace('/^TEST_/', '' ,$_POST['ctransaction']) : $_POST['ctransaction'];
		// check 
		$update_role = false;
		switch (trim($ctransaction)) {
			case "SALE" :
			case "BILL" :	
				$new_status = MGM_STATUS_ACTIVE;
				$member->status_str = __('Last payment was successful','mgm');				
				
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
				
				//cancel previous subscription:
				//issue#: 565				
				$this->cancel_recurring_subscription($passthrough['custom'], null, null, $pack_id);
				
				// role update
				if ($role) $update_role = true;		
				
				// transaction_id
				$transaction_id = $this->_get_transaction_id('custom', $passthrough);
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
			case "RFND" :
			case "CGBK" :
			case "INSF" :
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				// error
				$errors[] = $member->status_str;
			break;

			case "CANCEL-REBILL" :
			case "UNCANCEL-REBILL" :
				$new_status = MGM_STATUS_PENDING;

				$reason = 'Unnown';
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				// error
				$errors[] = $member->status_str;
			break;

			default:
				$new_status = MGM_STATUS_ERROR;
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $ctransaction);
				// error
				$errors[] = $member->status_str;
			break;
		}
				
		// old status
		$old_status = $member->status;	
		// set new status
		$member->status = $new_status;
		
		// whether to acknowledge the user - This should happen only once
		$acknowledge_user = $this->send_payment_email($passthrough['custom']);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $passthrough['custom']);
		
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
		
		// error condition redirect
		if(count($errors)>0){
			mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
		}
	}
	
	// cancel membership
	function _cancel_membership($user_id=null, $redirect = false){
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;	

		// parse
		$passthrough = $this->_parse_passthrough();	
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($passthrough['custom']);
		// local var
		extract($custom);
		
		// find user
		$user = get_userdata($user_id);
		$member = mgm_get_member($user_id);	
		// multiple membesrhip level update:		
		$multiple_update = false;	
		// check
		if((isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']) ||
			(isset($is_another_membership_purchase) && $is_another_membership_purchase == 'Y' )) {
			$multiple_update = true;		
			$multi_memtype = (isset($_POST['membership_type'])) ? $_POST['membership_type'] : $membership_type;
			$member = mgm_get_member_another_purchase($user_id, $multi_memtype);	
		}
		
		// get pack
		if($member->pack_id){
			$subs_pack = $s_packs->get_pack($member->pack_id);
		}else{
			$subs_pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
		}		
		
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'ctransaction', 'txn_id'=>'ctransreceipt');
		// save tracking fields
		$this->_save_tracking_fields($tracking_fields, $member);
		
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
			
			if(isset($member->payment_info->module)) {				
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
					mgm_log('RECALLing '. $member->payment_info->module .': cancel_recurring_subscription FROM: ' . $this->code);
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;
				
			}else 
				return false;
		}	
		
		
		//send email only if setting enabled
		if( !empty($subscr_id) || $subscr_id === 0 ) {
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
		
		return false;
	}

	// default setting
	function _default_setting(){
		// clickbank specific
		$this->setting['username']   = '';
		$this->setting['secret_key'] = '';
		$this->setting['cbskin']     = '';
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
		// parse
		$passthrough = $this->_parse_passthrough();	
		// check
		if($this->_is_transaction($passthrough['custom'])){	
			// tran id
			$tran_id = (int)$passthrough['custom'];			
			// return data				
			if(isset($_POST['ctransaction'])){
				$option_name = $this->module.'_'.strtolower($_POST['ctransaction']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_POST)));
			
			// options 
			$options = array('ctransaction','ctransreceipt');
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
	
	/*
	// get tran id
	function _get_transaction_id(){
		// parse
		$passthrough = $this->_parse_passthrough();	
		// validate
		if($this->_is_transaction($passthrough['custom'])){	
			// tran id
			return $tran_id = (int)$passthrough['custom'];
		}
		// return 
		return 0;	
	}*/
	
	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// verify callback 
	function _verify_callback(){			
		// encode post data
		$encoded_string = sha1($_POST['ccustname'] . '|' . $_POST['ccustemail'] . '|' . $_POST['ccustcc'] . '|' . $_POST['ccuststate'] . '|' . $_POST['ctransreceipt'] . '|' . $_POST['cproditem'] . '|' . $_POST['ctransaction'] . '|' . $_POST['ctransaffiliate'] . '|' . $_POST['ctranspublisher'] . '|' . $_POST['cprodtype'] . '|' . $_POST['cprodtitle'] . '|' . $_POST['ctranspaymentmethod'] . '|' . $_POST['ctransamount'] . '|' . $_POST['caffitid'] . '|' . $_POST['cvendthru'] . '|' . $this->setting['secret_key']);
		// get key
		$key = strtoupper(substr($encoded_string,0,8));
		// match
		if ($_POST['cverify'] == $key) {
			return true;
		}	
		// 2nd
		if($this->_verify_ipn21()){		
			return true;
		}
		// error
		return false;
	}	
	
	// new 
	function _verify_ipn21() {
		$secret_key = $this->setting['secret_key'];
		$pop       = "";
		$ipn_fields = array();
		foreach ($_POST as $key => $value) {
			if ($key == "cverify") {
				continue;
			}
			$ipn_fields[] = $key;
		}
		sort($ipn_fields);
		foreach ($ipn_fields as $field) {
			$field_value = $_POST[$field];
			if (get_magic_quotes_gpc()) {
				$field_value = stripslashes($field_value);
			}
			$pop = $pop . $field_value . "|";
		}
		$pop = $pop . $secret_key;
		$calced_verify = sha1(mb_convert_encoding($pop, "UTF-8"));
		$calced_verify = strtoupper(substr($calced_verify,0,8));
		return ($calced_verify == $_POST["cverify"]);
	}
	
	// get endpoint
	function _get_endpoint($pack, $tran_id=NULL){	
		// string
		if(is_string($pack)){
			return parent::_get_endpoint($pack);
		}else{	
		// array
			// get url
			$endpoint = parent::_get_endpoint();
			// product
			$endpoint = str_replace('[product_id]', $pack['product']['clickbank_product_id'], $endpoint);
			// username
			$endpoint = str_replace('[username]', $this->setting['username'], $endpoint);
			// custom // $this->_set_payment_type($pack)
			$endpoint = str_replace('[custom]', $tran_id, $endpoint);
			// skin
			if(!empty($this->setting['cbskin'])){
				$endpoint = add_query_arg(array('cbskin'=>$this->setting['cbskin']),$endpoint);
			}
			// return
			return $endpoint;
		}	
	}	
	
	//just to recreate the passthrough data
	function _parse_passthrough(){
		// parse		
		parse_str($_POST['cvendthru'], $temp);
		// return
		return $temp;
	}
	
	// setup
	function _setup_endpoints($end_points = array()){
		// define defaults
		$end_points_default = array('test' => false,
									'live' => 'http://[product_id].[username].pay.clickbank.net/?custom=[custom]');	
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// set 
	function _set_address_fields($user, &$data){
		// mappings
		$mappings= array('full_name'=>'name','address'=>'address','city'=>'county','state'=>'state','zip'=>'zip','country'=>'country');
						 
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter'));				 
	}
	
	// filter
	function _address_fields_filter($name, $value){
		// reuse parent filter unless needed
		switch($name){
			case 'address':
				$value = str_replace("\n","", trim($value));
			break;
			default:
				 $value = parent::_address_field_filter($name, $value);		
			break;
		}	
		// return 
		return $value;
	}
	
}

// end file