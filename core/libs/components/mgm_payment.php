<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members payment modules parent class
 *
 * @package MagicMembers
 * @since 2.0.0
 */
class mgm_payment extends mgm_component{
	// type
	var $type              = 'payment';
	// type
	var $button_type       = 'offsite'; // onsite / offsite
	// status
	var $status            = 'test';// test/live
	// name
	var $name              = 'Magic Members Payment Module';
	// internal name
	var $code              = 'mgm_payment';
	// module dir
	var $module            = 'payment';	
	// settings tab
	var $settings_tab      = true;// deprecated
	// description
	var $description       = '';
	// logo
	var $logo              = 'payment/assets/logo.jpg';
	// enabled/disabled : Y/N
	var $enabled           = 'N';	
	// supported buttons types, array('subscription', 'buypost')
	var $supported_buttons = array('subscription', 'buypost');
	// can setup trial mode: Y/N, for Paypal, Authorize.net, Paypal Pro 
	var $supports_trial    = 'N';
	// cancellation support via api/post: Y/N, for Paypal, Authorize.net, Paypal Pro, AlertPay, 2Checkout 
	var $supports_cancellation = 'N';	
	// requires_product_mapping, to differenciate between modules where external product mapping is required, 
	// i.e. clickbank, 2checkout, and not required i.e. paypal, authorize.net
	var $requires_product_mapping = 'N'; 	
	// type of integration,
	// Y => gateway/merchant hosted, offsite, html redirect, payment will be done on Gateway/Merchant hosted form, 
	// N => self hosted onsite, payment will be on site itself with credit card form
	// D => @todo add payflow iframe onsite (duo, onsite but loading form from getways using iframe)
	var $hosted_payment = 'Y';
	// extended hosted_payment for more options i,e html_redirect,credit_card,iframe,jsloader etc.
	// var $hosted_payment_type = 'html_redirect';
	// if supports rebill status check via API, only modules that support API based status check should set it as "Y"	
	var $supports_rebill_status_check = 'N';	
	// api end points
	var $end_points = array();
	// settings
	var $setting = array();
	// virtual payment, trial, free and manual pay
	var $virtual_payment = 'N';
	// card types
	var $card_types = array();
	// supported card types
	var $supported_card_types = array();	
	// webhooked called by 
	var $webhook_called_by = 'merchant';// merchant,self

	/** 
	 * construct
	 */	
	function __construct(){
		// php4 construct
		$this->mgm_payment();
	}
	
	/** 
	 * php4 construct
	 */	
	function mgm_payment(){		
		// call parent
		parent::__construct();		
		// set code
		$this->code = __CLASS__; 				
		// desc
		$this->description = __('Payment module description', 'mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');
	 	// card type values
	 	$this->card_types  = array('Amex' => __('Amex', 'mgm'), 'Discover' => __('Discover', 'mgm'), 
		                           'Mastercard' => __('Mastercard', 'mgm'), 'Visa' => __('Visa', 'mgm') );
		// default settings
		$this->_default_setting();				
	}
	
	/**
	 * set template path
	 *
	 * @param string $basedir (template dir, defaults to MGM_MODULE_BASE_DIR, use MGM_EXTEND_MODULE_BASE_DIR for modules in extend folder)
	 * @param string $prefix (module class prefix, default "mgm_", use "mgmx_" for extended modules
	 * @return void
	 */
	function set_tmpl_path($module_base_dir=''){		
		// set module
		$this->set_module();
		// set base dir
		$this->set_module_base_dir($module_base_dir);		
		// set dir
		$this->set_module_dir();
		// set path	mgm_module_paypal_tmpl_path
		$tmpl_path = ($this->get_module_base_dir() . $this->get_module_dir() . 'html' . MGM_DS);	
		// filter
		$tmpl_path = apply_filters('mgm_module_tmpl_path_' . $this->module, $tmpl_path);		
		// set		
		$this->load->set_tmpl_path($tmpl_path);
	}
	
	/**
	 * set module base
	 *
	 * @param string $dir
	 * @param string $url
	 * @return void
	 * @since 2.7
	 */
	function set_module_base($dir, $url){
		// set dir
		$this->set_module_base_dir($dir);
		// set url
		$this->set_module_base_url($url);		
	}

	/**
	 * set module base directory
	 *
	 * @param string $module_base_dir
	 * @return void
	 * @since 2.7
	 */	
	function set_module_base_dir($module_base_dir=''){
		// set
		if($module_base_dir) return $this->module_base_dir = $module_base_dir;		
		// default
		if(!$this->module_base_dir)	$this->module_base_dir = MGM_MODULE_BASE_DIR;				
	}
	
	/**
	 * set module base url
	 *
	 * @param string $module_base_url
	 * @return void
	 * @since 2.7
	 */	
	function set_module_base_url($module_base_url=''){
		// set
		if($module_base_url) return $this->module_base_url = $module_base_url;
		// default		
		if(!$this->module_base_url)	$this->module_base_url = MGM_MODULE_BASE_URL;			
	}
	
	/**
	 * set module directory
	 *
	 * @param string $module_dir
	 * @return void
	 * @since 2.7
	 */	
	function set_module_dir($module_dir=''){
		// set
		if($module_dir) return $this->module_dir = $module_dir;
		// default
		if(!$this->module_dir) $this->module_dir = (implode(MGM_DS, array($this->type, $this->module)) . MGM_DS);		
	}
		
	/**
	 * set module url
	 *
	 * @param string $module_url
	 * @return void
	 * @since 2.7
	 */	
	function set_module_url($module_url=''){
		// set
		if($module_url) return $this->module_url = $module_url;
		// default
		if(!$this->module_url) $this->module_url = (implode('/', array($this->type, $this->module)) . '/');		
	}	
	
	/**
	 * set module from code
	 *
	 * @param string $prefix
	 * @return void
	 * @since 2.7
	 */
	function set_module($prefix='mgm_'){
		// dir/module		
		if(!$this->module) $this->module = str_replace($prefix, '', $this->code);	
	}

	/**
	 * module url by path
	 *
	 * @param string $path
	 * @return string $url
	 * @since 2.7
	 */
	function module_url($path){
		// return
		return $this->get_module_base_url() . $this->get_module_url() . $path;
	}
	
	/**
	 * get module base directory
	 *
	 * @param void
	 * @return string $module_base_dir
	 * @since 2.7
	 */
	function get_module_base_dir(){
		// check
		if(!$this->module_base_dir) $this->set_module_base_dir();
		// set
		return apply_filters('mgm_module_base_dir_' . $this->module, $this->module_base_dir);
	}
	
	/**
	 * get module base url
	 *
	 * @param void
	 * @return string $module_base_url
	 * @since 2.7
	 */
	function get_module_base_url(){
		// check
		if(!$this->module_base_url) $this->set_module_base_url();
		// set
		return apply_filters('mgm_module_base_url_' . $this->module, $this->module_base_url);
	}
	
	/**
	 * get module directory
	 *
	 * @param void
	 * @return string $module_dir
	 * @since 2.7
	 */	
	function get_module_dir(){		
		// check
		if(!$this->module_dir) $this->set_module_dir();
		// return 
		return apply_filters('mgm_module_dir_' . $this->module, $this->module_dir);
	}
		
	/**
	 * get module url
	 *
	 * @param void
	 * @return string $module_url
	 * @since 2.7
	 */	
	function get_module_url(){
		// check
		if(!$this->module_url) $this->set_module_url();
		// return 
		return apply_filters('mgm_module_url_' . $this->module, $this->module_url);
	}
	
	/**
	 * get module name
	 *
	 * @param void
	 * @return string $name
	 */
	function get_name(){
		// return
		return $this->name;
	}

