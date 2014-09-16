<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * Zombaio Payment Module
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 * @updated    2013-03-14
 */
class mgm_zombaio extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_zombaio();
	}
	
	// construct
	function mgm_zombaio(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Zombaio';
		// logo
		$this->logo = $this->module_url( 'assets/zombaio.gif' );
		// desc
		$this->description = __('Zombaio is the only IPSP totally customized for the adult entertainment industry. Zombaio\'s technology '.
		                        'enables the webmaster to accept card payment within hours. No startup fees, daily payouts and low '.
		                        'transaction rates','mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription');
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
				// zombaio specific
				$this->setting['merchant_id']   = $_POST['setting']['merchant_id'];
				$this->setting['site_id']       = $_POST['setting']['site_id'];
				$this->setting['gw_pass']       = $_POST['setting']['gw_pass'];
				$this->setting['send_userpass'] = $_POST['setting']['send_userpass'];	
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
				// fix old data
				$this->supported_buttons = array('subscription');		
				// save
				$this->save();
				// create script
				$this->_check_zombaio_proxy(true);
				// message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));
			break;
		}		
	}		
		
	// hook for post purchase setting
	function settings_post_purchase($data=NULL){
		// not supported
		// if(!$this->is_button_supported('buypost')) return '';
		
		// price_id
		$price_id = isset($data->product['zombaio_price_id']) ? $data->product['zombaio_price_id'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data->allowed_modules) && in_array($this->code,(array)$data->allowed_modules)){
			$display = 'class="displayblock"';
		}
		// html
		$html = '<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '>
					 <div class="row">
						<div class="cell"><div class="postpurhase-heading">'.__('Zombaio Settings','mgm').'</div></div>
					 </div>
					 <div class="row">
						<div class="cell width125px mgm-padding-tb"><b>' . __('Price ID','mgm') . ':</b></div>
					 </div>
					 <div class="row">
						<div class="cell textalignleft">							
							<input type="text" name="mgm_post[product][zombaio_price_id]" class="mgm_text_width_payment" value="'.esc_html($price_id).'" />								
						</div>
					 </div>
				 </div>';
		/*// html
		$html=' <li>
					<label>'.__('Zombaio Price ID','mgm').' <input type="text" class="mgm_text_width_payment" name="mgm_post[product][zombaio_price_id]" value="'. esc_html($price_id) .'" /></label>
				</li>';*/
		// return
		return $html;
	}
	
	// hook for post pack purchase setting
	function settings_postpack_purchase($data=NULL){
		// not supported
		// if(!$this->is_button_supported('buypost')) return '';
		
		// price_id
		$price_id = isset($data->product['zombaio_price_id']) ? $data->product['zombaio_price_id'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data->modules) && in_array($this->code,(array)$data->modules)){
			$display = 'class="displayblock"';
		}
		// overwrite this
		$html = '<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '>
					 <div class="row">
						<div class="cell"><div class="subscription-heading">'.__('Zombaio Settings','mgm').'</div></div>
				     </div>
				     <div class="row">
						<div class="cell width125px"><b>'. __('Price ID','mgm') . ':</b></div>
					 </div>	
					 <div class="row">	
						<div class="cell textalignleft">
							<input type="text" name="product[zombaio_price_id]" value="'.esc_html($price_id).'" />
						</div>
				     </div>
			     </div>';
		// return
		return $html;
	}		
	
	// hook for subscription package setting
	function settings_subscription_package($data=NULL){
		// price_id
		$price_id = isset($data['pack']['product']['zombaio_price_id']) ? $data['pack']['product']['zombaio_price_id'] : '';
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data['pack']['modules']) && in_array($this->code,(array)$data['pack']['modules'])){
			$display = 'class="displayblock"';
		}
		// html
		$html = '<div id="settings_subscription_package_' . $this->module. '" ' . $display . '>
					 <div class="row">
						<div class="cell"><div class="subscription-heading">'.__('Zombaio Settings','mgm').'</div></div>
					 </div>
					 <div class="row">
						<div class="cell">
							<div class="marginleft10px">	
								<p class="fontweightbold">' . __('Price ID','mgm') . '</p>
								<input type="text" name="packs['.($data['pack_ctr']-1).'][product][zombaio_price_id]" value="'.esc_html($price_id).'" />
								<div class="tips width95">' . __('Zombaio Price ID','mgm') . '</div>
							</div>
						</div>
					 </div>
				 </div>';
		// return
		return $html;
	}
	
	// hook for coupon setting
	function settings_coupon($data=NULL){
		// price_id
		$price_id = isset($data['zombaio_price_id']) ? $data['zombaio_price_id'] : ''; 
		// overwrite this
		$html = '<div class="row">
					<div class="cell"><div class="subscription-heading">' . __('Zombaio Settings','mgm') . '</div></div>
			     </div>
			     <div class="row">
					<div class="cell width125px"><b>'. __('Price ID','mgm') . ':</b></div>	
				 </div>	
			     <div class="row">					
					<div class="cell textalignleft">
						<input type="text" name="product[zombaio_price_id]" value="' . esc_html($price_id) . '" />
					</div>
			     </div>';
		// return
		return $html;
	}
	
	// return process api hook, link back to site after payment is made
	function process_return(){	
		//record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		// check and show message
		if((isset($_REQUEST['status']) && $_REQUEST['status']=='success') && (isset($_REQUEST['extra']) && !empty($_REQUEST['extra']))){						
			// redirect as success if not already redirected
			$query_arg = array('status'=>'success',  'trans_ref' => mgm_encode_id($_REQUEST['extra']));
			// is a post redirect?
			$post_redirect = $this->_get_post_redirect($_REQUEST['extra']);
			// set post redirect
			if($post_redirect !== false){
				$query_arg['post_redirect'] = $post_redirect;
			}	
			// is a register redirect?
			$register_redirect = $this->_auto_login($_REQUEST['extra']);
			// set register redirect
			if($register_redirect !== false){
				$query_arg['register_redirect'] = $register_redirect;
			}			
			// redirect with success
			mgm_redirect(add_query_arg($query_arg, $this->_get_thankyou_url()));	
		}else{			
			// redirect with error
			mgm_redirect(add_query_arg(array('status'=>'error','errors'=>urlencode('zombaio receipt error')), $this->_get_thankyou_url()));
		}
	}
	
	// notify process api hook, background IPN url 
	function process_notify(){
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		// used for this module	
		if($this->_verify_callback()) {					
			// zombaio action
			$zombaio_action = strtolower($_REQUEST['Action']);		
			// populate transaction
			if(!isset($_REQUEST['extra'])){
				// recompose
				$this->_populate_transaction();
				// log
				// mgm_log($_REQUEST, ($this->module . '_' . __FUNCTION__ . '_populate_transaction'));
			}	
			// log data before validate
			$tran_id = $this->_log_transaction();		
			// payment type
			$payment_type = $this->_get_payment_type($_REQUEST['extra']);
			// custom
			$custom = $this->_get_transaction_passthrough($_REQUEST['extra'], false);// do not verify as user.delete do not have custom param
			// hook for pre process
			do_action('mgm_notify_pre_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));
			// log
			// mgm_log($payment_type . ' ' . $zombaio_action, ($this->module . '_' . __FUNCTION__));
			// check
			switch($payment_type){
				// buypost 
				case 'post_purchase':
				case 'buypost':
					$this->_buy_post(); //run the code to process a purchased post/page
				break;
				// subscription	
				case 'subscription':
					// actions: user.add, user.delete, rebill, chargeback
					switch($zombaio_action){
						case 'user.add':	
							$this->_buy_membership(); // run the code to process a new/extended membership
						break;	
						case 'user.delete':	
						case 'chargeback':	
							$this->_cancel_membership(); // run the code to process a membership cancellation
						break;
						case 'rebill':
							$this->process_status_notify();	// run the code to process a membership rebill	
						break;	
					}														
				break;	
				// other		
				default:				
					// all other notifications
					$this->process_status_notify();													
				break;							
			}		
			
			// after process		
			do_action('mgm_notify_post_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));
		}
		// after process unverified		
		do_action('mgm_notify_post_process_unverified_'.$this->module);	
		
		// 200 OK to zombaio, this is IMPORTANT, otherwise Gateway will keep on sending IPN .........	
		if( ! headers_sent() ){
			@header('HTTP/1.1 200 OK');		
			exit('OK|OK');
		}		
	}
	
	// status notify process api hook, background INS url, 
	// can not use for paypal standard as notify url once set via payment form, being used always
	function process_status_notify(){
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );		
		
		// zombaio action
		$zombaio_action = strtolower($_REQUEST['Action']);		
			
		// custom
		if(isset($_REQUEST['extra'])){
			$custom = $this->_get_transaction_passthrough($_REQUEST['extra'], false);
			extract($custom);
		}
		
		// log
		// mgm_log($custom, ($this->module . '_' . __FUNCTION__));	
		
		// process rebill,treat invoked by gateway
		if( $zombaio_action == 'rebill' && isset($user_id) && (int)$user_id > 0 ){
			$this->process_rebill_status($user_id); 
		}
		
		// 200 OK to gateway, only external		
		if(!headers_sent()){
			@header('HTTP/1.1 200 OK');
			exit('OK|OK');
		}
	}
	
	// rebill status check
	function process_rebill_status($user_id, $member=NULL){
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		
		// member 
		if(!$member) $member = mgm_get_member($user_id);
		
		// set
		if(isset($_REQUEST['Success']) && $_REQUEST['Success'] == 1){
			// old status
			$old_status = $member->status;	
			// set new status
			$member->status = $new_status = MGM_STATUS_ACTIVE;
			// status string
			$member->status_str = __('Last payment cycle processed successfully','mgm');	
			// last pay date
			$member->last_pay_date = date('Y-m-d');	
			// expire date
			if(isset($member->last_pay_date) && !empty($member->expire_date)){							
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
			
			// action
			if( isset($new_status) && $new_status != $old_status){
				// user status change
				do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);
				// rebill status change
				do_action('mgm_rebill_status_change', $user_id, $new_status, $old_status, 'notify');// query or notify	
			}
		}
		// log
		// mgm_log($member, ($this->module . '_' . __FUNCTION__));	
		// return
		return false;
	}
	
	// process cancel api hook 
	function process_cancel(){
		// redirect to cancel page
		mgm_redirect(add_query_arg(array('status'=>'cancel'), $this->_get_thankyou_url()));
	}
	
	// unsubscribe process, IPN for unsubscribe 
	function process_unsubscribe(){
		// overwrite this
		// get user id
		$user_id = $_POST['user_id'];
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// get user
		$user = get_userdata($user_id); 		
		$member = mgm_get_member($user_id);
		// multiple membesrhip level update:
		if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type'])
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']); 	
		
		// init
		$cancel_account = true;
		//if recurring
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
			
			// cancel at zombaio
			$cancel_account = $this->cancel_recurring_subscription(null, $user_id, $subscr_id);
		}
		
		// cancel in MGM
		if($cancel_account === true){
			$this->_cancel_membership($user_id, true);// redirected
		}

		// message
		$message = is_string($cancel_account) ? $cancel_account :  __('Error while cancelling subscription', 'mgm') ;			
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
		$html='<form action="'. $this->_get_endpoint($tran['data']) .'" method="post" class="mgm_form" name="' . $this->code . '_redirect_form" id="' . $this->code . '_redirect_form">
					'. $button_code .'					
					'. $additional_code .'						
					<img src="'.MGM_ASSETS_URL.'images/ajax/ajax-loader.gif"/><br>
					<b>'.sprintf(__('Please wait, you are being redirected to %s...','mgm'), $this->name).'</b>				
			  </form>				
			  <script language="javascript">document.' . $this->code . '_redirect_form.submit();</script>';
		// log
		// mgm_log('ZOMBAIO REDIRECT FORM : '.$html, 'zombaio-redirectform-'.date('dmY-His'));
		// return 	  
		return $html;					
	}	
		
	// subscribe button api hook
	function get_button_subscribe($options=array()){			
		// cb depends on product id, check for it before generating button
		if(!isset($options['pack']['product']['zombaio_price_id']) || intval($options['pack']['product']['zombaio_price_id'])==0){
			// return
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in Zombaio settings : No Price ID set.','mgm').'</b>
					</div>';			
		}	
		
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
				   <input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
				   <input type="image" class="mgm_paymod_logo" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">
				   <div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
			   </form>';
		// return 	   
		return $html;
	}
	
	// unsubscribe button hook
	function get_button_unsubscribe($options=array()){
		// action
		$action = add_query_arg(array('module'=>$this->code,'method'=>'payment_unsubscribe'), mgm_home_url('payments'));
		// message
		$message = sprintf(__('You have subscribed to <b>%s</b> via <b>%s</b>, if you wish to unsubscribe, please click the following link.','mgm'),get_option('blogname'), $this->name);
		// empty
		$html=' <div class="mgm_margin_bottom_10px">
					<h4>'. __('Unsubscribe','mgm').'</h4>
					<div class="mgm_margin_bottom_10px">' . $message . '</div>
				</div>	
				<form name="mgm_unsubscribe_form" id="mgm_unsubscribe_form" method="post" action="' . $action . ' ">
					<input type="hidden" name="user_id" value="' . $options['user_id'] . '"/>
					<input type="hidden" name="membership_type" value="' . $options['membership_type'] . '"/>
					<input type="button" name="btn_unsubscribe" value="' .__('Unsubscribe','mgm') . '" onclick="confirm_unsubscribe(this)" class="button" />
				</form>';
		// return
		return $html;		
	}
	
	/*****************************************NOT SUPPORTED********************************************
	// buypost button api hook
	function get_button_buypost($options=array(), $return = false) {
		// cb depends on product id, check for it before generating button
		if(!isset($options['pack']['product']['zombaio_price_id']) || intval($options['pack']['product']['zombaio_price_id'])==0){
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in Zombaio settings : No Price ID set.','mgm').'</b>
					</div>';
			exit;
		}	
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					<input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
					<input type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">
					<div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
			   </form>';				
		// return or print
		if ($return) {
			return $html;
		} else {
			echo $html;
		}
	}
	********************************************************************/
	
	// MODULE INFO API CALLBACKS ------------------------------------------------------------------- 
	
	// get module transaction info
	function get_transaction_info($member, $date_format){				
		// data
		$subscription_id = $member->payment_info->subscr_id;
		$transaction_id  = $member->payment_info->txn_id;		
		// info
		$info = sprintf('<b>%s:</b><br>%s: %s<br>%s: %s', __('ZOMBAIO INFO','mgm'), __('SUBSCRIPTION ID','mgm'), $subscription_id, 
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
		$html = sprintf('<p>%s: <input type="text" size="20" name="zombaio[subscriber_id]"/></p>
				 		 <p>%s: <input type="text" size="20" name="zombaio[transaction_id]"/></p>', 
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
		$data = $post_data['zombaio'];
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
			$return .= ' <input type="hidden" name="'. $key .'" value="'. esc_html($value) .'" />' . "\n";
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
		$member = mgm_get_member($user_id);
		
		// return url
		$return_url = add_query_arg(array('extra'=>$tran_id),$this->setting['return_url']);
		
		// set data
		$data = array(
			'processor_id' => $tran_id,		
			'Email'        => $user->user_email
		);
		
		// address fields
		$this->_set_address_fields($user, $data);
		
		// subscription purchase with ongoing/limited
		if( !isset($pack['buypost']) && isset($pack['duration_type']) && $pack['num_cycles'] != 1 ){ // does not support one-time recurring
		// if ($pack['num_cycles'] != 1 && $pack['duration_type']) { // old style
			// username/password	
			if($this->setting['send_userpass']=='yes'){			
				$data['Username'] = $user->user_login;			
				$data['Password'] = mgm_decrypt_password($member->user_password, $user_id); 
			}
		}
		
		// urls
		$data['return_url']	        = $return_url;
		$data['return_url_approve'] = add_query_arg(array('status'=>'success'), $return_url);
		$data['return_url_decline'] = add_query_arg(array('status'=>'error'), $return_url);
		// error url
		$data['return_url_error']   = add_query_arg(array('status'=>'error'), $return_url);
		
		// passthrough		
		$data['extra'] = $tran_id; 
		
		// add filter @todo test
		$data = apply_filters('mgm_payment_button_data', $data, $tran_id, $this->module, $pack);
		
		// update pack/transaction
		mgm_update_transaction(array('data'=>json_encode($pack),'module'=>$this->module), $tran_id);
		
		// data		
		return $data;
	}	
	
	/**********************************NOT SUPPORTED*****************************************
	// buy post
	function _buy_post() {
		global $wpdb;
		// get system settings
		$system_obj = mgm_get_class('system');
		$dge  = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
				
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($_REQUEST['extra']);
		// local var
		extract($custom);	
		
		// find user
		$user     = get_userdata($user_id);
		$blogname = get_option('blogname');
		$tran_success = false;
		
		// errors
		$errors = array();
		// set test status		
		$ctransaction=($this->status=='test') ? preg_replace('/^TEST_/', '', $_POST['ctransaction']) : $_POST['ctransaction'];
		
		// check 
		switch (trim($ctransaction)) {
			case "user.add" :
			case "sale" :
				$tran_success = true;
				$status_str = __('Last payment was successful','mgm');
				// $subject = sprintf(__('[%s] Payment receipt','mgm'),$blogname);
				// $message = __('This is an automatic notification from %1$s to %2$s (%3$s).<br />This is a notification to inform you that your payment was successful and the post you purchased is now available to view.<br />For more information please contact %4$s','mgm');
				// $message = sprintf($message, $blogname, $user->display_name, $user->user_email, $system_obj->setting['admin_email']);
				// issue#: 280 (2011 Feb 25)
				// subject
				$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
				// body
				$message = $system_obj->get_template('payment_success_email_template_body', 
										array('blogname'=>$blogname, 'name'=>$user->display_name, 
											  'email'=>$user->user_email, 
											  'admin_email'=>$system_obj->setting['admin_email']), true);
				// after succesful payment hook
				do_action('mgm_buy_post_transaction_success', array('post_id' => $post_id));// backward compatibility
				do_action('mgm_post_purchase_payment_success', array('post_id' => $post_id));// new organized name
				break;

			case "chargeback" :
			case "refund" :
				$status_str = __('Last payment was refunded or denied','mgm');
				// $subject = sprintf(__('[%s] Payment Failed','mgm'), $blogname);
				// $message = __('This is an automatic notification from %1$s to %2$s (%3$s).<br />This is a notification to inform you that your post purchase failed for the following reason: %4$s.<br />For more information please contact %5$s.','mgm');
				// $message = sprintf($message, $blogname, $user->display_name, $user->user_email, $status_str, $system_obj->setting['admin_email']);
				// issue#: 280 (2011 Feb 25)
				// subject
				$subject = $system_obj->get_template('payment_failed_email_template_subject', array('blogname'=>$blogname), true);
				 
				// body			
				$message = $system_obj->get_template('payment_failed_email_template_body', 
										array('blogname'=>$blogname, 'name'=>$user->display_name, 
											  'email'=>$user->user_email, 'payment_type'=>'post purchase payment','reason'=>$status_str,
											  'admin_email'=>$system_obj->setting['admin_email']), true);
				// error
				$errors[] = $status_str;
			break;

			case "cancel" :
				$reason = 'Unknown';
				$status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				// $subject = "[" . $blogname . "] Payment receipt - Status Pending";
				// $message = __('This is an automatic notification from %1$s to %2$s (%3$s).<br />This is a notification to inform you that you payment was received in pending status. Status: %4$s.<br />For more information please contact %5$s','mgm');
				// $message = sprintf($message, $blogname, $user->display_name, $user->user_email, $status_str, $system_obj->setting['admin_email']);
				// issue#: 280 (2011 Feb 25)
				// subject
				$subject = $system_obj->get_template('payment_pending_email_template_subject', array('blogname'=>$blogname), true);
				// body	
				$message = $system_obj->get_template('payment_pending_email_template_body', 
										array('blogname'=>$blogname, 'name'=>$user->display_name, 
											  'email'=>$user->user_email, 'reason'=>$status_str,
											  'admin_email'=>$system_obj->setting['admin_email']), true);
				// error
				$errors[] = $status_str;
			break;

			default:
				$status_str = sprintf(__('Last payment status: %s','mgm'),$ctransaction);
				// $subject = "[$blogname] Payment receipt";
				// $message = __('This is an automatic notification from %1$s to %2$s (%3$s).<br />Status: %4$s.<br />For more information please contact %5$s','mgm');
				// $message = sprintf($message, $blogname, $user->display_name, $user->user_email, $status_str, $system_obj->setting['admin_email']);
				// issue#: 280 (2011 Feb 25)
				// subject
				$subject = $system_obj->get_template('payment_unknown_email_template_subject', array('blogname'=>$blogname), true);				
				// body	
				$message = $system_obj->get_template('payment_unknown_email_template_body', array('blogname'=>$blogname, 'name'=>$user->display_name, 
				                                                                              'email'=>$user->user_email, 'reason'=>$status_str,
																							  'admin_email'=>$system_obj->setting['admin_email']), true);
				// error
				$errors[] = $status_str;
		}

		// do action
		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new 
		
		// notify user
		mgm_mail($user->user_email, $subject, $message); //send an email to the buyer		

		$status = __('Failed join', 'mgm'); //overridden on a successful payment
		if ($tran_success) {
			
			//issue #1421
			if($user_id){				
				do_action('mgm_update_coupon_usage', array('user_id' => $user_id));				
			}			
			
			$table_name = TBL_MGM_POST_PURCHASES;
			
			//if we are looking at a pack then explode the buy post item number and loop through it
			if (strpos($post_id, ',') !== false) {
				$posts = explode(',', $post_id);
			} else {
				$posts = array($post_id);
			}
			
			$posts = array_unique($posts);
			
			foreach ($posts as $post_id) {
				$sql = "INSERT INTO " . $table_name . " (user_id, post_id, unixtime)
					VALUES (" . $user_id . ", " . $post_id . ", UNIX_TIMESTAMP())";
				$wpdb->query($sql); //insert the post purchased record
			}

			$status = __('The post was purchased successfully', 'mgm');
		}
		
		// transaction status
		mgm_update_transaction_status($_REQUEST['custom'], $status, $status_str);
		
		// notify admin, only if gateway emails on
		if (!$dge) {
			$subject = "[" . $blogname . "] Admin Notification: " . $user->user_email . " purchased post " . $post_id;
			$message = "User display name: {$user->display_name}<br />User email: {$user->user_email}<br />User ID: {$user->ID}<br />Status: " . $status . "<br />Action: Purchase post:" . $subject . "<br /><br />" . $message . "<br /><br /><pre>" . print_r($_POST, true) . '</pre>';
			mgm_mail($system_obj->setting['admin_email'], $subject, $message);
		}
		
		// error condition redirect
		if(count($errors)>0){
			mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
		}
	}
	*********************************************************************/
	
	// buy membership
	function _buy_membership() {	
		// system	
		$system_obj       = mgm_get_class('system');		
		$s_packs      = mgm_get_class('subscription_packs');
		$duration_str = $s_packs->duration_str;
		$dge          = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));	
		$dpne         = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($_REQUEST['extra']);
		// local var
		extract($custom);		
		
		// currency
		if (!$currency) $currency = $system_obj->setting['currency'];		
		
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
				if(!$dge){
					$message = 'Could not read membership type in the following POST data. Please debug or contact magic members to fix the problem making sure to pass on the following data. <br /><br /><pre>' . "\n\n" . print_r($_POST, true) . '</pre>';
					mgm_mail($system_obj->setting['admin_email'], 'Error in Zombaio membership verification', $message);
				}
				// abort
				return;
			} else {
				$membership_type_verified = $member->membership_type;
			}
		}
		// pack
		if(!isset($subs_pack)) $subs_pack = $s_packs->get_pack($pack_id);
		
		//pack currency over rides genral setting currency - issue #1602
		if(isset($subs_pack['currency']) && $subs_pack['currency'] != $currency){
			$currency =$subs_pack['currency'];
		}		
		// set
		$membership_type = $membership_type_verified;
		// duration
		$member->duration        = $duration;
		$member->duration_type   = strtolower($duration_type);
		$member->amount          = $amount;
		$member->currency        = $currency;
		$member->membership_type = $membership_type;
		$member->pack_id         = $pack_id;		
		$member->payment_type    = 'subscription';
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 	
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'Action', 'subscr_id'=>array('SUBSCRIPTION_ID','SubscriptionID'), 'txn_id'=>'TRANSACTION_ID');
		// save tracking fields
		$this->_save_tracking_fields($tracking_fields, $member, $_REQUEST);		
		/*// payment info for unsubscribe		
		if(!isset($member->payment_info))
			$member->payment_info    = new stdClass;
		$member->payment_info->module = $this->code;
		
		// set data
		if(isset($_REQUEST['Action'])){
			$member->payment_info->txn_type = $_REQUEST['Action'];
		}	
		if(isset($_REQUEST['SUBSCRIPTION_ID'])){
			$member->payment_info->subscr_id = $_REQUEST['SUBSCRIPTION_ID'];		
		}elseif(isset($_REQUEST['SubscriptionID'])){
			$member->payment_info->subscr_id = $_REQUEST['SubscriptionID'];		
		}	
		if(isset($_REQUEST['TRANSACTION_ID'])){	
			$member->payment_info->txn_id = $_REQUEST['TRANSACTION_ID'];	
		}*/
		
		// mgm transaction id
		$member->transaction_id = $_REQUEST['extra'];
		
		// zombaio username
		if(isset($_REQUEST['username'])){	
			$member->zombaio_username = $_REQUEST['username'];	
		}
		
		// errors
		$errors = array();
		// set test status		
		$zombio_action = strtolower($_REQUEST['Action']);
		// status
		$new_status = $update_role = false;
		// zombio_action 
		switch ($zombio_action) {
			case "user.add" :
			case "rebill" :	
				// check if price id mismatch				
				if(isset($subs_pack['product']['zombaio_price_id']) && ($subs_pack['product']['zombaio_price_id'] != $_REQUEST['PRICING_ID'])){
					$new_status = MGM_STATUS_PENDING;
					$member->status_str = __('Last payment was successful (Different Price selected at Payment).','mgm');
					$member->extra_remarks = sprintf(__('Member selected different level/price at payment, register with %d, payment with %d','mgm'),
					                                        $subs_pack['product']['zombaio_price_id'], $_REQUEST['PRICING_ID']);
				}else{
					// do as default
					$new_status = MGM_STATUS_ACTIVE;
					$member->status_str = __('Last payment was successful','mgm');	
				}				
				
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
							
				//cancel previous subscription:	issue#: 565	
				$this->cancel_recurring_subscription($_REQUEST['extra'], null, null, $pack_id);
					
				//clear cancellation status if already cancelled:
				if(isset($member->status_reset_on)) unset($member->status_reset_on);
				if(isset($member->status_reset_as)) unset($member->status_reset_as);
				
				// role update
				if ($role) $update_role = true;
							
				// hook args			
				// transaction_id
				$transaction_id = $this->_get_transaction_id('extra', $_REQUEST);
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
			case "refund" :	
			case "chargeback":		
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				// error
				$errors[] = $member->status_str;
			break;

			case "cancel" :
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
		$acknowledge_user = $this->send_payment_email($_REQUEST['extra']);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $_REQUEST['extra']);
		
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
		do_action('mgm_return_subscription_payment_'.$this->module, array('user_id' => $user_id));// new 	
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
		// on status
		switch ($member->status) {
			case MGM_STATUS_ACTIVE:				
				//Sending notification email to user - issue #1468
				if($notify_user && $is_registration =='Y'){
					$user_pass = mgm_decrypt_password($member->user_password, $user_id);
					do_action('mgm_register_user_notification', $user_id, $user_pass);
				}
				//sending upgrade notifaction email to admin
				if(isset($subscription_option) && $subscription_option =='upgrade'){
					do_action('mgm_user_upgrade_notification', $user_id);
				}				
				// subject
				$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
				// check
				if ($member->payment_type == 'subscription') {
					$payment_type = 'recurring subscription';
					$subscription = sprintf('%1$s %2$s every %3$s %4$s',$member->amount, $member->currency, $member->duration, 
					                                                    $duration_str[$member->duration_type]);
				} else {					
					$payment_type = 'one-time payment';
					$subscription = sprintf('%1$s %2$s for %3$s %4$s',$member->amount, $member->currency, $member->duration, 
					                                                  $duration_str[$member->duration_type]);	
				}
				
				// body
				$message = $system_obj->get_template('payment_success_subscription_email_template_body', 
													array('blogname'=>$blogname, 'name'=>$user->display_name, 
														  'email'=>$user->user_email, 'payment_type'=>$payment_type,
														  'subscription'=>$subscription,'admin_email'=>$system_obj->setting['admin_email']), true);
				break;

			case MGM_STATUS_NULL:				
				// subject
				$subject = $system_obj->get_template('payment_failed_email_template_subject', array('blogname'=>$blogname), true);				
				// message
				$message = $system_obj->get_template('payment_failed_email_template_body', 
									array('blogname'=>$blogname, 'name'=>$user->display_name, 
										  'email'=>$user->user_email, 'payment_type'=>'subscription payment',
										  'reason'=>$member->status_str,
										  'admin_email'=>$system_obj->setting['admin_email']), true);
				break;

			case MGM_STATUS_PENDING:				
				// subject
				$subject = $system_obj->get_template('payment_pending_email_template_subject', array('blogname'=>$blogname), true);
				// body
				$message = $system_obj->get_template('payment_pending_email_template_body', 
										array('blogname'=>$blogname, 'name'=>$user->display_name, 
											  'email'=>$user->user_email, 'reason'=>$member->status_str,
											  'admin_email'=>$system_obj->setting['admin_email']), true);
				break;

			case MGM_STATUS_ERROR:				
				// subject
				$subject = $system_obj->get_template('payment_error_email_template_subject', array('blogname'=>$blogname), true);				
				// body	
				$message = $system_obj->get_template('payment_error_email_template_body', 
									array('blogname'=>$blogname, 'name'=>$user->display_name, 
										  'email'=>$user->user_email, 'reason'=>$member->status_str,
										  'admin_email'=>$system_obj->setting['admin_email']), true);
				break;
		}

		// notify user
		if(!$dpne) {
			if($acknowledge_user) {
				//issue #862
				$subject = mgm_replace_email_tags($subject,$user_id);
				$message = mgm_replace_email_tags($message,$user_id);
				
				mgm_mail($user->user_email, $subject, $message);
				//update as email sent 
				$this->update_paymentemail_sent($_REQUEST['extra']);	
			}
		}		

		// notify admin, only if gateway emails on
		if (!$dge && $acknowledge_user) {
			$subject = "[$blogname] {$user->user_email} - {$member->status}";
			$message = "	User display name: {$user->display_name}\n\n<br />
					User email: {$user->user_email}\n\n<br />
					User ID: {$user->ID}\n\n<br />
					Membership Type: {$membership_type}\n\n<br />
					New status: {$member->status}\n\n<br />
					Status message: {$member->status_str}\n\n<br />
					Subscription period: {$member->duration} ". $duration_str[$member->duration_type] ."\n\n<br />
					Subscription amount: {$member->amount} {$member->currency}\n<br />
					Payment Mode: {$member->payment_type}\n\n<br />
					[remarks]<br>					
					POST Data was: \n\n<br /><br /><pre>" . print_r($_POST, true) . '</pre>';
			// add remarks
			if(isset($member->extra_remarks)){
				$message = str_replace('[remarks]', $member->extra_remarks,$message);
			}else{
				$message = str_replace('[remarks]', '',$message);
			}		
			// mail		
			mgm_mail($system_obj->setting['admin_email'], $subject, $message);
		}
		
		// error condition redirect
		if(count($errors)>0){
			mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
		}
	}
	
	// cancel membership
	function _cancel_membership($user_id=NULL, $redirect=false){
		// system	
		$system_obj       = mgm_get_class('system');		
		$s_packs      = mgm_get_class('subscription_packs');
		$duration_str = $s_packs->duration_str;
		$dge          = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));	
		$dpne         = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));	
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// if passthrough provided
		if(isset($_REQUEST['extra'])){
			// get passthrough, stop further process if fails to parse
			$custom = $this->_get_transaction_passthrough($_REQUEST['extra']);
			// local var
			extract($custom);
		}elseif(isset($_REQUEST['SUBSCRIPTION_ID']) || isset($_REQUEST['SubscriptionID']))	{
			// get tran
			$tran = mgm_get_transaction_by_option('zombaio_subscription_id',( isset($_REQUEST['SUBSCRIPTION_ID']) ? $_REQUEST['SUBSCRIPTION_ID'] : $_REQUEST['SubscriptionID'] ));
			// local var
			extract($tran['data']);		
		}elseif(isset($_REQUEST['TRANSACTION_ID'])){
			// get tran
			$tran = mgm_get_transaction_by_option('zombaio_transaction_id',$_REQUEST['TRANSACTION_ID']);
			// local var
			extract($tran['data']);
		}elseif(isset($_REQUEST['username'])){
			// get user
			if($user = get_user_by( 'login', $_REQUEST['username'] )){
				$user_id = $user->ID;
			}
		}	
		// log		
		// mgm_log($user_id, ($this->module . '_' . __FUNCTION__));
		// no user id
		if (!$user_id) {
			// message
			$message = 'Could not read member in the following REQUEST data. 
			            Please debug or contact magic members to fix the problem making sure to pass 
						on the following data. <br /><br /><pre>' . "\n\n" . print_r($_REQUEST, true) . '</pre>';
			// notify admin, only if gateway emails on
			if(!$dge){				
				// mail
				mgm_mail($system_obj->get_setting('admin_email'), 'Error in Zombaio membership cancellation', $message);
			}else{
				// log
				mgm_log($message, ($this->module . '_' . __FUNCTION__));
			}
			// exit
			exit;
		}
		
		// find user
		$user = get_userdata($user_id);
		$member = mgm_get_member($user_id);
				
		// multiple membesrhip level update:
		$multiple_update = false;	
		// check		
		if((isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']) || (isset($membership_type) && $member->membership_type != $membership_type )) {
			$multiple_update = true;	
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);	
		}		
		
		// get pack
		if($member->pack_id){
			$subs_pack = $s_packs->get_pack($member->pack_id);
		}else{
			$subs_pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
		}
				
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'Action', 'subscr_id'=>array('SUBSCRIPTION_ID','SubscriptionID'), 'txn_id'=>'TRANSACTION_ID');
		// save tracking fields
		$this->_save_tracking_fields($tracking_fields, $member, $_REQUEST);	
		
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
				$expire_date = date('Y-m-d', $trial_expire_date);
			}
		}	
		
		// transaction_id	
		$trans_id = $member->transaction_id;
		// log		
		// mgm_log($member, ($this->module . '_' . __FUNCTION__));
		// mgm_log($expire_date . ' ' . date('Y-m-d H:i:s'), ($this->module . '_' . __FUNCTION__));
		// if today 
		if( time() >= strtotime($expire_date)){
			// status
			$new_status              = MGM_STATUS_CANCELLED;
			$new_status_str          = __('Subscription cancelled','mgm');
			// set
			$member->status      = $new_status;
			$member->status_str  = $new_status_str;					
			$member->expire_date = date('Y-m-d H:i:s');																															
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
		// log
		// mgm_log($member, ($this->module . '_' . __FUNCTION__));		
		// update user
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
		
		// subject
		$subject = $system_obj->get_template('subscription_cancelled_email_template_subject', array('blogname'=>$blogname), true);				
		// body	
		$message = $system_obj->get_template('subscription_cancelled_email_template_body', 
							array('blogname'=>$blogname, 'name'=>$user->display_name, 
								  'email'=>$user->user_email, 'admin_email'=>$system_obj->setting['admin_email']), true);
		// send email notification to user
		if(!$dpne) {
			//issue #862
			$subject = mgm_replace_email_tags($subject,$user_id);
			$message = mgm_replace_email_tags($message,$user_id);
			// mail
			mgm_mail($user->user_email, $subject, $message);		
		}

		// notify admin, only if gateway emails on
		if (!$dge) {
			$subject = "[$blogname] {$user->user_email} - {$new_status}";
			$message = "	User display name: {$user->display_name}\n\n<br />
					User email: {$user->user_email}\n\n<br />
					User ID: {$user->ID}\n\n<br />
					Membership Type: {$membership_type}\n\n<br />
					New status: {$new_status}\n\n<br />
					Status message: {$member->status_str}\n\n<br />					
					Payment Mode: Cancelled\n\n<br />
					POST Data was: \n\n<br /><br /><pre>" . print_r($_POST, true) . '</pre>';
			mgm_mail($system_obj->setting['admin_email'], $subject, $message);
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
				mgm_redirect( add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php')));
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
		if($subscr_id) {			
			// end  point
			$endpoint = $this->_get_endpoint('unsubscribe') ; // base
			// add query
			$url = add_query_arg(array('SUBSCRIPTION_ID'=>urlencode($subscr_id), 'MERCHANT_ID'=>urlencode($this->setting['merchant_id']), 
			                           'ZombaioGWPass'=>urlencode($this->setting['gw_pass']),'ReasonCode'=>urlencode(11)), $endpoint);		
			// create curl post				
			//$buffer = $this->_curl_post($url);	
			// headers
			$http_headers = array();							
			// fetch		
			$http_response = mgm_remote_post($url, $post_data, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false)); 			
			
			// verify
			if( $http_response == 1 ){
				return true;
			}else{
				// message
				return $this->_get_cancel_error($http_response);	
			}	
		}elseif($subscr_id === 0) {			
			//send email to admin if subscription Id is absent		
			$system_obj = mgm_get_class('system');
			$dge    = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
			//send email only if setting enabled
			if(!$dge) {
				$user = get_userdata($user_id);
				//send notification email to admin:
				$message = (__('The User: ', 'mgm')). $user->user_email.' ('. $user_id .') '.(__('has upgraded/cancelled subscription.', 'mgm'));
				$message .= "<br/>" .__('Please unsubscribe the user subscription from Gateway Merchant panel.', 'mgm');						
				if(!empty($member->transaction_id))
					$message .= "<br/>" .__('MGM Transaction Id:' ,'mgm' ) . $member->transaction_id;		
				//admin email:
				if(!empty($system_obj->setting['admin_email']))
					@mgm_mail($system_obj->setting['admin_email'], sprintf(__('[%s] User Subscription Cancellation', 'mgm'), get_option('blogname')), $message);
								
			}
						
			return true;
		}	
			
		return false;
	}

	// default setting
	function _default_setting(){
		// zombaio specific
		$this->setting['merchant_id']   = '';
		$this->setting['site_id']       = '';
		$this->setting['gw_pass']       = '';
		$this->setting['lang']          = 'ZOM';
		$this->setting['send_userpass'] = 'no';		
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
		if($this->_is_transaction($_REQUEST['extra'])){	
			// tran id
			$tran_id = (int)$_REQUEST['extra'];			
			// return data				
			if(isset($_REQUEST['Action'])){
				$option_name = $this->module.'_'.strtolower($_REQUEST['Action']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_REQUEST)));
			
			// options 
			$options = array('Action','SUBSCRIPTION_ID','SubscriptionID','TRANSACTION_ID');
			// loop
			foreach($options as $option){
				// check
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
		if($this->_is_transaction($_REQUEST['extra'])){	
			// tran id
			return $tran_id = (int)$_REQUEST['extra'];
		}
		// return 
		return 0;	
	}*/
	
	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// get languages
	function _get_languages(){
		/// define
		$languages = array(
						 'ZOM' => __('Default [IP Based]','mgm'), 	
						 'US'  => __('English','mgm'),
						 'FR'  => __('French','mgm'),
						 'DE'  => __('German','mgm'),
						 'IT'  => __('Italian','mgm'), 						
						 'JP'  => __('Japanese','mgm'),
        				 'ES'  => __('Spanish','mgm'),
						 'SE'  => __('Swedish','mgm'),
						 'KR'  => __('Korean','mgm'),									 
						 'CH'  => __('Traditional Chinese','mgm'),
						 'HK'  => __('Simplified Chinese','mgm')					 
						);
		// return
		return $languages;				
	}

	// verify callback 
	function _verify_callback(){			
		// pass form get
		$gw_pass = $_REQUEST['ZombaioGWPass'];
		// check
		if(!empty($gw_pass) && $this->setting['gw_pass'] == $gw_pass){
			return true;
		}
		// return
		return false;
	}		
	
	// get endpoint
	function _get_endpoint($pack){		
		// string
		if(is_string($pack)){
			return parent::_get_endpoint($pack);
		}else{
			// get url
			$endpoint = parent::_get_endpoint();
			// price_id
			$endpoint = str_replace('[price_id]', $pack['product']['zombaio_price_id'], $endpoint);
			// site_id
			$endpoint = str_replace('[site_id]', $this->setting['site_id'], $endpoint);
			// lang
			$endpoint = str_replace('[lang]', $this->setting['lang'], $endpoint);
			// return
			return $endpoint;
		}	
	}	
	
	// setup endpoints
	function _setup_endpoints($end_points = array()){		
		// define defaults
		$end_points_default = array('test'        => false,
									'live'        => 'https://secure.zombaio.com/?[site_id].[price_id].[lang]',
									'unsubscribe' => 'https://secure.zombaio.com/API/Cancel');	
									
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// _get_cancel_error
	function _get_cancel_error($buffer){
		// buffer
		switch((int)$buffer){
			case 0:
				return 'Unknown error';
			break;
			case 1:
				return 'Cancellation successful or subscription already cancelled'; 
			break;
			case 2:
				return 'Wrong Merchant Id or Zombaio GW Pass'; 
			break;
			case 3:
				return 'Wrong Subscription Id or subscription belogs to other merchant'; 
			break;
			case 4:
				return 'System unable to process the request at the moment'; 
			break;
			default:
				return 'Unknown error';
			break;	
		}
	}
	
	/*** deprecated
	// curl post
	function _curl_post($url, $post_fields=NULL, $http_header=array()){
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
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
		// post data
		if($post_fields){
			curl_setopt($ch, CURLOPT_POST, 1); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
		}		
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
		curl_setopt($ch, CURLOPT_USERAGENT, 'Magic Members Membership Software'); 
		curl_setopt($ch, CURLOPT_REFERER, get_option('siteurl')); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);				
	    $buffer = curl_exec($ch);
		curl_close($ch);
		
		// return
		return $buffer;
	}*/
	
	// create/check proxy
	function _check_zombaio_proxy($create=false){
		// create
		if( $create ){
			// check already exists
			if(!file_exists(ABSPATH . 'zombaio_proxy.php')){
				// return url
				$notify_url = $this->setting['notify_url'];
				// str
				$str ='<?php if(count($_REQUEST) <= 4): echo "OK|OK"; else: header("Location: '.$notify_url.'&".$_SERVER["QUERY_STRING"]);exit; endif;?>';
				// file create
				file_put_contents(ABSPATH . 'zombaio_proxy.php', $str);	
			}
		}
		
		// check
		if(file_exists(ABSPATH . 'zombaio_proxy.php')){
			// return
			return sprintf('<div class="mgm_proxy_installed">%s<br><br>%s</div>', site_url('zombaio_proxy.php'),
						   __('Zombaio Postback Proxy (ZScript) installed, copy the url above and paste in Zombaio Admin Site Settings<br> Postback URL (ZScript).','mgm'));
						
		}else{
			// return 
			return sprintf('<div class="mgm_proxy_not_installed">%s<br><br>%s</div>',site_url('zombaio_proxy.php'),
							__('Zombaio Postback Proxy (ZScript) not installed. Please save settings to create the proxy file.'. 
						      'Once done, copy the URL to Zombaio Admin Site Settings.','mgm'));			
				
		}
	}
	
	// set 
	function _set_address_fields($user, &$data){
		// mappings
		$mappings= array('first_name'=>'FirstName','last_name'=>'LastName','address'=>'Address','zip'=>'Postal',
		                 'state'=>'Region','city'=>'City','country'=>'Country','phone'=>'Phone');
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
	
	
	// try to capture transaction id from option
	function _populate_transaction(){
		// options
		$options = array('SUBSCRIPTION_ID','SubscriptionID');
		// init
		$zombaio_subscription_id = '';
		// loop
		foreach($options as $option){
			if(isset($_REQUEST[$option]) && !empty($_REQUEST[$option])){
				$zombaio_subscription_id = $_REQUEST[$option]; break;
			}
		}
		// check
		if($zombaio_subscription_id){
			$_REQUEST['extra'] = mgm_get_transaction_id_by_option('zombaio_subscription_id',$zombaio_subscription_id);
		}
	}
}

// end file