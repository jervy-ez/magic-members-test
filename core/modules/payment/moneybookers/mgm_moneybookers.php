<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * MoneyBookers (Skrill) Payment Module
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */
class mgm_moneybookers extends mgm_payment{	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_moneybookers();
	}
	
	// php4 construct
	function mgm_moneybookers(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Moneybookers';	
		// logo
		$this->logo = $this->module_url( 'assets/moneybookers.gif' );
		// description
		$this->description = __('Moneybookers is an e-money service allowing payments and money transfers to be made through the Internet. '.
		                        'Moneybookers operates with credit cards, debit cards, bank accounts and Moneybookers balance to make safe '.
		                        'purchases online, without disclosing your credit card number or financial information.', 'mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');
		// trial support available ?
		$this->supports_trial= 'Y';		
		// do we depend on product mapping	
		$this->requires_product_mapping = 'N';
		// type of integration
		$this->hosted_payment = 'Y';// html redirect
		// endpoints
		$this->_setup_endpoints();			
		// message
		$this->moneybookers_msg = 'Make payments with Moneybookers - it\'s fast, free and secure!';
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
				// moneybookers specific
				$this->setting['pay_to_email'] = $_POST['setting']['pay_to_email'];
				$this->setting['merchant_id']  = $_POST['setting']['merchant_id'];	
				$this->setting['secret_word']  = $_POST['setting']['secret_word'];
				$this->setting['currency']     = $_POST['setting']['currency'];		
				$this->setting['language']     = $_POST['setting']['language'];							
				// additional
				$this->setting['recipient_desc']    = $_POST['setting']['recipient_desc'];
				$this->setting['confirmation_note'] = $_POST['setting']['confirmation_note'];
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
		if( isset($_REQUEST['custom']) && !empty($_REQUEST['custom']) ){
			// query arg
			$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($_REQUEST['custom']));
			// is a post redirect?
			$post_redirect =$this->_get_post_redirect($_REQUEST['custom']);
			// set post redirect
			if($post_redirect !==false){
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
			mgm_redirect(add_query_arg(array('status'=>'error'), $this->_get_thankyou_url()));
		}
	}	
	
	// notify process api hook, background IPN url
	function process_notify() {	
		//record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );		
		// verify 
		if($this->_verify_callback()){
			// parse merchant_fields to custom
			$this->_parse_merchant_fields();			
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
					// cancellation
					if(isset($_POST['txn_type']) && $_POST['txn_type']=='subscr_cancel') {						
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
		
		// 200 OK to moneybookers, this is IMPORTANT, otherwise MB will keep on sending IPN max 10 times
		// ......... point 2.3.6 in mb_gateway_manual 6.9
		if( ! headers_sent() ){
			@header('HTTP/1.1 200 OK');
			exit('OK');
		}
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
			(!isset($tran['data']['user_id']) || (isset($tran['data']['user_id']) && (int) $tran['data']['user_id']  < 1))) {
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
		if ($return) {
			return $html;
		} else {
			echo $html;
		}
	}
	
	/*******************************************NOT TESTED ***********************************************	
	// unsubscribe button api hook
	function get_button_unsubscribe($options=array()){
		// action
		$action = add_query_arg(array('module'=>$this->code,'method'=>'payment_unsubscribe'), mgm_home_url('payments'));	
		// message
		$message = sprintf(__('You have subscribed to <b>%s</b> via <b>%s</b>, if you wish to unsubscribe, please click the following link. <br>','mgm'), get_option('blogname'), $this->name);		
		// html
		$html='<div style="margin-bottom: 10px;">
					<h4>'.__('Unsubscribe','mgm').'</h4>
					<div style="margin-bottom: 10px; line-height:15px;">' . $message . '</div>
			   <div>
			   <form name="mgm_unsubscribe_form" id="mgm_unsubscribe_form" method="post" action="'. $action .'">
					<input type="hidden" name="user_id" value="' . $options['user_id'] . '"/>
					<input type="hidden" name="membership_type" value="' . $options['membership_type'] . '"/>
					<input type="button" name="btn_unsubscribe" value="' . __('Unsubscribe','mgm') . '" onclick="confirm_unsubscribe(this)" class="button" />	
			   </form>';	
		// return
		return $html;		
	}	
	********************************************************************************************************/
		
	/*******************************DEPRECATED*************************************************************
	// subscribe button api hook
	function get_button_subscribe($options=array()){		
		$button_code     = $this->_get_button_code($options['pack']);
		$additional_code = do_action('mgm_additional_code');
		$html='<form action="'. $this->_get_endpoint() .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					'. $button_code .'				
					'. $additional_code .'
					<input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">
					<div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>			
				</form>';
		return $html;		
	}
	
	// buypost button api hook
	function get_button_buypost($options=array(), $return = false) {
		global $user;
		// title
		$item_name = (isset($options['pack']['ppp_pack_id'])) ? ('Purchase Post Pack - '.$options['pack']['title']) : ('Purchase Post - '.$options['pack']['title']) ;
		// merge pack
		$options['pack'] = array_merge($options['pack'], array('duration'=>1, 'item_name'=>$item_name, 'buypost'=>1));
		// button code		
		$button_code = $this->_get_button_code($options['pack']);
		$additional_code = do_action('mgm_additional_code');
		// html
		$html = '<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="form-table">
					<form action="'. $this->_get_endpoint() .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
						 <tr>
							 <td align="center">
								 '. $button_code .'							
								 '. $additional_code .'
								 <input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" name="submit" alt="' . $this->name . '">
								 <div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>			
							 </td>
						 </tr>
					 </form>
				</table>';

		if ($return) {
			return $html;
		} else {
			echo $html;
		}
	}
	*****************************************************************************************************/
	
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
		// item 		
		$item = $this->get_pack_item($pack);
		//pack currency over rides genral setting currency - issue #1602
		if(!isset($pack['currency']) || empty($pack['currency'])){
			$pack['currency']=$this->setting['currency'];
		}		
		// setup data array		
		$data = array(
			'pay_to_email'          => $this->setting['pay_to_email'],
			'detail1_description'   => $item['name'],
			'detail1_text'          => $item['name'],
			'recipient_description' => $this->setting['recipient_desc'],
			'confirmation_note'     => $this->setting['confirmation_note'],
			'transaction_id'        => $tran_id,
			'language'              => $this->setting['language'],
			'currency'              => $pack['currency'],
			'status_url'            => $this->setting['notify_url'],
			'cancel_url'            => $this->setting['cancel_url'],
			'return_url_text'       => 'Return to '.get_option('blogname'),									
			'pay_from_email'        => $user->user_email,
			'merchant_fields'       => 'custom'
		);
				
		// address fields	
		$this->_set_address_fields($user, $data);		
		
		// 'modify'=>1, // 1 new, 2 upgrade
		// subscription purchase with ongoing/limited
		if( !isset($pack['buypost']) && isset($pack['duration_type']) && $pack['num_cycles'] != 1 ){ // does not support one-time recurring
		// if ($pack['num_cycles'] != 1 && $pack['duration_type']) { // old style
			// types
			$rc_types                 = array('d'=>'day', 'm'=>'month', 'y'=>'year');	
			// set			
			$data['rec_amount']       = $pack['cost'];
			$data['rec_start_date']   = date('d/m/Y');						
			$data['rec_period']       = $pack['duration'];	
			$data['rec_cycle']        = $rc_types[$pack['duration_type']];
			$data['rec_grace_period'] = 7; // 7 days additional period ot pursue the payment if failed on inital attempt
			$data['rec_status_url']   = $this->setting['notify_url'];// cancel notification
			// greater than 0
			if ($pack['num_cycles']) {
				$data['rec_end_date'] = date('d/m/Y', strtotime("+{$pack['duration']} {$rc_types[$pack['duration_type']]}"));// expiration date				                                          
			}
			// trial
			if ($pack['trial_on']) {// set amount as trial and rec_amount as recurring ???? need test
				$data['amount'] = $pack['trial_cost'];				
			}
		} else {
		// post purchase			
			$data['amount'] = $pack['cost'];					
		}
		
		// custom
		$data['custom'] = $tran_id;
		
		// set custom on request so that it can be tracked for post purchase
		$data['return_url'] = add_query_arg(array('custom'=>$data['custom']), $this->setting['return_url']);
		
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
		$custom = $this->_get_transaction_passthrough($_POST['custom']);
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

		// mb status
		$payment_status = $this->_parse_payment_status($_POST['status']);
		// check
		switch ($payment_status) {
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

			case 'Failed':		
				// status	
				$status_str = __('Last payment was refunded or denied','mgm');
				// purchase status
				$purchase_status = 'Failure';

				// error
				$errors[] = $status_str;	
																  
			break;

			case 'Pending':
				// status
				$status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $_POST['failed_reason_code']);
				// purchase status
				$purchase_status = 'Pending';	

				// error
				$errors[] = $status_str;	
															  
			break;

			default:
				// status
				$status_str = sprintf(__('Last payment status: %s','mgm'),$payment_status);
				// purchase status
				$purchase_status = 'Unknown';	
				
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
		// $member->payment_type    = ($_POST['rec_payment_type']=='recurring') ? 'subscription' : 'one-time';// ondemand
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		// payment info for unsubscribe	
		if(!isset($member->payment_info))	
			$member->payment_info    = new stdClass;
		$member->payment_info->module = $this->code;
		if(isset($_POST['rec_payment_type'])){
			$member->payment_info->txn_type = $_POST['rec_payment_type'];
		}	
		if(isset($_POST['customer_id'])){
			$member->payment_info->subscr_id = $_POST['customer_id'];
		}	
		if(isset($_POST['rec_payment_id'])){	
			$member->payment_info->txn_id = $_POST['rec_payment_id'];	
		}	
		// mgm transaction id
		$member->transaction_id = $_POST['custom'];
		
		// process Moneybookers response
		$new_status = $update_role = false;
		// status
		$payment_status = $this->_parse_payment_status($_POST['status']);
		// status
		switch ($payment_status) {
			case 'Processed':
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
				
				// update expire date
				/* ***********************************/
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
				/*************************************/					
				
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
				
				//clear cancellation status if already cancelled:
				if(isset($member->status_reset_on)) unset($member->status_reset_on);
				if(isset($member->status_reset_as)) unset($member->status_reset_as);
				
				//cancel previous subscription:
				//issue#: 565				
				$this->cancel_recurring_subscription($_POST['custom'], null, null, $pack_id);
							
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
			case 'Failed':			
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was failed','mgm');
				break;

			case 'Pending':
				$new_status = MGM_STATUS_PENDING;

				$reason = $_POST['failed_reason_code'];
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				break;

			default:
				$new_status = MGM_STATUS_ERROR;
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $payment_status);
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
	}
	
	// cancel membership
	function _cancel_membership(){
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
		if (!$currency) $currency = $this->setting['currency'];
		
		// find user
		$user = get_userdata($user_id);
		$member = mgm_get_member($user_id);
		$multiple_update = false;
		// multiple membesrhip level update:		
		if((isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']) || (isset($is_another_membership_purchase) && $is_another_membership_purchase == 'Y' ) ){
			$multiple_update = true;
			$multi_memtype = (isset($_POST['membership_type'])) ? $_POST['membership_type'] : $membership_type;
			$member = mgm_get_member_another_purchase($user_id, $multi_memtype);
		}					
		
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'rec_payment_type', 'subscr_id'=>'customer_id', 'txn_id'=>'rec_payment_id');
		// save tracking fields
		$this->_save_tracking_fields($tracking_fields, $member);
		
		// expire
		$expire_date = $member->expire_date;
		// if lifetime:
		if($member->duration_type == 'l') $expire_date = date('Y-m-d');
		
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
		
		//send email only if setting enabled
		if( !empty($subscr_id) || $subscr_id === 0) {
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
		
		return false;
	}

	// default setting
	function _default_setting(){
		// moneybookers specific
		$this->setting['pay_to_email'] = get_option('admin_email');	
		$this->setting['merchant_id']  = '';	
		$this->setting['secret_word']  = '';				
		$this->setting['currency']     = mgm_get_class('system')->setting['currency'];
		$this->setting['language']     = 'EN';	
		// additional
		$this->setting['recipient_desc']    = get_option('blogname');
		$this->setting['confirmation_note'] = 'Thank you for your payment';
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
			if(isset($_POST['rec_payment_type'])){
				$option_name = $this->module.'_'.strtolower($_POST['rec_payment_type']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_POST)));
			
			// options 
			$options = array('rec_payment_type','customer_id','rec_payment_id','mb_transaction_id');
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
	
	//  parse mb response status
	function _parse_payment_status($status){
		// default
		$mb_status = 'error';
		// check
		switch($status){
			case -2:
			case '-2':
				$mb_status = 'Failed';
			break;
			case 2:
			case '2':
				$mb_status = 'Processed';
			break;
			case 0:
			case '0':
				$mb_status = 'Pending';
			break;
			case -1:
			case '-1':
				$mb_status = 'Cancelled';
			break;
			case -3:
			case '-3':
				$mb_status = 'Chargeback';
			break;
		}		
		// return 
		return $mb_status;
	}
	
	// verify callback 
	function _verify_callback(){
		// verify
		if(isset($_POST['status']) && !empty($_POST['status'])){
			// secret_word
			$secret_word = $this->setting['secret_word'];
			// ascii value
			array_map('ord', str_split($secret_word));
			// upper case of md5 ascii 
			$secret_word_md5 = strtoupper(md5($secret_word)) ;			
			// computed md5sig
			$md5sig_cp = strtoupper(md5($this->setting['merchant_id'] . $_POST['transaction_id'] . $secret_word_md5 . $_POST['mb_amount'] . $_POST['mb_currency'] . $_POST['status']));		
			// match
			if ($_POST['md5sig'] == $md5sig_cp) {
				return true;
			}	
		}	
		// error
		return false;
	}	
	
	// setup
	function _setup_endpoints($end_points = array()){
		// define defaults
		$end_points_default = array('test' => 'http://www.moneybookers.com/app/test_payment.pl',
									'live' => 'https://www.moneybookers.com/app/payment.pl');	
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// set 
	function _set_address_fields($user, &$data){
		// mappings
		$mappings= array('first_name'=>'firstname','last_name'=>'lastname','address'=>array('address','address2'),
		                 'zip'=>'postal_code','phone'=>'phone_number','birthdate'=>'date_of_birth');
						 
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter'));				 
	}
	
	// filter
	function _address_fields_filter($name, $value){
		// reuse parent filter unless needed
		switch($name){
			case 'birthdate':
				$value = date('dmY', strtotime($value));
			break;
			default:
				 $value = parent::_address_field_filter($name, $value);		
			break;
		}	
		// return 
		return $value;
	}
	
	// parse merchant fields
	function _parse_merchant_fields(){
		// check
		if(!isset($_POST['custom']) && isset($_POST['merchant_fields'])){
			// parse
			parse_str($_POST['merchant_fields'], $output);
			// set in post
			$_POST['custom'] = $output['custom'];
		}
	}		
}

// end file