	/**
	 * get module description
	 *
	 * @param void
	 * @return string $description
	 */
	function get_description(){
		// return
		return $this->description;
	}

	/**
	 * enable module
	 * 
	 * @param bool $activate 
	 * @return none
	 */
	function enable($activate=false){
		// activate
		if($activate) mgm_get_class('system')->activate_module($this->code,$this->type);
		// update state
		$this->enabled = 'Y'; 		
		// reset urls
		$this->_reset_callback_urls();		
		// save			
		$this->save();	
	}
		
	/**
	 * disable module
	 * 
	 * @param bool $deactivate
	 * @return none
	 */
	function disable($deactivate=false){
		// deactivate
		if($deactivate) mgm_get_class('system')->deactivate_module($this->code,$this->type);
		// update state
		$this->enabled = 'N'; 
		// reset urls
		$this->_reset_callback_urls();
		// save
		$this->save();
	}
	
	/**
	 * install module
	 * 
	 * @param none
	 * @return none
	 */
	function install(){				
		// enable
		$this->enable(true);
	}
	
	/**
	 * uninstall module
	 * 
	 * @param none
	 * @return none
	 */
	function uninstall(){						
		// disable
		$this->disable(true);			
	}
		
	/**
	 * invoke module method
	 * 
	 * @param string $method
	 * @param array @args
	 * @return function output
	 */
	function invoke($method, $args=false){	
		// check
		if(method_exists($this,$method)){
			return $this->$method($args);
		}else{
			die(sprintf(__('No such method: %s','mgm'),$method));
		}
	} 
	
	// API methods -----------------------------------------------------

	/**
	 * API method settings ui, callback main settings page
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none	 
	 */
	function settings(){
		// return
		return false;
	}	
	
	/**
	 * API method quick settings ui, callback quick/box settings page
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none	 
	 */
	function settings_box(){
		// override this
		return false;
	}	
	
	/**
	 * API method post purchase settings ui, callback post purchase settings page
	 *
	 * to be overriden in module	 
	 *
	 * @param array $data
	 * @return none	 
	 */
	function settings_post_purchase($data=NULL){
		// override this
		return false;
	}
	
	/**
	 * API method post pack purchase settings ui, callback post pack purchase settings page
	 *
	 * to be overriden in module	 
	 *
	 * @param array $data
	 * @return none	 
	 */
	function settings_postpack_purchase($data=NULL){
		// override this
		return false;
	}
	
	/**
	 * API method subscription package settings ui, callback subscription package settings page
	 *
	 * to be overriden in module	 
	 *
	 * @param array $data
	 * @return none	 
	 */
	function settings_subscription_package($data=NULL){
		// override this
		return false;
	}
	
	/**
	 * API method coupon settings ui, callback coupon settings page
	 *
	 * to be overriden in module	 
	 *
	 * @param array $data
	 * @return none	 
	 */
	function settings_coupon($data=NULL){
		// override this
		return '';
	}
	
	/**
	 * API method save settings, callback main and quick settings data save
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none	 
	 */
	function settings_update(){
		// override this
		// setting type
		switch($_POST['setting_form']){
			case 'box':
			// from box	
			break;
			default:
			case 'main':
			// form main
			break;
		}
		// return
		return false;
	}	
	
	/**
	 * API method process return, return callback to site after payment is made 
	 *
	 * to be overriden in module
	 * 
	 * @param none
	 * @return none
	 * @uses return_url
	 */
	function process_return(){
		// return
		return false;
	}
	
	/**
	 * API method process notify, IPN/Background Notify callback for silent POST after payment is processed, triggered when a payment is made
	 * on site. 
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none 
	 * @uses notify_url
	 */
	function process_notify(){
		// return
		return false;
	}
	
	/**
	 * API method process status notify, INS/Status Notify callback for silent POST from gateway at each payment cycle, EXTERNAL
	 * This url will be setup in gateway and called at regular interval by GATEWAY, which in turn will call process_rebill_status()
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none
	 * @uses status_notify_url 
	 */
	function process_status_notify(){
		// return
		return false;
	}
	
	/**
	 * API method process rebill status, callback for checking member last rebill status, call API from MGM or process IPN from Gateway, INTERNAL
	 * 
	 * called by CRON/LOGIN/MANUAL if API support for status check available in gateway
	 * called by GATEWAY via process_status_notify() internally if ONLY INS/REGULAR IPN available in gateway  
	 * revised functionlity of query_rebill_status() method call	 
	 *
	 * to be overriden in module	 
	 *
	 * @param int $user_id 
	 * @return bool processed status
	 */
	function process_rebill_status($user_id=NULL){
		// return
		return false;
	}
	
	/**
	 * API method process cancel, callback for payment cancel
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none
	 * @uses cancel_url 
	 */
	function process_cancel(){
		// return
		return false;
	}
	
	/**
	 * API method process unsubscribe, callback for unsubscribe process, via API or manual delete
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none
	 */
	function process_unsubscribe(){
		// return
		return false;
	}
	
	/**
	 * API method html redirect, callback proxy for html redirect
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none 
	 */
	function process_html_redirect(){
		// return
		return false;
	}
	
	/**
	 * API method process credit card, callback proxy for credit card gateway, send API request from MGM
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none	 
	 */
	function process_credit_card(){
		// retturn
		return false;
	}	
	
	/**
	 * API method subscribe button, callback for generating subscription payment button
	 *
	 * to be overriden in module	 
	 *
	 * @param array $options
	 * @return none	 
	 */
	function get_button_subscribe($options=array()){
		// override this
		return false;
	}
	
	/**
	 * API method post purchase button, callback for generating post purchase payment button
	 *
	 * to be overriden in module	 
	 *
	 * @param float $cost
	 * @param string $title
	 * @param bool $return
	 * @return none	 
	 */
	function get_button_buypost($cost, $title, $return = false) {
		// override this
		return false;
	}
	
	/**
	 * API method unsubscribe button, callback for generating unsubscribe button
	 *
	 * to be overriden in module	 
	 *
	 * @param array $options
	 * @return string $html	 
	 */
	function get_button_unsubscribe($options=array()){
		// action
		$action = add_query_arg(array('method'=>'payment_unsubscribe'), mgm_home_url('payments'));
		// message
		$message = sprintf(__('If you wish to unsubscribe from <b>%s</b>, please click the following link. You have to manually unsubscribe from any payment gateway you used while signup.','mgm'),get_option('blogname'));
		// override this
		$html='<div class="mgm_margin_bottom_10px">
					<h4>'.__('Unsubscribe','mgm').'</h4>
					<div class="mgm_margin_bottom_10px">' . $message . '</div>
				</div>
				<form name="mgm_unsubscribe_form" id="mgm_unsubscribe_form" method="post" action="'. $action .' ">
					<input type="hidden" name="user_id" value="' . $options['user_id'] . '"/>
					<input type="hidden" name="membership_type" value="' . $options['membership_type'] . '"/>
					<input type="button" name="btn_unsubscribe" value="' . __('Unsubscribe','mgm') . '" onclick="confirm_unsubscribe(this)" class="button" />
				</form>';
		// return
		return $html;		
	}
	
	/**
	 * API method subscribe buttons, callback for generating subscribe buttons
	 *
	 * @param array $options
	 * @return none
	 * @deprecated	 
	 */
	function get_buttons($options=array()){
		// override this
		return false;
	}
	
	/**
	 * API method get activation links, callback for generating activation links
	 *
	 * @param array $options
	 * @return none 
	 * @deprecated	  
	 */
	function get_activation_links(){
		// override this
		return false;
	}
	
	/**
	 * API method check module dependency, callback for checking module dependency
	 *
	 * to be overriden in module	 
	 *
	 * @param array $options
	 * @return none	 
	 */
	function dependency_check(){
		// override this
		return false;
	}	
	
