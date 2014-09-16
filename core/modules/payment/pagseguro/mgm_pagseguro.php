<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * Pagseguro Payment Module, integrates pagseguro standard integtaion 
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */
class mgm_pagseguro extends mgm_payment{	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_pagseguro();
	}
	
	// php4 construct
	function mgm_pagseguro(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'PagSeguro';	
		// logo
		$this->logo = $this->module_url( 'assets/pagseguro.gif' );
		// description
		$this->description = __('PagSeguro is an online Payment Solution, leader in the Brazilian market.', 'mgm');
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
		// endpoints
		$this->_setup_endpoints();		
		// button message
		$this->button_message = '';//'Make payments with PagSeguro - it\'s fast, free and secure!';
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
				// pagseguro specific
				$this->setting['receiver_email'] = $_POST['setting']['receiver_email'];
				$this->setting['token']          = $_POST['setting']['token'];
				$this->setting['currency']       = $_POST['setting']['currency'];								
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
	
	// return process api hook, link back to site after payment is made
	function process_return() {
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		// auto return page data - issue #1519
		if(count($_REQUEST)>3){
			//results
			$result = $this->_get_autoreturn_notification_post();
			//check
			if ($result == "VERIFICADO") {
				/*	
					Return transaction status
					Full (Completo): Full payment
					Payroll Waiting(Aguardando Pagto): Waiting for customer payment
					Approved(Aprovado): Payment approved, awaiting compensation
					Analysis(Em Análise): Payment approved, under review by PagSeguro
					Cancelled(Cancelado): Payment canceled by PagSeguro				
				*/
				
				$status_transacao_arr = array('Completo','Aguardando Pagto','Aprovado','Em Análise');
				// update auto return response log 
				update_option('mgm_'.$_REQUEST['TransacaoID'], serialize($_REQUEST));				
				//transaction status check
				if(in_array($_REQUEST['StatusTransacao'],$status_transacao_arr)) {
					//custom val
					update_option($_REQUEST['TransacaoID'],$_REQUEST['Referencia']);
				}	
				//O post foi validado pelo PagSeguro.
			} else if ($result == "FALSO") {
				//O post não foi validado pelo PagSeguro.
			} else {
				//Erro na integração com o PagSeguro.
			}	
		}else {		
			//check
			if(isset($_REQUEST['transaction_id'])){	
				//transaction id
				$pagseguro_tran_id = str_replace('-','',$_REQUEST['transaction_id']);
				//custom
				$custom_val = get_option($pagseguro_tran_id);
				$_REQUEST['custom']= $custom_val;
				//mgm_log('_pagseguro custom val : '.$custom_val,$this->module.'_'.__FUNCTION__);
								
				//fetch auto return response log data
				//$pagseguro_data = get_option('mgm_'.$pagseguro_tran_id);
				//$pagseguro_data = unserialize($pagseguro_data);
				//mgm_log('_pagseguro_data : '.mgm_array_dump($pagseguro_data,true),$this->module.'_'.__FUNCTION__);		
			}
		}
		// check and show message
		if( isset($_REQUEST['custom']) && !empty($_REQUEST['custom']) ){
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
			mgm_redirect(add_query_arg(array('status'=>'error'), $this->_get_thankyou_url()));
		}
	}	
		
	// notify process api hook, background IPN url
	function process_notify() {		
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		// verify 		
		if($this->_verify_callback()){ // verify pagseguro payment data				
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
					// subscription 						
					$this->_buy_membership(); //run the code to process a new/extended membership					
				break;	
				// other		
				default:
					// error				
				break;							
			}
			// after process		
			do_action('mgm_notify_post_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));				
		}else {
			//Note: Keep the below log: This is to log posts from IPN as theere are issues related to recurring IPN POST
			mgm_log('FROM PagSeguro process_notify: VERIFY Failed', $this->module);	
		}
		// after process unverified		
		do_action('mgm_notify_post_process_unverified_'.$this->module);		
		
		// 200 OK to PagSeguro, this is IMPORTANT, otherwise Gateway will keep on sending IPN .........
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
			if(!empty($member->payment_info->subscr_id))
				$subscr_id = $member->payment_info->subscr_id;
			elseif (!empty($member->pack_id)) {	
				//check the pack is recurring
				$s_packs = mgm_get_class('subscription_packs');				
				$sel_pack = $s_packs->get_pack($member->pack_id);										
				if($sel_pack['num_cycles'] != 1) 
					$subscr_id = 0;// 0 stands for a lost subscription id
			}

			// cancel at pagseguro
			$cancel_account = $this->cancel_recurring_subscription(null, $user_id, $subscr_id);
		}

		// cancel in MGM
		if($cancel_account === true) {
			$this->_cancel_membership($user_id, true);// redirected
		}

		// message
		$message = __('Error while cancelling subscription', 'mgm') ;				
		//issue #1521
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
					<input type="image" name="submit" width="0" height="0" src="https://p.simg.uol.com.br/out/pagseguro/i/botoes/pagamentos/120x53-pagar.gif" alt = "<?php _e("Pay with PagSeguro","mgm"); ?>" >
					<!--mandatory, needed for merchant gateway, KEEP AS IT IS and no line break!-->												
			  </form>				
			  <script language="javascript">document.' . $this->code . '_redirect_form.submit();</script>';
		  		  
		// return 	  
		return $html;					
	}	
		
	// subscribe button api hook
	function get_button_subscribe($options=array()){	
		// if payment initiaed from sidebar widget, do not use permalink : the current url		
		$include_permalink = (isset($options['widget'])) ? false : true;	
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect',$include_permalink) .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
				   <input type="hidden" name="tran_id" value="'.$options['tran_id'].'">				  
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
			   <form name="mgm_unsubscribe_form" id="mgm_unsubscribe_form" method="post" action="' . $action . '" >			   		
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
		$transaction_id  = $member->payment_info->txn_id;
		
		// info
		$info = sprintf('<b>%s:</b><br>%s: %s', __('PAGESEGURO INFO','mgm'), __('TRANSACTION ID','mgm'), $transaction_id);					
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
		$html = sprintf('<p>%s: <input type="text" size="20" name="pageseguro[transaction_id]"/></p>', 
						__('Transaction ID','mgm'));
		
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
		$fields = array('txn_id'=>'transaction_id');
		// data
		$data = $post_data['pageseguro'];
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

   /**
	*  get button data
    * 
    ** @see https://pagseguro.uol.com.br/v2/guia-de-integracao/pagamento-via-html.html/
    */
	function _get_button_data($pack, $tran_id=NULL) {
		// system setting
		$system_obj  = mgm_get_class('system');	
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
			'receiverEmail'    => $this->setting['receiver_email'],
			'reference'        => $tran_id,
			'itemId1'          => $item['id'],
			'itemDescription1' => substr($item['name'], 0, 100),
			'itemAmount1'      => number_format($pack['cost'], 2, '.', ''),
			'itemQuantity1'    => 1,
			'currency'         => $pack['currency'],			
			'senderEmail'      => $user->user_email,
			'encoding'         => 'UTF-8'
		);
				
		// address fields
		$this->_set_address_fields($user, $data);			
		
		// custom passthrough
		$data['custom'] = $tran_id; 
		
		// old v1
		// set custom on request so that it can be tracked for post purchase
		// $data['notify_url']    = $this->setting['notify_url'];
		// $data['return']        = add_query_arg(array('custom'=>$data['custom']), $this->setting['return_url']);
		// $data['cancel_return'] = $this->setting['cancel_url'];

		// new v2
		$data['notificationURL'] = $this->setting['notify_url'];		
		$data['redirectURL']     = add_query_arg(array('custom'=>$data['custom']), $this->setting['return_url']);	
		$data['cancelURL']       = $this->setting['cancel_url'];	
		
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
		$dge    = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne   = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		
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
		switch ($_POST['status_code']) {
			case 'Paid':			
			case 'Available':
			// 2 IPN occurs for successful payment, Paid first and after 14 days Available
			// refer https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-notificacoes.html
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

			case 'Returned':
			case 'In dispute':
				// status
				$status_str = __('Last payment was refunded or denied','mgm');
				// purchase status
				$purchase_status = 'Failure';
											  
				// error
				$errors[] = $status_str;
			break;

			case 'Awaiting Payment':
			case 'In analysis':
				// status
				$status_str = __('Last payment is pending. Reason: Awaiting Payment','mgm');
				// purchase status
				$purchase_status = 'Pending';	

				// error
				$errors[] = $status_str;
			break;

			default:
				// status
				$status_str = sprintf(__('Last payment status: %s','mgm'),$_POST['status_code']);
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
		// $member->payment_type = ($_POST['txn_type']=='subscr_signup' || $_POST['txn_type'] =='subscr_payment') ? 'subscription' : 'one-time';
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		// payment info for unsubscribe		
		if(!isset($member->payment_info))
			$member->payment_info    = new stdClass;
		// set module	
		$member->payment_info->module = $this->code;
		// pagseguro transaction type
		if(isset($_POST['type'])){
			$member->payment_info->txn_type = $_POST['type'];
		}	
		// pagsegurosubscription id
		/*if(isset($_POST['code'])){
			$member->payment_info->subscr_id = $_POST['code'];		
		}*/
		// pagseguro transaction id	
		if(isset($_POST['code'])){	
			$member->payment_info->txn_id = $_POST['code'];	
		}			
			
		// mgm transaction id
		$member->transaction_id = $_POST['custom'];
		
		// process PagSeguro response
		$new_status = $update_role = false;
		// status
		switch ($_POST['status_code']) {
			case 'Paid':
			case 'Available':// 2 IPN occurs for successful payment, Paid first and after 14 days Available
			                 // refer https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-notificacoes.html
				// status
				$new_status = MGM_STATUS_ACTIVE;
				$member->status_str = __('Last payment was successful','mgm');					
				
				// old type match
				$old_membership_type = mgm_get_user_membership_type($user_id, 'code');
				// set new pack join date
				if ($old_membership_type != $membership_type) {
					$member->join_date = time(); // type join date as different var
				}
				// old content hide
				$member->hide_old_content = $hide_old_content; 
				
				// time
				$time = time();
				$last_pay_date = isset($member->last_pay_date) ? $member->last_pay_date : null;			
				// last pay	
				$member->last_pay_date = date('Y-m-d', $time);				
				
				// THIS will cause double calculation pagseguro, check applied
				/* *********************************************************************************/
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
				/***********************************************************************************/					
				
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
			case 'Returned':
			case 'In dispute':
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				break;

			case 'Awaiting Payment':
			case 'In analysis':
				$new_status = MGM_STATUS_PENDING;

				$reason = 'Awaiting Payment';
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				break;

			default:
				$new_status = MGM_STATUS_ERROR;
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $_POST['status_code']);
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
		
		// role
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
		// check
		if(!$user_id) {
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
		if((isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']) || (isset($is_another_membership_purchase) && $is_another_membership_purchase == 'Y' )) {
			$multiple_update = true;	
			$multi_memtype = (isset($_POST['membership_type'])) ? $_POST['membership_type'] : $membership_type;
			$member = mgm_get_member_another_purchase($user_id, $multi_memtype);	
		}
				
		//Don't save if it is cancel request with an upgrade:
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
		// life
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
																																				
			//reassign expiry membership pack if exists: issue#: 535			
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
		
		
		//send email only if setting enabled
		if((!empty($subscr_id) || $subscr_id === 0) ) {
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
		// pagseguro specific
		$this->setting['receiver_email'] = get_option('admin_email');	
		$this->setting['token']          = '';
		$this->setting['currency']       = mgm_get_class('system')->setting['currency'];
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
			if(isset($_POST['type'])){
				$option_name = $this->module.'_'.strtolower($_POST['type']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// check
			if($tran_id>0){
				// set
				mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_POST)));
				
				// options 
				$options = array('type','code');
				// loop
				foreach($options as $option){
					// check
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
	
	// setup
	function _setup_endpoints($end_points = array()){
		// define defaults
		$end_points_default = array('test' => 'https://pagseguro.uol.com.br/v2/checkout/payment.html',
									'live' => 'https://pagseguro.uol.com.br/v2/checkout/payment.html',
									'notification' => 'https://ws.pagseguro.uol.com.br/v2/transactions/notifications/'
									);	
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// set 
	function _set_address_fields($user, &$data){
		// mappings, mgm=>module
		$mappings= array('full_name'=>'senderName','phone'=>'senderPhone');
						 
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
	
	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// verify callback 
	function _verify_callback(){
		// get notification data, populate in POST
		$this->_get_notification_data();
		
		// check
		if(isset($_POST['status_code']) && isset($_POST['custom'])){
		// ok
			return true;
		}	
		
		// system
		$system_obj = mgm_get_class('system');				
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));		
		
		// notify admin, only if gateway emails on
		if( ! $dge ){
			// return
			return mgm_notify_admin_ipn_verification_failed( $this->module );			
		}else{				
			mgm_log('PagSeguro verification failed: ' . $message, $this->module .'_' . __FUNCTION__);
		}	
		
		// error
		return false;				
	}
	
	// get notification data
	function _get_notification_data(){
		// notificationCode
		$notification_code = mgm_post_var('notificationCode');// code
		$notification_type = mgm_post_var('notificationType');// transaction		

		// check
		if($notification_code){
			// parse the pagseguro URL
			$notification_url = $this->_get_endpoint('notification');  			

			// build
			$notification_url = add_query_arg(array('email'=>$this->setting['receiver_email'],'token'=>$this->setting['token']), 
			                                  trailingslashit($notification_url . $notification_code));
			
			// headers
			$http_headers = array('Content-Type' => 'application/x-www-form-urlencoded');// just in case			

			// get
			$http_response = mgm_remote_get($notification_url, null, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));

			// log xml as came			
			mgm_log('http_response: ' . $http_response,  $this->module .'_' . __FUNCTION__);

			// parse as xml
			if($xml = @simplexml_load_string($http_response)){
			// parse
				$_POST['code']   = (string)$xml->code;
				$_POST['custom'] = (string)$xml->reference;
				$_POST['type']   = (string)$xml->type;
				$_POST['status'] = (string)$xml->status;
				// code 
				$_POST['status_code'] = $this->_get_status_code($_POST['status']);				

				// log
				mgm_log('PagSeguro IPN : processed :'.print_r($_POST, true), $this->module .'_' . __FUNCTION__);
			}			
		}
	}
	
	// get code
	function _get_status_code($status){
		// define
		$status_codes = array(
			// The buyer initiated the transaction,but so far not received the PayPal payment information.
			// 'A' => 'Awaiting Payment',	
			1 => 'Awaiting Payment',								
			// the buyer chose to pay with a credit card and PayPal is analyzing the risk of the transaction.
			2 => 'In analysis',
			// the transaction was paid by the buyer and has already received a PayPal confirmation of the financial institution responsible for processing.
			3 => 'Paid', 
			// The transaction was settled and reached the end of his period of release without having been returned and without any dispute opened.
			4 => 'Available', 
			// the buyer, within the release of the transaction, opened a dispute.
			5 => 'In dispute', 
			// the transaction amount was returned to the buyer.
			6 => 'Returned', 
			// The deal was canceled without having been terminated.				  
			7 => 'Cancelled');
		
		// a	
		if($status == 'A') return $status_codes[1];
			
		// return					
		return (isset($status_codes[(int)$status])) ? $status_codes[(int)$status] : 'Unknown';					  
	}	
	
	//checking post redirect autoreturn data - issue #1519
	function _get_autoreturn_notification_post() {		
		//mgm_log(mgm_array_dump($_POST,true),$this->module.'_'.__FUNCTION__);
		$postd_ata = 'Comando=validar&Token='.$this->setting['token'];
		//adding arguments		
		foreach ($_POST as $key => $value) {
			$valued    = $this->clearStr($value);
			$postd_ata .= "&$key=$valued";
		}		
		//mgm_log($postdata,$this->module.'_'.__FUNCTION__);
		return $this->_curl_post($postd_ata);
	}
	//adding slashes to string - issue #1519
	function clearStr($str) {
		if (!get_magic_quotes_gpc()) {
			$str = addslashes($str);
		}
		return $str;
	}
	//verifying post redirect autoreturn data - issue #1519
	function _curl_post($data) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://pagseguro.uol.com.br/pagseguro-ws/checkout/NPI.jhtml");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$result = trim(curl_exec($curl));
		curl_close($curl);		
		//mgm_log(mgm_array_dump($result,true),$this->module.'_'.__FUNCTION__);
		return $result;
	}		
	/*
	function _curl_post(){
		// open
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL            , $notification_url);
			curl_setopt($ch, CURLOPT_USERAGENT      , 'Magic Members Membership Software');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER     , array("Content-Type: application/x-www-form-urlencoded"));	
			//curl_setopt($ch, CURLOPT_POSTFIELDS     , $request);
			//curl_setopt($ch, CURLOPT_POST           , 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , 0);
			curl_setopt($ch, CURLOPT_NOPROGRESS     , 1); 
			curl_setopt($ch, CURLOPT_VERBOSE        , 1); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION , 0); 
			curl_setopt($ch, CURLOPT_TIMEOUT        , 30); 
			curl_setopt($ch, CURLOPT_REFERER        , get_option('siteurl')); 
			curl_setopt($ch, CURLOPT_HEADER         , 0);				
			
			// response
			$response = curl_exec($ch); 
	}
	
	*/
}
// end file