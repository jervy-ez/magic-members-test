<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');

// -----------------------------------------------------------------------



/**

 * IMDMS PayPal Pro Payment Module, integrates paypal pro nv method

 *

 * @author     MagicMembers

 * @copyright  Copyright (c) 2011, MagicMembers 

 * @package    MagicMembers plugin

 * @subpackage Payment Module

 * @category   Module 

 * @version    3.0

 */ 

class mgm_imdmspaypalpro extends mgm_payment{	

	// construct

	function __construct(){

		// php4 construct

		$this->mgm_imdmspaypalpro();

	}

	

	// php4 construct

	function mgm_imdmspaypalpro(){

		// parent

		parent::__construct();

		// set code

		$this->code = __CLASS__; 

		// set module

		$this->module = str_replace('mgm_', '', $this->code);

		// set name

		$this->name = 'IMDMS Paypal Pro';	

		// logo

		$this->logo = $this->module_url( 'assets/paypalpro.gif' );

		// description

		$this->description = __('Recurring payments and Single Purchase.', 'mgm');

		// supported buttons types

	 	$this->supported_buttons = array('subscription', 'buypost');

		// trial support available ?

		$this->supports_trial= 'Y';		

		// cancellation support available ?

		$this->supports_cancellation= 'Y';	

		// do we depend on product mapping	

		$this->requires_product_mapping = 'N';

		// type of integration

		$this->hosted_payment = 'N';// credit card process onsite

		// if supports rebill status check	

		$this->supports_rebill_status_check = 'Y';

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

				// paypalpro specific

				$this->setting['username']  = $_POST['setting']['username'];

				$this->setting['password']  = $_POST['setting']['password'];

				$this->setting['signature'] = $_POST['setting']['signature'];	

				$this->setting['currency']  = $_POST['setting']['currency'];					

				//issue #974

				$this->setting['max_failed_payments']    = $_POST['setting']['max_failed_payments'];								

				$this->setting['locale']    = $_POST['setting']['locale'];

				// purchase price

				if(isset($_POST['setting']['purchase_price'])){

					$this->setting['purchase_price']  = $_POST['setting']['purchase_price'];

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

				//  message

				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));