	/**
	 * API method module transaction info, callback for printing module transaction info
	 *
	 * to be overriden in module	 
	 *
	 * @param array $options
	 * @return none 
	 */
	function get_transaction_info($member, $date_format){
		// info
		$info = sprintf('<b>%s:</b>', sprintf(__('%s INFO','mgm'), strtoupper($this->module)));					
		// set
		$transaction_info = sprintf('<div class="overline">%s</div>', $info);
		// return 
		return 	$transaction_info;		
	}
	
	/**
     * API method module get tracking fields for sync
	 *
	 * @param none
	 * @return boolean 
	 */
	function get_tracking_fields_html(){
	 	// return
	 	return false; 			
	}
	 
	/**
	 * API method module update tracking fields for sync
	 *
	 * @param array $post_data
	 * @param object $member	  
	 * @return boolean 
	 * @uses _save_tracking_fields()
	 */
	function update_tracking_fields($post_data, &$member){
	 	// return
	 	return false; 			
	}
	 
	/**
	 * Specifically check recurring status of each rebill for an expiry date
	 * Eg: Eway
	 *
	 * @param int $user_id
	 * @param object $member
	 * @return boolean
	 * @todo move function to new api method process_rebill_status
	 */
	function query_rebill_status($user_id, $member=NULL) {		
		return false;// default to false to skip normal modules
	}

	/**
	 * This function needs to be overridden
	 *
	 * @param unknown_type $trans_ref
	 * @param unknown_type $user_id
	 * @param unknown_type $subscr_id
	 */
	function cancel_recurring_subscription($trans_ref = null, $user_id = null, $subscr_id = null) {
		return true;
	}

	// helpers ----------------------------------

	/**
	 * check module enabled status
	 * 
	 * @param none
	 * @return bool
	 */
	function is_enabled(){
		// return true|false on enabled
		return bool_from_yn($this->enabled);
	}

	/**
	 * alias of query_rebill_status()
	 * @deprecated 
	 */
	function is_rebill_occured($user_id, $member=NULL) {
	 	return $this->query_rebill_status($user_id, $member);	
	}
	
	/**
	 * check if product mapping required
	 *
	 * @param none
	 * @return boolean 
	 */
	function has_product_map(){
	 	// return
	 	return bool_from_yn( $this->requires_product_mapping );
	}
	 
	 /**
      * check if rebill status check
	  *
	  * @param none
	  * @return boolean 
	  */
	 function is_rebill_status_check_supported(){
	 	// return
	 	return bool_from_yn( $this->supports_rebill_status_check );
	 }
	 
	 /**
      * check if virtual payment module
	  *
	  * @param none
	  * @return boolean 
	  */
	 function is_virtual_payment(){
	 	// return
	 	return bool_from_yn( $this->virtual_payment );
	 }
	 
	 /**
      * check if module supports a button
	  *
	  * @param string $button
	  * @return boolean 
	  */
	 function is_button_supported($button){
	 	// return
	 	return (in_array($button, $this->supported_buttons)) ? true : false;
	 }
	 
	 /**
      * check if module supports trial subscription
	  *
	  * @param void
	  * @return boolean 
	  */
	 function is_trial_supported(){
	 	// return
	 	return bool_from_yn( $this->supports_trial );
	 }
	 
	 /**
      * check if module supports cancellation
	  *
	  * @param void
	  * @return boolean 
	  */
	 function is_cancellation_supported(){
	 	// return
	 	return bool_from_yn( $this->supports_cancellation );
	 }
	 
	 /**
      * check if hosted payment
	  *
	  * @param none
	  * @return boolean 
	  */
	 function is_hosted_payment(){
	 	// return
	 	return bool_from_yn( $this->hosted_payment );
	 }
	 
	 /**
      * check if webhook called by specified source
	  *
	  * @param string $called_by
	  * @return boolean 
	  */
	 function is_webhook_called_by($called_by){
		// set
		$webhook_called_by = (isset($this->webhook_called_by) ? $this->webhook_called_by : 'merchant');
		// return	
		return ($called_by == $webhook_called_by);
	 }

	 /**
      * check if webhook called by specified source
	  *
	  * @param string $called_by
	  * @return boolean 
	  */
	 function set_webhook_called_by($called_by='merchant'){
		// set
		$this->webhook_called_by = $called_by;		
	 }

	// internal private helpers -------------------------------------------------------
	
	/**
	 * API method module default settings, callback to setup default settings data
	 *
	 * to be overriden in module	 
	 *
	 * @param array $options
	 * @return none 
	 */
	function _default_setting(){
		// override this
		return false;
	}
	
	/**
	 * API method log transaction data
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none
	 */
	function _log_transaction(){
		// return
		return false;
	}
	
	/**
	 * API method get button code
	 *
	 * to be overriden in module	 
	 *
	 * @param array $pack
	 * @param int $tran_id
	 * @return string $html
	 */
	function _get_button_code($pack, $tran_id=NULL){
		// return
		return false;
	}
	
	/**
	 * API method get button data
	 *
	 * to be overriden in module	 
	 *
	 * @param array $pack
	 * @param int $tran_id
	 * @return array $data
	 */
	function _get_button_data($pack, $tran_id=NULL){
		// return
		return false;
	}
	
	/**
	 * API method buy post/content process
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none
	 */
	function _buy_post(){
		// return
		return false;
	}
	
	/**
	 * API method buy membership/subscription process
	 *
	 * to be overriden in module	 
	 *
	 * @param none
	 * @return none
	 */
	function _buy_membership(){
		// return
		return false;
	}
	
	/**
	 * API method log cancel membership/subscription
	 *
	 * to be overriden in module	 
	 *
	 * @param int $user_id
	 * @return none
	 */
	function _cancel_membership($user_id = NULL){
		// return
		return false;
	}
	
	/**
	 * API method get gateway endpoint, callback to get gateway endpoint
	 *
	 * @param bool $type
	 * @param bool $permalink include permalink
	 * @return string $endpoint	 
	 */
	function _get_endpoint($type = false, $permalink = true){
		// status
		$type = ($type===false) ? $this->status : $type;
		// type/status
		switch($type){
			case 'test':
				// is test available
				if(isset($this->end_points['test']) && $this->end_points['test'] !== FALSE) 
					return $this->end_points['test'];	
							
			// else it will pick live	
			case 'live':
				return $this->end_points['live']; // live
			break;
			case 'credit_card': // credit_card process proxy
			case 'html_redirect': // html_redirect proxy
			case 'cancel': // transaction cancel
			case 'return':// manualpay proxy
				// if on wordpress page or custompage	
				$post_id = get_the_ID();
				// in post
				if($post_id && $permalink){		
					// payments url
					$payments_url = get_permalink($post_id);								
				}else if($transactions_url = $this->_get_transactions_url()){
					// payments url
					$payments_url = $transactions_url;	
				}else{
					// payments url
					$payments_url = mgm_home_url('payments');
				}
				
				// return
				return add_query_arg(array('module'=>$this->code, 'method'=>'payment_' . $type), $payments_url);
			break;
			default:
			// dynamic
				if(isset($this->end_points[$type])) return $this->end_points[$type];		
			break;
		}	
		
		// error
		return '#';
	}
	
	/**
	 * API method set gateway endpoint, callback to set gateway endpoint
	 *
	 * @param bool $type
	 * @return none
	 */
	function _set_endpoint($status, $endpoint){
		// status
		$this->end_points[$status] = $endpoint;	
	}
	
	/**
	 * API method set gateway endpoints, callback to set gateway endpoints
	 *
	 * @param array $endpoints
	 * @return none
	 */
	function _set_endpoints($endpoints){
		// loop
		foreach($endpoints as $status => $endpoint){
			// set
			$this->_set_endpoint($status, $endpoint);	
		}
	}
	
