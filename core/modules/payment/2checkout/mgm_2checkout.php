<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * 2Checkout Payment Module
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */
class mgm_2checkout extends mgm_payment{	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_2checkout();
	}
	
	// php4 construct
	function mgm_2checkout(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = '2Checkout';	
		// logo
		$this->logo = $this->module_url( 'assets/2co.jpg' );
		// description
		$this->description = __('2Checkout.com is the authorized reseller for over 1.6 million tangible '.
		                        'or digital products and services. Web businesses (suppliers/vendors) agree to sell '.
								'their goods and services to 2CO for immediate resale.', 'mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');
		// trial support available ?
		$this->supports_trial = 'N';	
		// cancellation support available ?
		$this->supports_cancellation = 'Y';	
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
						$message = sprintf(__('%s module has been %s', 'mgm'),$this->name, $stat);							
						$extra   = array('enable' => $enable_state);	
					break;
				}							
				// print message
				echo json_encode(array_merge(array('status'=>'success','message'=>$message,'module'=>array('name'=>$this->name,'code'=>$this->code,'tab'=>$this->settings_tab)), $extra));
			break;
			case 'main':
			default:
			// from main				
				// 2Checkout specific
				$this->setting['sid']         = $_POST['setting']['sid'];
				$this->setting['secret_word'] = $_POST['setting']['secret_word'];
				$this->setting['apiusername'] = $_POST['setting']['apiusername'];
				$this->setting['apipassword'] = $_POST['setting']['apipassword'];
				$this->setting['currency']    = $_POST['setting']['currency'];		
				$this->setting['lang']        = $_POST['setting']['lang'];	
				$this->setting['subs_cancel'] = $_POST['setting']['subs_cancel'];						
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
				$this->supports_trial = 'N';
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
	
	// hook for post purchase setting
	function settings_post_purchase($data=NULL){
		// product_id
		$product_id = isset($data->product['2checkout_product_id']) ? $data->product['2checkout_product_id'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data->allowed_modules) && in_array($this->code,(array)$data->allowed_modules)){
			$display = 'class="displaynone"';
		}
		// overwrite this
		$html = '<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '>
					<div class="row">
						<div class="cell"><div class="postpurhase-heading">' . __('2Checkout Settings','mgm') . '</div></div>
					 </div>
					 <div class="row">
						<div class="cell width125px mgm-padding-tb"><b>' . __('Product ID','mgm') . ':</b></div>
					 </div>	
					 <div class="row"> 	
						<div class="cell textalignleft">
							<input type="text" name="mgm_post[product][2checkout_product_id]" classs="mgm_text_width_payment" value="'.esc_html($product_id).'" />
						</div>
					 </div>					
				 </div>';

				 /*<label>'.__('2Checkout Product ID','mgm').'</label>
					<input type="text" classs="mgm_text_width_payment" name="mgm_post[product][2checkout_product_id]" value="'. esc_html($product_id) .'" />*/
		// return
		return $html;
	}
	
	// hook for post pack purchase setting
	function settings_postpack_purchase($data=NULL){
		// product_id
		$product_id = isset($data->product['2checkout_product_id']) ? $data->product['2checkout_product_id'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data->modules) && in_array($this->code,(array)$data->modules)){
			$display = 'class="displayblock"';
		}
		// overwrite this
		$html = '<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '>
					 <div class="row">
						<div class="cell"><div class="subscription-heading">' . __('2Checkout Settings','mgm') . '</div></div>
					 </div>
					 <div class="row">
						<div class="cell width125px"><b>' . __('Product ID','mgm') . ':</b></div>
					 </div>	
					 <div class="row"> 	
						<div class="cell textalignleft">
							<input type="text" name="product[2checkout_product_id]" value="'.esc_html($product_id).'" />
						</div>
					 </div>
				 </div>';
		// return
		return $html;
	}
	
	// hook for subscription package setting
	function settings_subscription_package($data=NULL){
		// product_id
		$product_id = isset($data['pack']['product']['2checkout_product_id']) ? $data['pack']['product']['2checkout_product_id'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data['pack']['modules']) && in_array($this->code,(array)$data['pack']['modules'])){
			$display = 'class="displayblock"';
		}
		// html
		$html = '<div id="settings_subscription_package_' . $this->module. '" ' . $display . '>
				 	<div class="row">
						<div class="cell"><div class="subscription-heading">'.__('2Checkout Settings','mgm').'</div></div>
					</div>
					<div class="row">
						<div class="cell">
							<div class="marginleft10px">	
								<p class="fontweightbold">' . __('Product ID','mgm') . '</p>
								<input type="text" name="packs['.($data['pack_ctr']-1).'][product][2checkout_product_id]" value="'.esc_html($product_id).'" />
								<div class="tips width95">' . __('2CO ID for the product.','mgm') . '</div>
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
		$product_id = isset($data['2checkout_product_id']) ? $data['2checkout_product_id'] : ''; 
		// overwrite this
		$html = '<div class="row">
					<div class="cell"><div class="subscription-heading">' . __('2Checkout Settings','mgm') . '</div></div>
			    </div>
			    <div class="row">
					<div class="cell width125px"><b>'. __('Product ID','mgm') . ':</b></div>
			    </div>	
			    <div class="row">	
					<div class="cell textalignleft">
						<input type="text" name="product[2checkout_product_id]" value="' . esc_html($product_id) . '" />
					</div>
			    </div>';
		// return
		return $html;
	}
	
	// return process api hook, link back to site after payment is made
	function process_return() {	
		// passthrough
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// check and show message
		if( (isset($alt_tran_id) && ! empty($alt_tran_id)) ){
			// process notify, internally called for demo			
			// the below line is not required as IPN will be called seperately:
			// caller
			$this->set_webhook_called_by( 'self' );
			// issue #: 527
			$this->process_notify();// this records the order_number/sale_id for the user in transaction log			
			// redirect as success if not already redirected			
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
			// meta redirect, needed to change url from 2checkout
			mgm_redirect(add_query_arg($query_arg, $this->_get_thankyou_url()), 302, 'meta');
		}else{							
			// needed to change url from 2checkout
			mgm_redirect(add_query_arg(array('status'=>'error'), $this->_get_thankyou_url()), 302, 'meta');
		}
	}	
	
	// notify process api hook, background IPN url
	function process_notify() {		
		//record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		// update 'custom' with vendor_order_id INS post
		// passthrough
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// issue#: 734
		/*if (isset($_POST['vendor_order_id']) && !empty($_POST['vendor_order_id']))
			 $_POST['custom'] = $_POST['vendor_order_id'];*/
			 
		// verify 
		if ($this->_verify_callback()) {		
			// log data before validate
			$tran_id = $this->_log_transaction();			
			// payment type
			$payment_type = $this->_get_payment_type($alt_tran_id);
			// custom
			$custom = $this->_get_transaction_passthrough($alt_tran_id);
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
					// cancellation
					if(isset($_POST['message_type']) && $_POST['message_type']=='RECURRING_STOPPED') {						
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
		}elseif($this->_verify_callback_ins()){// end verify on first sale
			// process ins,for 2checkout only
			$this->_process_ins_messages();
		}
				
		// after process unverified		
		do_action('mgm_notify_post_process_unverified_'.$this->module);	
				
		// 200 OK to gateway, only external	
		if( $this->is_webhook_called_by('merchant') ){	
			if( ! headers_sent() ){
				@header('HTTP/1.1 200 OK');			
				exit('OK');
			}	
		}
	}
	
	// status notify process api hook, background INS url
	function process_status_notify(){
		//record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		// proces
		if($this->_verify_callback_ins()){// end verify on first sale
			// process ins,for 2checkout only
			$this->_process_ins_messages();
		}
		
		// 200 OK to gateway, only external		
		if(!headers_sent()){
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
		// set auth
		$auth = $this->setting['apiusername'] . ':' . $this->setting['apipassword'];
		
		// get user id
		$user_id = $_POST['user_id'];
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// get user
		$user = get_userdata($user_id);
		// member
		$member = mgm_get_member($user_id);
		// multiple membesrhip level update:
		if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type'])
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']); 		

		// init
		$cancel_account = true;		
		// sale id, returned order_number on approval url post back
		if(isset($member->payment_info->module) && $member->payment_info->module == $this->code) {// self check
			// init
			$sale_id = null;		
			// txn		
			if(!empty($member->payment_info->txn_id))
				$sale_id = $member->payment_info->txn_id;
			elseif (!empty($member->pack_id)) {	
				// check the pack is recurring
				$s_packs = mgm_get_class('subscription_packs');				
				$sel_pack = $s_packs->get_pack($member->pack_id);										
				if($sel_pack['num_cycles'] != 1) 
					$sale_id = 0;// 0 stands for a lost subscription id
			}		

			// cancel at 2checkout
			$cancel_account = $this->cancel_recurring_subscription(null, $user_id, $sale_id);			
		}
		
		// cancel in MGM
		if($cancel_account === true){
			$this->_cancel_membership($user_id, true);// redirected
		}

		// message
		$message = is_string($cancel_account) ? $cancel_account :  __('Error while cancelling subscription', 'mgm') ;
		//issue #1521
		if( $is_admin ){
			mgm_redirect( add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php')) );
		}			
		// redirect to membership details 
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
		$pack_id = (isset($options['pack']['pack_id'])) ? $options['pack']['pack_id'] : $options['pack']['id'];
		$pack = mgm_get_class('subscription_packs')->get_pack($pack_id);	
		// 2co depends on product id, check for it before generating button
		if(!isset($pack['product']['2checkout_product_id']) || @intval($pack['product']['2checkout_product_id'])==0){
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in 2Checkout settings : No Product ID set.','mgm').'</b>
					</div>';
			exit;
		}	
		/*// 2co depends on product id, check for it before generating button
		if(!isset($options['pack']['product']['2checkout_product_id']) || intval($options['pack']['product']['2checkout_product_id'])==0){
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in 2Checkout settings : No Product ID set.','mgm').'</b>
					</div>';
			exit;
		}*/
		$include_permalink = (isset($options['widget'])) ? false : true;
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect', $include_permalink) .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
				   <input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
				   <input type="image" name="submit" class="mgm_paymod_logo"  src="' . mgm_site_url($this->logo) . '" border="0"  alt="' . $this->name . '">
				   <div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
			   </form>';
		// return	   
		return $html;
	}
	
	// buypost button api hook
	function get_button_buypost($options=array(), $return = false) {
		// 2co depends on product id, check for it before generating button
		if(!isset($options['pack']['product']['2checkout_product_id']) || intval($options['pack']['product']['2checkout_product_id'])==0){
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in 2Checkout settings : No Product ID set.','mgm').'</b>
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
	
	// get module transaction info
	function get_transaction_info($member, $date_format){		
		// sale
		$sale_id  = (int)$member->payment_info->txn_id;		
		// info
		$info = sprintf('<b>%s:</b><br>%s: %d', __('2CHECKOUT INFO','mgm'), __('SALE ID','mgm'), $sale_id);					
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
		// <p>%s: <input type="text" size="20" name="2checkout[subscriber_id]"/></p> __('Subscription ID','mgm'),
		// html
		$html = sprintf('<p>%s: <input type="text" size="20" name="2checkout[transaction_id]"/></p>', __('Transaction ID','mgm'));
		
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
		$fields = array(/*'subscr_id'=>'subscriber_id',*/'txn_id'=>'transaction_id');
		// data
		$data = $post_data['2checkout'];
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
			'sid'               => $this->setting['sid'],						
			'product_name'      => $item['name'],					
			'tco_currency'      => $pack['currency'],
			'lang'	            => $this->setting['lang'],
			'pay_method'        => 'CC',
			'fixed'             => 'Y', // remove the Continue Shopping button and lock the quantity fields.
			'merchant_order_id' => $tran_id,  
			'return_url'        => $this->setting['return_url'],
			'email'	            => $user->user_email			
		);
			
		// address fields, see parent for all fields, only different given here
		$this->_set_address_fields($user, $data);
		
		// product based	
		if(isset($pack['product']['2checkout_product_id'])){
			$data['product_id'] = $pack['product']['2checkout_product_id'];	
			$data['quantity']   = 1;
		}else{
		// use total
			$data['total'] = $pack['cost'];
		}	
				
		// test flag
		if($this->status == 'test'){
		 	$data['demo'] = 'Y';
		} 
				
		// custom passthrough
		$data['custom'] = $tran_id;	
		
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
		
		// passthrough
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

		// process
		switch ($_POST['credit_card_processed']) {
			case 'Y':
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';		
				
				// transation id
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

			case 'K':		
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
				$status_str = sprintf(__('Last payment status: %s','mgm'),$_POST['credit_card_processed']);
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
		$s_packs    = mgm_get_class('subscription_packs');
		$dge        = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne       = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		
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
		// $member->payment_type    = 'subscription';
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		// payment info for unsubscribe	
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'message_type', 'subscr_id'=>array('order_number','sale_id'), 'txn_id'=>array('order_number','sale_id'));
		// save tracking fields
		$this->_save_tracking_fields($tracking_fields, $member);
		
		// mgm transaction_id id
		$member->transaction_id = $alt_tran_id;
		
		// process response
		$new_status = false;
		$update_role = false;
		
		//FOR INS recurring
		if(isset($_POST['credit_card_processed'])) {
			$response_status = $_POST['credit_card_processed'];
		}elseif( isset($_POST['recurring']) && $_POST['recurring'] == 1 ) { 
			switch ($_POST['message_type']) {
				case 'RECURRING_INSTALLMENT_SUCCESS':
					$response_status = 'Y';
					break;
				case 'RECURRING_INSTALLMENT_FAILED':
					$response_status = 'K';
					break;
				case 'RECURRING_STOPPED':
				case 'RECURRING_COMPLETE':
					$response_status = 'Expired';
					break;
				default:
					$response_status = 'Other';
					break;	
			}
		}
		
		// response status
		switch ($response_status) {
			case 'Y':
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
							// update expire date
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
				$this->cancel_recurring_subscription($alt_tran_id, null, null, $pack_id);
				
				//clear cancellation status if already cancelled:
				if(isset($member->status_reset_on)) unset($member->status_reset_on);
				if(isset($member->status_reset_as)) unset($member->status_reset_as);
						
				// role
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
			case 'K':
			
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				break;

			case 'Pending':
				$new_status = MGM_STATUS_PENDING;

				$reason = 'Unknown';
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				break;
				
			case 'Expired':
				$new_status = MGM_STATUS_EXPIRED;

				$reason = 'Expired';
				$member->status_str = sprintf(__('Recurring subscription expired. Reason: %s','mgm'), $reason);
				break;		

			default:
				$new_status = MGM_STATUS_ERROR;
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $_POST['credit_card_processed']);
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
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {			
			//issue #1227
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
		
		// action
		do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);		
		
		//update coupon usage
		do_action('mgm_update_coupon_usage', array('user_id' => $user_id));
		
		// update role
		if ($update_role) {			
			//update role;			
			$obj_role = new mgm_roles();	
			// add			
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
	function _cancel_membership($user_id=NULL, $redirect = false){
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs    = mgm_get_class('subscription_packs');
		$dge        = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne       = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// passthrough var
		$alt_tran_id = $this->_get_alternate_transaction_id();
		
		// get custom field values if not called with user id( internal)
		if(!$user_id){	
			// get passthrough, stop further process if fails to parse
			$custom = $this->_get_transaction_passthrough($alt_tran_id);
			// local var
			extract($custom);
		}	
		
		// user
		$user = get_userdata($user_id);	
		$member = mgm_get_member($user_id);		
		// multiple membesrhip level update:	
		$multiple_update = false;		
		// check
		if((isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']) || (isset($membership_type) && $member->membership_type != $membership_type )) {
			$multiple_update = true;	
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);
		} 
		
		// skip if IPN POST for previous subscription:
		if(!empty($alt_tran_id) && !empty($member->transaction_id) && $alt_tran_id != $member->transaction_id) {
			return false;
		}
		
		// get pack
		if($member->pack_id){
			$subs_pack = $s_packs->get_pack($member->pack_id);
		}else{
			$subs_pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
		}
				
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'message_type', 'subscr_id'=>array('order_number','sale_id'), 'txn_id'=>array('order_number','sale_id'));
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
		// if today or set as instant cancel
		if($expire_date == date('Y-m-d') || $this->setting['subs_cancel']=='instant'){
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
		}else {			
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
			mgm_redirect(add_query_arg(array('unsubscribed'=>'true','unsubscribe_errors'=>urlencode($message)), mgm_get_custom_url('membership_details')));	
		}
	}	
	
	/**
	 * Cancel Recurring Subscription
	 *
	 * @param int/string $trans_ref	
	 * @param int $user_id	
	 * @param int/string $subscr_id	
	 * @return boolean/string message
	 */			
	function cancel_recurring_subscription($trans_ref = null, $user_id = null, $sale_id = null, $pack_id = null) {
		// if coming form process return after a subscription payment
		if(!empty($trans_ref)) {
			// transaction data
			$transdata = $this->_get_transaction_passthrough($trans_ref);
			// validate
			if($transdata['payment_type'] != 'subscription_purchase')
				return false;				
			
			// user		
			$user_id = $transdata['user_id'];
			
			// multiple purchase				
			if(isset($transdata['is_another_membership_purchase']) && $transdata['is_another_membership_purchase'] == 'Y') {
				$member = mgm_get_member_another_purchase($user_id, $transdata['membership_type']);			
			}else {
				$member = mgm_get_member($user_id);			
			}
			// subscription exists
			if(isset($member->payment_info->module) && !empty($member->payment_info->module)) {
				// sale id				
				if(isset($member->payment_info->txn_id)) {
					$sale_id = $member->payment_info->txn_id; 
				}else {
					//check pack is recurring:
					$pid = $pack_id ? $pack_id : $member->pack_id;					
					if($pid) {
						$s_packs  = mgm_get_class('subscription_packs');
						$sel_pack = $s_packs->get_pack($pid);												
						if($sel_pack['num_cycles'] != 1)
							$sale_id = 0;//not found
					}										
				} 
				 
				// module info												
				// check for same module: if not call the same function of the applicale module.
				if(str_replace('mgm_','' , $member->payment_info->module) != str_replace( 'mgm_', '' , $this->code ) ) {						
					// recur
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;			
			}else{ 
			// error
				return false;
			}	
		}
		// only for subscription_purchase
		if($sale_id) {									
			// sale id, returned order_number on approval url post back
			// saledetail_url
			$saledetail_url = add_query_arg(array('sale_id'=>$sale_id), $this->_get_endpoint('saledetail'));
			// auth string
			$auth = $this->setting['apiusername'] . ':' . $this->setting['apipassword'];
			// post data
			$post_data = array();
			// headers
			$http_headers = array('Accept' => 'application/json', 'Authorization' => 'Basic ' . base64_encode( $auth ));							
			// fetch		
			$http_response = mgm_remote_post($saledetail_url, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false)); 
			// decode 	
			$response = json_decode($http_response);		
			// ind	
			$lineitem_id = '';		
			// validate
			if((string)$response->response_code == 'OK'){
				$lineitem_id = (string)$response->sale->invoices[0]->lineitems[0]->billing->lineitem_id;			
			}			
			// set post
			$post_data = array('vendor_id'=>$this->setting['sid'],'lineitem_id'=>$lineitem_id);							   
			// unsubscribe_url
			$unsubscribe_url =  $this->_get_endpoint('unsubscribe');	
			// fetch		
			$http_response = mgm_remote_post($unsubscribe_url, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false)); 				
			// decode 	
			$response = json_decode($http_response);
			// return status
			if((string)$response->response_code == 'OK')
				return true;
			else 
				return $response->errors[0]->message;	
		}elseif($sale_id === 0) {			
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
			return true;
		}
		// default
		return false;
	}

	// default setting
	function _default_setting(){
		// 2checkout specific
		$this->setting['sid']         = '';	
		$this->setting['secret_word'] = '';	
		$this->setting['apiusername'] = '';
		$this->setting['apipassword'] = '';
		$this->setting['currency']    = mgm_get_class('system')->get_setting('currency');
		$this->setting['lang']        = 'en';	
		$this->setting['subs_cancel'] = 'instant';// instant/delayed  
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
		// passthrough var
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// check
		if($this->_is_transaction($alt_tran_id)){
			// tran id
			$tran_id = (int)$alt_tran_id;			
			// return data				
			if(isset($_POST['message_type'])){
				$option_name = $this->module.'_'.strtolower($_POST['message_type']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_POST)));
			
			// options 
			$options = array('message_type','order_number','sale_id');
			// loop
			foreach($options as $option){
				// set
				if(isset($_POST[$option])){
					mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>strtolower($this->module.'_'.$option),'option_value'=>$_POST[$option]));
				}
			}
			// sale id
			if(isset($_POST['order_number']) && !isset($_POST['sale_id'])){
				mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>($this->module.'_sale_id'),'option_value'=>$_POST['order_number']));
			}	
			// return transaction id
			return $tran_id;		
		}	
		// error
		return false;	
	}
	
	// MODULE SPECIFIC PRIVATE HELPERS -----------------------------------------------------------------------
	
	// setup endpoints
	function _setup_endpoints($end_points = array()){					
		// define defaults
		$end_points_default = array('test'        => false,
									//'live_checkout' => 'https://www.2checkout.com/checkout/purchase',// not using
									'live'        => 'https://www.2checkout.com/2co/buyer/purchase',
									'unsubscribe' => 'https://www.2checkout.com/api/sales/stop_lineitem_recurring',
									'saledetail'  => 'https://www.2checkout.com/api/sales/detail_sale');	
		
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// list fo available languages
	function _get_languages(){
		// define
		$languages = array(
						   'zh'    => __('Chinese','mgm'),	
						   'da'    => __('Danish','mgm'),	
						   'nl'    => __('Dutch','mgm'),
						   'en'    => __('English (default)','mgm'),
						   'fr'    => __('French','mgm'),
						   'gr'    => __('German','mgm'),						   
						   'el'    => __('Greek','mgm'),
						   'it'    => __('Italian','mgm'),
						   'jp'    => __('Japanese','mgm'),
						   'no'    => __('Norwegian','mgm'),
						   'pt'    => __('Portugese','mgm'),
						   'sl'    => __('Slovenian','mgm'),
						   'es_ib' => __('Spanish (es_ib)','mgm'),
						   'es_la' => __('Spanish (es_la)','mgm'),
						   'sv'    => __('Swedish','mgm'));		
		// return 
		return $languages;
	}
	
	// verify callback 
	function _verify_callback(){			
		// post
		if(isset($_POST['order_number'])){
			$order_number = isset($_POST['order_number']) ? $_POST['order_number'] : $_POST['sale_id'];
			$order_total  = $_POST['total'];
				
			// If demo mode, the order number must be forced to 1
			if($this->status == 'test' || $_POST['demo'] == 'Y')
			{
				$order_number = 1;
			}
	
			// Calculate md5 hash as 2co formula: md5(secret_word + vendor_number + order_number + total)
			$key = strtoupper(md5($this->setting['secret_word'] . $this->setting['sid'] . $order_number . $order_total));
			
			// verify if the key is accurate
			if($_POST['key'] == $key || $_POST['x_MD5_Hash'] == $key)
			{
				// success 
				return true;
			}
		}//allow recurring INS	
		elseif(isset($_POST['message_type']) && in_array($_POST['message_type'], array('RECURRING_INSTALLMENT_SUCCESS', 'RECURRING_INSTALLMENT_FAILED', 'RECURRING_COMPLETE', 'RECURRING_STOPPED'))) {
			return true;
		}	
		// error
		return false;
	}	
	
	// verify ins callback 
	function _verify_callback_ins(){			
		// post
		if(isset($_POST['message_type']) && isset($_POST['sale_id'])){
			$sale_id    = $_POST['sale_id'];
			$invoice_id = $_POST['invoice_id'];		
	
			// Calculate md5 hash as 2co formula: UPPERCASE(MD5_ENCRYPTED(sale_id + vendor_id + invoice_id + Secret Word));
			$key = strtoupper(md5($sale_id . $this->setting['sid'] . $invoice_id . $this->setting['secret_word']));
			
			// verify if the key is accurate
			if($_POST['md5_hash'] == $key)
			{
				// success 
				return true;
			}
		}	
		// error
		return false;
	}	
	
	// process ins messages
	function _process_ins_messages(){
		// sale id
		$sale_id = $_POST['sale_id'];
		// recurring 
		$recurring = (bool)$_POST['recurring'];
		// we have sale id, get transaction details from it
		$transaction = mgm_get_transaction_by_option('2checkout_sale_id', $sale_id);
		// get user id 
		$user_id = $transaction['data']['user_id']; 
		// user data
		$user = get_userdata($user_id);
		// mgm data
		$member = mgm_get_member($user_id);
		// update flag
		$update = false;
		// check each type
		switch($_POST['message_type']){
			case 'ORDER_CREATED':
			// not processed	
			break;
			case 'FRAUD_STATUS_CHANGED':
			case 'INVOICE_STATUS_CHANGED':			
			// check
				if($recurring){		
					// check
					if(isset($_POST['fraud_status']) && $_POST['fraud_status']=='pass'){
						// cancel
						unset($member->status_reset_on,$member->status_reset_as);
						// status
						$member->status        = MGM_STATUS_ACTIVE;
						$member->status_str    = __('Last payment was successful','mgm');	
						$member->last_pay_date = date('Y-m-d');	
						// mark update
						$update = true;
					}
				}
			break;			
			case 'REFUND_ISSUED':
				// check
				if($recurring){							
					// refund amount less than actual order
					if($_POST['item_list_amount_1'] < $transaction['return_data']['total']){
						// cancel on , changed since partial refund will not be cancelled #734
						// $member->status_reset_on = $member->expire_date;
						// $member->status_reset_as = MGM_STATUS_CANCELLED;
					}else{
						// cancel instantly
						$member->status_str  = __('Subscription Cancelled','mgm');					
						$member->expire_date = date('Y-m-d');	
						// set new status
						$member->status = MGM_STATUS_CANCELLED;
					}
					// mark update
					$update = true;
				}	
			break;	
			case 'RECURRING_INSTALLMENT_SUCCESS':
				// check
				if($recurring){	
					// cancel
					unset($member->status_reset_on,$member->status_reset_as);
					// status
					$member->status        = MGM_STATUS_ACTIVE;
					$member->status_str    = __('Last payment was successful','mgm');	
					$member->last_pay_date = date('Y-m-d');	
					// mark update
					$update = true;
				}
			break;	
			case 'RECURRING_INSTALLMENT_FAILED':
				// check
				if($recurring){	
					// cancel on
					$member->status_reset_on = $member->expire_date;
					$member->status_reset_as = MGM_STATUS_CANCELLED;
					// mark update
					$update = true;
				}
			break;	
			case 'RECURRING_STOPPED':// we already have this
				// check
				if($recurring){	
					// if today or set as instant cancel
					if($member->expire_date == date('Y-m-d') || $this->setting['subs_cancel']=='instant'){
						$member->status_str  = __('Subscription Cancelled','mgm');					
						$member->expire_date = date('Y-m-d');	
						// set new status
						$member->status = MGM_STATUS_CANCELLED;
					}else{										
						// reset on
						$member->status_reset_on = $member->expire_date;
						$member->status_reset_as = MGM_STATUS_CANCELLED;
					}
					// mark update
					$update = true;
				}
			break;	
			case 'RECURRING_COMPLETE':
				// not processed
			break;	
			case 'RECURRING_RESTART':
			case 'RECURRING_RESTARTED':
				// check
				if($recurring){	
					// cancel
					unset($member->status_reset_on,$member->status_reset_as);
					// status
					$member->status        = MGM_STATUS_ACTIVE;
					$member->status_str    = __('Last payment was successful','mgm');	
					$member->last_pay_date = date('Y-m-d');	
					// mark update
					$update = true;							
				}
			break;		
		}
		// update
		if($update){
			// save
			$member->save();			
		}		
			
	}
	
	// set 
	function _set_address_fields($user, &$data){
		// mappings
		$mappings = array('first_name'=>'first_name','last_name'=>'last_name','address'=>array('street_address','street_address2'),
		                  'city'=>'city','state'=>'state','zip'=>'zip','country'=>'country','phone'=>'phone');
						 
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter'));				 
	}
	
	// filter
	function _address_fields_filter($name, $value){
		// reuse parent filter unless needed
		switch($name){
			case 'state':
				// trim chars
				$value = substr($value, 0, 64);
			break;
			default:
				 $value = parent::_address_field_filter($name, $value);		
			break;
		}	
		// return 
		return $value;
	}		
	
	/**
	 * fetch sales/transaction details
	 * @param int $sale_id
	 * @return multitype:string NULL |multitype:
	 */
	function getSaleDetails($sale_id) {
		// url
		$saledetail_url = $this->_get_endpoint('saledetail');			
		// url
		$url = add_query_arg(array('sale_id'=>$sale_id), $saledetail_url);
		// auth string
		$auth = $this->setting['apiusername'] . ':' . $this->setting['apipassword'];	
		// post
		$post_data = array();	
		// headers
		$http_headers = array('Accept' => 'application/json', 'Authorization' => 'Basic ' . base64_encode( $auth ));							
		// fetch		
		$http_response = mgm_remote_post($url, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false)); 	
		// log
		// mgm_log($http_response, __FUNCTION__);				
		// post
		// $response = $this->_curl_post($url, NULL, $auth, array("Accept: application/json") );				
		// mgm_log('$response:=' . $response);
		// decode 	
		$response = json_decode($http_response);
		// mgm_log(mgm_array_dump($response, true), '2checkout1');
		
		if(isset($response->sale->invoices) && is_array($response->sale->invoices)) {
			$invoices = $response->sale->invoices;
			$inv_count = count($invoices);
		    if ($inv_count > 0) {
		    	if( 1
					//isset($invoices[$inv_count-1]->lineitems[0]->billing->recurring_status) &&
		    		//$invoices[$inv_count-1]->lineitems[0]->billing->recurring_status == 'active' &&
		    		//isset($invoices[$inv_count-1]->lineitems[0]->billing->date_next) &&
					//strtotime($invoices[$inv_count-1]->lineitems[0]->billing->date_next) >= strtotime(date('Y-m-d'))
		    	) {
		    		return array('status' => 'active', 'expire_date' => $invoices[$inv_count-1]->lineitems[0]->billing->date_next,
		    						'sale_id' => $response->sale->sale_id,
		    						'email' => $response->sale->customer->email_address,
		    						'last_pay' => date('Y-m-d',  strtotime($invoices[$inv_count-1]->date_placed)),
		    						'recurring_status' => $invoices[$inv_count-1]->lineitems[0]->billing->recurring_status,
		    						'status_type' => $invoices[$inv_count-1]->lineitems[0]->billing->status
		    						);
		    	}
		    }			
		}else {
			$mssg = (isset($response->errors->message)) ? $response->errors->message : 'No Invoice details found'; 
			return array('status' => 'error', 'message' => $mssg );
		}
		
		return array();
		//mgm_log($invoices[$inv_count-1]->lineitems[0]->billing->date_next);
		//mgm_log($invoices[$inv_count-1]->lineitems[0]->billing->recurring_status);
	}
	/**
	 * This will update user expirey date/status if any issues
	 * @param $user_id
	 */
	function updateUserDetails($user_id = null) {
		if(is_numeric($user_id))
			$users[] = $user_id;
		else	 
			$users = mgm_get_all_userids();
		//mgm_log(mgm_array_dump($users, true));
		$arr_skip = array('guest', 'free', 'trial');
		foreach ($users as $user_id) {
			$member = mgm_get_member($user_id);
			if(!isset($member->membership_type) || 
			(isset($member->membership_type) && in_array(strtolower($member->membership_type), $arr_skip))
			) {
				continue;
			}
			
			if(strtolower($member->status) == 'cancelled') {
				//mgm_log($user_id, 'not_updated_cacncelled', true);
			}
			
			if(
			//$member->status == 'Inactive' && 
			isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id)) {
				$update = $this->getSaleDetails($member->payment_info->subscr_id);
				$update['user_id'] = $user_id;
				if($update['status'] == 'active') {					
					if(strtotime($update['expire_date']) >= strtotime(date('Y-m-d'))) {
						$update['user_id'] = $user_id;
						//mgm_log(mgm_array_dump($update, true), 'updated-users', true);
						$member->expire_date = $update['expire_date']; 
						$member->status = MGM_STATUS_ACTIVE; 
						$member->status_str    = __('Last payment was successful','mgm');
						$member->last_pay_date = $update['last_pay'];
						$member->save();
					}else {
						//mgm_log(mgm_array_dump($update, true), 'not_updated_past_date', true);
					}					
				}else {
					//mgm_log(mgm_array_dump($update, true), 'not_updated_no_details', true);
				}
			}else {
				//mgm_log($user_id, 'not_updated_no_saleid', true);
			}
		}
	}
	
	// get custom passthrough var from multiple sources
	function _get_alternate_transaction_id(){
		// custom
		$alt_tran_id = '';
		// post
		if(isset($_POST['custom']) && !empty($_POST['custom'])){
			$alt_tran_id = $_POST['custom'];
		}elseif(isset($_GET['custom']) && !empty($_GET['custom'])){
			$alt_tran_id = strip_tags($_GET['custom']);
		}elseif(isset($_POST['vendor_order_id']) && !empty($_POST['vendor_order_id'])){
			$alt_tran_id = $_POST['vendor_order_id'];
		} 		
		// return 
		return $alt_tran_id;
	}
}

// end file