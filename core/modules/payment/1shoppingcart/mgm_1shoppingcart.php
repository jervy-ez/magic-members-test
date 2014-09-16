<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * 1ShoppingCart Payment Module
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 * @todo       return post data from gateway failed, not fully working, need confirmation 
 * @supports   recurring 
 */
class mgm_1shoppingcart extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_1shoppingcart();
	}
	
	// construct
	function mgm_1shoppingcart(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = '1ShoppingCart';
		// logo
		$this->logo = $this->module_url( 'assets/1shoppingcart.jpg' );
		// desc
		$this->description = __('The All-In-One eCommerce & Marketing Solution.','mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');
		// trial support available ?
		$this->supports_trial= 'N';	
		// cancellation support via api available ?
		$this->supports_cancellation= 'N';	
		// do we depend on product mapping	
		$this->requires_product_mapping = 'Y';
		// type of integration
		$this->hosted_payment = 'Y';// gateway hosted, html redirect		
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
							// logo
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
						$enable_state = (isset($_POST['payment']) && bool_from_yn($_POST['payment']['enable'])) ? 'Y' : 'N';
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
				// 1shoppingcart specific :  needs to be changed - as per 1shoppingcart 
				$this->setting['merchant_id']      = $_POST['setting']['merchant_id'];
				$this->setting['merchant_api_key'] = $_POST['setting']['merchant_api_key'];
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
		$product_id = isset($data->product['1shoppingcart_product_id']) ? $data->product['1shoppingcart_product_id'] : ''; 
		// checkout_url
		$checkout_url = isset($data->product['1shoppingcart_checkout_url']) ? $data->product['1shoppingcart_checkout_url'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data->allowed_modules) && in_array($this->code,(array)$data->allowed_modules)){
			$display = 'class="displayblock"';
		}
		// html
		$html='<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '> 
					<div class="row">
						<div class="cell"><div class="postpurhase-heading">'.__('1ShoppingCart Settings','mgm').'</div></div>
					</div>
					<div class="row">
						<div class="cell width125px mgm-padding-tb"><b>'. __('Product ID','mgm') . ':</b></div>
					</div>	
					<div class="row">
						<div class="cell textalignleft">
							<input type="text" name="mgm_post[product][1shoppingcart_product_id]" class="mgm_text_width_payment"  value="'.esc_html($product_id).'"/>
							<div class="tips width95">' . __('Product ID from 1ShoppingCart. Used to verify subscription purchase.','mgm') . '</div>
						</div>
					 </div>
					<div class="row">
						<div class="cell width125px mgm-padding-tb"><b>'. __('Checkout URL','mgm') . ':</b></div>
					</div>	 
					<div class="row">
						<div class="cell textalignleft">
							<input type="text" name="mgm_post[product][1shoppingcart_checkout_url]" class="mgm_text_width_payment"  value="'.esc_html($checkout_url).'" size="30"/>
							<div class="tips width95">' . __('Checkout URL from 1ShoppingCart. Used to redirect to Shopping Cart.','mgm') . '</div>
						</div>
					</div>
		       </div>';	
		// htnl
		/*$html=' <li>
					<label>'.__('1ShoppingCart Product ID','mgm').' <input type="text" class="mgm_text_width_payment" name="mgm_post[product][1shoppingcart_product_id]" value="'. esc_html($product_id) .'" size="40"/></label><br>					
				</li>
				<li>
					<label>'.__('1ShoppingCart Checkout URL','mgm').' <input type="text" class="mgm_text_width_payment" name="mgm_post[product][1shoppingcart_checkout_url]" value="'. esc_html($checkout_url) .'" size="40"/></label>
				</li>';*/
		// return
		return $html;
	}
	
	// hook for post pack purchase setting
	function settings_postpack_purchase($data=NULL){
		// product_id
		$product_id = isset($data->product['1shoppingcart_product_id']) ? $data->product['1shoppingcart_product_id'] : ''; 
		// checkout_url
		$checkout_url = isset($data->product['1shoppingcart_checkout_url']) ? $data->product['1shoppingcart_checkout_url'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data->modules) && in_array($this->code,(array)$data->modules)){
			$display = 'class="displayblock"';
		}
		// overwrite this
		$html = '<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '>
					 <div class="row">
						<div class="cell"><div class="subscription-heading">'.__('1ShoppingCart Settings','mgm').'</div></div>
					 </div>				 
					 <div class="row">
						<div class="cell width125px"><b>'. __('Product ID','mgm').':</b></div>
					 </div>	
					 <div class="row">	
						<div class="cell textalignleft">
							<input type="text" name="product[1shoppingcart_product_id]" value="'.esc_html($product_id).'" size="30"/>
							<div class="tips width95">' . __('Product ID from 1ShoppingCart. Used to verify subscription purchase.','mgm') . '</div>
						</div>
					 </div>
					 <div class="row">
						<div class="cell width125px"><b>' . __('Checkout URL','mgm') . ':</b></div>
					 </div>	
					 <div class="row">						
						<div class="cell textalignleft">
							<input type="text" name="product[1shoppingcart_checkout_url]" value="'.esc_html($checkout_url).'" size="100"/>
							<div class="tips width95">' . __('Checkout URL from 1ShoppingCart. Used to redirect to Shopping Cart.','mgm') . '</div>
						</div>
					 </div>
				 </div>';
		// return
		return $html;
	}
	
	// hook for subscription package setting
	function settings_subscription_package($data=NULL){		
		// product_id
		$product_id = isset($data['pack']['product']['1shoppingcart_product_id']) ? $data['pack']['product']['1shoppingcart_product_id'] : ''; 
		// checkout_url
		$checkout_url = isset($data['pack']['product']['1shoppingcart_checkout_url']) ? $data['pack']['product']['1shoppingcart_checkout_url'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data['pack']['modules']) && in_array($this->code,(array)$data['pack']['modules'])){
			$display = 'class="displayblock"';
		}
		// html
		$html='<div id="settings_subscription_package_' . $this->module. '" ' . $display . '> 
				<div class="row">
					<div class="cell"><div class="subscription-heading">'.__('1ShoppingCart Settings','mgm').'</div></div>
				</div>
				<div class="row">
					<div class="cell">
						<div class="marginleft10px">	
							<p class="fontweightbold">' . __('Product ID','mgm') . '</p>
							<input type="text" name="packs['.($data['pack_ctr']-1).'][product][1shoppingcart_product_id]" value="'.esc_html($product_id).'"/>
							<div class="tips width95">' . __('Product ID from 1ShoppingCart. Used to verify subscription purchase.','mgm') . '</div>
						</div>
					</div>
				 </div>
				 <div class="row">
					<div class="cell">
						<div class="marginleft10px">	
							<p class="fontweightbold">' . __('Checkout URL','mgm') . '</p>
							<input type="text" name="packs['.($data['pack_ctr']-1).'][product][1shoppingcart_checkout_url]" value="'.esc_html($checkout_url).'" size="65"/>
							<div class="tips width95">' . __('Checkout URL from 1ShoppingCart. Used to redirect to Shopping Cart.','mgm') . '</div>
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
		$product_id = isset($data['1shoppingcart_product_id']) ? $data['1shoppingcart_product_id'] : ''; 
		// checkout_url
		$checkout_url = isset($data['1shoppingcart_checkout_url']) ? $data['1shoppingcart_checkout_url'] : ''; 
		// overwrite this
		$html = '<div class="row">
					<div class="cell"><div class="subscription-heading">' . __('1ShoppingCart Settings','mgm') . '</div></div>
			    </div>
			    <div class="row">
					<div class="cell width125px"><b>'. __('Product ID','mgm') . ':</b></div>
			    </div>	
			    <div class="row">	
					<div class="cell textalignleft">
						<input type="text" name="product[1shoppingcart_product_id]" value="' . esc_html($product_id) . '" />
					</div>
			    </div>
			    <div class="row">
					<div class="cell width125px"><b>' . __('Checkout URL','mgm') . ':</b></div>
			    </div>	
			    <div class="row">	
					<div class="cell textalignleft">
						<input type="text" name="product[1shoppingcart_checkout_url]" value="' . esc_html($checkout_url) . '" size="100" />
					</div>
			    </div>';
		// return
		return $html;
	}
	
	// return process api hook, link back to site after payment is made
	function process_return(){	
		// read input stream
		$this->_inputstream_post();		
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );		
		// check and show message			
		if((isset($_POST['order_id']) && !empty($_POST['order_id'])) || isset($_GET['status'])){			
			// tran
			$_POST['custom'] = '';
			// check
			if($user_tran_id = mgm_cookie_var('CK_USER_LAST_TRANSACTION_ID')){
				// set tran
				$_POST['custom'] = $user_tran_id;
				// delete it
				mgm_delete_cookie_var('CK_USER_LAST_TRANSACTION_ID'); 
			}			
			// redirect as success if not already redirected			
			$query_arg = array('status'=>'success');
			// set 
			if(!empty($query_arg)){
				$query_arg = array_merge($query_arg, array('trans_ref' => mgm_encode_id($_POST['custom']))); 
			}
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
			mgm_redirect(add_query_arg(array('status'=>'error','errors'=>urlencode('1ShoppingCart Order Reference Error')), $this->_get_thankyou_url()));
		}
	}
	
	// notify process api hook, background IPN url 
	function process_notify(){	
		// read input stream
		$this->_inputstream_post();	
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );				
		// verify
		if ($this->_verify_callback()) {						
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
					// cancellation : change here if required
					if(isset($_POST['status']) && $_POST['status']=='canceled' ) {// invalid					
						$this->_cancel_membership(); //run the code to process a membership cancellation
					}else{
						$this->_buy_membership(); //run the code to process a new/extended membership
					}					
				break;										
			}
			// after process		
			do_action('mgm_notify_post_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));
		}
		// after process unverified		
		do_action('mgm_notify_post_process_unverified_'.$this->module);	
		
		// 200 OK to 1shoppingcart
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
		// get user id
		$user_id = $_POST['user_id'];
		// get user
		$user = get_userdata($user_id); 		
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
		$button_code = $this->_get_button_data($tran['data'],$tran_id);
			
		// the html
		$html='<form action="' . $button_code['checkout_url'] . '" method="POST" class="mgm_form" name="' . $this->code . '_redirect_form" id="' . $this->code . '_redirect_form">								
					<img src="'.MGM_ASSETS_URL.'images/ajax/ajax-loader.gif"/><br>
					<b>'.sprintf(__('Please wait, you are being redirected to %s...','mgm'), $this->name).'</b>												
			  </form>				
			  <script language="javascript">document.' . $this->code . '_redirect_form.submit();</script>';
		// return 	  
		return $html;					
	}	
	
	// subscribe button api hook
	function get_button_subscribe($options=array()){	
		// 1shoppingcart depends on product id, check for it before generating button
		if(!isset($options['pack']['product']['1shoppingcart_product_id']) || empty($options['pack']['product']['1shoppingcart_product_id'])){
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in 1ShoppingCart settings : No Product ID set.','mgm').'</b>
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
		// 1shoppingcart depends on product id, check for it before generating button
		if(!isset($options['pack']['product']['1shoppingcart_product_id']) || empty($options['pack']['product']['1shoppingcart_product_id'])){
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in 1ShoppingCart settings : No Product ID set.','mgm').'</b>
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

	// get button data: _get_endpoint
	function _get_button_data($pack, $tran_id=NULL) {
		// user
		$user_id = $pack['user_id'];
		
		// add temp user meta
		update_user_option($user_id, 'mgm_1shoppingcart_tran_id', $tran_id, true);	
		// add cookie for return tracking
		mgm_set_cookie_var('CK_USER_LAST_TRANSACTION_ID', $tran_id, '3 DAY');
		
		// set data 
		$data = array(
			'checkout_url' => $pack['product']['1shoppingcart_checkout_url']
		);	
		
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

		// status
		switch ($_POST['status']) {
			case "Approved" :
			case "Accepted" :	
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';	
				
				// transation id
				$transaction_id = $this->_get_transaction_id();// custom from POST
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

			case "Declined" :	
				// status
				$status_str = __('Last payment was refunded or denied','mgm');
				// purchase status
				$purchase_status = 'Failure';
													  
				// error
				$errors[] = $status_str;
			break;

			case "Pending" :
				// status
				$status_str = __('Last payment is pending. Reason: Unknown','mgm');
				// purchase status
				$purchase_status = 'Pending';	

				// error
				$errors[] = $status_str;
			break;

			default:
				// status
				$status_str = sprintf(__('Last payment status: %s','mgm'),$ctransaction);
				// purchase status
				$purchase_status = 'Unknown';	
																										  
				// error
				$errors[] = $status_str;
		}

		// do action
		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new, individual
		do_action('mgm_return_post_purchase_payment', array('post_id' => $post_id));// new, global 
			
		// update
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

		// notify user, only if gateway emails on	
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
		$system_obj  = mgm_get_class('system');		
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
		// verified
		if (!$membership_type_verified) {
			// check
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
		
		// verify product for another purchase
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
			// check pack 
			if(isset($subs_pack['product']['1shoppingcart_product_id']) && !empty($subs_pack['product']['1shoppingcart_product_id'])){
				// pack
				$pack_product_id = $subs_pack['product']['1shoppingcart_product_id'];
				// match
				if($post_product_id = mgm_post_var('product_id')){
					// match
					if(trim($post_product_id) != trim($pack_product_id)){
						// log
						mgm_log(sprintf('Product mismatch: pack[%s] => post[%s]', $pack_product_id, $post_product_id), ($_POST['custom'] . '_' .__FUNCTION__));
						// exit
						exit;
					}
				}
			}
		}
		
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
		$member->duration          = $duration;
		$member->duration_type     = strtolower($duration_type);
		$member->amount            = $amount;
		$member->currency          = $currency;
		$member->membership_type   = $membership_type;
		$member->pack_id           = $pack_id;		
		$member->payment_type      = 'subscription';		
		$member->transaction_id    = $_POST['custom'];
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 		
				
		// tracking fields module_field => post_field, will be used to unsubscribe
		$tracking_fields = array('txn_type'=>'status', 'subscr_id'=>'client_id', 'txn_id'=> (isset($_POST['order_id']) ? 'order_id' : 'transaction_id'));
		// save tracking fields 
		$this->_save_tracking_fields($tracking_fields, $member);
		
		// process response
		$new_status = $update_role = false;
		// errors
		$errors = array();		
		// by status
		switch ($_POST['status']) {
			case "Approved" :
			case "Accepted" :	
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
					// if lifetime
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
				// role
				if ($role) $update_role = true;					
				
				//cancel previous subscription:
				//issue#: 565				
				$this->cancel_recurring_subscription($_POST['custom'], null, null, $pack_id);
				
				// transaction_id
				$transaction_id = $this->_get_transaction_id();
				// hook args
				$args = array('user_id'=>$user_id, 'transaction_id'=>$transaction_id);
				// after succesful payment hook
				do_action('mgm_membership_transaction_success', $args);// backward compatibility				
				do_action('mgm_subscription_purchase_payment_success', $args);// new organized name
				
			break;
			case "Declined" :			
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				// error
				$errors[] = $member->status_str;
			break;

			case "Pending" :
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
		$acknowledge_user = $this->send_payment_email($_POST['custom']);
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
			// update
			$member->save();		
		}	
		
		// status change event
		do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);		
		
		//update coupon usage
		do_action('mgm_update_coupon_usage', array('user_id' => $user_id));

		// update role		
		if ($update_role) {			
			// role;			
			$obj_role = new mgm_roles();	
			// set			
			$obj_role->add_user_role($user_id, $role);	
		}
		
		// return action
		do_action('mgm_return_'.$this->module, array('user_id' => $user_id));// backward compatibility
		do_action('mgm_return_subscription_payment_'.$this->module, array('user_id' => $user_id));// new , individual	
		do_action('mgm_return_subscription_payment', array('user_id' => $user_id, 'acknowledge_ar' => $acknowledge_ar, 'mgm_member' => $member));// new, global: pass mgm_member object to consider multiple level purchases as well.		

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
	function _cancel_membership($user_id = null, $redirect = false){
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($_POST['Custom']);
		// local var
		extract($custom);
		
		// find user
		$user = get_userdata($user_id);
		$member = mgm_get_member($user_id);
		
		// multiple membesrhip level update:		
		$multiple_update = false;
		// check
		if((isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']) || (isset($is_another_membership_purchase) && $is_another_membership_purchase == 'Y' )){
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
		
		// reset payment info
		if(isset($_POST['status'])){
			$member->payment_info->txn_type = $_POST['status'];
		}			
		if(isset($_POST['order_id'])){	
			$member->payment_info->txn_id = $_POST['order_id'];	
		}	
		
		// types
		$duration_exprs = $s_pcaks->get_duration_exprs();	
						
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
			$new_status_str = sprintf(__('Subscription awaiting cancellation on %s','mgm'),date($date_format, strtotime($expire_date)));		
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
					// mgm_log('RECALLing '. $member->payment_info->module .': cancel_recurring_subscription FROM: ' . $this->code);
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;				
			}else 
				return false;
		}	
		
		//send email only if setting enabled
		if((is_null($subscr_id) || $subscr_id === 0)) {
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
		}
		// return
		return true;
	}	

	// default setting
	function _default_setting(){
		// 1shoppingcart specific
		$this->setting['merchant_id']      = '';
		$this->setting['merchant_api_key'] = '';
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
			if(isset($_POST['status'])){
				$option_name = $this->module.'_'.strtolower($_POST['status']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_POST)));
			
			// options 
			$options = array('status','order_id');
			// loop
			foreach($options as $option){
				// check
				if(isset($_POST[$option])){
					// add
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
	
	// setup endpoints
	function _setup_endpoints($end_points = array()){
		// define defaults
		$end_points_default = array('test'            => false,
									'live'            => false,
									'api_read_order'  => 'https://www.mcssl.com/API/[merchant_id]/Orders/[order_id]/READ',
									'api_read_client' => 'https://www.mcssl.com/API/[merchant_id]/Clients/[client_id]/READ');
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// verify callback 
	function _verify_callback(){			
		// return init
		$return = false;
		// check post vars
		$post_vars = array('order_id','status','token');
		// loop
		foreach($post_vars as $post_var){
			// check
			if( isset($_POST[$post_var]) && !empty($_POST[$post_var]) ){
				$return = true; break;
			}
		}		
		// return
		return $return;	
	}	
	
	// set 
	function _set_address_fields($user, &$data){
		// mappings
		$mappings= array('first_name'=>'first_name','last_name'=>'last_name','address'=>'address1',
		                 'city'=>'city','state'=>'state','zip'=>'zip','country'=>'country');
						 
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
	
	// read order with id/token
	function read_order($order_id){		
		// endpoint
		$endpoint = str_replace(array('[merchant_id]','[order_id]'),array($this->setting['merchant_id'],$order_id),$this->_get_endpoint('api_read_order'));	
		
		// content
		$post_data = '<Request>
						<Key>'.$this->setting['merchant_api_key'].'</Key>						
					  </Request>';
		
		// headers
		$http_headers = array('Content-Type' => 'text/xml');
							
		// fetch		
		$http_response = mgm_remote_post($endpoint, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false)); 
		// $this->_curl_post($endpoint, $content, array("Content-Type: text/xml"));	
		
		// simple xml
		$xml = @simplexml_load_string($http_response);
		
		// check
		if($xml){			
			// success
			if($this->is_api_success($xml)){	
				// set to post var	
				$_POST['order_id']       = (int)$xml->OrderInfo->Id;
				$_POST['status']         = (string)$xml->OrderInfo->OrderStatusType;
				$_POST['client_id']      = (int)$xml->OrderInfo->ClientId;				
				$_POST['transaction_id'] = (int)$xml->OrderInfo->TransactionId;			
				// read email
				if(!isset($_POST['email'])){
					$_POST['email']      = $this->read_client($_POST['client_id']);
				}
				// product
				$_POST['product_id']     = (int)$xml->OrderInfo->LineItems->LineItemInfo->ProductId;
			}else{
				$_POST['status']         = 'Error';				
			}			
		}
	}
	
	// read client and return email
	function read_client($client_id){	
		// email
		$email = '';	
		// endpoint
		$endpoint = str_replace(array('[merchant_id]','[client_id]'),array($this->setting['merchant_id'],$client_id),$this->_get_endpoint('api_read_client'));	
		
		// content
		$post_data = '<Request>
						<Key>'.$this->setting['merchant_api_key'].'</Key>						
					  </Request>';
							
		// headers
		$http_headers = array('Content-Type' => 'text/xml');
							
		// fetch		
		$http_response = mgm_remote_post($endpoint, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false)); 	
		// $buffer = $this->_curl_post($endpoint, $content, array("Content-Type: text/xml") );	
		
		// simple xml
		$xml = @simplexml_load_string($http_response);		
		
		// check
		if($xml){			
			// success
			if($this->is_api_success($xml)){				
				$email = (string)$xml->ClientInfo->Email;				
			}		
		}
		
		// return 
		return $email;
	}
	
	// CRON service: not implemented
	function read_orders(){
		// endpoint
		$endpoint = sprintf('https://www.mcssl.com/API/%d/Orders/LIST',$this->setting['merchant_id']);
		
		// start date
		$start_dt = date('m/d/Y H:i:s', strtotime('-1 DAY'));
		
		// content
		$post_data = '<Request>
						<Key>'.$this->setting['merchant_api_key'].'</Key>
						<LimitCount>10</LimitCount>
						<LimitOffset>0</LimitOffset>
						<LimitStartDate>'.$start_dt.'</LimitStartDate>
						<LimitEndDate>'.date('m/d/Y H:i:s').'</LimitEndDate>						
						<SortOrder>DESC</SortOrder>
					  </Request>';				
		
		// headers
		$http_headers = array('Content-Type' => 'text/xml');
							
		// fetch		
		$http_response = mgm_remote_post($endpoint, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false)); 	
		// $buffer = $this->_curl_post($endpoint, $content, array("Content-Type: text/xml") );	
		
		// simple xml
		$xml = @simplexml_load_string($http_response);
		
		// check
		if($xml){			
			// success		
			if($this->is_api_success($xml)){	
				$orders = array();
			}			
		}		
	}
	
	// check api status
	function is_api_success($xml){
		// init
		$success = false;
		// loop
		foreach($xml->attributes() as $k=>$v){
			// check
			if($k == 'success'){
				// true
				if($v == 'true'){
					// set
					$success = true;
				}
			}
		}
		// return 
		return $success;
	}
	
	
	
	// oneshop input stream 2 post
	function _inputstream_post(){
		// access raw HTTP POST data
		$post_body = file_get_contents('php://input');
		
		// log
		mgm_log($post_body, __FUNCTION__);
		
		// load
		if($xml = @simplexml_load_string($post_body)){
			// log
			mgm_log($xml, __FUNCTION__);
			
			// token
			if(isset($xml->Token)){
				// set
				$_POST['token'] = (string)$xml->Token;
				
				// read order token
				$this->read_order($_POST['token']);				
				
				// get user by email
				if(isset($_POST['email'])){
					// fetch
					$user = get_user_by('email',$_POST['email']);		
					// log
					// mgm_log($user, __FUNCTION__);
					// tran id/ custom	
					if($user->ID > 0){
						// custom
						$_POST['custom'] = get_user_option('mgm_1shoppingcart_tran_id', $user->ID);
		
						// remove temp meta	
						if(isset($_POST['custom']) && !empty($_POST['custom'])){
							delete_user_option($user->ID, 'mgm_1shoppingcart_tran_id', true);
						}
					}	
				}	
			}
		}
	}
}

// end file