	/**
	 * API helper method validate membership type, validateand return membership type
	 *
	 * @param string $membership_type
	 * @param string $type encryption md5|plain|both
	 * @return string $match
	 */
	function _validate_membership_type($membership_type, $type='md5') {
		// packs
		$packs = mgm_get_class('subscription_packs');
		// loop
		foreach ($packs as $i=>$pack) {
			// loop
			foreach ($pack as $j=>$apack) {
				// type
				$raw_mt = $apack['membership_type'];
				
				// md5
				if (preg_match('/md5/i',$type)) {
					// encrypt
					$apack['membership_type_md5'] = md5($apack['membership_type']);
					
					// match
					if (strtolower($apack['membership_type_md5']) == strtolower($membership_type) ) {
						$match = $raw_mt;
						break;
					}
				}
				
				// plain
				if (preg_match('/plain/i',$type)) {
					// match
					if (strtolower($apack['membership_type']) == strtolower($membership_type) ) {
						$match = $raw_mt;
						break;
					}
				}
			}
			// match
			if ($match) {
				break;
			}
		}
		// return
		return $match;
	}
	
	/**
	 * API helper method get user data, get user data
	 *
	 * @param int $user_id
	 * @return array $user
	 */
	function _get_user($user_id){
		// get user
		$user = mgm_get_userdata((int) $user_id);						
		// check null
		if(is_null($user)){	
			// error	
			_e('User cannot be found.','mgm');
			// return
			return false;
		}
		// send user
		return $user;
	}
	
	/**
	 * API helper method redirect
	 *
	 * @param array $arg
	 * @return none
	 */
	function _redirect($arg=false){
		// add arg	
		if(is_array($arg))
			$redirect = add_query_arg(array('status'=>'success'), $this->setting['processed_url']);
		else
			$redirect = $this->setting['processed_url'];	
		
		// redirect			
		mgm_redirect($redirect);	
	}
	
	// transactions related -------------------------------------------------------
	
	/**
	 * API helper method create transaction
	 *
	 * @param array $data pack data
	 * @param array $options
	 * @return int $transaction_id
	 * @deprecated 
	 * @see mgm_add_transaction()
	 */
	function _create_transaction($data, $options = NULL){
		// user helper
		return mgm_add_transaction($data, $options);	
	}
	
	/**
	 * API helper method update transaction
	 *
	 * @param array $data column data
	 * @param int $id
	 * @return int $affected
     * @deprecated 
	 * @see mgm_update_transaction()
	 */
	function _update_transaction($data, $id){
		// return
		return mgm_update_transaction($data, $id);
	}
	
	/**
	 * API helper method update transaction status
	 *
	 * @param int $id
	 * @param string $status
	 * @param string $status_text
	 * @return int $affected
	 * @deprecated 
	 * @see mgm_update_transaction_status()
	 */
	function _update_transaction_status($id, $status, $status_text){
		// return
		return mgm_update_transaction_status($id, $status, $status_text);					
	}
	
	/**
	 * API helper method get transaction data
	 *
	 * @param int $id
	 * @return array $transaction
	 * @deprecated 
	 * @see mgm_get_transaction()
	 */
	function _get_transaction($id){
		// return
		return mgm_get_transaction($id);
	}
	
	/**
	 * API helper method get transaction payment type
	 *
	 * @param int $id
	 * @return string $payment_type
	 * @deprecated 
	 * @see mgm_get_transaction_payment_type()
	 */
	function _get_transaction_type($transaction_id){
		// return 
		return mgm_get_transaction_payment_type($id);					
	}
	
	/**
	 * API helper method add transaction option
	 *
	 * @param array $data
	 * @return int $id
	 * @deprecated 
	 * @see mgm_add_transaction_option()
	 */
	function _add_transaction_option($data){
		// return 
		return mgm_add_transaction_option($data);	
	}
	
	/**
	 * API helper method get transaction data by option
	 *
	 * @param string $option_name
	 * @param string $option_value
	 * @return array $transaction
	 * @deprecated 
	 * @see mgm_get_transaction_by_option()
	 */
	function _get_transaction_by_option($option_name,$option_value){
		// return 
		return mgm_get_transaction_by_option($option_name,$option_value);		
	}
	
	/**
	 * API helper method check if new style transaction process using database
	 *
	 * @param mixed string or int $passthrough
	 * @return bool
	 */
	function _is_transaction($passthrough){
		// we are using transaction id as custom var
		return ((int)$passthrough > 0 && preg_match('/^(buypost|subscription)_/', $passthrough) == FALSE);
	}
	
	/**
	 * API helper method get custom passthrough, consider both old style string with underscore separator and new style transaction id
	 *
	 * @param mixed string or int $passthrough
	 * @param bool verify
	 * @return array 
	 */
	function _get_transaction_passthrough($passthrough, $verify=true){
		// int
		$custom = false;
		// buy post
		if(is_string($passthrough) && preg_match('/^buypost_/', $passthrough)){
			// unset
			unset($custom);
			// init
			$custom = array('payment_type'=>'post_purchase');
			// split
			list($custom['duration'], $custom['cost'], $custom['currency'], $custom['user_id'], $custom['post_id'], $custom['user_id'], $custom['client_ip']) = explode('_', preg_replace('/^buypost_/', '', $passthrough));
		}else if(is_string($passthrough) && preg_match('/^subscription_/', $passthrough)){	
		// subscription	    
			// unset
			unset($custom);
			// init
			$custom = array('payment_type'=>'subscription_purchase');
			// split
			list($custom['duration'], $custom['amount'], $custom['currency'], $custom['user_id'], $custom['membership_type'], $custom['duration_type'], $custom['role'], $custom['client_ip'], $custom['hide_old_content'], $custom['pack_id']) = explode('_', preg_replace('/^subscription_/', '', $passthrough));
		}else{
		// new 
			if($this->_is_transaction($passthrough)){
				// fetch
				$transaction = mgm_get_transaction($passthrough);
				// check
				if(isset($transaction['id'])){
					// unset
					unset($custom);
					// set
					$custom = array_merge(array('payment_type'=>$transaction['payment_type']),$transaction['data']);
					// rename some fields for backward compatibility
					$custom['amount'] = $custom['cost'];
					// post id
					if(!isset($custom['pack_id']) && isset($custom['id'])) $custom['pack_id'] = $custom['id'];
				}
			}
		}		
		
		// verify, if not parsed, treat as error
		if($custom === false && $verify == true){
			// notify
			@mgm_notify_admin_passthrough_verification_failed($passthrough, $this->module);
			// abort
			exit();
		}		
			
		// return
		return $custom;			
	}
	
	/**
	 * API method verify payment data
	 *
	 * @param string $passthrough
	 * @return bool
	 * @deprecated
	 */
	function _verify_transaction($passthrough){
		// return
		return true;		
	}	
	
	/**
	 * API helper method set payment type, used as wrapper for backward compatibility
	 *
	 * @param array $pack
	 * @param string $currency
	 * @return string $payment_type
	 * @deprecated
	 */
	function _set_payment_type($pack, $currency=NULL){	
		// encript membership_type		
		$membership_type = md5($pack['membership_type']);
		// user
		$user_id = mgm_get_user_id();
		// currency
		if(!$currency) $currency = mgm_get_class('system')->get_setting('currency');
		// ip address
		$ip_address = mgm_get_client_ip_address();
		// custom string
		if(isset($pack['buypost'])){
			// get_the_ID()
			$payment_type = implode('_', array('buypost', $pack['duration'], $pack['cost'], $currency, $user_id, $pack['post_id'], $ip_address));
			// 'buypost_' . $pack['duration'] .'_'. $pack['cost'] .'_'. $currency .'_'. $user_id .'_' . $pack['post_id'] 
			// . '_' . mgm_get_client_ip_address() ;
		}else{		
			$payment_type = implode('_', array('subscription', $pack['duration'], $pack['cost'], $currency, $user_id, $membership_type, 
			                                   strtoupper($pack['duration_type']), $pack['role'], $ip_address, (int)$pack['hide_old_content'], 
											   (int)$pack['id']));
			// 'subscription_' . $pack['duration'] .'_'. $pack['cost'] .'_'. $currency .'_'. $user_id .'_'. $membership_type . '_'. strtoupper($pack['duration_type']) 
			// . '_' . $pack['role'] . '_' . mgm_get_client_ip_address() . '_' . (int)$pack['hide_old_content']. '_' . (int)$pack['id'];
		}
		
		// return
		return $payment_type;
	}
	