			break;

		}		

	}

	

	// return process api hook, link back to site after payment is made

	function process_return() {	

		if(!isset($this->response)) $this->response = array();					

		// only save once success, there may be multiple try

		if($this->response['ACK']=='Success' || $this->response['ACK']=='SuccessWithWarning'){			

			// process 
			//mgm_log('here1',__FUNCTION__);	
			
			$this->process_notify();

			//mgm_log('here2',__FUNCTION__);	

			// query arg

			$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($_POST['custom']));


			//mgm_log(print_r($query_arg,true), __FUNCTION__);

			// is a post redirect?

			$post_redirect = $this->_get_post_redirect($_POST['custom']);

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

			do_action('imdms_programmes_add_user',$query_arg);

			// redirect
			//error_log($this->_get_thankyou_url());



			//mgm_redirect(add_query_arg($query_arg, $this->_get_thankyou_url()));			

		}else{

			// teat as error			

			$errors = urlencode($this->response['L_ERRORCODE0'] . ': ' . $this->response['L_SHORTMESSAGE0'] . ' - ' . $this->response['L_LONGMESSAGE0']); 	

			// redirect			

			mgm_redirect(add_query_arg(array('status'=>'error','errors'=>$errors), $this->_get_thankyou_url()));

		}		

	}	

	

	// notify process api hook, background IPN url

	function process_notify() {

		// record POST/GET data

		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );

		// init

		if(!isset($this->response)) $this->response = array();

		// this is to confirm module for IPN POST

		if(!$this->_confirm_notify()) return;

		// default				

		if(!empty($_POST['rp_invoice_id']) ) $_POST['custom'] = $_POST['rp_invoice_id'];		

		// check					

		if ($this->_verify_callback()) {						

			// log data before validate

			$tran_id = $this->_log_transaction();

			// payment type

			$payment_type = $this->_get_payment_type($_POST['custom']);

			// custom

			$custom = $this->_get_transaction_passthrough($_POST['custom']);

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


				//mgm_log('here',__FUNCTION__);

					// cancellation

					if(isset($_POST['txn_type']) && in_array($_POST['txn_type'], array('subscr_cancel', 'recurring_payment_profile_cancel','recurring_payment_suspended'))) {	// prototype, will change											

						$this->_cancel_membership(); //run the code to process a membership cancellation

					}else{	
												

						$this->_buy_membership(); //run the code to process a new/extended membership

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

		}else {

			mgm_log('PAYPALPRO process_notify verify failed');

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

			if(!empty($member->payment_info->subscr_id))

				$subscr_id = $member->payment_info->subscr_id;

			elseif (!empty($member->pack_id)) {	

				//check the pack is recurring

				$s_packs = mgm_get_class('subscription_packs');

				

				$sel_pack = $s_packs->get_pack($member->pack_id);									

				if($sel_pack['num_cycles'] != 1) 

					$subscr_id = 0;// 0 stands for a lost subscription id

			}



			// cancel at paypal

			$cancel_account = $this->cancel_recurring_subscription(null, $user_id, $subscr_id);

		}

		

		// cancel in MGM

		if($cancel_account === true) {

			$this->_cancel_membership($user_id, true);// redirected

		}



		// message

		$message =isset($data['L_SHORTMESSAGE0']) ? $data['L_SHORTMESSAGE0'].': '.$data['L_LONGMESSAGE0'] : __('Error occured while cancelling the subscription', 'mgm');					

		//issue #1521

		if( $is_admin ){

			mgm_redirect( add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php')) );

		}			

		// redirect to custom url:

		mgm_redirect(mgm_get_custom_url('membership_details', false,array('unsubscribe_errors'=>urlencode($message))));		

	}

		 /**

	  * read transacrion id from get/post

	  */

	 function _read_transaction_id(){



	 	// check post

		if(isset($_POST['tran_id'])	&& (int)$_POST['tran_id'] > 0){

			// set

			return $tran_id = (int)$_POST['tran_id'];		

		}else if(isset($_GET['tran_id'])){	// encoded

			// set

			return $tran_id = mgm_decode_id(strip_tags($_GET['tran_id']));

		} else if(isset($_GET['order_ref'])){	// encoded

			// set

			return $tran_id = mgm_decode_id(strip_tags($_GET['order_ref']));


		}else if(isset($_GET['trans_ref'])){// encoded

			// set

			return $tran_id = mgm_decode_id(strip_tags($_GET['trans_ref']));

		}	

		// error

		return false;

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

		

		// update pack/transaction: this is to confirm the module code if it is different

		mgm_update_transaction(array('module'=>$this->module), $tran_id);

		// Check user id is set if subscription_purchase. issue #1049

		if ($tran['payment_type'] == 'subscription_purchase' && 

			(!isset($tran['data']['user_id']) || (isset($tran['data']['user_id']) && (int) $tran['data']['user_id'] < 1))) {

			return $this->throw_cc_error(__('Transaction invalid . User id field is empty','mgm'));		

		}

		// get data		

		$data = $this->_get_button_data($tran['data'], $tran_id);	

		// merge

		$post_data = array_merge($_POST, $data); 


		//mgm_log(print_r($post_data,true), __FUNCTION__);

		

		// set email

		$this->_set_default_email($post_data, 'email');

		

		//issue #1062

		if(isset($tran['data']['currency']) && !empty($tran['data']['currency'])){

			$currency = $tran['data']['currency'];

		}else {

			$currency = $this->setting['currency'];

		}		

		

		// add internal vars

		$secure = array(

			'USER'         => $this->setting['username'],	

			'PWD'          => $this->setting['password'],		

			'SIGNATURE'    => $this->setting['signature'],

			'VERSION'      => '64.0',

			'IPADDRESS'    => $_SERVER["REMOTE_ADDR"],

			'CURRENCYCODE' => $currency);

		// merge

		$thing_post_data = $this->_filter_postdata(array_merge($post_data, $secure)); // overwrite post data array with secure params				

		// TODO	

		$endpoint = $this->_get_endpoint();	

		//mgm_log($endpoint, __FUNCTION__);

		//issue #1508

		$url_parsed = parse_url($endpoint); 





		// domain/host

		$domain = $url_parsed['host'];

		// headers

		$http_headers = array ('POST / HTTP/1.1\r\n',

							'Content-Type: application/x-www-form-urlencoded\r\n',

							'Host: '.$domain.'\r\n',

							'Connection: close\r\n\r\n');	

		// log


		// post

		//$http_response = mgm_remote_post($endpoint, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));	



		


	 	$http_response = $this->do_curl($thing_post_data);


		// log

		//mgm_log($http_response, __FUNCTION__);

		

		// parse

		$this->response = array();

		// parse response

		parse_str($http_response, $this->response);	


		//mgm_log($this->response, __FUNCTION__);

		// store custom
		 
		//mgm_log(print_r($post_data['custom'],true), __FUNCTION__);

		$_POST['custom'] = !empty($post_data['custom']) ? $post_data['custom'] : $tran_id;


		//mgm_log(print_r($_POST,true), __FUNCTION__);

		// check

		if (empty($this->response['ACK']) || ($this->response['ACK'] == 'Failure' && !empty($this->response['L_LONGMESSAGE0']))) {

			// return to credit card form

			$error_string = sprintf('Paypal Error %s: %s - %s',$this->response['L_ERRORCODE0'], $this->response['L_SHORTMESSAGE0'], $this->response['L_LONGMESSAGE0']);

			// return

			return $this->throw_cc_error($error_string);

		}

		// treat as return

		$this->process_return();			

	}

	function do_curl($post_data){



		$endpoint = $this->_get_endpoint();	

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL ,$endpoint);
		curl_setopt($ch,CURLOPT_VERBOSE ,1);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER ,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST ,false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER ,1);
		//curl_setopt($ch,CURLOPT_TIMEOUT_MS, 200);
		curl_setopt($ch,CURLOPT_HTTPGET ,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS ,$post_data);

	 	return curl_exec($ch);

	}

	

	// process html_redirect, proxy for form submit

	//The credit card form will get submitted to the same function, then validate the card and if everything is clear

	//process_credit_card() will be called internally

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

		// get user

		$user_id = $tran['data']['user_id'];

		$user    = get_userdata($user_id);

		

		// cc field

		$cc_fields = $this->_get_ccfields($user, $tran);

		//validate card: This will validate card and reload the form with errors

		//if validated process_credit_card() method will be called internally
		//
		$html = '<div class="container grid tpd40"><div class="grid__item two-thirds">';
		$html .= $this->validate_cc_fields_process(__FUNCTION__);
		$html .= '</div></div>';

		// the html

		$html .='<form action="'. $this->_get_endpoint('html_redirect') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">

					<input type="hidden" name="tran_id" value="'.$tran_id.'">

					<input type="hidden" name="submit_from" value="'.__FUNCTION__.'">

					'. $cc_fields .'

			    </form>				

			    <script language="javascript"></script>';

		// return 	  

		return $html;					

	}	

	

	// subscribe button api hook

	function get_button_subscribe($options=array()){

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

			   <form name="mgm_unsubscribe_form" id="mgm_unsubscribe_form" method="post" action="'. $action .'">

					<input type="hidden" name="user_id" value="' . $options['user_id'] . '"/>

					<input type="hidden" name="membership_type" value="' . $options['membership_type'] . '"/>

					<input type="button" name="btn_unsubscribe" value="' . __('Unsubscribe','mgm') . '" onclick="confirm_unsubscribe(this)" class="button" />	

			   </form>';	

		// return

		return $html;		

	}		

	

	// get module transaction info

	function get_transaction_info($member, $date_format){				

		// data

		$subscription_id = $member->payment_info->subscr_id;

		$transaction_id  = $member->payment_info->txn_id;		

		// info

		$info = sprintf('<b>%s:</b><br>%s: %s<br>%s: %s', __('PAYPALPRO INFO','mgm'), __('SUBSCRIPTION ID','mgm'), $subscription_id, 

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

		$html = sprintf('<p>%s: <input type="text" size="20" name="paypalpro[subscriber_id]"/></p>

				 		 <p>%s: <input type="text" size="20" name="paypalpro[transaction_id]"/></p>', 

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

		$data = $post_data['paypalpro'];

	 	// return

	 	return $this->_save_tracking_fields($fields, $member, $data); 			

	 }

	 

	// MODULE API COMMON PRIVATE HELPERS /////////////////////////////////////////////////////////////////	

	

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

			'invnum'	   => $tran_id, 								

			'description'  => $item['name'],	

			'currencycode' => $pack['currency'],	

			'notifyurl'    => $this->setting['notify_url'],							

			'email'        => $user->user_email,

			'amount'       => $pack['cost'] // base amount is always same for both type of payment

		);	

		

		// additional fields

		$this->_set_address_fields($user, $data);		

		

		// subscription purchase with ongoing/limited

		if( !isset($pack['buypost']) && isset($pack['duration_type']) && $pack['num_cycles'] != 1){ // does not support one-time recurring

		// if ($pack['num_cycles'] != 1 && $pack['duration_type']) { // old style

			// recurring

			$data['recurring_billing'] = 'TRUE';

			// types

			$bp_types = array('d'=>'Day', 'm'=>'Month', 'y'=>'Year');			

			// start date			

			$data['start_date']        = date(DATE_ATOM);// Mon, 15 Aug 2005 15:12:46 UTC	

			// billing period					

			$data['billing_period']    = $bp_types[$pack['duration_type']]; // Day|Month|Year

			$data['billing_frequency'] = $pack['duration']; // 3|12 etc.

			// greater than 0, limited

			if ( (int)$pack['num_cycles'] > 0) {

				$data['total_billing_cycles'] = $pack['num_cycles'];

			}			

			// trial

			if ($pack['trial_on']) {

				$data['trial_billing_period'] = $bp_types[$pack['trial_duration_type']];

				$data['trial_billing_frequency'] = $pack['trial_duration'];	

				// greated than 0, limited cycle

				if( (int)$pack['trial_num_cycles'] > 0){// need this checked				

					$data['trial_total_billing_cycles'] = $pack['trial_num_cycles']; 

				}

				// cost

				$data['trial_amt'] = $pack['trial_cost']; 							

			}			

		}else{

		// post purchase/one time billing	

			$data['recurring_billing'] = 'FALSE';

		} 	

		

		// custom passthrough		

		$data['custom'] = $tran_id;

		

		// update currency - issue #1602

/*		if($pack['currency'] != $this->setting['currency']){

			$pack['currency'] = $this->setting['currency'];

		}*/

		

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

		$custom = $this->_get_transaction_passthrough($_POST['custom']);

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



		// process on response code

		switch ($this->response['ACK']) {

			case 'Success':		

			case 'SuccessWithWarning':

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

				do_action('mgm_buy_post_transaction_success', $args );// backward compatibility

				do_action('mgm_post_purchase_payment_success', $args );// new organized name

			break;



			case 'Failure':

			case 'FailureWithWarning':

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

				$status_str = sprintf(__('Last payment status: %s','mgm'), (isset($this->response['ACK']) ? $this->response['ACK'] : 'Unknown'));

				// purchase status

				$purchase_status = 'Unknown';	

				

				// error

				$errors[] = $status_str;

		}

		

		// do action

		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new, individual

		do_action('mgm_return_post_purchase_payment', array('post_id' => $post_id));// new, global 

		

		// set as purchase

		$status = __('Failed join', 'mgm'); //overridden on a successful payment

		// check status

		if ( $purchase_status == 'Success' ) {

			// mark as purchased

			if( isset($user->ID) ){	// purchased by user	

				// call coupon action

				do_action('mgm_update_coupon_usage', array('user_id' => $user_id));		

				// set as purchased	

				$this->_set_purchased($user_id, $post_id, NULL, $_POST['custom']);

			}else{

				// purchased by guest

				if( isset($guest_token) ){

					// issue #1421, used coupon

					if(isset($coupon_id) && isset($coupon_code)) {

						// call coupon action

						do_action('mgm_update_coupon_usage', array('guest_token' => $guest_token,'coupon_id' => $coupon_id));

						// set as purchased

						$this->_set_purchased(NULL, $post_id, $guest_token, $_POST['custom'], $coupon_code);

					}else {

						$this->_set_purchased(NULL, $post_id, $guest_token, $_POST['custom']);				

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

			

		// notify user, only if gateway emails on	

		if( ! $dpne ) {			

			// notify user

			if( isset($user->ID) ){

				// mgm post setup object

				$post_obj = mgm_get_post($post_id);

				// check

				if( $this->send_payment_email($_POST['custom']) ) {	

				// check

					if( mgm_notify_user_post_purchase($blogname, $user, $post, $purchase_status, $system_obj, $post_obj, $status_str) ){

					// update as email sent 

						$this->update_paymentemail_sent($_POST['custom']);

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
	

	
		//mgm_log(print_r( debug_backtrace(),true ),__FUNCTION__);

		$system_obj = mgm_get_class('system');		

		$s_packs = mgm_get_class('subscription_packs');

		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));

		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));

		$new_subscription = false;

		

		// get passthrough, stop further process if fails to parse

		$custom = $this->_get_transaction_passthrough($_POST['custom']);



		//mgm_log(print_r($custom,true),__FUNCTION__);	
		//mgm_log(print_r($this->response,true),__FUNCTION__);	

		// local var

		extract($custom);

		

		// currency

		if (!$currency) $currency = $this->setting['currency'];

		

		// find user

		$user = get_userdata($user_id);

		

		// multiple subscription level modification

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

		// not verified

		if ( ! $membership_type_verified ) {

			// not free

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

				

		// payment info for unsubscribe	

		if(!isset($member->payment_info))	

			$member->payment_info    = new stdClass;

		$member->payment_info->module = $this->code;		

		// transaction type

		$member->payment_info->txn_type = 'subscription';

		// subscriber id

		if(isset($this->response['PROFILEID'])) {

			// tag

			$new_subscription = true;

			// set

			$member->payment_info->subscr_id = $this->response['PROFILEID'];

			// reset rebilled count

			if(isset($member->rebilled)) unset($member->rebilled);		

		}

		// refer id

		if(isset($this->response['CORRELATIONID']))

			$member->payment_info->txn_id = $this->response['CORRELATIONID'];	

		// mgm transaction id

		$member->transaction_id = $_POST['custom'];

		// process PayPal response

		$new_status = $update_role = false;

		

		//FROM IPN		

		if(isset($_POST['txn_type'])) { 

			//exit if IPN posts after a successful recurring proile creation

			if($_POST['txn_type'] == 'recurring_payment_profile_created' ) { exit;

			}else {				 

				$this->response['ACK'] = (in_array($_POST['txn_type'], array('recurring_payment','subscr_payment','web_accept'))) ? 'Success' : 'Failure'; 

			}

		}


		//mgm_log(print_r($_POST,true),__FUNCTION__);

		

		// status

		switch ($this->response['ACK']) {

			case 'Success':		

			case 'SuccessWithWarning':

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

							$time = strtotime('+' . $trial_duration . ' ' . $duration_exprs[$trial_duration_type], $time);								

						}					

					} else {

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

					if($member->duration_type == 'dr'){// el = date range

						$member->expire_date = $duration_range_end_dt;

					}																		

				}

								

				//update rebill: issue #: 489

				if($member->active_num_cycles != 1 && (int)$member->rebilled < (int)$member->active_num_cycles) {

					// rebill

					$member->rebilled = (!$member->rebilled) ? 1 : ((int)$member->rebilled+1);		

				}

				

				//cancel previous subscription:

				//This is required only for the transactions initiated by the user

				if(isset($_POST['custom'])) $this->cancel_recurring_subscription($_POST['custom'], null, null, $pack_id);

				

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

			case 'Failure':

			case 'FailureWithWarning':

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

				$member->status_str = sprintf(__('Last payment status: %s','mgm'), (isset($_POST['payment_status']) ? $_POST['payment_status'] : 'Unknown'));

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

		$acknowledge_user = $this->send_payment_email($_POST['custom']);

		// whether to subscriber the user to Autoresponder - This should happen only once

		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $_POST['custom']);

		

		// another_subscription modification

		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {//issue #1227

			// hide old content

			if($subs_pack['hide_old_content'])	$member->hide_old_content = $subs_pack['hide_old_content']; 

			

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

		do_action('mgm_return_subscription_payment_'.$this->module, array('user_id' => $user_id, 'new_subscription' => $new_subscription));// new , individual	

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

				if(mgm_notify_user_membership_purchase($blogname, $user, $member, $custom, $subs_pack, $s_packs, $system_obj) ){						

					// update as email sent 

					$this->update_paymentemail_sent($_POST['custom']);	

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

	function _cancel_membership($user_id=NULL, $redirect=false){

		// system	

		$system_obj = mgm_get_class('system');		

		$s_packs  = mgm_get_class('subscription_packs');

		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));

		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));

		//issue #1521

		$is_admin = (is_super_admin()) ? true : false;

		// get custom field values

		if( ! $user_id ){

			// get passthrough, stop further process if fails to parse

			$custom = $this->_get_transaction_passthrough($_POST['custom']);

			// local var

			extract($custom);

		}

		

		// find user

		$user = get_userdata($user_id);	

		$member = mgm_get_member($user_id);	

		// multiple membesrhip level update:

		$multiple_update = false;		

		// check

		if( (isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type'] ) || (isset($is_another_membership_purchase) && $is_another_membership_purchase == 'Y' )){

			$multiple_update = true;

			$multi_memtype = (isset($_POST['membership_type'])) ? $_POST['membership_type'] : $membership_type;

			$member = mgm_get_member_another_purchase($user_id, $multi_memtype);		

		}

		

		// check IPN profile id with member current profile id:

		if(isset($_POST['recurring_payment_id']) && isset($member->payment_info->subscr_id)) {

			//this is to skip updation if IPN posted is for internal cancellation while upgrade(related to issue#: 566)

			if(!empty($member->payment_info->subscr_id) && $_POST['recurring_payment_id'] != $member->payment_info->subscr_id) {

				// return

				return;

			}

		}

				

		// get pack

		if($member->pack_id){

			$subs_pack = $s_packs->get_pack($member->pack_id);

		}else{

			$subs_pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);

		}

		

		// tracking fields module_field => post_field

		$tracking_fields = array('txn_id'=>'CORRELATIONID');

		// save tracking fields

		$this->_save_tracking_fields($tracking_fields, $member, $this->response);		

		

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

		if( $redirect ){

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

	 * @param int/string $subscr_id	// if -1, it will treated as a lost subscription id

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

							$subscr_id = 0;

					}					

					

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

		//ony for subscription_purchase		

		if(!is_null($subscr_id)) {	

								

			$user = get_userdata($user_id);

			$date_format = mgm_get_date_format('date_format');

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

			$endpoint =  $this->_get_endpoint();	

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

			//$http_response = mgm_remote_post($endpoint, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));	



			 $http_response = $this->do_curl(http_build_query($post_data));		

			// sleep

			@sleep(1);

			// parse

			$response = array();

			// parse

			parse_str($http_response, $response);						

			// cancel				

			return ((isset($response['ACK']) && $response['ACK'] == 'Success') ? true : $response);

		}elseif(is_null($subscr_id) || $subscr_id === 0) {			

			// send email to admin if subscription Id is absent		

			$system_obj = mgm_get_class('system');

			$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));

			// send email only if setting enabled

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


	function do_rebill($args){


		$tran = mgm_get_transaction($args['transaction_id']);


		$pack_id = $tran['data']['id'];

		if($pack_id) $currency = mgm_get_pack_currency($pack_id);	


		$post_data = array();	

		$secure =array(

			'USER'         => $this->setting['username'],	

			'PWD'          => $this->setting['password'],		

			'SIGNATURE'    => $this->setting['signature'],

			'VERSION'      => '64.0',

			'IPADDRESS'    => mgm_get_client_ip_address(),

			'CURRENCYCODE' => ($currency) ? $currency : $this->setting['currency']);


		$post_data = array_merge($post_data, $secure);

		$post_data['METHOD']     = 'GetRecurringPaymentsProfileDetails';

		$post_data['PROFILEID']  = $args['subscr_id'];

		$endpoint = $this->_get_endpoint();	

		$url_parsed = parse_url($endpoint);  

		$domain = $url_parsed['host'];	


		$http_response = $this->do_curl(http_build_query($post_data));	


		$this->response = array();					

			// parse

		parse_str($http_response, $this->response);	

	}


	function rebill_status_check($member, $args){

		//mgm_log($args['subscr_id'],__FUNCTION__);
		
		$update_other = false;


		$user_id = $member->id;

		//if ($member->payment_info->subscr_id == $args['subscr_id']) {
			//mgm_log('here1',__FUNCTION__);		
			//$current_membership = $member;
		//}

		if(count($member->other_membership_types) > 0){
			$count = 0;
			foreach($member->other_membership_types as $other){

				if($other['payment_info']['subscr_id'] == $args['subscr_id']){
					//mgm_log('here2',__FUNCTION__);	
					//$current_membership  = $member->other_membership_types[$count];
					$update_other = true;
					if(is_array($other)) $member = mgm_convert_array_to_memberobj($other, $member->id);	


					//if(is_array($other)) $current_membership = $member->other_membership_types[$count];

				

				}
				$count++;

			}
		}

		//mgm_log(print_r($member, true),__FUNCTION__);

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


				if($update_other) {

					mgm_save_another_membership_fields($member, $user_id);

				} else {				

			   		$member->save();

				} 

				// save

				//$member->save();



				// only run in cron, other wise too many tracking will be added
				// if( defined('DOING_QUERY_REBILL_STATUS') && DOING_QUERY_REBILL_STATUS != 'manual' ){
				// transaction_id
				//$transaction_id = $member->transaction_id;
				
				$transaction_id = $args['transaction_id'];

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

				//$member->save();

				
				if($update_other) {

					mgm_save_another_membership_fields($member, $user_id);

				} else {				

			   		$member->save();

				} 

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
				//$member->save();
				
				if($update_other) {

					mgm_save_another_membership_fields($member, $user_id);

				} else {				

			   		$member->save();

				} 

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



	/**

	 * Specifically check recurring status of each rebill for an expiry date

	 * ALong with IPN post mechanism for rebills, the module will need to specifically request for the rebill status

	 * @param int $user_id

	 * @param object $member

	 * @return boolean

	 */

	function query_rebill_status($user_id, $member=NULL) {	

		// check 
		$subscriptions = array();


		//mgm_log(print_r($user_id, true),__FUNCTION__);	

		if (isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id)) {		

			$subscriptions[] = array(
				'subscr_id'=>$member->payment_info->subscr_id,
				'transaction_id'=>$member->transaction_id,


				);
		}

		if(count($member->other_membership_types) > 0){
			foreach($member->other_membership_types as $other){

				if(isset($other['payment_info']['subscr_id'])){
					$subscriptions[] = array(
						'subscr_id'=>$other['payment_info']['subscr_id'],
						'transaction_id'=>$other['transaction_id'],
					);
				}
			}
		}

		//mgm_log(print_r($subscriptions, true),__FUNCTION__);


		foreach($subscriptions as $subscription){


		//	mgm_log($subscription,__FUNCTION__);

			$this->do_rebill($subscription);


			//mgm_log(print_r($this->response, true),__FUNCTION__);


			if (isset($this->response['STATUS'])) {

				$this->rebill_status_check($member, $subscription);
				//if($this->rebill_status_check($member,$subscription)){
					return true;
				//} else {
				//	return false;
				//}

			}




		}	

			
		// return
		//return true;
		//return false;//default to false to skip normal modules

	}


		

	// default setting

	function _default_setting(){

		// paypalpro specific		

		$this->setting['username']  = '';

		$this->setting['password']  = '';

		$this->setting['signature'] = '';	

		$this->setting['locale']    = 'US';	

		$this->setting['currency']  = mgm_get_class('system')->setting['currency'];

		//issue #974

		$this->setting['max_failed_payments'] = 3;		

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

			if(isset($this->response['TRANSACTIONTYPE'])){

				$option_name = $this->module.'_'.strtolower($this->response['TRANSACTIONTYPE']).'_return_data';

			}else{

				$option_name = $this->module.'_return_data';

			}

			// set

			//mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=> (isset($this->response[$option]) ? json_encode($this->response) : '')));

			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=> (!empty($this->response) ? json_encode($this->response) : json_encode($_POST))));

			

			// options 

			$options = array('PROFILEID','CORRELATIONID','TRANSACTIONID'); 

			// loop

			foreach($options as $option){

				if(isset($this->response[$option])){

					mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>strtolower($this->module.'_'.$option),'option_value'=> (isset($this->response[$option]) ? $this->response[$option] : '' )));

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

		$end_points_default = array('test' => 'https://api-3t.sandbox.paypal.com/nvp',

									'live' => 'https://api-3t.paypal.com/nvp');	

		// merge

		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;

		// set

		$this->_set_endpoints($end_points);

	}

	

	// set 

	function _set_address_fields($user, &$data){

		// mappings

		$mappings = array('first_name'=>'firstname','last_name'=>'lastname', 'address'=>array('street','street2'),

						  'city'=>'city','state'=>'state','zip'=>'zip','country'=>'countrycode','phone'=>'shiptophonenum');

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

		return (isset($_POST['custom'])) ? true : false;

	}



	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////

	

	// filter postdata

	function _filter_postdata($post_data, $join=false){

		// card holder name

		list($ch_first_name, $ch_last_name) = explode(' ', $post_data['mgm_card_holder_name']);	

		// init

		$filtered = array();				

		// capture some as sent

		$fields_sent = array('USER', 'PWD', 'SIGNATURE', 'VERSION', 'IPADDRESS', 'CURRENCYCODE');		

		// set

		foreach($fields_sent as $field){

			// take only when set

			if(isset($post_data[$field]) && !empty($post_data[$field])){

				$filtered[$field] = $post_data[$field];

			}

		}	

		

		// common		

		$filtered['FIRSTNAME'] = (isset($post_data['firstname']) ? $post_data['firstname'] : $ch_first_name);

		$filtered['LASTNAME']  = (isset($post_data['lastname']) ? $post_data['lastname'] : $ch_last_name);		

		$filtered['EMAIL']     = $post_data['email'];

		$filtered['DESC']      = $post_data['description'];

		$filtered['SUBSCRIBERNAME'] = (isset($post_data['lastname']) ? $post_data['lastname'] : $ch_last_name).' '.(isset($post_data['lastname']) ? $post_data['lastname'] : $ch_last_name);

		$filtered['AMT']       = $post_data['amount'];

		// street

		if(isset($post_data['street'])){

			$filtered['STREET'] = $post_data['street'];

		}

		if(isset($post_data['street2'])){

			$filtered['STREET2'] = $post_data['street2'];

		}

		// city

		if(isset($post_data['city'])){

			$filtered['CITY'] = $post_data['city'];

		}

		// state

		if(isset($post_data['state'])){

			$filtered['STATE'] = $post_data['state'];

		}

		// zip

		if(isset($post_data['zip'])){

			$filtered['ZIP'] = $post_data['zip'];

		}

		// country

		if(isset($post_data['countrycode'])){

			$filtered['COUNTRYCODE'] = $post_data['countrycode'];

		}

		// notifyurl		

		if(isset($post_data['notifyurl'])){

			$filtered['NOTIFYURL'] = $post_data['notifyurl'];

		}



		//issue #974

		if(is_numeric($this->setting['max_failed_payments'])){

			$max_failed_payments = round($this->setting['max_failed_payments']);

		}else {

			$max_failed_payments = 3;

		}

		

		// mgm_log('max_failed_payments '.$max_failed_payments);

		

		// subscription purchase 

		if($post_data['recurring_billing'] == 'TRUE'){

			$filtered['METHOD']            = 'CreateRecurringPaymentsProfile';

			$filtered['PROFILEREFERENCE']  = $post_data['invnum'];

			$filtered['PROFILESTARTDATE']  = $post_data['start_date'];

			//issue #974

			$filtered['MAXFAILEDPAYMENTS'] = $max_failed_payments;

			$filtered['AUTOBILLAMT']       = 'AddToNextBilling';

			// period

			$filtered['BILLINGPERIOD']      = $post_data['billing_period']; // Day|Month|Year

			$filtered['BILLINGFREQUENCY']   = $post_data['billing_frequency']; // 3|12 etc.	frequency

			if(isset($post_data['total_billing_cycles'])){		

				$filtered['TOTALBILLINGCYCLES'] = $post_data['total_billing_cycles'];			

			}

			// trial

			if(isset($post_data['trial_billing_period'])){

				$filtered['TRIALBILLINGPERIOD']    = $post_data['trial_billing_period'];

				$filtered['TRIALBILLINGFREQUENCY'] = $post_data['trial_billing_frequency'];

				if($post_data['trial_total_billing_cycles']){// need this checked				

					$filtered['TRIALTOTALBILLINGCYCLES'] = $post_data['trial_total_billing_cycles'];

				}

				$filtered['TRIALAMT'] = $post_data['trial_amt'];

			}

		}else{

			// post purchase

			$filtered['METHOD']        = 'DoDirectPayment';										

			$filtered['INVNUM']        = $post_data['invnum'];

			$filtered['PAYMENTACTION'] = 'Sale'; // Sale/Authorization/Order

			// custom

			if(isset($post_data['custom'])){

				$filtered['CUSTOM'] = $post_data['custom'];

			}					

		}		

		

		// card data

		$filtered['CREDITCARDTYPE'] = $post_data['mgm_card_type'];

		$filtered['ACCT']           = $post_data['mgm_card_number'];

		$filtered['EXPDATE']        = $post_data['mgm_card_expiry_month'] . $post_data['mgm_card_expiry_year'];

		$filtered['CVV2']           = $post_data['mgm_card_code'];						

		

		// send filtered
		// 
		return http_build_query($filtered);

		//return ($join) ? mgm_http_build_query($filtered) : $filtered;

	}
		/**

	 * API helper method get credit card page html

	 *

	 * @param array $user

	 * @param array $tran

	 * @param string $html_type (div|table)

	 * @return string $html

	 */

	function _get_ccfields($user=NULL, $tran=NULL, $html_type='div'){

		global $wpdb;

		// data

		$data = array();

		

		// name, amount

		$data['name'] = $data['billing_info'] = $purchase_desc = '';

		// address

		$address_fields = array();

		

		// user id

		if(isset($user->ID) && (int)$user->ID > 0) {

			// name

			$data['name'] = isset($user->first_name) && isset($user->last_name) ? mgm_str_concat($user->first_name, $user->last_name) : $user->display_name;	

			// member

			$member = mgm_get_member($user->ID);

			// packs 	

			$packs_obj = mgm_get_class('subscription_packs');



			//issue #806

			// pack

			if(isset($tran['data']['pack_id']) && !empty($tran['data']['pack_id'])){

				$pack_id = $tran['data']['pack_id'];	

			}elseif(isset($tran['data']['id']) && !empty($tran['data']['id'])){

				//Issue #1058

				$pack_id = $tran['data']['id'];	

			}else {

				$pack_id = $member->pack_id;

			}

			

			$pack = $packs_obj->get_pack($pack_id);

			// using coupon - issue #1501

			if( isset($tran['data']['subscription_option']) && $tran['data']['subscription_option'] == 'create'){

				mgm_get_register_coupon_pack($member, $pack);

			}

			$coupon_id = $tran['data']['coupon_id'];

	


			$sql = $wpdb->prepare("SELECT *  FROM `" . TBL_MGM_COUPON . "` WHERE id = %s", $coupon_id);	
			$coupon = $wpdb->get_row($sql);




			// pack desc			

			$purchase_desc = sprintf( '<div class="ccfields_pack_desc">%s</div>', $packs_obj->get_pack_desc($pack) );

			$coupon_details = '';

			if(isset($coupon)){
				
				$coupon_details =   '<div class="bg5 tbpd28 rel">
                            <span class="icon-uniF0D6 coupon-icon"></span>
                            <p class="zeta color2 uppercase semibold lpd60">'.$coupon->name.'</p>
                            <p class="zeta color3 italic lpd60">'.$coupon->description.'</p>
                        </div>';

			}



			if($tran['data']['duration_type'] == 'w'){
        		$length = $tran['data']['duration'] - 1;
        		$unit = ' Week Programme';

    		} 
    		if($tran['data']['duration_type'] == 'd'){
        		$unit = ' Day Programme';
        		$length = $tran['data']['duration'];
    		}
    		if($tran['data']['duration_type'] == 'm'){
        
	        	if( $tran['data']['duration'] == 1){
	            	$unit = 'Monthly Subscription';
	            	$length = '';
	        	}
      


		    }
		     if($tran['data']['num_cycles'] == '1'){
		        $payment_type = 'Single one-off payment';

		    }
		    if($tran['data']['num_cycles'] == '0'){
		         $payment_type = 'Paid Monthly';
		    }


		



			 $purchase_desc = '</div>

                <div class="grid__item one-third lpd20">
                    <div class="round-border1">
                        <div class="text-center bg5 b-br-grey2">
                            <p class="epsilon color2 tbpd20 semibold">You\'ve Selected</p>
                        </div>
                        <div class="bg18 pd20 text-center b-br-grey2">
                            <p class="delta color2 semibold">'.$pack['description'].'</p>
                            <p class="epsilon color3 bmg28 semibold">'.$length . $unit.'</p>
                            <a href="" class="btn btn--full btn--soft btn--primary big-button">£'.$tran['data']['cost'].'</a>
                            <span class="uppercase color2 single-off btn--soft semibold">'.$payment_type .'</span>
                        </div>'.$coupon_details .'</div></div></div>';

			// buypost

			if(isset($tran['payment_type']) && $tran['payment_type']=='post_purchase'){

				$purchase_desc = $tran['data']['item_name'] . ' ['. $tran['data']['cost'] . ' ' . $tran['data']['currency'].']';

			}		

		}	

		// address fields

		$address_fields = $this->_get_address_fields($user, 'both');	

		// cancel

		$data['cancel_url'] = $this->_get_endpoint('cancel');		

		

		// head
		// 
		$html = '';

		//$html = $purchase_desc;

		// billing_info

		if(!empty($address_fields['fields'])){

			// get mgm_form_fields generator

			$form_fields = new mgm_form_fields();

			// info

			//$billing_info = sprintf('<h2>%s</h2><br>', __('Billing Info','mgm'));

			$billing_info = '';

			// check

			if(isset($address_fields['fields'])){

				// row template

				$row_template = '';

				//$req_tag = "<span class='required'>*</span>";
				$req_tag = "";

				// template

				switch($html_type){

					case 'table':

						$row_template =	"<tr>

											<td valign='top'><label for='%s'>%s %s</label></td>

											<td valign='top'>%s</td>	

										 </tr>";

					break;

					case 'div':

					default: 

						$row_template = '<li class="grid__item one-half tmg20 rpd8"><p class="epsilon color2 bmg12">%s</p><span class="form-border"><label class="form-label infield" for="%s">Your %s %s</label>%s</span></li>';

					break;

				}

				// form_html

				$form_html = '';

				// loop

				foreach($address_fields['fields'] as $field){

					// req

					$req  = ((bool)$field['attributes']['required'] == true) ? $req_tag : '';

					// value

					$value = (isset($address_fields['captured'][$field['name']])) ? $address_fields['captured'][$field['name']] : '';

					// type cls

					$type_class = ($field['type'] == 'select') ? 'select' : 'input';

					// class

					if((bool)$field['attributes']['required'] == true){			

						// append

						$type_class .= ' form-text-input';									

						// reset to skip default required class

						$field['attributes']['required'] = false;

					}

					// set class
			

					$field['attributes']['id'] = $field['name'];
				



					// elem

					//$elem = $form_fields->get_field_element($field, 'mgm_payment_field', $value);

					//$elem = '<input name="'.$field['name'].'" value="" class="" id="" />';
					//
					$elem = '<input id="'.$field['name'].'" type="text" name="mgm_payment_field['.$field['name'].']" value="'.$value.'" class="mgm_payment_field form-text-input"/>';

					// form 

					$form_html .= sprintf($row_template,$field['label'], $field['name'], $field['label'], $req, $elem);				

				}

				// set

				$billing_info .= $form_html;

				// set

				$data['billing_info'] = $billing_info;

			}

		}




		// credit card types: read from settings

		$card_types = array();

		// loop

		foreach ($this->card_types as $type => $label) {

			// check

			if( isset($this->setting['supported_card_types']) && is_array($this->setting['supported_card_types']) ){

				if (in_array($type, $this->setting['supported_card_types'])) {

					$card_types[$type] = $label;

				}

			}			

		}

		// set		

		$data['card_types'] = $card_types;

		// code

		$data['code'] = $this->code;




		

		// html

		$html .= mgm_get_include(MGM_CORE_DIR . sprintf('modules/payment/imdmspaypalpro/html/payment_cc_form_%s.php', $html_type), array('data'=>$data));

		$html .= $purchase_desc;

		

		// cc form

		$cc_form = sprintf("<div id='%s_form_cc'>%s</div>", $this->code, $html);

		

		// apply filter

		return apply_filters('mgm_cc_form_html', $cc_form, $this->code, $data);

	}


	

}



// end file