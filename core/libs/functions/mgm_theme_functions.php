<?php  if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members theme functions
 *
 * @package MagicMembers
 * @subpackage Facebook
 * @since 2.6
 */
 
/**
 * get payment subscribe page title
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_payment_subscribe_page_title(){
	// default
	$page_title = __('Select Payment Gateway','mgm');
	// edit info
	if(isset($_GET['edit_userinfo'])){	
		$page_title = __('Edit Personal Information','mgm');
	}else{
	// post
		// mgm_pr($_POST);
		if(empty($_POST)){
			// action (complete_payment, upgrade, extend, purchase_another )
			if(isset($_GET['action']) ){
				// selected pack payemnt
				if($_GET['action'] == 'complete_payment' && !isset($_GET['show_other_packs'])){
					$page_title = __('Your Membership Package','mgm');
				}else{
				// select
					$page_title = __('Select Membership Package','mgm');
				}
			}	
		}	
	}	
	// return
	return apply_filters('mgm_payment_subscribe_page_title', $page_title);	
}

/**
 * get payment subscribe page html
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_payment_subscribe_page_html(){
	// attach scripts, returns
	do_action('mgm_attach_scripts');
	
	// content
	$html = sprintf('<p>%s</p>',__('You are already subscribed or an error occurred. Please contact an administrator for more information.','mgm'));
	
	// get user query string
	$user = mgm_get_user_from_querystring();
	// member
	if(isset($user) && is_object($user)) $member = mgm_get_member($user->ID);
	// action
	$action = mgm_get_var('action', '', true);				
	// print
	if (!empty($action) && count(mgm_get_class('membership_types')->membership_types) > 0):				
		// upgrade or complete
		if(in_array($action, array('upgrade', 'complete_payment'))):
			$html = mgm_get_upgrade_buttons(); 
		// extend	
		elseif($action == 'extend'):
			$html = mgm_get_extend_button();
		// extend	
		elseif($action == 'purchase_another'):
			$html = mgm_get_purchase_another_subscription_button();//TODO	
		// bad action	
		else:
			$html = sprintf('<p>%s</p>', sprintf(__('Error - Unknown action - "%s", Exiting...','mgm'), $action));						
		endif;											
	elseif ((isset($member) && is_object($member)) && in_array($member->status, array(MGM_STATUS_NULL, MGM_STATUS_EXPIRED, MGM_STATUS_TRIAL_EXPIRED))):
		$html = mgm_get_subscription_buttons($user, true);		
	elseif ((isset($member) && is_object($member)) && $member->status == MGM_STATUS_PENDING):
		$html = sprintf('<p>%s</p>',__('Error - Your subscription status is pending. Please contact an administrator for more information.','mgm'));
	endif;	
		
	// return
	return apply_filters('mgm_payment_subscribe_page_html', $html);
}	

/**
 * get payment processing page title
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_payment_processing_page_title(){
	// title
	$title = __('Processing Payment', 'mgm');
	
	// get module
	if($module = mgm_get_var('module', '', true)){
		// module
		if($module_obj = mgm_get_module($module, 'payment')){
			// onsite with credit cards, @todo add payflow iframe onsite
			if( !$module_obj->is_hosted_payment() ){			
				$title = __('Enter Credit Card Details', 'mgm');
			}	
		}
	}
	
	// return 
	return apply_filters('mgm_payment_processing_page_title', mgm_stripslashes_deep($title));
}

/**
 * get payment processing page html
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_payment_processing_page_html(){
	global $mgm_html_outout;
			
	// attach scripts, returns
	do_action('mgm_attach_scripts');
	
	// content
	$html = $mgm_html_outout;
		
	// return
	return apply_filters('mgm_payment_processing_page_html', $html);
}

/**
 * get payment processed page title
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_payment_processed_page_title(){
	// current module
	$module = strip_tags($_GET['module']);
	// check
	if (!mgm_is_valid_module($module) || empty($module)) {	
		// redirect		
		mgm_redirect($home_url);
	} 
	// system
	$system_obj = mgm_get_class('system');	
	// module object
	$module_object = mgm_get_module($module, 'payment');
	
	// get title
	if (!isset($_GET['status']) || $_GET['status'] == 'success') {	
		$title = ($module_object->setting['success_title'] ? $module_object->setting['success_title'] : $system_obj->get_template('payment_success_title', array(), true));
	} else if (!isset($_GET['status']) || $_GET['status'] == 'cancel') {	
		$title = __('Transaction cancelled','mgm');
	} else {	
		$title = ($module_object->setting['failed_title'] ? $module_object->setting['failed_title'] : $system_obj->get_template('payment_failed_title', array(), true));
	}
	
	// return
	return apply_filters('mgm_payment_processed_page_title', mgm_stripslashes_deep($title));
}

/**
 * get payment processed page html
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_payment_processed_page_html(){
	// home url
	$home_url = trailingslashit(get_option('siteurl'));	
	// current module
	$module = mgm_request_var('module', '', true);
	// check
	if (!mgm_is_valid_module($module) || empty($module)) {	
		// redirect		
		mgm_redirect($home_url);
	} 
	
	// init
	$html = '';
	// refresh wait time
	$refresh_wait_time = 5;//in seconds
	// redirect url
	$redirect_url = '';
	// redirect
	$do_redirect = true;
	// refresh header for post redirecr
	if(isset($_GET['post_redirect'])) {
		// redirect url
		$redirect_url = strip_tags($_GET['post_redirect']);		
	}elseif(isset($_GET['register_redirect'])) {	
		// redirect url, if 1/true, redirect to profile, else its register & redirect url
		if($_GET['register_redirect'] != 1 ){
			$redirect_url = strip_tags($_GET['register_redirect']);  
		}else{
			// auto login
			$system_obj = mgm_get_class('system');			
			//issue# 1392
			$current_user_id = get_current_user_id();
			// check if set
			if($autologin_redirect_url = $system_obj->get_setting('autologin_redirect_url')){
				$page_title   = '';
				$redirect_url = $autologin_redirect_url;
				//short code support
				if(!empty($current_user_id)) {					
					$user = get_userdata($current_user_id);
					$redirect_url = str_replace('[username]',$user->user_login,$redirect_url);
				}			
			}// check if set 	
			elseif  (mgm_get_user_package_redirect_url($current_user_id) && $current_user_id) {			
				$page_title   = '';
				$redirect_url = mgm_get_user_package_redirect_url($current_user_id);
			}else {
				$page_title   = 'Profile';
				$redirect_url = mgm_get_custom_url('profile');
			}
		}	
				
		// check not logged in, #948 paypal fails to redirect
		if(!is_user_logged_in()){		
			// user login
			if(isset($_GET['trans_ref']) ){				
				// re construct redirect url
				$redirect_url = mgm_get_custom_url('login', false, array('trans_ref'=> strip_tags($_GET['trans_ref']),'auto_login'=>true,'redirect_to'=>$redirect_url));
			}
		}		
	}		
	// check and set
	if(!empty($redirect_url) && $do_redirect){
		// alter
		$redirect_url = apply_filters('mgm_register_redirect', $redirect_url);	
		// no headers
		if(!headers_sent()){ 				
			@header(sprintf('Refresh: %d;url=%s', $refresh_wait_time, $redirect_url));
		}else{
			$html .= sprintf('<script language="javascript">window.setTimeout(function(){window.location.href="%s";}, %d)</script>', $redirect_url, (int)$refresh_wait_time * 5);	
		}	
	}	
	
	// module object
	$module_object = mgm_get_module($module, 'payment');
	// [domain]/subscribe/?method=payment_processed&module=mgm_paypal&status=success
	// [domain]/subscribe/?method=payment_processed&module=mgm_paypal&status=cancel
	// status and message
	$arr_shortcodes = array('transaction_amount' => '');
	// check
	if (!isset($_GET['status']) || $_GET['status'] == 'success') {			
		// mgm_replace_oldlinks_with_tag is a patch for replacing the old link
		$message = ($module_object->setting['success_message'] ? mgm_replace_oldlinks_with_tag($module_object->setting['success_message'], 'payment_success_message') : $system_obj->get_template('payment_success_message', array(), true));		
		// get price
		if(isset($_GET['trans_ref'])) {
			// tarns
			$_GET['trans_ref'] = mgm_decode_id(strip_tags($_GET['trans_ref']));
			// get transaction data
			$trans = mgm_get_transaction($_GET['trans_ref']);			
			// set amount
			if($trans['module'] == 'manualpay') {				
				$arr_shortcodes['transaction_amount'] = $trans['data']['cost'] .' '. $trans['data']['currency'];
			}
			// update googe analytics:
			$html .= apply_filters('mgm_payment_processed_page_analytics', $trans);// @todo, callback in template function
			// mgm_update_google_analytics($trans);	deprecated, use hook		
		}
	} else if (!isset($_GET['status']) || $_GET['status'] == 'cancel') {	
		// set message		
		$message = __('You have cancelled the transaction.','mgm');
	} else {	
		// mgm_replace_oldlinks_with_tag is a patch for replacing the old link
		$message = ($module_object->setting['failed_message'] ? mgm_replace_oldlinks_with_tag($module_object->setting['failed_message'], 'payment_failed_message') : $system_obj->get_template('payment_failed_message', array(), true));
	}
	
	// parse short codes:
	// [transaction_amount] = amount paid
	foreach ($arr_shortcodes as $code => $value) {
		$message = str_replace( '['.$code.']', $value, $message );
	}
	
	// html
	$html .= mgm_stripslashes_deep(mgm_get_message_template($message));
	// get error
	if (isset($_GET['errors'])) {
		// get errors
		$errors = explode('|', strip_tags($_GET['errors']));
		// html
		$html .= sprintf('<h3> %s </h3><div><ul>',  __('Messages', 'mgm'));
		// loop
		foreach ($errors as $error) {
			$html .= sprintf('<li> %s </li>', $error);
		}
		// end
		$html .= '</ul></div>';
	}	
		
	// auto redirect to post purchased
	if(isset($_GET['post_redirect'])){
		// message
		$m = sprintf(__('You will be automatically redirected to the post you purchased within %d seconds. Please <a href="%s"> click here </a> to go to the page. ', 'mgm'),$refresh_wait_time, strip_tags($_GET['post_redirect']));
		// set
		$html .= sprintf('<b>%s</b>', $m);  
	}elseif(isset($_GET['register_redirect'])) {// auto login redirect 
		// message
		$m = sprintf(__('You will be automatically redirected to your %s page within %d seconds. Please <a href="%s"> click here </a> to go to the page. ', 'mgm'), ($_GET['register_redirect'] == 1 ? __($page_title,'mgm') : __('Post','mgm')), $refresh_wait_time, $redirect_url);
		// set	
		$html .= sprintf('<b>%s</b>', $m);		
	}
		
	// return
	return apply_filters('mgm_payment_processed_page_html', $html);
}

/**
 * get post purchase page title
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_post_purchase_page_title(){
	// return
	return apply_filters('mgm_post_purchase_page_title', __('Select Payment Gateway','mgm'));
}

/**
 * get post purchase page html
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_post_purchase_page_html(){
	// html
	$html = mgm_get_post_purchase_buttons();
		
	// return
	return apply_filters('mgm_post_purchase_page_html', $html);
}

/**
 * get register page title
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_register_page_title(){
	// return
	return apply_filters('mgm_register_page_title', __('Register','mgm'));
}

/**
 * get register page html
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_register_page_html(){
	// html
	$html = mgm_user_register_form();
	
	// return
	return apply_filters('mgm_register_page_html', $html);
}

/**
 * get user profile page title
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_user_profile_page_title(){
	// return
	return apply_filters('mgm_user_profile_page_title', __('Profile','mgm'));
}

/**
 * get user profile page html
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_user_profile_page_html(){
	// html
	$html = mgm_user_profile_form();

	// return
	return apply_filters('mgm_user_profile_page_html', $html);
}

/**
 * get lost password page title
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_lost_password_page_title(){
	// return
	return apply_filters('mgm_lost_password_page_title', __('Retrieve Password','mgm'));
}

/**
 * get lost password page html
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_lost_password_page_html(){
	// html
	$html = mgm_user_lostpassword_form(false);
		
	// return
	return apply_filters('mgm_lost_password_page_html', $html);
}

/**
 * get user login page title
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_user_login_page_title(){
	// return
	return apply_filters('mgm_user_login_page_title', __('Login','mgm'));
}

/**
 * get user login page html
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_user_login_page_html(){
	// html
	$html = mgm_user_login_form(false);
		
	// return
	return apply_filters('mgm_user_login_page_html', $html);
}

/**
 * get guest purchase page title
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_guest_purchase_page_title(){
	// return
	return apply_filters('mgm_guest_purchase_page_title',  __('Purchase Content','mgm'));
}

/**
 * get guest purchase page html
 *
 * @param void
 * @return string
 * @since 1.5
 */