	/**
	 * API helper method get payment type, used as wrapper for backward compatibility
	 *
	 * @param string $passthrough
	 * @return string $payment_type
	 * @deprecated
	 */
	function _get_payment_type($passthrough){
		// buy post : backward compatibility
		if(is_string($passthrough) && preg_match('/^buypost_/', $passthrough)){
			return 'buypost';
		}else if(is_string($passthrough) && preg_match('/^subscription_/', $passthrough)){
		// subscription : backward compatibility
		    return 'subscription';	
		}else{
			// new
			if($this->_is_transaction($passthrough)){
				// type
				$transaction_type = mgm_get_transaction_payment_type($passthrough);
				// check
				if(isset($transaction_type)){
					// set
					return $transaction_type;
				}
			}			
		}		
		// return other
		return 'other';	 
	}
	
	/**
	 * API helper method create order
	 *
	 * @param array $pack
	 * @param int $user_id
	 * @return int $id
	 * @deprecated
	 */
	function _create_order($pack,$user_id){	
		// check		
		if ($pack['buypost'] == 1 ) {
			if (isset($pack['ppp_pack_id'])) {		
				$post_id = $pack['ppp_pack_id'];
			} else {
				$post_id = get_the_ID();	
			}		
			return 	$user_id . $post_id;
		} else{
			return $user_id;
		}
	}	
	
	/**
	 * API helper method get post purchase redirect, after purchase, redirect to post
	 *
	 * @param string $passthrough
	 * @return string $url
	 */
	function _get_post_redirect($passthrough){
		// get custom
		$custom = $this->_get_transaction_passthrough($passthrough, false);
		
		// check
		// if ( isset($custom['payment_type']) && $custom['payment_type'] == 'post_purchase' ) {// allow register & purchase
		if ( (isset($custom['payment_type']) && $custom['payment_type'] == 'post_purchase') || isset($custom['postpack_post_id'])) {// allow register & purchase	
			// extract
			extract($custom);
			// check if its a postpack
			if(isset($postpack_post_id) && (int)$postpack_post_id>0){
				$permalink = get_permalink($postpack_post_id);
			// check if its a series of posts, mgm 1.0 support	
			}else if (strpos($post_id, ',') !== false) {
				// is pack, get first
				$posts = explode(',', $post_id); unset($post_id);
				// get first
				$post_id = array_shift($posts);
				// return
				$permalink =  get_permalink($post_id);
			// check a single post purchased, as per iss#715 in mind, not sure why it was removed and by whom?	
			}else if (isset($post_id) && (int)$post_id > 0) {
				$permalink = get_permalink($post_id);
			} 				
			
			// guest token arg
			if(isset($custom['guest_token'])){
				$permalink = add_query_arg(array('guest_token' => $custom['guest_token']), $permalink);
			}	

			// return 
			return $permalink;
		}
		// nothing
		return false;	
	}
	
	/**
	 * API helper method setup calback messages
	 *
	 * @param array $setting
	 * @param bool $use_global_message
	 * @return none
	 */
	function _setup_callback_messages($setting=array(),$use_global_message=false){
		// system
		$system_obj = mgm_get_class('system');		
		// keys
		$msg_keys = array('success_title','success_message','failed_title','failed_message');
		// take global message/ TODO, update settings page
		if(bool_from_yn(mgm_post_var('use_global_message','N')) || $use_global_message == true){
			// loop
			foreach($msg_keys as $msg_key){
				// set
				$this->setting[$msg_key] = $system_obj->get_template('payment_'.$msg_key);// the raw format, without urls parsed
			}	
		}else{
		// messages from post
			// loop
			foreach($msg_keys as $msg_key){
				// set
				$this->setting[$msg_key] = (isset($setting[$msg_key]) && !empty($setting[$msg_key])) ? $setting[$msg_key] :  $system_obj->get_template('payment_'.$msg_key, array(), true);
			}			
		}	
	}
	
	/**
	 * API helper method setup calback urls
	 *
	 * @param array $setting
	 * @return none
	 */
	function _setup_callback_urls($setting=array()){		
		// urls keys
		$url_keys = array('notify_url'        => 'payment_notify', // payment notify/ipn etc. background	
						  'status_notify_url' => 'payment_status_notify', // payment ins etc. background
						  'return_url'        => 'payment_return',// payment return, link back
						  'cancel_url'        => 'payment_cancel',// payment cancel, link back	
						  'processed_url'     => 'payment_processed',// payment processed, thank you,cancel, failure urls
						  'thankyou_url'      => 'payment_processed');// customizable thankyou url
		// loop
		foreach($url_keys as $url_key=>$callback){			
			// set in POST
			if(isset($setting[$url_key]) && !empty($setting[$url_key])){
				// set
				$this->setting[$url_key] = $setting[$url_key];
			}else{
				// on key
				switch($url_key){
					case 'notify_url' :
					case 'status_notify_url' :
					case 'return_url' :
						$payments_baseurl = mgm_home_url('payments');
					break;
					default;
						// first check module thankyou
						if($transactions_url = $this->_get_transactions_url()){
							// thankyou_url url
							$payments_baseurl = $transactions_url;			
						}else{
							$payments_baseurl = mgm_home_url('payments');
						}
					break;
				}
				// set
				$this->setting[$url_key] = add_query_arg(array('module'=>$this->code,'method'=>$callback), $payments_baseurl);
			}
		}	
	}	
	
	/**
	 * API helper method reset calback urls
	 *
	 * @param none
	 * @return none
	 */
	function _reset_callback_urls(){				
		// reset
		$this->_setup_callback_urls();
	}
	
	/**
	 * API helper method get custom thankyou url
	 *
	 * @param bool $query_arg
	 * @return string $url
	 */
	function _get_thankyou_url($query_arg=true){		
		// first check module thankyou
		if(isset($this->setting['thankyou_url']) && !empty($this->setting['thankyou_url'])){
			// thankyou_url
			$thankyou_url = $this->setting['thankyou_url'];		
		// next check system transactions url				
		}else if($transactions_url = $this->_get_transactions_url()){
			// transactions_url
			$thankyou_url = $transactions_url;					
		// default processed url
		}else{		
			// processed_url
			$thankyou_url = $this->setting['processed_url'];
		}
		// return
		return (!$query_arg) ? $thankyou_url : add_query_arg(array('module'=>$this->code,'method'=>'payment_processed'), $thankyou_url);
	}	
	
