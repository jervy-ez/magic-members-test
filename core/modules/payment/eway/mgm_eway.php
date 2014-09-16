<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * Eway payment module, integrates Eway Recurring & Merchant Hosted payment integtaion 
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */
class mgm_eway extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_eway();
	}
	
	// construct
	function mgm_eway(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Eway';
		// logo
		$this->logo = $this->module_url( 'assets/eway_logo.png' );
		// description
		$this->description = __('Eway Recurring and Merchant Hosted Payments integration for Recurring and Onetime payments.', 'mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');
		// trial support available ?
		$this->supports_trial = 'Y';	
		// cancellation support available ?
		$this->supports_cancellation = 'N';	
		// do we depend on product mapping	
		$this->requires_product_mapping = 'N'; 
		// type of integration
		$this->hosted_payment = 'N';// credit card process onsite
		// if supports rebill status check	
		$this->supports_rebill_status_check = 'Y';
		// endpoints		
		$this->_setup_endpoints();			
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
							// install
							$this->install();
							// status
							$stat = ' enabled.';
						}else{
						// disable
							$this->uninstall();	
							// disabled
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
				// Eway specific				
				$this->setting['customer_id'] 	= $_POST['setting']['customer_id'];						
				//webservice username
				$this->setting['username'] 		= $_POST['setting']['username'];	
				//webservice password					
				$this->setting['password'] 		= $_POST['setting']['password'];						
				// purchase price
				if(isset($_POST['setting']['purchase_price'])){
					$this->setting['purchase_price'] = $_POST['setting']['purchase_price'];
				}
				// update supported card types
				$this->setting['supported_card_types'] = !empty($_POST['card_types']) ? $_POST['card_types'] : array();
				// common
				$this->description = $_POST['description'];
				$this->status      = $_POST['status'];
				// logo if uploaded
				if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
					$this->logo = $_POST['logo_new_'.$this->code];
				}				
				// fix old data
				$this->hosted_payment = 'N';
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
	function process_return($external_data = array()) {	
		// no redirect for schedular
		$redirect = true;
		// check
		/*if(isset($external_data['from']) && $external_data['from'] == 'scheduler') {
			$redirect = false;
		}*/
		
		// response
		if(!isset($this->response)) $this->response = array();			
		// check and show message
		if(isset($this->response['response_status']) && $this->response['response_status'] != 3 ){// 3 == Error
			// process notify, internally called
			$this->process_notify($external_data);
			// redirct
			if($redirect){
				// redirect as success if not already redirected
				$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($_POST['Option1']));
				// is a post redirect?
				$post_redirect = $this->_get_post_redirect($_POST['Option1']);
				// set post redirect
				if($post_redirect !== false){
					$query_arg['post_redirect'] = $post_redirect;
				}		
				// is a register redirect?
				$register_redirect = $this->_auto_login($_POST['Option1']);	
				// set register redirect
				if($register_redirect !== false){
					$query_arg['register_redirect'] = $register_redirect;
				}
				// redirect
				mgm_redirect(add_query_arg($query_arg, $this->_get_thankyou_url()));
			}
		}else{
			// error redirct
			if($redirect){
				mgm_redirect(add_query_arg(array('status'=>'error','errors'=>urlencode($this->response['message_text'])), $this->_get_thankyou_url()));
			}	
		}		
	}
	
	/**
	 * notify process api hook, background IPN url 
	 * used as proxy IPN for this module
	 *
	 */
	function process_notify($external_data = array()){		
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		// response
		if(!isset($this->response)) $this->response = array();					
		// verify
		if ($this->_verify_callback()) {		
			// log data before validate
			$tran_id = $this->_log_transaction();
			// payment type
			$payment_type = $this->_get_payment_type($_POST['Option1']);	
			// custom
			$custom = $this->_get_transaction_passthrough($_POST['Option1']);
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
					// run		
					$this->_buy_membership($external_data); //run the code to process a new/extended membership
				break;	
				// other		
				default:
					// error, do we log it?			
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
	function process_unsubscribe() {				
		// get user id
		$user_id = (int)$_POST['user_id'];		
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// get user
		$user = get_userdata($user_id);	
		$member = mgm_get_member($user_id);		
		//multiple membesrhip level update:
		if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']){
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);				
		}			

		// init
		$cancel_account = true;		
		// check
		if(isset($member->payment_info->module) && $member->payment_info->module == $this->code) {// self check
			// init
			$rebill = null;				
			// subs
			if(!empty($member->payment_info->subscr_id) && !empty($member->payment_info->rebill_customerid) ) {
				// data required to unsubscribe
				$rebill['rebill_id'] 		 	= $member->payment_info->subscr_id;
				$rebill['rebill_customer_id'] 	= $member->payment_info->rebill_customerid;
			}elseif (!empty($member->pack_id)) {	
				// check the pack is recurring
				$s_packs = mgm_get_class('subscription_packs');				
				$sel_pack = $s_packs->get_pack($member->pack_id);			
				// not recurring							
				if($sel_pack['num_cycles'] != 1) $rebill['rebill_id'] = 0;// 0 stands for a lost subscription id
			}

			// cancel at eway
			$cancel_account = $this->cancel_recurring_subscription(null, $user_id, $rebill);						
		}	
			
		// cancel in MGM
		if($cancel_account === true) {
		// cancel
			$this->_cancel_membership($user_id, true);// redirected
		}
			
		// message
		$message = isset($this->response['message_text']) ? $this->response['message_text'] : __('Error while cancelling subscription', 'mgm') ;
		//issue #1521
		if( $is_admin ){
			mgm_redirect( add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php')) );
		}			
		// force full url, bypass custom rewrite bug
		mgm_redirect(mgm_get_custom_url('membership_details', false,array('unsubscribe_errors'=>urlencode($message))));		
	}
		
	// process credit_card, proxy for credit_card processing
	function process_credit_card(){		
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
			return $this->throw_cc_error(__('Transaction invalid . User id field is empty','mgm'));		
		}
		// custom params
		$custom = $this->_get_transaction_passthrough($tran_id);				
		
		// web service
		$use_webservice = true;//whether to use webservice OR recurring payment API
		
		// get data		
		$data = $this->_get_button_data($tran['data'], $tran_id);	
		
		// merge credit card info
		$post_data = array_merge($_POST, $data); 	
		
		// set email
		$this->_set_default_email($post_data, 'CustomerEmail');
		
		// gateway
		$gateway_method = ($post_data['RecurringBilling'] == 'TRUE') ? ($use_webservice ? 'webservice' : 'recurring') : 'xml_cvn';	
			
		// add internal vars
		$secure = array( 'CustomerID' => $this->setting['customer_id'] );
		// webservice credentials
		if($gateway_method == 'webservice') {
			$secure['Username'] = $this->setting['username'];//eWay webservice Username
			$secure['Password'] = $this->setting['password'];//eWay webservice Password			
			// webservice action
			$post_data['webservice_action'] = "CreateRebillCustomer";//CreateRebillCustomer//CreateRebillEvent/QueryNextTransaction/DeleteRebillEvent/QueryTransactions
		}else{
		// no action
			$post_data['webservice_action'] = null;
		}	
					 			
		// merge
		$post_data = array_merge($post_data, $secure);// overwrite post data array with secure params
						
		// store custom
		$_POST['Option1'] = $post_data['Option1'];		
		
		// pack
		$pack = $tran['data'];
		// if live pre-auth
		if($this->status == 'live' && $gateway_method != 'xml_cvn' ){
			// pre auth
			if($this->_pre_auth($post_data) === FALSE){
				// return to credit card form
				return $this->throw_cc_error();	
			}
			// keep track
			$post_data['OrgRebillInitAmt'] = $post_data['RebillInitAmt'];	
			$post_data['OrgRebillStartDate'] = $post_data['RebillStartDate'];	
			
			// InitAmount should be zero when we have a complete pre auth
			if( isset($this->response['preauth_complete']) && $this->response['preauth_complete'] == TRUE ){
				$post_data['RebillInitAmt'] = 0;	
			}						
		}		

		// log 
		mgm_log( 'Post Data RebillEvent [' . print_r($post_data,true) . ']', __FUNCTION__);	

		// filter post data for api
		$post_string = $this->_filter_postdata($gateway_method, $post_data);
		
		// end  point						
		$endpoint = $this->_get_endpoint($this->status.'_'.$gateway_method); // test_recurring, live_recurring etc.		
		
		// headers
		$http_headers = $this->_get_http_headers($gateway_method, $post_data['webservice_action']);
		
		// log
		mgm_log( 'Request Headers [' . $post_data['webservice_action'] . ']' . mgm_pr($http_headers, true), __FUNCTION__);	
		
		// log
		mgm_log( 'Request [' . $post_data['webservice_action'] . ']' . $post_string, __FUNCTION__);	
							
		// create curl post				
		$http_response = mgm_remote_post($endpoint, $post_string, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
		
		// log
		mgm_log( 'Response [' . $post_data['webservice_action'] . ']' . $http_response, __FUNCTION__);	
		
		// create curl post				
		// $buffer = $this->_curl_post($endpoint, $post_string, $http_header);
		
		// parse response		
		$this->_process_response($gateway_method, $http_response, $post_data['webservice_action']);
		
		// log
		mgm_log('Response Parsed [' . $post_data['webservice_action'] . ']' . mgm_pr($this->response, true), __FUNCTION__);	
		
		// return error if any
		if($this->response['response_status'] != 1 && isset($this->response['message_text']) && !empty($this->response['message_text']) ) {
			// return to credit card form
			return $this->throw_cc_error();			
		}
		
		// web service
		if($gateway_method == 'webservice') {
			// status OK, create rebill event
			if($this->response['response_status'] === 1) {
				// create rebill event
				$post_data['webservice_action'] = 'CreateRebillEvent';
				// created rebill customer id
				$post_data['RebillCustomerID'] = $this->response['rebillcustomerid'];
				// soap_action
				$soap_action = sprintf('http://www.eway.com.au/gateway/rebill/manageRebill/%s', $post_data['webservice_action']);
				// http_headers
				$http_headers = array_merge($http_headers, array('SOAPAction' => $soap_action)); 										
				// create post string
				$post_string = $this->_filter_postdata($gateway_method, $post_data);	
				// log
				mgm_log( 'Request Headers [' . $post_data['webservice_action'] . ']' . mgm_pr($http_headers, true), __FUNCTION__);	
				// log
				mgm_log( 'Request [' . $post_data['webservice_action'] . ']' . $post_string, __FUNCTION__);	
				// create curl post				
				$http_response = mgm_remote_post($endpoint, $post_string, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
				// log
				mgm_log( 'Response [' . $post_data['webservice_action'] . ']' . $http_response, __FUNCTION__);	
				// create curl post				
				// $buffer = $this->_curl_post($endpoint, $post_string, $http_header);				
				// parse response		
				$this->_process_response($gateway_method, $http_response, $post_data['webservice_action']);			
				// log
				mgm_log('Response Parsed [' . $post_data['webservice_action'] . ']' . mgm_pr($this->response, true), __FUNCTION__);	
			}
		}
		
		// treat as return
		$this->process_return();			
	}	
	
	// process html_redirect, proxy for form submit
	// The credit card form will get submitted to the same function, then validate the card and if everything is clear
	// process_credit_card() will be called internally
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
		$html.='<form action="'. $this->_get_endpoint('html_redirect') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					<input type="hidden" name="tran_id" value="'.$tran_id.'">
					<input type="hidden" name="submit_from" value="'.__FUNCTION__.'">
					'. $cc_fields .'
			   </form>';
		// return 	  
		return $html;					
	}	
	
	// subscribe button api hook
	function get_button_subscribe($options=array()){	
		// sidebar payment
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
		$message = sprintf(__('You have subscribed to %s via %s, if you wish to unsubscribe, please click the following link. <br>','mgm'), get_option('blogname'), $this->name);
		// html
		$html='<div class="mgm_margin_bottom_10px">
					<h4>'.__('Unsubscribe','mgm').'</h4>
					<div class="mgm_margin_bottom_10px">' .  $message .	'</div>
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
		// init
		$this->dependency = array();		
		// check
		if(!extension_loaded('SimpleXML')){
			$this->dependency[] = '<b class="mgm_module_dependency_high">'.__('SimpleXML PHP extension must be loaded for Eway Recurring and Hosted Payments','mgm').'.</b>';
		}
		// error		
		return (count($this->dependency)>0) ? true : false ;		
	}
	
	// get module transaction info
	function get_transaction_info($member, $date_format){		
		// data
		$rebill_customerid = (int)$member->payment_info->rebill_customerid;
		$rebill_id         = (int)$member->payment_info->subscr_id;
		// check rebill start/end
		// set default
		$rebill_start_date = $rebill_end_date = $eway_txn_id  = __('N/A','mgm');
		// check
		if( !isset($member->payment_info->rebill_start_date) ){
			// run
			$this->query_rebill_info($user->ID,$member);							
		}						
		// set
		if( isset($member->payment_info->rebill_start_date) ){ 
			$rebill_start_date = date($date_format, strtotime($member->payment_info->rebill_start_date));	
			$rebill_end_date   = date($date_format, strtotime($member->payment_info->rebill_end_date));
		}
		// eway tran
		if(isset($member->payment_info->eway_txn_id)){
			$eway_txn_id = $member->payment_info->eway_txn_id;
		}
		// info
		$info = sprintf('<b>%s:</b><br>%s: %d<br>%s: %d<br>%s: %s<br>%s: %s<br>%s: %s', 
						__('EWAY INFO','mgm'), __('CUSTOMER ID','mgm'), $rebill_customerid, 
						__('REBILL ID','mgm'), $rebill_id, __('REBILL START DT.','mgm'), $rebill_start_date,
						__('REBILL END DT.','mgm'), $rebill_end_date, __('TRANSACTION ID','mgm'), $eway_txn_id);					
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
		// html
		$html = sprintf('<p>%s: <input type="text" size="20" name="eway[rebill_customerid]"/></p>
				 		 <p>%s: <input type="text" size="20" name="eway[rebill_id]"/></p>
						 <p>%s: <input type="text" size="20" name="eway[invoice_ref]"/></p>
						 <p>%s: <input type="text" size="20" name="eway[eway_txn_id]"/></p>', 
						 __('Rebill Customer ID','mgm'), __('Rebill ID','mgm'), __('Invoice Ref','mgm'), __('Eway Transaction ID','mgm'));
		
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
		$fields = array('rebill_customerid'=>'rebill_customerid','subscr_id'=>'rebill_id','txn_id'=>'invoice_ref','eway_txn_id'=>'eway_txn_id');
		// data
		$data = $post_data['eway'];
		// unset unnecessary
		if(isset($member->payment_info->rebill_start_date)){
			unset($member->payment_info->rebill_start_date, $member->payment_info->rebill_end_date);	
		}
	 	// update
	 	return $this->_save_tracking_fields($fields, $member, $data); 				
	 }
	 
	// MODULE API COMMON PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// get button data
	function _get_button_data($pack, $tran_id=NULL) {				
		// system
		$system_obj = mgm_get_class('system');	
		$s_packs = mgm_get_class('subscription_packs');	
		$user_id = $pack['user_id'];
		$user = get_userdata($user_id);
		// item 		
		$item = $this->get_pack_item($pack);	
		// set data
		$data = array(							
			'TrxnNumber'	             => $tran_id, 	
			'CustomerInvoiceDescription' => $item['name'],			
			'CustomerEmail'              => $user->user_email		
		);
		
		// additional fields
		$this->_set_address_fields($user, $data);
		
		// subscription purchase with ongoing/limited
		if( !isset($pack['buypost']) && isset($pack['duration_type']) && $pack['num_cycles'] != 1 ){ // does not support one-time recurring
		// if ($pack['num_cycles'] != 1 && isset($pack['duration_type'])) { // old style
			$data['RecurringBilling'] = 'TRUE'; 
			//get current datetime for eWay
			$data['EwayTime'] = $eway_time = $this->_datetime_to_eway_serverdatetime(date('Y-m-d H:i:s'));
			// cust id
			$data['CustomerRef'] = $user_id;
			// interval types
			$interval_types = array('d'=>'1','w' => '2', 'm'=>'3', 'y'=>'4' );
			// exprs
			$data['DurationExprs'] = $duration_exprs = $s_packs->get_duration_exprs();//array('d'=>'DAY','w' => 'WEEK', 'm'=>'MONTH', 'y'=>'YEAR' );
			
			// first payment amount
			$data['RebillInitAmt'] = 0;	
			// first payment date
			$data['RebillInitDate'] = date('d/m/Y', strtotime($eway_time));	// today
			
			// normal recur amount
			$data['RebillRecurAmt']     = number_format(($pack['cost'] * 100), 0, '.', ''); //send price in cents: $1 = 100 cents
			$data['RebillStartDate']    = date('d/m/Y', strtotime($eway_time));	// today		
			$data['RebillInterval']     = $pack['duration'];
			$data['RebillIntervalType'] = $interval_types[$pack['duration_type']]; // days|months|years
					
			// recurring ongoing =0 must be set as 9999, or integer 1-99
			// occurrences: greater than 0
			if ($pack['num_cycles']) { // limited
				$end_ts = '+' . ((int)$pack['num_cycles'] * (int)$pack['duration']) . ' ' . $duration_exprs[$pack['duration_type']];
			}else{// ongoing 
				// $end_ts = '+99 ' . $intv_types_ts[$pack['duration_type']];
				$end_ts = '+10 YEAR' ;// ongoing, 10 years ahead as per iss#645
			}						
			
			// recurring end date
			$data['RebillEndDate'] = date('d/m/Y', strtotime($end_ts, strtotime($eway_time)));				
			
			// temp var
			$data['TrialOn'] = $pack['trial_on'];	

			// trial on
			if ($pack['trial_on']) {
				// init amount
				$data['RebillInitAmt'] = number_format(($pack['trial_cost'] * 100), 0, '.', '');// 0 or greater
				// update RebillStartDate, it will be later than Trial init date				
				$trial_end_ts = '+' . ((int)$pack['trial_num_cycles'] * (int)$pack['trial_duration']) . ' ' . $duration_exprs[$pack['trial_duration_type']];
				// update			
				$data['RebillStartDate'] = date('d/m/Y', strtotime($trial_end_ts, strtotime($eway_time)));																				
			}else{
				$data['RebillStartDate'] = date('d/m/Y', strtotime('+' . (1 * (int)$pack['duration']) . ' ' . $duration_exprs[$pack['duration_type']]));
			}			
		}else{
		// post purchase
			$data['RecurringBilling'] = 'FALSE';
			// set price for addons
			$data['TotalAmount'] = $pack['cost'];				
			// apply addons
			$this->_apply_addons($pack, $data, array('amount'=>'TotalAmount','description'=>'CustomerInvoiceDescription'));
			// set
			$data['TotalAmount'] = number_format(($data['TotalAmount'] * 100), 0, '.', ''); // total amount
		} 
				
		// custom
		$data['Option1'] = $tran_id;
		
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
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($_POST['Option1']);
		// local var
		extract($custom);

		// set user
		$user = null;
		// check
		if(isset($user_id) && (int)$user_id > 0) $user = get_userdata($user_id);

		// errors
		$errors = array();
		// purchase status
		$purchase_status = 'Error';

		// response code
		$response_code = $this->_get_response_code($this->response['response_status'], 'status');
		// process on response code
		switch ($response_code) {
			case 'Approved':
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';
				
				// transation id
				$transaction_id = $this->_get_transaction_id('Option1');
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
				// status
				$reason = $this->response['message_text'];
				$status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
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
		
		// join 
		$status = __('Failed join', 'mgm'); // overridden on a successful payment
		// check status
		if ( $purchase_status == 'Success' ) {
			// mark as purchased
			if( isset($user->ID) ){	// purchased by user	
				// call coupon action
				do_action('mgm_update_coupon_usage', array('user_id' => $user_id));		
				// set as purchased	
				$this->_set_purchased($user_id, $post_id, NULL, $_POST['Option1']);
			}else{
				// purchased by guest
				if( isset($guest_token) ){
					// issue #1421, used coupon
					if(isset($coupon_id) && isset($coupon_code)) {
						// call coupon action
						do_action('mgm_update_coupon_usage', array('guest_token' => $guest_token,'coupon_id' => $coupon_id));
						// set as purchased
						$this->_set_purchased(NULL, $post_id, $guest_token, $_POST['Option1'], $coupon_code);
					}else {
						$this->_set_purchased(NULL, $post_id, $guest_token, $_POST['Option1']);				
					}
				}
			}	
			
			// status
			$status = __('The post was purchased successfully', 'mgm');
		}
		
		// transaction status
		mgm_update_transaction_status($_POST['Option1'], $status, $status_str);
		
		// blog
		$blogname = get_option('blogname');	
		// post being purchased			
		$post = get_post($post_id);
			
		// notify user, only if gateway emails on	
		if( ! $dpne ) {			
			// notify user
			if( isset($user->ID) ){
				// mgm post setup object
				$post_obj = mgm_get_post($post_id);
				// check
				if( $this->send_payment_email($_POST['Option1']) ) {	
				// check
					if( mgm_notify_user_post_purchase($blogname, $user, $post, $purchase_status, $system_obj, $post_obj, $status_str) ){
					// update as email sent 
						$this->update_paymentemail_sent($_POST['Option1']);
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
	
	/**
	 * update membership details
	 *
	 * @param array $external_data : Eg: array('from' => 'scheduler', 'expire_date' => $member->expire_date );
	 * 
	 */
	function _buy_membership($external_data = array()) {			
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));		
		
		// time stamp
		$local_timestamp = time();
		$eway_timestamp  = strtotime( $this->_datetime_to_eway_serverdatetime( date('Y-m-d H:i:s') ) );
		$redirect        = true;
		$timestamp       = $eway_timestamp;
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($_POST['Option1']);
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
		if (!$join_date = $member->join_date) $member->join_date = $timestamp; // Set current AC join date		

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
			// free
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
		// $member->payment_type    = 'subscription';
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		$member->transaction_id  = $_POST['Option1'];
		// payment info for unsubscribe	
		if(!isset($member->payment_info))	
			$member->payment_info = new stdClass;
		// set 	
		$member->payment_info->module = $this->code;
		
		// transaction type
		if(isset($this->response['transaction_type'])){	
			$member->payment_info->txn_type = $this->response['transaction_type'];	
		}
		// subscription
//		if(isset($this->response['subscription_id'])){
//			$member->payment_info->subscr_id = $this->response['subscription_id'];		
//		}
		//add Rebill Id to payment_info
		if(isset($this->response['rebillid'])){
			$member->payment_info->subscr_id = $this->response['rebillid'];		
		}
		//add Rebill Customer Id to payment_info		
		if(isset($this->response['rebillcustomerid'])){
			$member->payment_info->rebill_customerid = $this->response['rebillcustomerid'];		
		}
		// mgm transaction id	
		if(isset($this->response['transaction_id'])){	
			$member->payment_info->txn_id = $this->response['transaction_id'];	
		}
		// process response
		$new_status = $update_role = false;
		// errors
		$errors = array();	
		// response code
		$response_code = $this->_get_response_code($this->response['response_status'], 'status');				
		// process on response code
		switch ($response_code) {
			case 'Approved':
				// new 
				$new_status = MGM_STATUS_ACTIVE;
				$member->status_str = __('Last payment was successful','mgm');					
				
				// time when its updated
				$time = $timestamp;	
				// if coming from scheduler, use preset timestamp, else use localtimestamp	
				#645 repeat bug			
				/*if(isset($external_data['from']) && $external_data['from'] == 'scheduler') {
					// eway transaction date
					$member->last_pay_date = (isset($this->response['transactiondate'])) ? date('Y-m-d', strtotime($this->response['transactiondate'])) : date('Y-m-d', $time);	
				}else{ 
					$member->last_pay_date = date('Y-m-d', $local_timestamp);										
				}*/
				
				// last pay
				$last_pay_date = isset($member->last_pay_date) ? $member->last_pay_date : null;
				$member->last_pay_date = date('Y-m-d', $local_timestamp);	
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
							// update expire date
							// calc expiry	- issue #1226
							// membership extend functionality broken if we try to extend the same day so removed && $last_pay_date != date('Y-m-d', $time) check	
							if (!empty($member->expire_date) ) {
								// expiry
								$expiry = strtotime($member->expire_date);
								// check
								// later date
								if ($expiry > 0 && $expiry > $time) {
									// update
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
							$time = strtotime('+' . $trial_duration . ' ' . $duration_exprs[$trial_duration_type], $time);								
						}					
					}else {
						// time - issue #1068
						$time = strtotime('+' . $member->duration . ' ' . $duration_exprs[$member->duration_type], $time);								
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
				
				// cancel previous subscription:
				// issue#: 565			
				$this->cancel_recurring_subscription($_POST['Option1'], null, null, $pack_id);
					
				// role update
				if ($role) $update_role = true;		
				
				// transaction_id
				$transaction_id = $this->_get_transaction_id('Option1');
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

			case 'Declined':
			case 'Refunded':
			case 'Denied':
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				// error
				$errors[] = $member->status_str;
			break;
			
			case 'Pending':
			case 'Held for Review':
				$new_status = MGM_STATUS_PENDING;
				$reason = $this->response['message_text'];
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);				
				// error
				$errors[] = $member->status_str;
			break;
			
			case 'Expired':
				$new_status = MGM_STATUS_EXPIRED;
				
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $response_code.' - '.$this->response['message_text']);
				// error
				$errors[] = $member->status_str;
			break;
			default:
				$new_status = MGM_STATUS_ERROR;
				
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $response_code.' - '.$this->response['message_text']);
				// error
				$errors[] = $member->status_str;
			break;
		}
		
		// old status
		$old_status = $member->status;	
		// set new status
		$member->status = $new_status;
		
		// whether to acknowledge the user - This should happen only once
		$acknowledge_user = $this->send_payment_email($_POST['Option1']);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $_POST['Option1']);		
		
		//another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {			//issue #1227
			// hide
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
		
		// action
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
					$this->update_paymentemail_sent($_POST['Option1']);	
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
		if($redirect && count($errors)>0){			
			mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
		}
	}
	
	// cancel membership
	function _cancel_membership($user_id, $redirect = false){
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs  = mgm_get_class('subscription_packs');
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
		
		// transaction	
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
			$message = sprintf(__("You have successfully Unsubscribed. Your account has been marked for Cancellation on %s", "mgm"), ($expire_date == date('Y-m-d') ? 'Today' : date($lformat, strtotime($expire_date)) ));
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
	 * Cancellation includes 2 webservice calls
	 * 	1. delete Rebill Event on eWay
	 *  2. delete Rebill Customer on eWay
	 * This is not a private function
	 * @param int/string $trans_ref	
	 * @param int $user_id	
	 * @param int/string $rebill_id	
	 * @param int $pack_id	
	 * @return boolean
	 */	
	function cancel_recurring_subscription($trans_ref = null, $user_id = null, $rebill = null, $pack_id = null) {
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
					$rebill['rebill_id'] 			= $member->payment_info->subscr_id; 
					$rebill['rebill_customer_id'] 	= $member->payment_info->rebill_customerid; 
				}else {
					//check pack is recurring:
					$pid = $pack_id ? $pack_id : $member->pack_id;
					
					if($pid) {
						$s_packs = mgm_get_class('subscription_packs');
						$sel_pack = $s_packs->get_pack($pid);												
						if($sel_pack['num_cycles'] != 1)
							$rebill['rebill_id'] = 0;// 0 stands for a lost subscription id
					}										
				}			
													
				//check for same module: if not call the same function of the applicale module.
				if(str_replace('mgm_','' , $member->payment_info->module) != str_replace( 'mgm_','' , $this->code ) ) {
					// log
					mgm_log('RECALLing '. $member->payment_info->module .': cancel_recurring_subscription FROM: ' . $this->module,  $this->module . '_cancel');
					// return
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;
				
			}else 
				return false;
		}		
		//if valid rebill data is found and settings are set
		if((!empty($rebill['rebill_id']) && !empty($rebill['rebill_customer_id'])) &&
			!empty($this->setting['customer_id']) &&
			!empty($this->setting['username']) &&
			!empty($this->setting['password'])			
		) {
						
			$gateway_method = 'webservice';
			$secure = array( 'CustomerID' => $this->setting['customer_id'],
							 'Username'	  => $this->setting['username'],//eWay webservice Username
							 'Password'	  => $this->setting['password'],//eWay webservice Username
					  );
			//data to post			
			//Delete Rebill Event 	 
			$post_data = array( 'webservice_action'	=> 'DeleteRebillEvent',
								'RebillCustomerID'	=> $rebill['rebill_customer_id'],
								'RebillID'			=> $rebill['rebill_id']								
						 );				 
			$post_data = array_merge($post_data, $secure);					
			// filter post data and create soap xml
			$post_string = $this->_filter_postdata($gateway_method, $post_data);				
			// endpoint					 
			$endpoint = $this->_get_endpoint($this->status.'_'.$gateway_method) ; // test_webservice / live_webservice	
			
			// headers
			$http_headers = $this->_get_http_headers($gateway_method, $post_data['webservice_action']);	
			// log
			mgm_log( 'Request Headers [' . $post_data['webservice_action'] . ']' . mgm_pr($http_headers, true), __FUNCTION__);	
			// log
			mgm_log( 'Request [' . $post_data['webservice_action'] . ']' . $post_string, __FUNCTION__);							
			// create curl post				
			$http_response = mgm_remote_post($endpoint, $post_string, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));	
			// log
			mgm_log( 'Response [' . $post_data['webservice_action'] . ']' . $http_response, __FUNCTION__);
			/*$http_header = array('User-Agent: NuSOAP/0.9.5 (1.123)', 'Content-Type: text/xml; charset=ISO-8859-1', 
			                     sprintf('SOAPAction: "http://www.eway.com.au/gateway/rebill/manageRebill/%s"', $post_data['webservice_action']));*/	
			// post soap data				
			// $buffer = $this->_curl_post($endpoint, $post_string, $http_header);
			// parse response		
			$this->_process_response($gateway_method, $http_response, $post_data['webservice_action']);
			// log
			mgm_log( 'Response Parsed [' . $post_data['webservice_action'] . ']' . mgm_pr($this->response, true), __FUNCTION__);	
			//if rebill event is deleted, delete rebill customer			
			if(isset($this->response['response_status']) && $this->response['response_status'] == 1) {
				//delete rebill customer:			
				$post_data['webservice_action']	= 'DeleteRebillCustomer';
				// filter post data and create soap xml
				$post_string = $this->_filter_postdata($gateway_method, $post_data);	
				// soap_action
				$soap_action = sprintf('http://www.eway.com.au/gateway/rebill/manageRebill/%s', $post_data['webservice_action']);
				// http_headers
				$http_headers = array_merge($http_headers, array('SOAPAction' => $soap_action)); 	
				// log
				mgm_log( 'Request Headers [' . $post_data['webservice_action'] . ']' . mgm_pr($http_headers, true), __FUNCTION__);	
				// log
				mgm_log( 'Request [' . $post_data['webservice_action'] . ']' . $post_string, __FUNCTION__);					
				// create curl post				
				$http_response = mgm_remote_post($endpoint, $post_string, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
				// log
				mgm_log( 'Response [' . $post_data['webservice_action'] . ']' . $http_response, __FUNCTION__);
				/*$http_header = array('User-Agent: NuSOAP/0.9.5 (1.123)', 'Content-Type: text/xml; charset=ISO-8859-1', 
				                     sprintf('SOAPAction: "http://www.eway.com.au/gateway/rebill/manageRebill/%s"', $post_data['webservice_action']));*/	
				// post soap data				
				// $buffer = $this->_curl_post($endpoint, $post_string, $http_header);
				// parse response		
				$this->_process_response($gateway_method, $http_response, $post_data['webservice_action']);
				// log
				mgm_log( 'Response Parsed [' . $post_data['webservice_action'] . ']' . mgm_pr($this->response, true), __FUNCTION__);	
				// check
				if(isset($this->response['response_status']) && $this->response['response_status'] == 1) {
					//done
					return true;
				}
			}			
		}
		//send notification email to Admin
		elseif ($rebill['rebill_id'] === 0) {	
			$system_obj = mgm_get_class('system');
			
			$dge    = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
			//send email only if setting enabled	
			if(!$dge) {
				$user = get_userdata($user_id);
				//send notification email to admin:
				$message = (__('The User: ', 'mgm')). $user->user_email.' ('. $user_id .') '.(__('has upgraded/cancelled subscription.', 'mgm'));
				$message .= "<br/>" .__('Please unsubscribe the user from Gateway Merchant panel.', 'mgm');
				if(!empty($rebill['rebill_customer_id']))
					$message .= "<br/><br/>" .__('Customer Rebill Id: ','mgm' ) . $rebill['rebill_customer_id'];
				if(!empty($rebill['rebill_id']))
					$message .= "<br/><br/>" .__('Rebill Id: ','mgm' ) . $rebill['rebill_id'];	
				if(isset($member->transaction_id))				
					$message .= "<br/>" .__('MGM Transaction Id:' ,'mgm' ) . $member->transaction_id;		
				//admin email:
				if(!empty($system_obj->setting['admin_email']))
					@mgm_mail($system_obj->setting['admin_email'], sprintf(__('[%s] User Subscription Cancellation', 'mgm'), get_option('blogname')), $message);
			}
			//treat as done
			return true;
		}
		
		return false;
	}

	/**
	 * Specifically check recurring status of each rebill for an expiry date
	 * As Eway doesn't have an IPN post mechanism for rebills, the module will need to specifically request for the rebill status
	 * @param int $user_id
	 * @param object $member
	 * @return boolean
	 */
	function query_rebill_status($user_id, $member=NULL) {	
		// check	
		if (isset($member->payment_info->rebill_customerid) && !empty($member->payment_info->rebill_customerid) 
		    && isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id) ) {			
			//if settings are blank
			if (empty($this->setting['username']) || empty($this->setting['password']) || empty($this->setting['customer_id']) ) 
				return false;			
			// eway time
			$eway_time = $this->_datetime_to_eway_serverdatetime( date('Y-m-d H:i:s') );
			// check rebill start date
			if( ! isset($member->payment_info->rebill_start_date) // not set
				|| (isset($member->payment_info->rebill_start_date) // calling before
					&& strtotime( $eway_time ) <= strtotime($member->payment_info->rebill_start_date)) ){
				
				// log
				mgm_log( sprintf('Exit flag since rebill start date not reached Eway Time Today: %s, Rebill Start Date: %s', $eway_time, $member->payment_info->rebill_start_date), $this->module . '_' . __FUNCTION__);
				// return
				return false;						
			}	
			// setup
			$gateway_method = 'webservice';
			$secure = array( 'CustomerID' => $this->setting['customer_id'],
							 'Username'	  => $this->setting['username'],//eWay webservice Username
							 'Password'	  => $this->setting['password'],//eWay webservice Username
							 );
			// packs
			$s_packs = mgm_get_class('subscription_packs');			
			// pack
			$pack = $s_packs->get_pack((int)$member->pack_id);
			// durations				 
			$duration_exprs = $s_packs->get_duration_exprs();
			// time
			$time = time();
			// start date is last pay date as per pack
			$start_date = date('Y-m-d', strtotime(sprintf('- %d %s', $pack['duration'], $duration_exprs[$pack['duration_type']]), $time));
			// end date is today
			$end_date = date('Y-m-d', $time);				 
			//data to post				 
			$post_data = array( 'Option1' 			=> $member->payment_info->txn_id,
								'webservice_action'	=> 'QueryTransactions',
								'RebillCustomerID'	=> $member->payment_info->rebill_customerid,
								'RebillID'			=> $member->payment_info->subscr_id,
								'startDate'			=> $start_date,//
								'endDate'			=> $end_date
								//'startDate'	    => $member->expire_date,//
								//'endDate'			=> $member->expire_date,// { To find the transaction for the expiry date
								//'rebillStatus'	=> 'Successful', //look for success status 
								);	
			// merge								 
			$post_data = array_merge($post_data, $secure);					
			// filter post data and create soap xml
			$post_string = $this->_filter_postdata($gateway_method, $post_data);				
			// endpoint
			$endpoint = $this->_get_endpoint($this->status.'_'.$gateway_method) ; // test_webservice / live_webservice			
			
			// headers
			$http_headers = $this->_get_http_headers($gateway_method, $post_data['webservice_action']);	
			// log
			mgm_log( 'Request Headers [' . $post_data['webservice_action'] . ']' . mgm_pr($http_headers, true), __FUNCTION__);	
			// log
			mgm_log( 'Request [' . $post_data['webservice_action'] . ']' . $post_string, __FUNCTION__);								
			// create curl post				
			$http_response = mgm_remote_post($endpoint, $post_string, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
			// log
			mgm_log( 'Response [' . $post_data['webservice_action'] . ']' . $http_response, __FUNCTION__);
			/*// header
			$http_header = array('User-Agent: NuSOAP/0.9.5 (1.123)', 'Content-Type: text/xml; charset=ISO-8859-1', 
			                     sprintf('SOAPAction: "http://www.eway.com.au/gateway/rebill/manageRebill/%s"', $post_data['webservice_action']));*/
			// post soap data				
			// $buffer = $this->_curl_post($endpoint, $post_string, $http_header);		
				
			// parse response		
			$this->_process_response($gateway_method, $http_response, $post_data['webservice_action']);	
			// log
			mgm_log( 'Response Parsed [' . $post_data['webservice_action'] . ']' . mgm_pr($this->response, true), __FUNCTION__);				
			// check		
			if (isset($this->response['response_status'])) {
				// old status
				$old_status = $member->status;	
				// get response code				
				$response_code = $this->_get_response_code($this->response['response_status'], 'status');	
				// log
				mgm_log('Response Code' . $response_code, __FUNCTION__);
				// process on response code
				switch ($response_code) {
					case 'Approved':
						// set new status
						$member->status = $new_status = MGM_STATUS_ACTIVE;
						// status string
						$member->status_str = __('Last payment cycle processed successfully','mgm');	
						// last pay date
						$member->last_pay_date = (isset($this->response['transactiondate'])) ? date('Y-m-d', strtotime($this->response['transactiondate'])) : date('Y-m-d');	
						// expire date
						if(isset($this->response['transactiondate']) && !empty($member->expire_date)){							
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
						// set eway txn no
						if(isset($this->response['transactionnumber'])){
							$member->payment_info->eway_txn_id = $this->response['transactionnumber'];
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

						// only run in cron, other wise too many tracking will be added
						// if( defined('DOING_QUERY_REBILL_STATUS') && DOING_QUERY_REBILL_STATUS != 'manual' ){
						// after cancellation hook
						do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));	
						// }
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
				if( isset($new_status)  && $new_status != $old_status){
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
	
	/**
	 * Specifically check recurring status of each rebill for an expiry date
	 * As Eway doesn't have an IPN post mechanism for rebills, the module will need to specifically request for the rebill status
	 * @param int $user_id
	 * @param object $member
	 * @return boolean
	 */
	function query_rebill_info($user_id, &$member) {	
		// check	
		if (isset($member->payment_info->rebill_customerid) && isset($member->payment_info->subscr_id) ) {			
			//if settings are blank
			if (empty($this->setting['username']) || empty($this->setting['password']) || empty($this->setting['customer_id']) ) 
				return false;	
			
			// method
			$gateway_method = 'webservice';
			// secure
			$secure = array( 'CustomerID' => $this->setting['customer_id'],
							 'Username'	  => $this->setting['username'],//eWay webservice Username
							 'Password'	  => $this->setting['password'],//eWay webservice Username
							 );						 
			// data to post				 
			$post_data = array( 'webservice_action'	=> 'QueryRebillEvent',
								'RebillCustomerID'	=> $member->payment_info->rebill_customerid,
								'RebillID'			=> $member->payment_info->subscr_id);	
			// merge								 
			$post_data = array_merge($post_data, $secure);					
			// filter post data and create soap xml
			$post_string = $this->_filter_postdata($gateway_method, $post_data);				
			// endpoint
			$endpoint = $this->_get_endpoint($this->status.'_'.$gateway_method) ; // test_webservice / live_webservice			
			// headers
			$http_headers = $this->_get_http_headers($gateway_method, $post_data['webservice_action']);						
			// log
			mgm_log( 'Request Headers [' . $post_data['webservice_action'] . ']' . mgm_pr($http_headers, true), __FUNCTION__);	
			// log
			mgm_log( 'Request [' . $post_data['webservice_action'] . ']' . $post_string, __FUNCTION__);			
			// create curl post				
			$http_response = mgm_remote_post($endpoint, $post_string, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
			// log
			mgm_log( 'Response [' . $post_data['webservice_action'] . ']' . $http_response, __FUNCTION__);
			// header
			/*$http_header = array('User-Agent: NuSOAP/0.9.5 (1.123)', 'Content-Type: text/xml; charset=ISO-8859-1', 
			                     sprintf('SOAPAction: "http://www.eway.com.au/gateway/rebill/manageRebill/%s"', $post_data['webservice_action']));*/
			// post soap data				
			// $buffer = $this->_curl_post($endpoint, $post_string, $http_header);			
			// parse response		
			$this->_process_response($gateway_method, $http_response, $post_data['webservice_action']);	
			// log
			mgm_log( 'Response Parsed [' . $post_data['webservice_action'] . ']' . mgm_pr($this->response, true), __FUNCTION__);	
			// check
			if (isset($this->response['response_status']) && $this->response['response_status']==1) {
				// make time
				list($d,$m,$y) = explode('/',$this->response['rebillstartdate']);
				// set
				$member->payment_info->rebill_start_date = date('Y-m-d', mktime(0,0,0,$m,$d,$y));
				// make time
				list($d,$m,$y) = explode('/',$this->response['rebillenddate']);
				// set
				$member->payment_info->rebill_end_date   = date('Y-m-d', mktime(0,0,0,$m,$d,$y));
				// save
				$member->save();
				// return 
				return true;
			}				
		}
		// return
		return false;//default to false to skip normal modules
	}
	
	// default setting
	function _default_setting(){
		// eway specific
		$this->setting['customer_id'] = '';	
			
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
		if($this->_is_transaction($_POST['Option1'])){	
			// tran id
			$tran_id = (int)$_POST['Option1'];			
			// return data				
			if(isset($this->response['transaction_type'])){
				$option_name = $this->module.'_'.strtolower($this->response['transaction_type']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($this->response)));
			
			// options 
			$options = array('transaction_type','subscription_id','transaction_id');
			// loop
			foreach($options as $option){
				if(isset($this->response[$option])){
					mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>strtolower($this->module.'_'.$option),'option_value'=>$this->response[$option]));
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
		// validate
		if($this->_is_transaction($_POST['Option1'])){	
			// tran id
			return $tran_id = (int)$_POST['Option1'];
		}
		// return 
		return 0;	
	}*/
	
	// setup
	function _setup_endpoints($end_points = array()){
		// define defaults
		$end_points_default = array('test_xml_cvn'    => 'https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp',// test xml cvn
									'live_xml_cvn'    => 'https://www.eway.com.au/gateway_cvn/xmlpayment.asp', // live xml cvn
									'test_recurring'  => 'https://www.eway.com.au/gateway/rebill/test/Upload_test.aspx',// test recurring
									'live_recurring'  => 'https://www.eway.com.au/gateway/rebill/upload.aspx',// live recurrng
									'test_webservice' => 'https://www.eway.com.au/gateway/rebill/test/managerebill_test.asmx?WSDL',// test webservice
									'live_webservice' => 'https://www.eway.com.au/gateway/rebill/manageRebill.asmx?WSDL',// live webservice									
									'live_xmlauth'    => 'https://www.eway.com.au/gateway_cvn/xmlauth.asp',// auth
									'live_xmlauthco'   => 'https://www.eway.com.au/gateway/xmlauthcomplete.asp',  // live xml cvn auth complete, only used in live
									'live_xmlauthvoid' => 'https://www.eway.com.au/gateway/xmlauthvoid.asp' // live xml cvn auth void, only used in live
									);	// live recurring
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// set 
	function _set_address_fields($user, &$data){
		// eway prefix is not added here		
		// mappings
		$mappings= array('first_name'=>'CustomerFirstName','last_name'=>'CustomerLastName','address'=>'CustomerAddress',
		                 'city'=>'CustomerSuburb','state'=>'CustomerState','zip'=>'CustomerPostCode','country'=>'CustomerCountry',
						 'phone'=>'CustomerPhone1');						 
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter'));				 
	}
	
	// filter
	function _address_fields_filter($name, $value){
		// reuse parent filter unless needed
		switch($name){
			case 'state':			 
				$value = substr($value, 0, 50);
			break;
			case 'zip':
				// trim chars
				$value = substr($value, 0, 6);
			break;
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
		return (isset($_POST['Option1'])) ? true : false;		
	}

	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// filter postdata
	function _filter_postdata($gateway_method, $post_data){				
		// card holder name
		if(isset($post_data['mgm_card_holder_name'])){
			list($ch_first_name, $ch_last_name) = explode(' ', $post_data['mgm_card_holder_name']);	
		}else{
			$ch_first_name = $ch_last_name = '';
		}
		// gateway method
		switch($gateway_method) {
			case 'recurring':
			// subscribe buy
				// request xml
				$content  = "<RebillUpload>";	
				$content .= " <NewRebill>";	
				$content .= " <eWayCustomerID>" . $post_data['CustomerID'] . "</eWayCustomerID>";	
				$content .= " <Customer>";	
				$content .= "  <CustomerRef>" . $post_data['CustomerRef'] . "</CustomerRef>";	
				$content .= "  <CustomerTitle></CustomerTitle>";					
				$content .= "  <CustomerFirstName>". (isset($post_data['CustomerFirstName']) && !empty($post_data['CustomerFirstName']) ? $post_data['CustomerFirstName'] : $ch_first_name) . "</CustomerFirstName>";	
				$content .= "  <CustomerLastName>" . (isset($post_data['CustomerLastName']) && !empty($post_data['CustomerLastName']) ? $post_data['CustomerLastName'] : $ch_last_name) . "</CustomerLastName>";		
				$content .= "  <CustomerCompany>". (isset($post_data['CustomerCompany']) ? $post_data['CustomerCompany'] : " " ) ."</CustomerCompany>";	
				$content .= "  <CustomerJobDesc>". (isset($post_data['CustomerJobDesc']) ? $post_data['CustomerJobDesc'] : " " ) ."</CustomerJobDesc>";	
				$content .= "  <CustomerEmail>" . $post_data['CustomerEmail'] . "</CustomerEmail>";	
				// address
				$content .= "<CustomerAddress>". (isset($post_data['CustomerAddress']) ? $post_data['CustomerAddress'] : " " ) ."</CustomerAddress>";										
				$content .= "<CustomerSuburb>". (isset($post_data['CustomerSuburb']) ? $post_data['CustomerSuburb'] : " " ) ."</CustomerSuburb>";
				//state				
				$content .= "<CustomerState>". (isset($post_data['CustomerState']) ? $post_data['CustomerState'] : " " ) ."</CustomerState>";	
				// zip
				$content .= "<CustomerPostCode>". (isset($post_data['CustomerPostCode']) ? $post_data['CustomerPostCode'] : " " ) ."</CustomerPostCode>";	
				//country
				$content .= "<CustomerCountry>". (isset($post_data['CustomerCountry']) ? $post_data['CustomerCountry'] : " " ) ."</CustomerCountry>";		
				
				//CustomerPhone1
				$content .= "<CustomerPhone1>". (isset($post_data['CustomerPhone1']) ? $post_data['CustomerPhone1'] : " " ) ."</CustomerPhone1>";
				$content .= "<CustomerPhone2>". (isset($post_data['CustomerPhone2']) ? $post_data['CustomerPhone2'] : " " ) ."</CustomerPhone2>";		
				$content .= "<CustomerFax>". (isset($post_data['CustomerFax']) ? $post_data['CustomerFax'] : " " ) ."</CustomerFax>";		
				$content .= "<CustomerURL>". (isset($post_data['CustomerURL']) ? $post_data['CustomerURL'] : " " ) ."</CustomerURL>";		
				$content .= "<CustomerComments>". (isset($post_data['CustomerComments']) ? $post_data['CustomerComments'] : " " ) ."</CustomerComments>";						
				$content .= "</Customer>";	
				$content .= "<RebillEvent>";				
				$content .= "<RebillInvRef>" . $post_data['TrxnNumber'] . "</RebillInvRef>";	
				$content .= "<RebillInvDesc>" . substr($post_data['CustomerInvoiceDescription'],0,255) . "</RebillInvDesc>";	
				$content .= "<RebillCCName>". $post_data['mgm_card_holder_name'] ."</RebillCCName>";	
				$content .= "<RebillCCNumber>". $post_data['mgm_card_number'] ."</RebillCCNumber>";	
				$content .= "<RebillCCExpMonth>". $post_data['mgm_card_expiry_month'] ."</RebillCCExpMonth>";	
				$content .= "<RebillCCExpYear>". $post_data['mgm_card_expiry_year'] ."</RebillCCExpYear>";	
				$content .= "<RebillInitAmt>" . $post_data['RebillInitAmt'] . "</RebillInitAmt>";	
				$content .= "<RebillInitDate>" . $post_data['RebillInitDate'] . "</RebillInitDate>";	
				$content .= "<RebillRecurAmt>" . $post_data['RebillRecurAmt'] . "</RebillRecurAmt>";	
				$content .= "<RebillStartDate>" . $post_data['RebillStartDate'] . "</RebillStartDate>";	
				$content .= "<RebillInterval>" . $post_data['RebillInterval'] . "</RebillInterval>";	
				$content .= "<RebillIntervalType>" . $post_data['RebillIntervalType'] . "</RebillIntervalType>";
				
				/*
				$unit = array(1 => 'day', 2 => 'week', 3 => 'month', 4 => 'year');
				$occurances = $post_data['x_total_occurrences'] * $post_data['x_interval_length'];
				$addby_string = $unit[ $post_data['x_interval_unit'] ];				
				$end_date = date('d/m/Y',strtotime("+".$occurances." ".$addby_string, strtotime($post_data['x_start_date']))); 	
				*/	
						
				$content .= "<RebillEndDate>". $post_data['RebillEndDate'] ."</RebillEndDate>";	
				$content .= "</RebillEvent>";	
				$content .= "</NewRebill>";	
				$content .= "</RebillUpload>";									
				// return
				return $content;			
			break;
			
			case 'xml_cvn':
			// post buy
				$content = "<ewaygateway>";	
				$content .= "<ewayCustomerID>" . $post_data['CustomerID'] . "</ewayCustomerID>";	
				$content .= "<ewayTotalAmount>" . $post_data['TotalAmount'] . "</ewayTotalAmount>";	
				$content .= "<ewayCustomerFirstName>". (isset($post_data['CustomerFirstName']) && !empty($post_data['CustomerFirstName']) ? $post_data['CustomerFirstName'] : $ch_first_name) . "</ewayCustomerFirstName>";	
				$content .= "<ewayCustomerLastName>" . (isset($post_data['CustomerLastName']) && !empty($post_data['CustomerLastName']) ? $post_data['CustomerLastName'] : $ch_last_name) . "</ewayCustomerLastName>";	
				$content .= "<ewayCustomerEmail>" . $post_data['CustomerEmail'] . "</ewayCustomerEmail>";	
				// address
				$address = " ";
				if(isset($post_data['CustomerAddress']) && !empty($post_data['CustomerAddress'])) {
					$address = $post_data['CustomerAddress'];
					if(isset($post_data['CustomerState']) && !empty($post_data['CustomerState']))
						$address .= "," . $post_data['CustomerState'];
					if(isset($post_data['CustomerCountry']) && !empty($post_data['CustomerCountry']))
						$address .= "," . $post_data['CustomerCountry'];	
				}
				$content .= "<ewayCustomerAddress>" . $address . "</ewayCustomerAddress>";				
				// zip
				$content .= "<ewayCustomerPostcode>". (isset($post_data['CustomerPostCode']) ? $post_data['CustomerPostCode'] : " " ) ."</ewayCustomerPostcode>";	
				
				$content .= "<ewayCustomerInvoiceDescription>". (isset($post_data['CustomerInvoiceDescription']) ? substr($post_data['CustomerInvoiceDescription'], 0, 254) : " " ) ."</ewayCustomerInvoiceDescription>";	
				$content .= "<ewayCustomerInvoiceRef>" . $post_data['TrxnNumber'] . "</ewayCustomerInvoiceRef>";	
				$content .= "<ewayCardHoldersName>" . $post_data['mgm_card_holder_name'] . "</ewayCardHoldersName>";	
				$content .= "<ewayCardNumber>" . $post_data['mgm_card_number'] . "</ewayCardNumber>";	
				$content .= "<ewayCardExpiryMonth>" . $post_data['mgm_card_expiry_month'] . "</ewayCardExpiryMonth>";	
				$content .= "<ewayCardExpiryYear>" . $post_data['mgm_card_expiry_year'] . "</ewayCardExpiryYear>";	
				$content .= "<ewayTrxnNumber>" . $post_data['TrxnNumber'] . "</ewayTrxnNumber>";	
				$content .= "<ewayOption1></ewayOption1>";	
				$content .= "<ewayOption2></ewayOption2>";	
				$content .= "<ewayOption3></ewayOption3>";
				$content .= "<ewayCVN>". $post_data['mgm_card_code']. "</ewayCVN>"; 	
				$content .= "</ewaygateway>";					
				// return	
				return $content;				
			break;
			
			case 'xmlauth':// pre auth
				// address
				$address = " ";
				if(isset($post_data['CustomerAddress']) && !empty($post_data['CustomerAddress'])) {
					$address = $post_data['CustomerAddress'];
					if(isset($post_data['CustomerState']) && !empty($post_data['CustomerState']))
						$address .= "," . $post_data['CustomerState'];
					if(isset($post_data['CustomerCountry']) && !empty($post_data['CustomerCountry']))
						$address .= "," . $post_data['CustomerCountry'];	
				}
				// set
				$content = "<ewaygateway>
							<ewayCustomerID>" . $post_data['CustomerID'] . "</ewayCustomerID>
							<ewayTotalAmount>" . $post_data['TotalAmount'] . "</ewayTotalAmount>
							<ewayCustomerFirstName>". (isset($post_data['CustomerFirstName']) && !empty($post_data['CustomerFirstName']) ? $post_data['CustomerFirstName'] : $ch_first_name) . "</ewayCustomerFirstName>
							<ewayCustomerLastName>" . (isset($post_data['CustomerLastName']) && !empty($post_data['CustomerLastName']) ? $post_data['CustomerLastName'] : $ch_last_name) . "</ewayCustomerLastName>
							<ewayCustomerEmail>" . $post_data['CustomerEmail'] . "</ewayCustomerEmail>
							<ewayCustomerAddress>" . $address . "</ewayCustomerAddress>
							<ewayCustomerPostcode>". (isset($post_data['CustomerPostCode']) ? $post_data['CustomerPostCode'] : " " ) ."</ewayCustomerPostcode>
							<ewayCustomerInvoiceDescription>". (isset($post_data['CustomerInvoiceDescription']) ? substr($post_data['CustomerInvoiceDescription'], 0, 254) : " " ) ."</ewayCustomerInvoiceDescription>
							<ewayCustomerInvoiceRef>" . $post_data['TrxnNumber'] . "</ewayCustomerInvoiceRef>
							<ewayCardHoldersName>" . $post_data['mgm_card_holder_name'] . "</ewayCardHoldersName>
							<ewayCardNumber>" . $post_data['mgm_card_number'] . "</ewayCardNumber>
							<ewayCardExpiryMonth>" . $post_data['mgm_card_expiry_month'] . "</ewayCardExpiryMonth>
							<ewayCardExpiryYear>" . $post_data['mgm_card_expiry_year'] . "</ewayCardExpiryYear>
							<ewayTrxnNumber>" . $post_data['TrxnNumber'] . "</ewayTrxnNumber>
							<ewayOption1></ewayOption1>
							<ewayOption2></ewayOption2>
							<ewayOption3></ewayOption3>
							<ewayCVN>". $post_data['mgm_card_code']. "</ewayCVN>
							</ewaygateway>";
				// return	
				return $content;				
			break;
			case 'xmlauthco':// pre auth complete
				$content = "<ewaygateway> 
							<ewayCustomerID>" . $post_data['CustomerID'] . "</ewayCustomerID> 
							<ewayAuthTrxnNumber>" . $post_data['AuthTrxnNumber'] . "</ewayAuthTrxnNumber> 
							<ewayTotalAmount>" . $post_data['TotalAmount'] . "</ewayTotalAmount> 
							<ewayCardExpiryMonth>" . $post_data['mgm_card_expiry_month'] . "</ewayCardExpiryMonth>
							<ewayCardExpiryYear>" . $post_data['mgm_card_expiry_year'] . "</ewayCardExpiryYear>
							<ewayOption1></ewayOption1>
							<ewayOption2></ewayOption2> 
							<ewayOption3></ewayOption3> 
							</ewaygateway>";
				// return	
				return $content;				
			break;
			case 'xmlauthvoid':// pre auth void
				$content = "<ewaygateway> 
							<ewayCustomerID>" . $post_data['CustomerID'] . "</ewayCustomerID> 
							<ewayAuthTrxnNumber>" . $post_data['AuthTrxnNumber'] . "</ewayAuthTrxnNumber> 
							<ewayTotalAmount>" . $post_data['TotalAmount'] . "</ewayTotalAmount> 							
							<ewayOption1></ewayOption1>
							<ewayOption2></ewayOption2> 
							<ewayOption3></ewayOption3> 
							</ewaygateway>";
				// return	
				return $content;				
			break;
			case 'webservice':	
				//name space	
				$xmlns = 'http://www.eway.com.au/gateway/rebill/manageRebill';
				// action
				switch ($post_data['webservice_action']) {
					//create Rebill Customer					
					case 'CreateRebillCustomer':
						$content ='<?xml version="1.0" encoding="utf-8"?>
										<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
										  <soap:Header>
										    <eWAYHeader xmlns="'.$xmlns.'">
										      <eWAYCustomerID>'. $post_data['CustomerID'] .'</eWAYCustomerID>
										      <Username>'.$post_data['Username'].'</Username>
										      <Password>'.$post_data['Password'].'</Password>
										    </eWAYHeader>
										  </soap:Header>
										  <soap:Body>
										    <CreateRebillCustomer xmlns="'.$xmlns.'">
										      <customerTitle></customerTitle>
										      <customerFirstName>'.(isset($post_data['CustomerFirstName']) && !empty($post_data['CustomerFirstName']) ? $post_data['CustomerFirstName'] : $ch_first_name).'</customerFirstName>
										      <customerLastName>' . (isset($post_data['CustomerLastName']) && !empty($post_data['CustomerLastName']) ? $post_data['CustomerLastName'] : $ch_last_name) . '</customerLastName>
										      <customerAddress>'. (isset($post_data['CustomerAddress']) ? $post_data['CustomerAddress'] : "" ) .'</customerAddress>
										      <customerSuburb>'. (isset($post_data['CustomerSuburb']) ? $post_data['CustomerSuburb'] : "" ) .'</customerSuburb>
										      <customerState>'. (isset($post_data['CustomerState']) ? $post_data['CustomerState'] : "" ) .'</customerState>
										      <customerCompany>'. (isset($post_data['CustomerCompany']) ? $post_data['CustomerCompany'] : "" ) .'</customerCompany>
										      <customerPostCode>'. (isset($post_data['CustomerPostCode']) ? $post_data['CustomerPostCode'] : "" ) .'</customerPostCode>
										      <customerCountry>'. (isset($post_data['CustomerCountry']) ? $post_data['CustomerCountry'] : "" ) .'</customerCountry>
										      <customerEmail>'. $post_data['CustomerEmail'] .'</customerEmail>
										      <customerFax>'. (isset($post_data['CustomerFax']) ? $post_data['CustomerFax'] : "" ) .'</customerFax>
										      <customerPhone1>'. (isset($post_data['CustomerPhone1']) ? $post_data['CustomerPhone1'] : "" ) .'</customerPhone1>
										      <customerPhone2>'. (isset($post_data['CustomerPhone2']) ? $post_data['CustomerPhone2'] : "" ) .'</customerPhone2>
										      <customerRef>' . $post_data['CustomerRef'] . '</customerRef>
										      <customerJobDesc>'. (isset($post_data['CustomerJobDesc']) ? $post_data['CustomerJobDesc'] : "" ) .'</customerJobDesc>
										      <customerComments>'. (isset($post_data['CustomerComments']) ? $post_data['CustomerComments'] : "" ) .'</customerComments>
										      <customerURL>'. (isset($post_data['CustomerURL']) ? $post_data['CustomerURL'] : "" ) .'</customerURL>
										    </CreateRebillCustomer>
										  </soap:Body>
										</soap:Envelope>';
					break;
					case 'CreateRebillEvent':
						$content ='<?xml version="1.0" encoding="utf-8"?>
									<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
									  <soap:Header>
									    <eWAYHeader xmlns="'. $xmlns .'">
									      <eWAYCustomerID>'. $post_data['CustomerID'] .'</eWAYCustomerID>
									      <Username>'. $post_data['Username'] .'</Username>
									      <Password>'. $post_data['Password'] .'</Password>
									    </eWAYHeader>
									  </soap:Header>
									  <soap:Body>
									    <CreateRebillEvent xmlns="'. $xmlns .'">
									      <RebillCustomerID>'. $post_data['RebillCustomerID'] .'</RebillCustomerID>
									      <RebillInvRef>'. $post_data['TrxnNumber'] . '</RebillInvRef>
									      <RebillInvDes>'. substr($post_data['CustomerInvoiceDescription'],0,255) .'</RebillInvDes>
									      <RebillCCName>'. $post_data['mgm_card_holder_name'] .'</RebillCCName>
									      <RebillCCNumber>'. $post_data['mgm_card_number'] .'</RebillCCNumber>
									      <RebillCCExpMonth>'. $post_data['mgm_card_expiry_month'] .'</RebillCCExpMonth>
									      <RebillCCExpYear>'. $post_data['mgm_card_expiry_year'] .'</RebillCCExpYear>
									      <RebillInitAmt>'. $post_data['RebillInitAmt'] .'</RebillInitAmt>
									      <RebillInitDate>'. $post_data['RebillInitDate'] .'</RebillInitDate>
									      <RebillRecurAmt>'. $post_data['RebillRecurAmt'] .'</RebillRecurAmt>
									      <RebillStartDate>'. $post_data['RebillStartDate'] .'</RebillStartDate>
									      <RebillInterval>'. $post_data['RebillInterval'] .'</RebillInterval>
									      <RebillIntervalType>'. $post_data['RebillIntervalType'] .'</RebillIntervalType>
									      <RebillEndDate>'. $post_data['RebillEndDate'] .'</RebillEndDate>
									    </CreateRebillEvent>
									  </soap:Body>
									</soap:Envelope>';
					break;
					case 'UpdateRebillEvent':
						/*$content ='<?xml version="1.0" encoding="utf-8"?>
									<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
									  <soap:Header>
									    <eWAYHeader xmlns="'. $xmlns .'">
									      <eWAYCustomerID>'. $post_data['CustomerID'] .'</eWAYCustomerID>
									      <Username>'. $post_data['Username'] .'</Username>
									      <Password>'. $post_data['Password'] .'</Password>
									    </eWAYHeader>
									  </soap:Header>
									  <soap:Body>
									    <CreateRebillEvent xmlns="'. $xmlns .'">
									      <RebillCustomerID>'. $post_data['RebillCustomerID'] .'</RebillCustomerID>
									      <RebillInvRef>'. $post_data['TrxnNumber'] . '</RebillInvRef>
									      <RebillInvDes>'. substr($post_data['CustomerInvoiceDescription'],0,255) .'</RebillInvDes>
									      <RebillCCName>'. $post_data['mgm_card_holder_name'] .'</RebillCCName>
									      <RebillCCNumber>'. $post_data['mgm_card_number'] .'</RebillCCNumber>
									      <RebillCCExpMonth>'. $post_data['mgm_card_expiry_month'] .'</RebillCCExpMonth>
									      <RebillCCExpYear>'. $post_data['mgm_card_expiry_year'] .'</RebillCCExpYear>
									      <RebillInitAmt>'. $post_data['RebillInitAmt'] .'</RebillInitAmt>
									      <RebillInitDate>'. $post_data['RebillInitDate'] .'</RebillInitDate>
									      <RebillRecurAmt>'. $post_data['RebillRecurAmt'] .'</RebillRecurAmt>
									      <RebillStartDate>'. $post_data['RebillStartDate'] .'</RebillStartDate>
									      <RebillInterval>'. $post_data['RebillInterval'] .'</RebillInterval>
									      <RebillIntervalType>'. $post_data['RebillIntervalType'] .'</RebillIntervalType>
									      <RebillEndDate>'. $post_data['RebillEndDate'] .'</RebillEndDate>
									    </CreateRebillEvent>
									  </soap:Body>
									</soap:Envelope>';*/
					break;
					case 'QueryRebillEvent':
						$content ='<?xml version="1.0" encoding="utf-8"?>
									<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
									  <soap:Header>
									    <eWAYHeader xmlns="'. $xmlns .'">
									      <eWAYCustomerID>'. $post_data['CustomerID'] .'</eWAYCustomerID>
									      <Username>'. $post_data['Username'] .'</Username>
									      <Password>'. $post_data['Password'] .'</Password>
									    </eWAYHeader>
									  </soap:Header>
									  <soap:Body>
									    <QueryRebillEvent xmlns="'. $xmlns .'">
									      <RebillCustomerID>'. $post_data['RebillCustomerID'] .'</RebillCustomerID>
									      <RebillID>'. $post_data['RebillID'] . '</RebillID>									     
									    </QueryRebillEvent>
									  </soap:Body>
									</soap:Envelope>';
					break;
					case 'DeleteRebillEvent':
						$content = '<?xml version="1.0" encoding="utf-8"?>
									<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
									  <soap:Header>
									   <eWAYHeader xmlns="'. $xmlns .'">
									      <eWAYCustomerID>'. $post_data['CustomerID'] .'</eWAYCustomerID>
									      <Username>'. $post_data['Username'] .'</Username>
									      <Password>'. $post_data['Password'] .'</Password>
									   </eWAYHeader>
									  </soap:Header>
									  <soap:Body>
									    <DeleteRebillEvent xmlns="'. $xmlns .'">
									      <RebillCustomerID>'. $post_data['RebillCustomerID'] .'</RebillCustomerID>
									      <RebillID>'. $post_data['RebillID'] .'</RebillID>
									    </DeleteRebillEvent>
									  </soap:Body>
									  </soap:Envelope>';
					break;
					case 'DeleteRebillCustomer':
						$content = '<?xml version="1.0" encoding="utf-8"?>
									<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
									  <soap:Header>
									   <eWAYHeader xmlns="'. $xmlns .'">
									      <eWAYCustomerID>'. $post_data['CustomerID'] .'</eWAYCustomerID>
									      <Username>'. $post_data['Username'] .'</Username>
									      <Password>'. $post_data['Password'] .'</Password>
									    </eWAYHeader>
									  </soap:Header>
									  <soap:Body>
									    <DeleteRebillCustomer xmlns="'. $xmlns .'">
									      <RebillCustomerID>'. $post_data['RebillCustomerID'] .'</RebillCustomerID>
									    </DeleteRebillCustomer>
									  </soap:Body>
									</soap:Envelope>';
					break;
					case 'QueryTransactions':
						$content = '<?xml version="1.0" encoding="utf-8"?>
									<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
									  <soap:Header>
									   <eWAYHeader xmlns="'. $xmlns .'">
									      <eWAYCustomerID>'. $post_data['CustomerID'] .'</eWAYCustomerID>
									      <Username>'. $post_data['Username'] .'</Username>
									      <Password>'. $post_data['Password'] .'</Password>
									   </eWAYHeader>
									  </soap:Header>
									  <soap:Body>
									    <QueryTransactions xmlns="'. $xmlns .'">
									      <RebillCustomerID>'. $post_data['RebillCustomerID'] .'</RebillCustomerID>
									      <RebillID>'. $post_data['RebillID'] .'</RebillID>';
						//transaction start date
						//put same date for startDate and endDate to request for the transaction for that day  
						if(!empty($post_data['startDate']))	
					      	$content .= '<startDate>'. $post_data['startDate'] .'</startDate>';
					    //transaction end date   	
					    if(!empty($post_data['endDate']))	
							$content .= '<endDate>'. $post_data['endDate'] .'</endDate>';
						//transaction status	
						if(!empty($post_data['rebillStatus']))	  									    
							$content .=	'<status>'. $post_data['rebillStatus'] .'</status>';//Future or Successful or Failed or Pending								      
						$content .='</QueryTransactions>'.
									'</soap:Body>'.
									'</soap:Envelope>';
					break;	
				}
								
				return $content;
				
			break;
		}		
	}
	
	// process response
	function _process_response($gateway_method, $content, $action = null){
		// reset
		$this->response = array();
		// gateway method
		switch($gateway_method){
			case 'recurring':
				// xml
				if($xml = @simplexml_load_string($content)){
					$this->response['response_status'] = ((string)$xml->Result == 'Success') ? 1 : 3; // 1 success, 3 error
					//$this->response['message_code']    = (string)$xml->messages->message->code ;	
					if(isset($xml->ErrorDetails))
						$this->response['message_text']    = $xml->ErrorDetails ;
					if(isset($_POST['Option1']))	
						$this->response['subscription_id'] = $_POST['Option1'];	//check here as eway doesn't return subscription id:(important)				
				}else{
					$this->response['response_status'] = 3;
					$this->response['message_text']    = 'Error parsing XML';					 
				}
				break;
			case 'xml_cvn':
				// xml									
				if($xml = @simplexml_load_string($content)){
					$this->response['response_status'] = ((string)$xml->ewayTrxnStatus == 'True') ? 1 : 3; // 1 success, 3 error
					//$this->response['message_code']    = (string)$xml->messages->message->code ;	
					$this->response['message_text']    = (string)$xml->ewayTrxnError ;						
					if(isset($_POST['Option1']))
						$this->response['transaction_id']   = $_POST['Option1'];			
				}else{
					$this->response['response_status'] = 3;
					$this->response['message_text']    = 'Error parsing XML';					
				}
				
			break;
			case 'xmlauth':
			case 'xmlauthco':
			case 'xmlauthvoid':
				// xml									
				if($xml = @simplexml_load_string($content)){
					$this->response['response_status'] = ((string)$xml->ewayTrxnStatus == 'True') ? 1 : 3; // 1 success, 3 error
					$this->response['message_text']    = $this->_get_error_code((string)$xml->ewayTrxnError);					
					$this->response['txn_no']          = (string)$xml->ewayTrxnNumber;		
					$this->response['auth_code']       = (string)$xml->ewayAuthCode;		
					$this->response['txn_ref']         = (string)$xml->ewayTrxnReference;			
				}else{
					$this->response['response_status'] = 3;
					$this->response['message_text']    = 'Error parsing XML';					
				}
			break;
			// parse webservice sml string
			case 'webservice':
				if(!empty($content)) {	
					$arr_resp_fields = array('result' => null, 'errordetails' => null);
					//result: Success 
					
					switch ($action) {
						case 'CreateRebillCustomer':
							$arr_resp_fields['rebillcustomerid'] = null;
							$arr_resp_fields['transaction_type'] = 'subscription';
						break;
						
						case 'CreateRebillEvent':
							$arr_resp_fields['rebillcustomerid'] = null;							
							$arr_resp_fields['rebillid'] = null;
							$arr_resp_fields['transaction_type'] = 'subscription';
						break;	
						
						case 'UpdateRebillEvent':
							$arr_resp_fields['rebillcustomerid'] = null;							
							$arr_resp_fields['rebillid'] = null;
							$arr_resp_fields['transaction_type'] = 'subscription';
						break;	
						
						case 'QueryRebillEvent':
							$arr_resp_fields['rebillstartdate'] = null;							
							$arr_resp_fields['rebillenddate'] = null;							
						break;	
						
						case 'DeleteRebillEvent':
							$arr_resp_fields['rebillcustomerid'] = null;							
							$arr_resp_fields['rebillid'] = null;
							$arr_resp_fields['transaction_type'] = 'ubsubscribe';
						break;
						
						case 'DeleteRebillCustomer':
							$arr_resp_fields['customerref'] = null;	//mgm user id
						break;
						
						case 'QueryTransactions':							
							$arr_resp_fields['transactiondate'] = null;							
							$arr_resp_fields['status'] = null;
							$arr_resp_fields['transactionerror'] = null;
							$arr_resp_fields['transaction_type'] = 'rebill';
							$arr_resp_fields['transactionnumber'] = '';							
						break;	
					}
					//parse soap xml
					$parser = xml_parser_create();
					xml_parse_into_struct($parser, $content, $response);
					xml_parser_free($parser);
					
					//loop through tags and get the action related vars					
					if(!empty($response)) {						
						foreach ($response as $resp) {
							$tag = strtolower($resp['tag']);							
							if(array_key_exists($tag, $arr_resp_fields)) {								
								$arr_resp_fields[ $tag ] = isset($resp['value']) ? $resp['value'] : '';
							}
						}
					}
										
					//assign to response:
					$this->response = $arr_resp_fields;
					
					//set response_status
					switch ($action) {
						case 'CreateRebillCustomer':							
						case 'CreateRebillEvent':							
						case 'DeleteRebillEvent':							
						case 'DeleteRebillCustomer':	
						case 'QueryRebillEvent':						
							$this->response['response_status'] = ((string)$arr_resp_fields['result'] == 'Success') ? 1 : 3; // 1 success, 3 error					
						break;												
						case 'QueryTransactions':		
							// statsu set
							if(isset($arr_resp_fields['status']) && !empty($arr_resp_fields['status'])){												
								// success
								if($arr_resp_fields['status'] == 'Successful'){
									$this->response['response_status'] = 1; // 1 success, 3 error
								}else{
								// other, Failed, Future, 
									$this->response['response_status'] = 3;// error
								}	
							}else{
								$this->response['response_status'] = 5; // expired
							}
							
							// message
							if(isset($arr_resp_fields['transactionnumber']) && !empty($arr_resp_fields['transactionnumber'])){
								$this->response['message_text'] = sprintf('%s %s: %s', $arr_resp_fields['transactionerror'], __(' EWAY Transaction No', 'mgm'), $arr_resp_fields['transactionnumber']);
							}else{
								$this->response['message_text'] = __('Eway Transaction not found', 'mgm');								
							}
						break;	
					}	
					// unset				
					unset($this->response['result']);
					//errors
					if(!empty($arr_resp_fields['errordetails']))
						$this->response['message_text'] = mgm_stripslashes_deep($arr_resp_fields['errordetails']);
					unset($this->response['errordetails']);	
					//set trans id
					if(isset($_POST['Option1']))										
						$this->response['transaction_id'] = $_POST['Option1'];	//check here as eway doesn't return subscription id:(important)
				}
				break;
		}			
	}	
	
	// get code
	function _get_response_code($key, $type='status'){
		// status
		$response_code['status']  = array(1 => "Approved", 2 => "Declined", 3 => "Error", 4 => "Held for Review" , 5 => 'Expired');	
		// avs: address verification
		$response_code['avs']     = array("A" => "Address (Street) matches, ZIP does not",
										  "B" => "Address information not provided for AVS check",
										  "E" => "AVS error",
										  "G" => "Non-U.S. Card Issuing Bank",
										  "N" => "No Match on Address (Street) or ZIP",
										  "P" => "AVS not applicable for this transaction",
										  "R" => "Retry System unavailable or timed out",
										  "S" => "Service not supported by issuer",
										  "U" => "Address information is unavailable",
										  "W" => "Nine digit ZIP matches, Address (Street) does not",
										  "X" => "Address (Street) and nine digit ZIP match",
										  "Y" => "Address (Street) and five digit ZIP match",
										  "Z" => "Five digit ZIP matches, Address (Street) does not)");
										 
		// cvv: card verification						 
		$response_code['cvv2']      = array("M" => "Match", "N" => "No Match", "P" => "Not Processed", "S" => "Should have been present", 
								            "U" => "Issuer unable to process request");
		// check
		if(isset($response_code[$type][$key])){
			return $response_code[$type][$key];
		}				   
		// default
		return 'error';
	}
	
	// get error codes
	function _get_error_code($error){
		// split if comma
		if( preg_match('/,/', $error) ){
			list($code, $message) = explode(',', $error, 2);
		}elseif( preg_match('/:/', $error) ){
			list($code, $message) = explode(':', $error, 2);
		}else{
			$message = $error;
		}
		
		// codes, first is original 2nd is formatted
		$codes = array( '00' =>	array(__('Transaction Approved','mgm')),
						'01' =>	array(__('Refer to Issuer','mgm')),
						'02' =>	array(__('Refer to Issuer, special','mgm')),
						'03' =>	array(__('No Merchant','mgm')),
						'04' =>	array(__('Pick Up Card','mgm')),
						'05' =>	array(__('Do Not Honour','mgm'),__('Your Card was declined by Bank','mgm')),
						'06' =>	array(__('Error','mgm')),
						'07' =>	array(__('Pick Up Card, Special','mgm')),
						'08' =>	array(__('Honour With Identification','mgm'),__('Your Card was processed','mgm')),
						'09' =>	array(__('Request In Progress','mgm')),
						'10' =>	array(__('Approved For Partial Amount','mgm')),
						'11' =>	array(__('Approved, VIP','mgm')),
						'12' =>	array(__('Invalid Transaction','mgm')),
						'13' =>	array(__('Invalid Amount','mgm')),
						'14' =>	array(__('Invalid Card Number','mgm')),
						'15' =>	array(__('No Issuer','mgm')),
						'16' =>	array(__('Approved, Update Track 3','mgm')),
						'19' =>	array(__('Re-enter Last Transaction','mgm')),
						'21' =>	array(__('No Action Taken','mgm')),
						'22' =>	array(__('Suspected Malfunction','mgm')),
						'23' =>	array(__('Unacceptable Transaction Fee','mgm')),
						'25' =>	array(__('Unable to Locate Record On File','mgm')),
						'30' =>	array(__('Format Error','mgm')),
						'31' =>	array(__('Bank Not Supported By Switch','mgm')),
						'33' =>	array(__('Expired Card, Capture','mgm')),
						'34' =>	array(__('Suspected Fraud, Retain Card','mgm')),
						'35' =>	array(__('Card Acceptor, Contact Acquirer, Retain Card','mgm')),
						'36' =>	array(__('Restricted Card, Retain Card','mgm')),
						'37' =>	array(__('Contact Acquirer Security Department, Retain Card','mgm')),
						'38' =>	array(__('PIN Tries Exceeded, Capture','mgm')),
						'39' =>	array(__('No Credit Account','mgm')),
						'40' =>	array(__('Function Not Supported','mgm')),
						'41' =>	array(__('Lost Card','mgm')),
						'42' =>	array(__('No Universal Account','mgm'),__('Credit Card Denied','mgm')),
						'43' =>	array(__('Stolen Card','mgm')),
						'44' =>	array(__('No Investment Account','mgm')),
						'51' =>	array(__('Insufficient Funds','mgm')),
						'52' =>	array(__('No Cheque Account','mgm')),
						'53' =>	array(__('No Savings Account','mgm')),
						'54' =>	array(__('Expired Card','mgm')),
						'55' =>	array(__('Incorrect PIN','mgm')),
						'56' =>	array(__('No Card Record','mgm')),
						'57' =>	array(__('Function Not Permitted to Cardholder','mgm')),
						'58' =>	array(__('Function Not Permitted to Terminal','mgm'),__('Credit Card Denied, using test Card','mgm')),
						'59' =>	array(__('Suspected Fraud','mgm')),
						'60' =>	array(__('Acceptor Contact Acquirer','mgm')),
						'61' =>	array(__('Exceeds Withdrawal Limit','mgm')),
						'62' =>	array(__('Restricted Card','mgm')),
						'63' =>	array(__('Security Violation','mgm')),
						'64' =>	array(__('Original Amount Incorrect','mgm')),
						'66' =>	array(__('Acceptor Contact Acquirer, Security','mgm')),
						'67' =>	array(__('Capture Card','mgm')),
						'75' =>	array(__('PIN Tries Exceeded','mgm')),
						'82' =>	array(__('CVV Validation Error','mgm')),
						'90' =>	array(__('Cutoff In Progress','mgm')),
						'91' =>	array(__('Card Issuer Unavailable','mgm')),
						'92' =>	array(__('Unable To Route Transaction','mgm')),
						'93' =>	array(__('Cannot Complete, Violation Of The Law','mgm')),
						'94' =>	array(__('Duplicate Transaction','mgm')),
						'96' =>	array(__('System Error','mgm'))
					); 
					
		$message = $error;
		if(isset($code) && isset($codes[$code])){
			if(isset($codes[$code][1])){
				$message = $codes[$code][1];
			}else{
				$message = $codes[$code][0];
			}
		}	
		// return
		return $message;		
	}
	
	/**
	 * Convert server time to eWay Server time(Australian time)
	 *
	 * @param unknown_type $datetime
	 * @param unknown_type $format
	 * @return unknown
	 */
	function _datetime_to_eway_serverdatetime($datetime, $format = 'Y-m-d H:i:s') {		
		$tz_eway = 'Australia/Sydney';
		$current_time = new DateTime(date( $format, strtotime($datetime) ));
		$eway_zone = new DateTimeZone($tz_eway);
		$current_time->setTimezone($eway_zone);
		return $current_time->format($format);
	}
	
	// pre auth
	function _pre_auth($post_data){
		// log		
		mgm_log('Post Data' . mgm_pr($post_data, true), __FUNCTION__);
		// xmlauth		
		$gateway_method = 'xmlauth';
		// end  point						
		$endpoint = $this->_get_endpoint($this->status.'_'.$gateway_method); // live_xmlauth, live_xmlauthco etc.		
		
		// amount
		$post_data['TotalAmount'] = ($post_data['RebillInitAmt'] > 0) ? $post_data['RebillInitAmt'] : $post_data['RebillRecurAmt'];
		
		// filter post data for api
		$post_string = $this->_filter_postdata($gateway_method, $post_data);
		
		// headers
		$http_headers = $this->_get_http_headers($gateway_method);
		
		// log
		mgm_log( 'Request Headers [' . $gateway_method . ']' . mgm_pr($http_headers, true), __FUNCTION__);	
		
		// log
		mgm_log( 'Request [' . $gateway_method . ']' . $post_string, __FUNCTION__);	
							
		// create curl post				
		$http_response = mgm_remote_post($endpoint, $post_string, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
		
		// log
		mgm_log( 'Response [' . $gateway_method . ']' . $http_response, __FUNCTION__);	
		
		// parse response		
		$this->_process_response($gateway_method, $http_response);
		
		// log
		mgm_log( 'Response Parsed [' . $gateway_method . ']' . mgm_pr($this->response, true), __FUNCTION__);	
		
		// testing 
		// $this->response['response_status'] = 1;

		$post_data['ValidRebillInitDate'] = $this->_get_valid_date($post_data['RebillInitDate']);
		// log
		mgm_log( 'Post Data after Date Convertion [' . $gateway_method . ']' . mgm_pr($post_data, true), __FUNCTION__);	
		// OK
		if( $this->response['response_status'] == 1 ) {
			// @todo void is Trial On and Trial Cost = 0, RebillInitDate is not today
			if( ((int)$post_data['TrialOn'] == 1 && $post_data['RebillInitAmt'] == 0) 
				|| strtotime($post_data['ValidRebillInitDate']) > strtotime($post_data['EwayTime']) ){
			// void	
				return $this->_pre_auth_void($post_data);
			}
			// complete
			return $this->_pre_auth_complete($post_data);			
		}
		
		// return
		return false;		
	}
	
	// pre auth void
	function _pre_auth_void($post_data){
		// charge	
		$gateway_method = 'xmlauthvoid';
		// txn
		$post_data['AuthTrxnNumber'] =  $this->response['txn_no'];// txn_no,auth_code
		
		// end  point						
		$endpoint = $this->_get_endpoint($this->status.'_'.$gateway_method); //live_xmlauthvoid etc.		
		
		// filter post data for api
		$post_string = $this->_filter_postdata($gateway_method, $post_data);
		
		// headers
		$http_headers = $this->_get_http_headers($gateway_method);
		
		// log
		mgm_log( 'Request Headers [' . $gateway_method . ']' . mgm_pr($http_headers, true), __FUNCTION__);	
		
		// log
		mgm_log( 'Request [' . $gateway_method . ']' . $post_string, __FUNCTION__);	
						
		// create curl post				
		$http_response = mgm_remote_post($endpoint, $post_string, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
		
		// log
		mgm_log( 'Response [' . $gateway_method . ']' . $http_response, __FUNCTION__);	
		
		// parse response		
		$this->_process_response($gateway_method, $http_response);
		
		// log
		mgm_log( 'Response Parsed [' . $gateway_method . ']' . mgm_pr($this->response, true), __FUNCTION__);	
		
		// testing 
		// $this->response['response_status'] = 1;

		// return 
		if($this->response['response_status'] == 1) {
			// track
			$this->response['preauth_void'] = true;
			// return
			return true;
		}

		// return
		return false;
	}

	// pre auth complete
	function _pre_auth_complete($post_data){
		// charge	
		$gateway_method = 'xmlauthco';
		// txn
		$post_data['AuthTrxnNumber'] =  $this->response['txn_no'];// txn_no,auth_code
		
		// end  point						
		$endpoint = $this->_get_endpoint($this->status.'_'.$gateway_method); // live_xmlauth, live_xmlauthco etc.		
		
		// filter post data for api
		$post_string = $this->_filter_postdata($gateway_method, $post_data);
		
		// headers
		$http_headers = $this->_get_http_headers($gateway_method);
		
		// log
		mgm_log( 'Request Headers [' . $gateway_method . ']' . mgm_pr($http_headers, true), __FUNCTION__);	
		
		// log
		mgm_log( 'Request [' . $gateway_method . ']' . $post_string, __FUNCTION__);	
						
		// create curl post				
		$http_response = mgm_remote_post($endpoint, $post_string, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
		
		// log
		mgm_log( 'Response [' . $gateway_method . ']' . $http_response, __FUNCTION__);	
		
		// parse response		
		$this->_process_response($gateway_method, $http_response);
		
		// log
		mgm_log( 'Response Parsed [' . $gateway_method . ']' . mgm_pr($this->response, true), __FUNCTION__);	
		
		// testing 
		// $this->response['response_status'] = 1;

		// return 
		if($this->response['response_status'] == 1) {
			// track
			$this->response['preauth_complete'] = true;
			// return
			return true;
		}

		// return
		return false;
	}

	// get headers 
	function _get_http_headers($gateway_method, $webservice_action=NULL){
		// method
		switch($gateway_method){
			case 'webservice':
				// webservice headers
				$soap_action = sprintf('http://www.eway.com.au/gateway/rebill/manageRebill/%s', $webservice_action);
				// set
				$http_headers = array('User-Agent'   => 'NuSOAP/0.9.5 (1.123)', 
								  	  'Content-Type' => 'text/xml; charset=ISO-8859-1', 
						              'SOAPAction'   => $soap_action); 
			break;
			default:
			// xml
				$http_headers = array('Content-Type' => 'text/xml');
			break;			  
		}
		// return
		return $http_headers;
	}

	// eway date to mysql valid date
	function _get_valid_date($date, $format='d/m/Y'){
		return preg_replace("/(\d+)[-\/\.](\d+)[-\/\.](\d+)/i", "$3-$2-$1", $date);// d/m/Y => Y-m-d
	}
	
}
// end file