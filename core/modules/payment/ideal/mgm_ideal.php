<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

 /**
 * iDEAL payment module, integrates eCommerce
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */
class mgm_ideal extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_ideal();
	}
	
	// construct
	function mgm_ideal(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'iDEAL';
		// logo
		$this->logo = $this->module_url( 'assets/ideal.gif' );
		// desc
		$this->description = __('iDEAL is a secure online retail outlet for more than 10,000 digital product '.
			                    'vendors and 100,000 active affiliate marketers.','mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');//does not support recurring subscription though
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
	
	// settings
	function settings(){
		global $wpdb;
		// data
		$data = array();		
		// set 
		$data['module'] 		= $this;	
		// aquirers
		$data['aquirer_list'] 	= array('ing' => __('ING','mgm'), 'rabo' => __('Rabo - Lite','mgm'), 
		                                'rabo_omnikassa' => __('Rabo - OmniKassa','mgm'), 
		                                'abnamro'=>__('ABN-Amro','mgm'), 'sisow'=>__('SISOW','mgm'));
		// Payment request options
		$data['payment_mean_brand_list'] 	= array('' => __('DEFAULT','mgm'), 'IDEAL' => __('IDEAL','mgm'), 
		                                	'MINITIX' => __('MINITIX','mgm'),'VISA'=>__('VISA','mgm'), 
		                                	'MASTERCARD' => __('MASTERCARD','mgm'),'MAESTRO'=>__('MAESTRO','mgm'), 
		                                	'INCASSO' => __('INCASSO','mgm'),'ACCEPTGIRO'=>__('ACCEPTGIRO','mgm'), 
		                                	'REMBOURS'=>__('REMBOURS','mgm'));		                                
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
				// ideal specific
				$this->setting['aquirer']     			= $_POST['setting']['aquirer'];
				$this->setting['merchant_id'] 			= $_POST['setting']['merchant_id'];				
				$this->setting['secret_key']  			= $_POST['setting']['secret_key'];
				$this->setting['key_version'] 			= $_POST['setting']['key_version'];
				$this->setting['sub_id'] 	  			= $_POST['setting']['sub_id'];
				$this->setting['language'] 	  			= $_POST['setting']['language'];
				$this->setting['currency'] 	  			= $_POST['setting']['currency'];
				$this->setting['payment_mean_brand'] 	= $_POST['setting']['payment_mean_brand'];
				
				// purchase price
				if(isset($_POST['setting']['purchase_price'])){
					$this->setting['purchase_price']  = $_POST['setting']['purchase_price'];
				}
				// setup callback messages				
				$this->_setup_callback_messages($_POST['setting']);
				// re setup callback urls
				$this->_setup_callback_urls($_POST['setting']);
				// common
				$this->description = $_POST['description'];
				$this->status      = $_POST['status'];
				
				// logo if uploaded
				if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
					$this->logo = $_POST['logo_new_'.$this->code];
				}				
				// save
				$this->save();
				// create script
				$this->_check_idealraboomnikassa_proxy(true);
				//  message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));
			break;
		}		
	}		
	
	// return process api hook, link back to site after payment is made
	function process_return() {			
		//record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		//check get data
		if((isset($_REQUEST['status']) && !empty($_REQUEST['status'])) && (isset($_REQUEST['extra']) && !empty($_REQUEST['extra']))){						
			// redirect as success if not already redirected
			// query arg	
			$_REQUEST['trans_id'] = isset($_REQUEST['transactionReference']) ? $_REQUEST['transactionReference'] : $this->_decode_id($_REQUEST['extra']);
			// check
			if(strtolower($_REQUEST['status']) == 'success' && is_numeric($_REQUEST['trans_id']) && $this->_is_transaction($_REQUEST['trans_id'])) {				
				// process notify, internally called
				if( ! in_array($this->setting['aquirer'], array('rabo_omnikassa', 'sisow')) ){
					// caller
					$this->set_webhook_called_by( 'self' );
					// call
					$this->process_notify();// internal		
				}	
				// query arg
				$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($_REQUEST['trans_id']));
				// is a post redirect?
				$post_redirect =$this->_get_post_redirect($_REQUEST['trans_id']);
				// set post redirect
				if($post_redirect !== false){
					$query_arg['post_redirect'] = $post_redirect;
				}
				// is a register redirect?
				$register_redirect = $this->_auto_login($_REQUEST['trans_id']);		
				// set register redirect
				if($register_redirect !== false){
					$query_arg['register_redirect'] = $register_redirect;
				}
				// redirect				
				mgm_redirect(add_query_arg($query_arg, $this->_get_thankyou_url()));
			}elseif ($_REQUEST['status'] == 'error') {
				// error				
				mgm_redirect(add_query_arg(array('status'=>'error','errors'=>urlencode('ideal receipt error')), $this->_get_thankyou_url()));
			}
		}else{
			// error
			mgm_redirect(add_query_arg(array('status'=>'error','errors'=>urlencode('ideal receipt error')), $this->_get_thankyou_url()));
		}
	}
	
	// notify process
	function process_notify(){	
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );			
		// verify
		if ($this->_verify_callback()) {	
			// trans id
			$_REQUEST['trans_id'] = isset($_REQUEST['transactionReference']) ? $_REQUEST['transactionReference'] : $this->_decode_id($_REQUEST['extra']);
			// log data before validate
			$tran_id = $this->_log_transaction();				
			// payment type
			$payment_type = $this->_get_payment_type($_REQUEST['trans_id']);
			// custom
			$custom = $this->_get_transaction_passthrough($_REQUEST['trans_id']);
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

		// issue #: 366 (send header only if called directly from ideal)		
		// 200 OK to iDEAL, this is IMPORTANT, otherwise SS will keep on sending IPN .........		
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
		// only for sisow
		if( $this->setting['aquirer'] != 'sisow' ){
			return parent::process_unsubscribe();
		}		
		// get user id
		$user_id = (int)$_POST['user_id'];		
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// get user
		$user = get_userdata($user_id);	
		$member = mgm_get_member($user_id);		
		// multiple membesrhip level update:
		if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']){
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);				
		}	

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

			// cancel at ideal
			$cancel_account = $this->cancel_recurring_subscription(null, $user_id, $subscr_id);						
		}	
			
		// cancel in MGM
		if($cancel_account === true){
			$this->_cancel_membership($user_id, true);// redirected
		}
		
		// message
		$message = isset($this->response['message_text']) ? $this->response['message_text'] : __('Error while cancelling subscription', 'mgm') ;
		// issue #1521
		if( $is_admin ){
			mgm_redirect( add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php')) );
		}
		// force full url, bypass custom rewrite bug
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
		
		// endpoint
		$endpoint = $this->_get_endpoint();
		// sisow
		if( 'sisow' ==  $this->setting['aquirer'] ){
			// set action
			$endpoint = str_replace('[action]', 'TransactionRequest', $endpoint);
			// get post data
			$post_data = $this->_get_button_data($tran['data'], $tran_id);			 
			// headers
			$http_headers = array();//array('Content-Type' => 'text/xml');
			// create curl post				
			$http_response = mgm_remote_post($endpoint, $post_data, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false));
			// parse to xml
			if($xml = @simplexml_load_string( $http_response )){				
				// redirect to issuer
				if(isset($xml->transaction->issuerurl)){
					wp_redirect(urldecode($xml->transaction->issuerurl));
				}else{
					// capture error
					if(isset( $xml->error->errormessage ) ){
						$html = (string)$xml->error->errormessage;
					}else{
						$html = __('Unknown error occured, please try again!','mgm');
					}
					// return 	  
					return $html;	
				}
			}			
			// exit
			exit();
		}

		// generate
		$button_code     = $this->_get_button_code($tran['data'],$tran_id);
		// extra code
		$additional_code = do_action('mgm_additional_code');
		
		// the html
		$html='<form action="'. $endpoint .'" method="post" class="mgm_form" name="' . $this->code . '_redirect_form" id="' . $this->code . '_redirect_form">
					'. $button_code .'					
					'. $additional_code .'											
					<img src="'.MGM_ASSETS_URL.'images/ajax/ajax-loader.gif"/><br>
					<b>'.sprintf(__('Please wait, you are being redirected to %s...','mgm'), $this->name).'</b>
			   </form>
			   <script language="javascript">document.' . $this->code . '_redirect_form.submit();</script>';
		// return 	  
		return $html;					
	}
	
	// get button subscribe api hook	
	function get_button_subscribe($options=array()) {
		$html = "";
		//iDEAL doesn't support subscription payment
		if(((int)$options['pack']['num_cycles'] == 1)) {
			$include_permalink = (isset($options['widget'])) ? false : true;	
			// get html
			$html='<form action="'. $this->_get_endpoint('html_redirect', $include_permalink) .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					   <input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
					   <input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">
					   <div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
				   </form>';
			// return	 		
		}		
		return $html;		
	}
	
	// buypost button api hook
	function get_button_buypost($options=array(), $return = false) {
		global $user;	
		
		// get post id
		if (isset($options['pack']['ppp_pack_id'])) {
			// get comma separated list of post ids					
			$options['pack']['post_id'] = mgm_get_postpack_posts_csv($options['pack']['ppp_pack_id']);
		} else {				
			// single post	
			$options['pack']['post_id'] = get_the_ID();					
		}
		// merge pack
		$options['pack'] = array_merge($options['pack'], array('duration'=>1, 'item_name'=>'Purchase Post - '.$options['pack']['title'], 'buypost'=>1));		
		
		$include_permalink = (isset($options['widget'])) ? false : true;	
		// html
		$html = '<form action="'. $this->_get_endpoint('html_redirect',$include_permalink) .'" method="POST" class="mgm_form" name="' . $this->code . '_form">	
					<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="form-table"> 					
						<tr>
							<td align="center">
								<input type="hidden" name="tran_id" value="'.$options['tran_id'].'">						 								
								<input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" name="submit" alt="' . $this->name . '" border="0">
								<div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>	
							</td>
						</tr>
					</table>
				</form>';
		// return
		if ($return) {
			return $html;
		} else {
			echo $html;
		}
	}
	
	// unsubscribe button api hook
	function get_button_unsubscribe($options=array()){	
		// only for sisow
		if( $this->setting['aquirer'] != 'sisow' ){
			return parent::get_button_unsubscribe();
		}
		
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

	// get module transaction info
	function get_transaction_info($member, $date_format){			
		// data
		$subscription_id  = $member->payment_info->subscr_id;
		$transaction_id   = $member->payment_info->txn_id;	
		$authorisation_id = $member->payment_info->authorisation_id;
		// set default
		$ideal_txn_id  = __('N/A','mgm');
		// eway tran
		if(isset($member->payment_info->ideal_txn_id)){
			$ideal_txn_id = $member->payment_info->ideal_txn_id;
		}
		// info
		$info = sprintf('<b>%s:</b><br>%s: %s<br>%s: %s<br>%s: %s', __('IDEAL INFO','mgm'), __('SUBSCRIPTION ID','mgm'), $subscription_id, 
						__('TRANSACTION ID','mgm'), $transaction_id, __('AUTHORISATION ID','mgm'), $authorisation_id);					
		// set
		$transaction_info = sprintf('<div class="overline">%s</div>', $info);
		
		// return 
		return $transaction_info;
	}
	
	/////////////////////////////////////////////// internal/private methods /////////////////////////////////////////////////
	
	// get button code	
	function _get_button_code($pack, $tran_id = NULL) {
		// data
		$data   = $this->_get_button_data($pack, $tran_id);
		// strip 
		$data = mgm_stripslashes_deep($data);
		// return
		$return = '';	
		// loop
		foreach ($data as $key => $value) {
			$return .= '	<input type="hidden" name="'. $key .'" value="'. esc_html($value) .'" />' . "\n";
		}	
		// return
		return $return;
	}

	// get button data
	function _get_button_data($pack, $tran_id = NULL) {
		// system
		$system_obj      = mgm_get_class('system');		
		// item 		
		$item            = $this->get_pack_item($pack);
		//pack currency over rides genral setting currency - issue #1602
		if(!isset($pack['currency']) || empty($pack['currency'])){
			$pack['currency']=$this->setting['currency'];
		}			
		// type
		$membership_type = md5($pack['membership_type']);
		$user_id         = mgm_get_user_id();
		$user            = get_userdata($user_id);
		$member          = mgm_get_member($user_id);	
		$amount			 = mgm_convert_to_cents($pack['cost']);
		$payment_type	 = ((int)$pack['num_cycles']==1) ? 'one-time' : 'subscription';
		$s_valid_until	 = date('Y-m-d\TG:i:s\Z', strtotime('+1 hour'));		
		$description	 = substr($item['name'], 0, 32);
		
		// Setup hash string
		$hash_string = $this->setting['secret_key'] . $this->setting['merchant_id'] . $this->setting['sub_id'] 
						. $amount . $tran_id . $payment_type . $s_valid_until 
						. '1' . $description . '1' . $amount;

		// Remove HTML Entities
		$hash_string = html_entity_decode($hash_string);
		// Remove space characters: "\t", "\n", "\r" and " "
		$hash_string = str_replace(array("\t", "\n", "\r", " "), '', $hash_string);
		// Generate hash
		$hash_string  = sha1($hash_string);
		$enc_trans_id = $this->_encode_id($tran_id);
		$cancel_url   = add_query_arg( array('status' => 'cancel','extra' => $enc_trans_id), $this->setting['cancel_url']);
		$success_url  = add_query_arg( array('status' => 'success','extra' => $enc_trans_id), $this->setting['return_url']);
		$error_url 	  = add_query_arg( array('status' => 'error','extra' => $enc_trans_id), $this->setting['return_url']);
		// set data
		$data = array(			
			'merchantID'	   => $this->setting['merchant_id'], 
			'subID'			   => $this->setting['sub_id'],
			'language'		   => $this->setting['language'],
			'currency'		   => $pack['currency'],
			'amount'		   => $amount,
			'purchaseID'	   => $tran_id,
			'description'	   => $description,
			'hash'			   => $hash_string,
			'paymentType' 	   => $payment_type,
			'validUntil'	   => $s_valid_until,
			'itemNumber1'	   => '1',
			'itemDescription1' => $description,
			'itemQuantity1'	   => '1',
			'itemPrice1'       => $amount,
			'urlCancel'		   => $cancel_url,
			'urlSuccess'	   => $success_url,
			'urlError'		   => $error_url,
		);
		
		// add filter @todo test
		$data = apply_filters('mgm_payment_button_data', $data, $tran_id, $this->module, $pack);
		
		// rabo changes
		if($this->setting['aquirer'] == 'rabo_omnikassa'){
			// convert for rabobank
			$data = $this->_get_rabobank_data($data, $tran_id);		
		}elseif($this->setting['aquirer'] == 'sisow'){
			// convert for rabobank
			$data = $this->_get_sisow_data($data, $tran_id);		
		}
		
		// update pack/transaction
		mgm_update_transaction(array('data'=>json_encode($pack),'module'=>$this->module), $tran_id);
		
		// data		
		return $data;
	}	
	
	//robo data	
	function _get_rabobank_data($data, $tran_id){
		// new data
		$n_data = array();
		// map
		$map = array('currencyCode'=>'currency','merchantId'=>'merchantID','normalReturnUrl'=>'urlSuccess','amount'=>'amount',
		             'transactionReference'=>'purchaseID','customerLanguage'=>'language','orderId'=>'purchaseID');
		// loop
		foreach($map as $k=>$v){
			$n_data[$k] = $data[$v] ;
		}		
		// date
		// $n_data['expirationDate']	    = $data['validUntil'];// date(DATE_ISO8601, strtotime('+1 hour'));	 
		// key version		 
		$n_data['keyVersion']           = $this->setting['key_version'];		
		$n_data['paymentMeanBrandList'] = $this->setting['payment_mean_brand'];
		// current 4217 code
		$n_data['currencyCode']         = mgm_get_currency_iso4217($n_data['currencyCode']);
		$n_data['customerLanguage']	    = substr($n_data['customerLanguage'],0,2);	
		// url
		$n_data['automaticResponseUrl'] = site_url() . '/idealraboomnikassa_proxy.php?url_' . base64_encode(add_query_arg( array('extra' => $data['purchaseID']), $this->setting['notify_url']));
		$n_data['normalReturnUrl']      = site_url() . '/idealraboomnikassa_proxy.php?url_' . base64_encode(add_query_arg( array('extra' => $data['purchaseID']), $this->setting['return_url']));
		
		// convert = to %3D, rabo strips = 
		// $n_data['automaticResponseUrl'] = str_replace('=', '%3D', $n_data['automaticResponseUrl']);
		// $n_data['normalReturnUrl']      = str_replace('=', '%3D', $n_data['normalReturnUrl']);
				
		// filter null
		$n_data = array_filter($n_data);
		// data string
		$data_str = _http_build_query($n_data, null, '|', '', false);
		$seal_str = $data_str . $this->setting['secret_key'];
		// seal
		$seal = hash('sha256', utf8_encode($seal_str)); //computeSeal
		// interface
		$interface = 'HP_1.0';		
		// return 
		return array('Data'=>$data_str, 'Seal'=>$seal, 'InterfaceVersion'=>$interface);
	}	
	
	// sisow data
	function _get_sisow_data($data, $tran_id){
		// new data
		$n_data = array();		
		// map
		$map = array('purchaseid'=>'purchaseID','entrancecode'=>'purchaseID','amount'=>'amount',
					 'shopid'=>'shopid','merchantid'=>'merchantID','description'=>'description');
		// loop
		foreach($map as $k=>$v){
			if( isset($data[$v]) ){
				$n_data[$k] = $data[$v];
			}else{
				$n_data[$k] = '';
			}			
		}	
		// rest			
		$n_data['payment']   = ''; 
		$n_data['testmode']  = ($this->status == 'test') ? 'True' : 'False'; 		
		
		$enc_trans_id = $this->_encode_id($tran_id);		
		$n_data['returnurl'] = add_query_arg( array('extra' => $enc_trans_id), $this->setting['return_url']);	
		$n_data['notifyurl'] = add_query_arg( array('extra' => $enc_trans_id), $this->setting['notify_url']);	
		$n_data['cancelurl'] = add_query_arg( array('extra' => $enc_trans_id), $this->setting['cancel_url']);	
		// data string
		$sha1_plain = implode('', array_merge(array_slice($n_data,0,5), array($this->setting['secret_key'])));
		// sha1	
		$n_data['sha1'] = sha1($sha1_plain);		
		// set
		return $n_data;
	}

	// buy post
	function _buy_post() {
		global $wpdb;
		// get system settings
		$system_obj = mgm_get_class('system');
		$dge  = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		
		// get passthrough data		
		$custom = $this->_get_transaction_passthrough($_REQUEST['trans_id']);
		extract($custom);

		// find user
		$user = null;
		// check
		if(isset($user_id) && (int)$user_id > 0) $user = get_userdata($user_id);

		// errors
		$errors = array();
		// purchase status
		$purchase_status = 'Error';

		// set status		
		if( 'sisow' ==  $this->setting['aquirer'] ){
			$payment_status = (isset($_REQUEST['status'])) ? $_REQUEST['status'] : 'Error';	
		}else{		
			$payment_status = (isset($_REQUEST['status']) && $_REQUEST['status'] == 'success') ? 'SALE' : 'ERROR';				
		}	
		
		// payment_status 
		switch (trim($payment_status)) {
			case "SALE" :			
			case 'Success':	
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';

				// transation id
				$transaction_id = $this->_get_transaction_id('trans_id', $_REQUEST);
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
			case "ERROR" :
			case 'Failure':	
				// status	
				$status_str = __('Last payment was refunded or denied','mgm');
				// purchase status
				$purchase_status = 'Failure';	

				// error
				$errors[] = $status_str;
			break;

			case "CANCEL-REBILL" :
			case "UNCANCEL-REBILL" :
			case 'Pending':	
				// status
				$status_str = __('Last payment is pending. Reason: Unnown','mgm');
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
		mgm_update_transaction_status($_REQUEST['trans_id'], $status, $status_str);
		
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
			if( $this->is_webhook_called_by('self') ){// only when proxied via payment_return
				mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
			}
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
		$custom = $this->_get_transaction_passthrough($_REQUEST['trans_id']);	
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
		
		// set	
		$member->payment_info->module = $this->code;
		// subscription id
		if(isset($_REQUEST['subscr_id'])){
			$member->payment_info->subscr_id = $_REQUEST['subscr_id'];		
		}			
		// sisow txn id
		if(isset($_REQUEST['trxid'])){	
			$member->payment_info->txn_id = $_REQUEST['trxid'];	// ideal txn for cancel subscription
		}elseif(isset($_REQUEST['trans_id'])){	
			$member->payment_info->txn_id = $_REQUEST['trans_id'];	
		}
		
		// magicmem txn id
		if(isset($_REQUEST['ec'])){	
			$member->transaction_id = $_REQUEST['ec'];
		}elseif(isset($_REQUEST['trans_id'])){	
			$member->transaction_id = $_REQUEST['trans_id'];	
		}
		// author id	
		if(isset($_REQUEST['authorisationId'])){
			$member->payment_info->authorisation_id = $_REQUEST['authorisationId'];		
		}
		// process response
		$new_status = $update_role = false;
		// errors
		$errors = array();
		// set status		
		if( 'sisow' ==  $this->setting['aquirer'] ){
			$payment_status = (isset($_REQUEST['status'])) ? $_REQUEST['status'] : 'Error';	
		}else{		
			$payment_status = (isset($_REQUEST['status']) && $_REQUEST['status'] == 'success') ? 'SALE' : 'ERROR';				
		}			
		// on transaction
		switch (trim($payment_status)) {			
			case "SALE" :	
			case 'Success':
				$new_status = MGM_STATUS_ACTIVE;
				$member->status_str = __('Last payment was successful','mgm');				
				
				$time = time();
				$last_pay_date = isset($member->last_pay_date) ? $member->last_pay_date : null;				
				$member->last_pay_date = date('Y-m-d', $time);
				
				// update expire date
				/* ***********************************
				if ($member->expire_date) {
					$expiry = strtotime($member->expire_date);
					if ($expiry > 0) {
						if ($expiry > $time) {
							$time = $expiry;
						}
					}
				}
				*************************************/
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
				$this->cancel_recurring_subscription($_REQUEST['trans_id'], null, null, $pack_id);
				
				// role update
				if ($role) $update_role = true;		
				
				// transaction_id
				$transaction_id = $this->_get_transaction_id('trans_id', $_REQUEST);
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
			case "ERROR" :
			case 'Failure':			
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				// error
				$errors[] = $member->status_str;
			break;

			case "CANCEL-REBILL" :
			case "UNCANCEL-REBILL" :
			case 'Pending':
				$new_status = MGM_STATUS_PENDING;

				$reason = 'Unknown';
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
		$acknowledge_user = $this->send_payment_email($_REQUEST['trans_id']);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $_REQUEST['trans_id']);
		
		//another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {			//issue #1227
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
			if( $this->is_webhook_called_by('self') ){// only when proxied via payment_return
				mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
			}	
		}
	}	
	
	// cancel membership
	function _cancel_membership($user_id, $redirect = false){
		// system	
		$system_obj  = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));	
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// find user
		$user   = get_userdata($user_id);
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
					
					mgm_log('RECALLing '. $member->payment_info->module .': cancel_recurring_subscription FROM: ' . $this->code);
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;
				
			}else 
				return false;
		}else{ 
			if($this->setting['aquirer'] != 'sisow') { // skip sisow
				return false; //skip as trans reff will need to be passed always	
			}
		}	
		
		
		//only for subscription_purchase		
		if($this->setting['aquirer'] == 'sisow'){
			if( !isset($member) ) $member = mgm_get_member($user_id);		
			$txn_id = $member->payment_info->txn_id; 
			
			//mgm_pr($member);
			// endpoint
			$endpoint = $this->_get_endpoint();
			// set
			$endpoint = str_replace('[action]', 'CancelReservationRequest', $endpoint);
			// keys
			$compute_keys = array($txn_id,$this->setting['merchant_id'],$this->setting['secret_key']);
			$compute_sha1 = sha1( implode('', $compute_keys) );			
			$post_data = array('trxid'=>$txn_id, 'merchantid'=>$this->setting['merchant_id'],'sha1'=>$compute_sha1);			 
			// headers
			$http_headers = array();//array('Content-Type' => 'text/xml');
			//mgm_pr($post_data); die;
			// create curl post				
			$http_response = mgm_remote_post($endpoint, $post_data, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false));
			mgm_log($http_response, __FUNCTION__);
			// parse
			if($xml = @simplexml_load_string( $http_response )){		
				mgm_log($xml, __FUNCTION__);		
				// redirect to issuer
				if(isset($xml->reservation->status)){
					return ((string)$xml->reservation->status == 'Cancelled') ? true : false;
				}else{
					return false;
				}
			}				
		}
			
		
		//send email only if setting enabled
		if((!empty($subscr_id) || $subscr_id === 0)) {
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

	// get end point
	function _get_endpoint($type=false, $include_permalink = true) {
		// status
		$type    = ($type===false) ? $this->status : $type;
		$aquirer = $this->setting['aquirer'];		
		// type/status
		switch($type) {
			case 'sim': //simulator				
				return $this->end_points['sim'];	
			break;
			case 'test':
			case 'live':
				// test
				if($type == 'test'){
					$replace = ($aquirer == 'rabo_omnikassa' ? '.simu' : 'test');
				}else{
					$replace = ($aquirer == 'abnamro' ? 'prod' : '');
				}	
				// replace
				return str_replace('[TEST]', $replace, $this->end_points[$aquirer]);
			break;			
		}	
		
		// default
		return parent::_get_endpoint($type, $include_permalink);
	}
	
	//just to recreate the passthrough data
	function _parse_passthrough(){		
		return (isset($_REQUEST['trans_id']) && (int)$_REQUEST['trans_id'] > 0 ) ? $_REQUEST['trans_id'] : 0;
	}		
			
	// default setting
	function _default_setting(){
		// ideal specific
		$this->setting['aquirer']    			= '';
		$this->setting['merchant_id']   		= '';		
		$this->setting['secret_key'] 			= '';
		$this->setting['key_version'] 			= '1';
		$this->setting['sub_id'] 				= '';
		$this->setting['language']    			= 'nl';
		$this->setting['currency']   			= mgm_get_class('system')->setting['currency'];
		$this->setting['payment_mean_brand']	= '';
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
		if($this->_is_transaction($_REQUEST['trans_id'])){	
			// tran id
			$tran_id = (int)$_REQUEST['trans_id'];			
			// return data				
			if(isset($_REQUEST['transaction_type'])){
				$option_name = $this->module.'_'.strtolower($_REQUEST['transaction_type']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_REQUEST)));
			
			// options 
			$options = array('transaction_type','subscr_id','trans_id','trxid');
			// loop
			foreach($options as $option){
				if(isset($_REQUEST[$option])){
					mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>strtolower($this->module.'_'.$option),'option_value'=>$_REQUEST[$option]));
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
		if($this->_is_transaction($_REQUEST['trans_id'])){	
			// tran id
			return $tran_id = (int)$_REQUEST['trans_id'];
		}
		// return 
		return 0;	
	}*/
	
	// setup
	function _setup_endpoints($end_points = array()){
		// define defaults
		$end_points_default = array('rabo'    => 'https://ideal[TEST].rabobank.nl/ideal/mpiPayInitRabo.do', // Rabobank test: test, prod:
									'rabo_omnikassa' 
									          => 'https://payment-webinit[TEST].omnikassa.rabobank.nl/paymentServlet', // .simu / 
									'ing'     => 'https://ideal[TEST].secure-ing.com/ideal/mpiPayInitIng.do', //ING Bank test: test, prod:
									'sim'     => 'https://www.ideal-simulator.nl/lite/', //simulator
									'abnamro' => 'https://internetkassa.abnamro.nl/ncol/[TEST]/orderstandard.asp', // ABN Amro  test: test, prod: prod
									'sisow'   => 'https://www.sisow.nl/Sisow/iDeal/RestHandler.ashx/[action]'
									);	
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// list fo available languages
	function _get_languages(){
		$languages = array('en_US' => __('English (default)','mgm'),
						   'dk_DK' => __('Danish','mgm'),
						   'nl_NL' => __('Dutch','mgm'),
						   'nl_BE' => __('Flemish','mgm'),
						   'fr_FR' => __('French','mgm'),
						   'de_DE' => __('German','mgm'),
						   'it_IT' => __('Italian','mgm'),
						   'ja_JP' => __('Japanese','mgm'),
						   'no_NO' => __('Norwegian','mgm'),
						   'pl_PL' => __('Polish','mgm'),
						   'pt_PT' => __('Portugese','mgm'),
						   'es_ES' => __('Spanish','mgm'),
						   'se_SE' => __('Swedish','mgm'),
						   'tr_TR' => __('Turkish','mgm'));		
		return $languages;
	}
	//encode
	function _encode_id($trans_id) {
		return mgm_encode_id($trans_id);
	}
	
	//decode
	function _decode_id($trans_id) {
		return base64_decode(base64_decode($trans_id));
	}
	
	// verify callback 
	function _verify_callback(){
		// check sisow
		if($this->setting['aquirer'] == 'sisow'){			
			// sha1
			if( isset($_REQUEST['sha1']) ){
				// keys
				$compute_keys = array($_REQUEST['trxid'],$_REQUEST['ec'],$_REQUEST['status'],$this->setting['merchant_id'],$this->setting['secret_key']);
				$compute_sha1 = sha1( implode('', $compute_keys) );				
				// match
				return ($compute_sha1 == $_REQUEST['sha1']) ? true : false;
			}
			// error			
			return false;
		}
		// check
		return (isset($_REQUEST['extra'])) ? true : false;	
	}	

	
	
	// rabo parse piped string
	function parse_rabo_piped_string($string) {
		$data = array();

		$pairs = explode('|', $string);
		foreach($pairs as $pair) {
			list($key, $value) = explode('=', $pair);

			$data[$key] = $value;
		}
		// status
		$data['status'] = ($data['responseCode'] == '00') ? 'success' : 'error';
		
		// return
		return $data;
	}
	
	// response code
	function get_rabo_response_code_description() {
		return array(
			'00' => 'Transaction success, authorization accepted' , 
			'02' => 'Please call the bank because the authorization limit on the card has been exceeded' , 
			'03' => 'Invalid merchant contract' , 
			'05' => 'Do not honor, authorization refused' , 
			'12' => 'Invalid transaction, check the parameters sent in the request' , 
			'14' => 'Invalid card number or invalid Card Security Code or Card (for MasterCard) or invalid Card Verification Value (for Visa/MAESTRO)' , 
			'17' => 'Cancellation of payment by the end user' , 
			'24' => 'Invalid status' , 
			'25' => 'Transaction not found in database' , 
			'30' => 'Invalid format' , 
			'34' => 'Fraud suspicion' , 
			'40' => 'Operation not allowed to this Merchant' , 
			'60' => 'Pending transaction' , 
			'63' => 'Security breach detected, transaction stopped' , 
			'75' => 'The number of attempts to enter the card number has been exceeded (three tries exhausted)' , 
			'90' => 'Acquirer server temporarily unavailable' , 
			'94' => 'Duplicate transaction' , 
			'97' => 'Request time-out; transaction refused' , 
			'99' => 'Payment page temporarily unavailable' 
		);
	}
	
	// reconstruct rabo postback
	function _reconstruct_rabo_postback(){
		// rabo
		if($this->setting['aquirer'] == 'rabo_omnikassa'){
			// post
			if(isset($_POST['Data'])){
				// convert for rabobank	
				$_POST = $this->parse_rabo_piped_string($_POST['Data']);	
				// status	
				$_REQUEST['status'] = $_POST['status']; 				
			}elseif(isset($_REQUEST['Data'])){
				// convert for rabobank
				$_REQUEST = $this->parse_rabo_piped_string($_REQUEST['Data']);		
			}	
		}		
	}
	
	// create/check proxy
	function _check_idealraboomnikassa_proxy($create=false){
		// for rabo omnikassa
		if($this->setting['aquirer'] == 'rabo_omnikassa'){
			// create
			if($create){
				// check if already exists
				if(!file_exists(ABSPATH . 'idealraboomnikassa_proxy.php')){
					// return url
					$notify_url = $this->setting['notify_url'];
					// str
					$str ='<?php if(isset($_SERVER["QUERY_STRING"]) && preg_match("/^url_/",$_SERVER["QUERY_STRING"])){ header("Location: " . base64_decode(str_replace("url_", "", $_SERVER["QUERY_STRING"])) . "&" . http_build_query(parse_rabo_piped_string($_POST["Data"]))); exit;} '.
						  'function parse_rabo_piped_string($string) {$data = array(); $pairs = explode("|", $string); foreach($pairs as $pair) {list($key, $value) = explode("=", $pair);$data[$key] = $value;} $data["status"] = ($data["responseCode"] == "00") ? "success" : "error"; return $data;}?>';
					// file create
					file_put_contents(ABSPATH . 'idealraboomnikassa_proxy.php', $str);	
				}
			}
			
			// check
			if(file_exists(ABSPATH . 'idealraboomnikassa_proxy.php')){
				// return
				return sprintf('<div class="mgm_proxy_installed">%s<br><br>%s</div>',site_url('idealraboomnikassa_proxy.php'),
								__('Ideal Rabo OmniKassa Postback Proxy installed.','mgm'));
							
			}else{
				// return 
				return sprintf('<div class="mgm_proxy_not_installed">%s<br><br>%s</div>', site_url('idealraboomnikassa_proxy.php'), 
							   __('IdealRabo Postback Proxy not installed. If you select Rabo OmniKassa, this is needed to process PostBack from Rabo,'.
							      'Please save settings to create the proxy file.','mgm'));
							
			}
		}else{
			return $this->setting['notify_url'];
		}		
	}
}

// end file