	/**
	 * API helper method get custom transactions page url
	 *
	 * @param none
	 * @return string $url
	 */
	function _get_transactions_url(){
		// system
		$system_obj = mgm_get_class('system');	
		// first check module thankyou
		if(isset($system_obj->setting['transactions_url']) && !empty($system_obj->setting['transactions_url'])){
			return $system_obj->setting['transactions_url'];			
		}		
		// none
		return '';
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
			// pack desc			
			$purchase_desc = sprintf( '<div class="ccfields_pack_desc">%s</div>', $packs_obj->get_pack_desc($pack) );	
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
		$html = $purchase_desc;
		// billing_info
		if(!empty($address_fields['fields'])){
			// get mgm_form_fields generator
			$form_fields = new mgm_form_fields();
			// info
			$billing_info = sprintf('<h2>%s</h2><br>', __('Billing Info','mgm'));
			// check
			if(isset($address_fields['fields'])){
				// row template
				$row_template = '';
				$req_tag = "<span class='required'>*</span>";
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
						$row_template = "<p><label for='%s'>%s %s</label><br />%s</p>";
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
						$type_class .= ' {required: true}';									
						// reset to skip default required class
						$field['attributes']['required'] = false;
					}
					// set class
					$field['attributes']['class'] = $type_class;					
					// elem
					$elem = $form_fields->get_field_element($field, 'mgm_payment_field', $value);
					// form 
					$form_html .= sprintf($row_template, $field['name'], $field['label'], $req, $elem);				
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
		$html .= mgm_get_include(MGM_CORE_DIR . sprintf('html/payment_cc_form_%s.php', $html_type), array('data'=>$data));
		
		// cc form
		$cc_form = sprintf("<div id='%s_form_cc' class='ccfields ccfields_block_left'>%s</div>", $this->code, $html);
		
		// apply filter
		return apply_filters('mgm_cc_form_html', $cc_form, $this->code, $data);
	}
	
	/**
	 * API helper method get address fileds
	 * 
	 * @param object $user data
	 * @param string $return (captured|fields|both)
	 * @return array assoc array
	 */
	function _get_address_fields($user, $return='captured'){
		// member_custom_fields
		if(isset($user->ID) && (int)$user->ID > 0) {
			$member_custom_fields = mgm_get_member($user->ID)->custom_fields;			
		}else{
			$member_custom_fields = new stdClass();
		}
		// user fields
		$uf_on_paymentpage = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_payment'=>true)));	
		// fields only
		if($return == 'fields') return $uf_on_paymentpage;					
		// address_fields
		$address_fields = array();
		// found some
		if($uf_on_paymentpage){
			// loop
			foreach($uf_on_paymentpage as $uf){	
				// field
				$field = $uf['name'];			
				// check
				if(isset($member_custom_fields->{$field})){
					// check
					if($field_value = $member_custom_fields->{$field}){
						$address_fields[$field] = $field_value;
					}
				}
			}
		}	
		// return 
		return ($return == 'both') ? array('fields'=>$uf_on_paymentpage, 'captured'=>$address_fields) : $address_fields;		
	}
	
	/**
	 * API helper method set address fields
	 *
	 * @param object $user
	 * @param array $data	 
	 * @param array $mapping	
	 * @param string $callback	 
	 * @return none
	 */
	function _set_address_fields($user, &$data, $mapping, $callback=NULL){	
		// get address_fields
		$address_fields = $this->_get_address_fields($user);
		// consider post alter, #762
		if(isset($_POST['mgm_payment_field']) && is_array($_POST['mgm_payment_field'])){			
			// merge
			$address_fields = array_merge($address_fields, $_POST['mgm_payment_field']);
		}
				
		// loop custom fields map
		foreach($mapping as $custom_field=>$payment_field){
			// string 
			if(is_string($payment_field) ){
				// set
				if(isset($address_fields[$custom_field])){
					// filter
					$value = ($callback) ? call_user_func_array($callback, array($custom_field, $address_fields[$custom_field])) : $this->_address_field_filter($custom_field,$address_fields[$custom_field]);
					// set
					$data[$payment_field] = $value;
				}
			}else if(is_array($payment_field)){
				// array for address
				$uf_values = explode("\n", $address_fields[$custom_field]);				
				// loop
				foreach($payment_field as $pf){
					// set
					if($uf_value = array_shift($uf_values)){
						$data[$pf] = substr($uf_value, 0, 64);// take 64 chars only per line
					}	
				}
			}	
		}
		
		// concat name
		if(isset($mapping['full_name'])){		
			// value
			$value = ($user->first_name) ? mgm_str_concat($user->first_name,$user->last_name): $user->display_name;;
			// filter			
			$value = ($callback) ? call_user_func_array($callback, array('full_name', $value)) : $this->_address_field_filter('full_name', $value);		
			// set
			$data[$mapping['full_name']] = $value;		
		}
	}
	
	/**
	 * API helper method address fields filter, can be overridden for apply gateway specific filters/limits
	 *
	 * @param string $name
	 * @param string $value	 
	 * @return string $value
	 */
	function _address_field_filter($name, $value){
		// trim space
		$value = trim($value);
		// apply filter
		switch($name){
			case 'full_name':
				// trim chars
				$value = substr($value, 0, 40);
			break;
			case 'address':								
				// trim chars
				$value = substr($value, 0, 255);
			break;
			case 'zip':
				// trim chars
				$value = substr($value, 0, 12);
			break;
			case 'phone':
				// trim chars
				$value = substr($value, 0, 20);
			break;
			case 'first_name':
				// trim chars
				$value = substr($value, 0, 40);
			break;
			case 'last_name':
				// trim chars
				$value = substr($value, 0, 40);
			break;
			case 'city':
				// trim chars
				$value = substr($value, 0, 40);
			break;
			case 'state':
				// trim chars
				$value = substr($value, 0, 2);
			break;
			default:
				// trim chars
				$value = substr($value, 0, 50);
			break;	
		}
		// return
		return $value;
	}	
	
	/**
	 * API helper method set default email
	 *
	 * @param array $post_data
	 * @param string $field	 
	 * @return none
	 */
	function _set_default_email(&$post_data, $field='x_email'){
		// check
		if(empty($post_data[$field])){
		// check
			if(isset($post_data['user_email'])){
				$post_data[$field] = $post_data['user_email'];
			}
		}
	}
	
	/**
	 * API helper method set post as purchased
	 *
	 * @param int $user_id
	 * @param int $post_id	
	 * @param string $guest_token	 
	 * @param int $tran_id	
	 * @return void
	 */
	function _set_purchased($user_id=NULL, $post_id, $guest_token=NULL, $tran_id=0, $coupon_code=NULL){
		global $wpdb;
		//if we are looking at a pack then explode the buy post item number and loop through it
		if (strpos($post_id, ',') !== false) {
			$posts = explode(',', $post_id);
		} else {
			$posts = array($post_id);
		}
		
		// create unique
		$posts = array_unique($posts);
		
		// insert
		foreach ($posts as $post_id) {
			// sql
			if($guest_token){
				$sql = $wpdb->prepare("REPLACE INTO `" . TBL_MGM_POST_PURCHASES . "` (guest_token, post_id, transaction_id, guest_coupon, purchase_dt) VALUES (%s, %d, %d, %s, NOW())", $guest_token, $post_id, $tran_id, $coupon_code);
			}else{
				$sql = $wpdb->prepare("REPLACE INTO `" . TBL_MGM_POST_PURCHASES . "` (user_id, post_id, transaction_id, purchase_dt) VALUES (%d, %d, %d, NOW())", $user_id, $post_id, $tran_id);
			}	
			// insert
			$wpdb->query($sql); //insert the post purchased record
		}
		
		// addon 
		if($tran_id > 0){
			// fetch
			if($tran = mgm_get_transaction($tran_id)){
				// if set
				if(isset($tran['data']['addon_options']) && !empty($tran['data']['addon_options'])){					
					// loop 
					foreach($tran['data']['addon_options'] as $addon_option_id=>$addon_option){
						// user id
						if($user_id){
							$sql = $wpdb->prepare("REPLACE INTO `" . TBL_MGM_ADDON_PURCHASES . "` (user_id, addon_option_id, transaction_id, purchase_dt) VALUES (%d, %d, %d, NOW())", $user_id, $addon_option_id, $tran_id);
						}else{
							$sql = $wpdb->prepare("REPLACE INTO `" . TBL_MGM_ADDON_PURCHASES . "` (addon_option_id, transaction_id, purchase_dt) VALUES (%d, %d, NOW())", $user_id, $addon_option_id, $tran_id);							
						}	
						// insert
						$wpdb->query($sql); //insert the addon purchased record
					}
				}	
			}
		}
	}				
	
	/**
	 * API helper method auto login after subscribed
	 *
	 * @param int $user_id / transaction_id
	 * @return bool
	 */
	function _auto_login($id) {
		// get setting
		$setting = mgm_get_class('system')->get_setting();	
		// if autologin not enabled	
		if(!isset($setting['enable_autologin']) || (isset($setting['enable_autologin']) && !bool_from_yn($setting['enable_autologin']))) 
			return false;
		
		// check when enabled	
		if(is_numeric($id)) {
			// user id 
			$user_id = null;
			// get custom param
			$custom = $this->_get_transaction_passthrough($id);
			// verify if not subs purchase
			if($custom['payment_type'] != 'subscription_purchase' || (!isset($custom['is_registration']) || (isset($custom['is_registration']) && !bool_from_yn($custom['is_registration']) )))
				return false;
			
			// fetch user id	
			if(is_numeric($custom['user_id']) && $custom['user_id'] > 0 ) {
				// return id // #711, autologin is done in background
				mgm_auto_login($id);
				// redirect url for register & purchase	
				if(isset($custom['post_id'])){
					return get_permalink($custom['post_id']);
				}elseif(isset($custom['postpack_post_id']) && isset($custom['postpack_id']) ){
					return get_permalink($custom['postpack_post_id']);
				}else {
					return true;
				}	
			}						
		}
		// return false
		return false;
	}
	
	/**
	 * Confirm notify URL.
	 *
	 * related to issue#: 528
	 * As PAYPALPRO doesn't use overridden notifyurl, and posts IPN to default IPN settings URL on merchant panel
	 * Confirm module field in transactions table/mgm_member object
	 * 
	 */
	function _confirm_notify() {
		$mod_code = '';
		//check possible params for transaction id [rp_invoice_id, invoice, custom]
		if(isset($_POST['rp_invoice_id']) && is_numeric($_POST['rp_invoice_id'])) {
			$transaction_id = $_POST['rp_invoice_id'];
		}elseif (isset($_POST['invoice']) && is_numeric($_POST['invoice'])) {
			$transaction_id = $_POST['invoice'];
		}elseif (isset($_POST['custom']) && is_numeric($_POST['custom'])) {
			$transaction_id = $_POST['custom'];
		}elseif (isset($_POST['custom']) && !is_numeric($_POST['custom']) &&  preg_match('/^subscription_/', $_POST['custom'])) {
			//for backward compatibility:
			//transaction cannot be found for old users: 
			$transdata = $this->_get_transaction_passthrough($_POST['custom']);
			$member = mgm_get_member($transdata['user_id']);
			if(isset($member->payment_info->module) && !empty($member->payment_info->module)) {
				$mod_code = preg_match('/mgm_/', $member->payment_info->module) ? $member->payment_info->module : 'mgm_' .$transdata['module'];
			}
		}
		//if a transaction id is found
		if(isset($transaction_id)) {
			$transdata = mgm_get_transaction($transaction_id);
			if(!empty($transdata['module'])) {
				$mod_code = preg_match('/mgm_/', $transdata['module']) ? $transdata['module'] : 'mgm_' .$transdata['module'];  				
			}
		}
		//if module code is found and not belongs to current module, then invode process_notify() function of the applicable module.
		
		if(!empty($mod_code) && $mod_code != $this->code) {
			// recall process_notifyof the module
			// keep the log untill paypal is resolved.
			// mgm_log('FROM PAYMENT: recalling ' . $mod_code .'->process_notify() FROM: '. $this->code );	
			mgm_get_module($mod_code, 'payment')->process_notify();
			// return
			return false;
		}
		
		return true;
	}
	
	/**
	 * get transaction form passthrough id
	 *
	 * @param string $key
	 * @param array $source
	 * @return int $tran_id 
	 */
	function _get_transaction_id($key='custom', $source=null){
		// set source
		if( is_null($source) ) $source = $_POST; 
		// validate
		if( isset($source[$key]) ){
			// check
			if($this->_is_transaction($source[$key])){	
				// tran id
				return $tran_id = (int)$source[$key];
			}
		}		
		// return 
		return 0;	
	}

	/**
	  * get alternate transaction id from multiple sources
	  *
	  * @param void
	  * @return mixed $alt_tran_id
	  */
	function _get_alternate_transaction_id(){
		// var
		$tran_id = '';
		// post
		if(isset($_POST['custom']) && !empty($_POST['custom'])){
			$tran_id = $_POST['custom'];
		}elseif(isset($_GET['custom']) && !empty($_GET['custom'])){
			$tran_id = strip_tags($_GET['custom']);
		}elseif(isset($_POST['vendor_order_id']) && !empty($_POST['vendor_order_id'])){
			$tran_id = $_POST['vendor_order_id'];
		} 		
		// return 
		return $tran_id;
	}
	
	 /**
      * save tracking fields
	  *
	  * save default tracking fields posted/returned by gateway
	  *
	  * @param array $fields array('module_field' => 'post_field') 
	  * @param object $member
	  * @param array $data, default taken from POST
	  * @return boolean $updated
	  */
	 function _save_tracking_fields($fields, &$member, $data=NULL){
	 	// data
		if(!$data) $data = $_POST;		
		// updated
		$updated = 0;
		// object not initialized
		if(!isset($member->payment_info)) {
			$member->payment_info = new stdClass;					
		}	
		// module code
		if(!isset($member->payment_info->module)) {
			$member->payment_info->module = $this->code;
		}		
		// loop
		foreach( $fields as $m_var=>$p_var ){// module_var => post_var
			// array
			if(is_array($p_var)){
				// loop
				foreach($p_var as $p_v){
				// check
					if(isset($data[$p_v]) && !empty($data[$p_v])){
						$member->payment_info->{$m_var} = $data[$p_v]; $updated++;
					}
				}
			}else{
				// check
				if(isset($data[$p_var]) && !empty($data[$p_var])){
					$member->payment_info->{$m_var} = $data[$p_var]; $updated++;
				}
			}	
		}	 
		
		// log
		// mgm_log($member->payment_info, __FUNCTION__);
		
		// return 
		return $updated;			
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
		}else if(isset($_GET['trans_ref'])){// encoded
			// set
			return $tran_id = mgm_decode_id(strip_tags($_GET['trans_ref']));
		}	
		// error
		return false;
	 }
	 	 