function mgm_get_guest_purchase_page_html(){
	// html
	$html = mgm_guest_purchase_form();
	
	// return
	return apply_filters('mgm_guest_purchase_page_html', $html);
}

/**
 * get transaction page html
 *
 * @param bool $return
 * @return string
 * @since 1.5
 */
function mgm_get_transaction_page_html($return=false, $method=NULL){
	// get method
	if(!$method) $method = mgm_request_var('method', '', true); 	
	// switch $method
	switch($method){
		case 'payment_return':// after payment return with get/post values and process
		case 'payment_notify':// silent post back, IPN, just after first payment		
		case 'payment_status_notify':// INS, post back, at each payment cycle,i.e, 2CO INS, PayPal IPN
		case 'payment_cancel':// cancelled	
		case 'payment_unsubscribe':// unsubscribe tracking	
		case 'payment_html_redirect': // proxy for html redirect
		case 'payment_credit_card': // proxy for credit_card processing	
			// get module
			$module = mgm_request_var('module', '', true);
			// validate module
			if( $module_obj = mgm_is_valid_module($module, 'payment', 'object') ){
				// process, invoke process_return,process_notify,process_cancel,process_unsubscribe
				$output = $module_obj->invoke(str_replace(array('payment_'), 'process_', $method));				
				// html redirect					
				if($method == 'payment_html_redirect'){
					// set in globals
					$GLOBALS['mgm_html_outout'] = $output;						
					// if template exists
					if($return){
						$template_file = MGM_CORE_DIR . 'html/payment_processing_return.php';	
					}else if( file_exists( TEMPLATEPATH . '/payment_processing.php' ) ){	
						$template_file = TEMPLATEPATH . '/payment_processing.php';
					}else{
						$template_file = MGM_CORE_DIR . 'html/payment_processing.php';	
					}	
					// apply template filter
					$template_file = apply_filters('mgm_page_template', $template_file, $method);
					// return template
					if($return) return mgm_get_include($template_file);// @todo check payment
					// include template
					@include($template_file);
				}
			}else{
			// not a valiud module, call default for unsubscribe
				if($method == 'payment_unsubscribe'){
				// default unsubscribe
					return mgm_member_unsubscribe();
				}else{
				// error
					return __('Invalid module supplied','mgm');
				}
			}								
		break;
		case 'payment_processed':// processed				
			// get module
			$module = mgm_request_var('module', '', true);
			// validate module
			if($module_obj = mgm_is_valid_module($module, 'payment', 'object')){
				// redirect logic moved, in all cases same page is loaded			
				// if template exists
				if($return){
					$template_file = MGM_CORE_DIR . 'html/payment_processed_return.php';	
				}else if( file_exists( TEMPLATEPATH . '/payment_processed.php' ) ){	
					$template_file = TEMPLATEPATH . '/payment_processed.php';
				}else{
					$template_file = MGM_CORE_DIR . 'html/payment_processed.php';	
				}	
				// apply template filter
				$template_file = apply_filters('mgm_page_template', $template_file, $method);	
				// return template
				if($return) return mgm_get_include($template_file);
				// include template
				@include($template_file);
			}else{
				return __('Invalid module supplied','mgm'); 
			}
		break;			
		case 'payment_purchase': // post purchase 					
			// if template exists
			if($return){
				$template_file = MGM_CORE_DIR . 'html/payment_post_purchase_return.php';	
			}else if( file_exists( TEMPLATEPATH . '/payment_post_purchase.php' ) ){	
				$template_file = TEMPLATEPATH . '/payment_post_purchase.php';
			}else{
				$template_file = MGM_CORE_DIR . 'html/payment_post_purchase.php';	
			}	
			// apply template filter
			$template_file = apply_filters('mgm_page_template', $template_file, $method);	
			// return template
			if($return) return mgm_get_include($template_file);
			// include template
			@include($template_file);
		break;			
		case 'guest_purchase':// form
			// if template exists
			if($return){
				$template_file = MGM_CORE_DIR . 'html/guest_purchase_return.php';	
			}else if( file_exists( TEMPLATEPATH . '/guest_purchase.php' ) ){	
				$template_file = TEMPLATEPATH . '/guest_purchase.php';
			}else{
				$template_file = MGM_CORE_DIR . 'html/guest_purchase.php';	
			}	
			// apply template filter
			$template_file = apply_filters('mgm_page_template', $template_file, $method);		
			// return template
			if($return) return mgm_get_include($template_file);
			// include template
			@include($template_file);
		break;
		case 'register':				
			// if template exists
			$template = mgm_get_page_template($method, $return);			
			// return template
			if($return) return mgm_get_include($template);
			// include template
			@include($template);			
		break;
		case 'profile'://user profile page								
			// if template exists
			$template = mgm_get_page_template($method, $return);	
			// return template
			if($return) return mgm_get_include($template);
			// include template
			@include($template);		
		break;
		case 'lost_password':			
			// if template exists
			$template = mgm_get_page_template($method, $return);	
			// return template
			if($return) return mgm_get_include($template);
			// include template
			@include($template);
		break;	
		case 'user_login':
		case 'login':			
			// if template exists
			$template = mgm_get_page_template('login', $return);		
			// return template
			if($return) return mgm_get_include($template);
			// include template
			@include($template);
		break;	
		case 'payment_subscribe':// form
		case 'payment':// form
		default:				
			// if template exists
			if($return){
				$template_file = MGM_CORE_DIR . 'html/payment_subscribe_return.php';	
			}elseif( file_exists( TEMPLATEPATH . '/payment_subscribe.php' ) ){	
				$template_file = TEMPLATEPATH . '/payment_subscribe.php';
			}else{
				$template_file = MGM_CORE_DIR . 'html/payment_subscribe.php';	
			}	
			// apply template filter
			$template_file = apply_filters('mgm_page_template', $template_file, $method);				
			// return template
			if($return) return mgm_get_include($template_file);
			// include template
			@include($template_file);
		break;
	}
}

/**
 * get page template
 *
 * @param string $name
 * @param bool $return
 * @return string
 * @since 1.5
 */
function mgm_get_page_template($name, $return=false, $theme=false){
	// default nothing
	$template = '';
	// check theme 
	if( file_exists( TEMPLATEPATH . '/' . $name . '_page.php' ) ){	
	// theme full page
		$template = TEMPLATEPATH . '/' . $name . '_page.php';
	}else if(!$theme){// not theme 
		// custom part page or full page
		$template = MGM_CORE_DIR . 'html/' . $name . '_page' . ( ($return) ? '_return' : '' ) . '.php';			
	}	
	// apply template filter
	return apply_filters('mgm_page_template', $template, $name);
}
// end file