	 /**
	  * apply addon if exists
	  *
	  * @param array $pack
	  * @param array $data
	  * @param array $fields
	  * @return void
	  */
	 function _apply_addons($pack, &$data, $fields=array()){
	 	// if set
	 	if(isset($pack['addon_options']) && !empty($pack['addon_options'])){
			// default
			$fields = array_merge(array('amount'=>'amount','description'=>'description'), $fields);
			// labe;s
			$option_labels = array();
			// loop
			foreach($pack['addon_options'] as $addon_option){
				// alter amount
				if(isset($data[$fields['amount']])){
					$data[$fields['amount']] += (float)$addon_option['price'];
				}
				// push to labels
				$option_labels[] = $addon_option['option'];
			}
			// set
			if(isset($data[$fields['description']])){
				$data[$fields['description']] .= sprintf(' ( %s : %s )', __('Addons','mgm'), implode(', ', $option_labels));
			}
		}
	 }
	 	 
	 
	
	// public ---------------------------

	/**
	 * Validate credit card fields.
	 * Modules can override this function
	 * @param unknown_type $calling_fun
	 * @return unknown
	 */
	function validate_cc_fields($calling_fun) {		
		// init error 
		$error = new WP_Error();
		
		// post
		$post = array();		
					
		// post data		
		$post['mgm_card_holder_name']  = mgm_post_var('mgm_card_holder_name');
		$post['mgm_card_number'] 	   = mgm_post_var('mgm_card_number');				
		$post['mgm_card_code'] 		   = mgm_post_var('mgm_card_code');
		$post['mgm_card_type'] 		   = mgm_post_var('mgm_card_type');		
		// card exp
		$post['mgm_card_expiry_month'] = mgm_post_var('mgm_card_expiry_month');
		$post['mgm_card_expiry_year']  = mgm_post_var('mgm_card_expiry_year');
		
		// trim
		$post = array_map('trim', $post);
		
		// check
		if(empty($post['mgm_card_holder_name'])) {
			$error->add('invalid_card_holder_name', __('<strong>ERROR</strong>: Invalid Card Holder Name', 'mgm'));
		}
		
		if(!is_numeric($post['mgm_card_number']) || (strlen($post['mgm_card_number']) > 16 && strlen($post['mgm_card_number']) < 13)) {
			$error->add('invalid_card_number', __('<strong>ERROR</strong>: Invalid Credit Card Number', 'mgm'));
		}
		
		if(!is_numeric($post['mgm_card_expiry_month']) || !is_numeric($post['mgm_card_expiry_year'])) {
			$error->add('invalid_expiry', __('<strong>ERROR</strong>: Invalid Credit Card Expiry', 'mgm'));
		}
		
		if(!is_numeric($post['mgm_card_code']) || (strlen($post['mgm_card_code']) > 4 && strlen($post['mgm_card_code']) < 3)) {
			$error->add('invalid_card_code', __('<strong>ERROR</strong>: Invalid CVV', 'mgm'));
		}
		
		if(empty($post['mgm_card_type'])) {
			$error->add('invalid_cctype', __('<strong>ERROR</strong>: Invalid Card Type', 'mgm'));
		}	
		
		// no error
		if($error->get_error_message() == ''){			
			return false;
		}
		
		// return
		return $error;	
	}

	/**
	 * Throw error if any error in Credit card processing
	 * @return WP_Error
	 */
	function throw_cc_error($error_string = null) {
		$error = new WP_Error();	
		$error_string = !is_null($error_string) ? $error_string : (isset($this->response['message_text']) && !empty($this->response['message_text']) ? $this->response['message_text'] : null );	
		if ($error_string) {
			$error->add('cc_error', sprintf(__('<strong>ERROR</strong>: %s', 'mgm'), $error_string));
		}
		
		//return $error; 
		return apply_filters( 'mgm_thrown_cc_error', $error, $error_string ); 
	}
	
	/**
	 * Wrapper function for validate_cc_fields
	 *
	 * @param unknown_type $calling_fun
	 */
	function validate_cc_fields_process($calling_func = 'process_html_redirect', $return = true) {
		$error_string = '';
		//Only if submitted from credit card form 
		if(isset($_POST['submit_from']) && $_POST['submit_from'] == $calling_func ) {
			$errors = $this->validate_cc_fields($calling_func);				
			if(is_wp_error($errors)) {			
				$error_string = mgm_set_errors($errors, true);
				
				/*if($return)
					return $error_string;
				else 
					echo $error_string;	*/
			}else {			
				//call process_credit_card:
				$errors = $this->process_credit_card();				
				if(is_wp_error($errors) && $errors->get_error_code()) {			
					$error_string = mgm_set_errors($errors, true);				
					/*if($return)
						return $error_string;
					else 
						echo $error_string;	*/
				}			
			}
		}
		
		// apply filter
		$error_string = apply_filters( 'mgm_validated_cc_fields_process', $error_string );
		
		// return
		if($return)
			return $error_string;

		// print
		echo $error_string;
	}

	 /**
	 * check payment notification email(acknowledgement) sent or not
	 *
	 * @param int $tran_id
	 * @return boolean
	 */
	function send_payment_email($tran_id) {			
		if(is_numeric($tran_id)) {			
			$trans = mgm_get_transaction($tran_id);			
			if((isset($trans['data']['payment_email']) && $trans['data']['payment_email'] == 0) || //first time
				( strtotime('now') >= strtotime('+1 day', strtotime( $trans['transaction_dt'] )) ) //to consider rebill
				) {					
					return true;
				}
		}elseif (!empty($tran_id))
			return true; //for backward compatibility 
			
		return false;	
	}
	
	/**
	 * Update payment notification email sent count  
	 *
	 * @param int $tran_id
	 */
	function update_paymentemail_sent($tran_id) {		
		if(!is_numeric($tran_id))
			return;
			
		$trans = mgm_get_transaction($tran_id);		
		if(!isset($trans['data']['payment_email']))
			$trans['data']['payment_email'] = 0;
			
		$trans['data']['payment_email'] = (int)$trans['data']['payment_email']+1;	
		// update	
		mgm_update_transaction(array('data'=>json_encode($trans['data']),'module'=>$this->module), $tran_id);
	}

	 /**
	  * get setting 
	  */
	 function get_setting($key=NULL, $default=false){
		// all
		if(!$key) return $this->setting;
		
		// check
		if(isset($this->setting[$key])) return $this->setting[$key];
		
		// error
		return $default;	
	 }	
	 
	 /**
	  * get item id from pack
	  *
	  * @param array $pack
	  * @param strin $field optional
	  * @return int $item_id
	  */
	 function get_pack_item($pack, $field=false){
	 	// name
		$name = sprintf('subscription for %s', get_option('blogname'));
	 	// init
		$item = array('id'=>mt_rand(8,12), 'name'=> $name, 'description'=>$name);		
	 	// id, post_id, postpack_id, pack_id, id
		if(isset($pack['buypost'])){
			$item['id'] = (isset($pack['postpack_id'])) ? $pack['postpack_id'] : $pack['post_id'];
		}else{			
			$item['id'] = (isset($pack['pack_id'])) ? $pack['pack_id'] : $pack['id'];
		}
		// name
		$item['name'] = (isset($pack['item_name']) ? $pack['item_name'] : mgm_get_class('system')->get_subscription_name($pack));
		// desc
		$item['description'] = (isset($pack['description']) ? $pack['description'] : $item['name']);
		// return 
		return $item;
	 }

  	 // private for module restructuring --------------------------------
	
	 /**
	  * Apply sync fix
	  *
	  * @param object $old_obj
	  * @return none
	  */
	 function apply_fix($old_obj){
		// to be copied vars
		$vars = array('button_type','status','settings_tab','description','logo','enabled','supported_buttons','supports_trial',
					  'supports_cancellation','requires_product_mapping','hosted_payment','end_points','setting');
		// set
		foreach($vars as $var){
			// var
			$this->{$var} = (isset( $old_obj->{$var} ) ) ? $old_obj->{$var} : '';
		}			
		// save
		$this->save();	
	 }
	
	 /**
	  * prepare save, define the object vars to be saved
	  *
	  * @param none
 	  * @return none
	  */
	 function _prepare(){		
		// init array
		$this->options = array();
		// to be saved
		$vars = array('button_type','status','settings_tab','description','logo','enabled','supported_buttons','supports_trial',
					  'supports_cancellation','requires_product_mapping','hosted_payment','end_points','setting');
		// set
		foreach($vars as $var){
			// var
			$this->options[$var] = $this->{$var};
		}		
	 }
	
	 /**
	  * serialize
	  *
	  * @param none
	  * @return none
	  */
	 function __toString(){
		return serialize($this);
	 }
}
// end of file core/libs/components/mgm_payment.php