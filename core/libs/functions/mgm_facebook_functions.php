<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members facebook functions
 *
 * @package MagicMembers
 * @subpackage Facebook
 * @since 2.6
 */
 
// add filters
add_filter('mgm_login_form_buttons', 'mgm_login_form_facebook_button');
add_filter('mgm_logout_links', 'mgm_facebook_logout_link');
add_action('mgm_user_login_pre_process', 'mgm_pre_process_facebook_login');
add_action('mgm_user_registration_pre_process', 'mgm_pre_process_facebook_registration');

/**
 * pre process facebook login callback
 * @return mixed/object error
 */	
function mgm_pre_process_facebook_login(){
	// errors
	global $fb_errors;
	// facebook redirect
	if(isset($_GET['connect'])){
		// facebook connect, @ToDO will have more connect methods later
		if($_GET['connect'] == 'facebook'){
			// process and exit if ok, return error otherwise
			$fb_errors = mgm_process_facebook_login();
		}
	}
	// return 
	return $fb_errors;
}

/**
 * add login button in form
 *
 * @param string buttons
 * @return string buttons
 */	
function mgm_login_form_facebook_button($buttons,$callback_url=''){
	// return 
	if(isset($_GET['connect']) && $_GET['connect'] == 'facebook'){
		return $buttons;
	}
	
	// system
	$system_obj = mgm_get_class('system');
	// Issue #727
	if( bool_from_yn($system_obj->get_setting('enable_facebook')) ) {
		// url
		if(empty($callback_url)) $callback_url = mgm_get_custom_url('login', false, array('connect'=>'facebook'));
		// script
		$fb_script = '
			<div id="fb-root"></div>
			<script>
				window.fbAsyncInit = function() {
					// init
					FB.init({appId: "'.$system_obj->get_setting('facebook_id').'", status: true, cookie: true, xfbml: true, oauth: true});
					// login			
					FB.Event.subscribe("auth.login", function (response) { 						
						//window.location.reload();
						window.location.href = "'.$callback_url.'";
					});
					// logout			
					//FB.Event.subscribe("auth.logout", function (response) { 						
						//window.location.reload();
					//});
					// status
					FB.getLoginStatus(function(response) {								
						if (response.status === "connected") {		
							var facebook_id      = response.authResponse.userID;
							var accessToken      = response.authResponse.accessToken;	
							window.location.href = "'.$callback_url.'&facebook_id="+facebook_id+"&accessToken="+accessToken;
						} else if (response.status === "not_authorized") {
							// the user is logged in to Facebook, 
							// but has not authenticated your app
						} else {
							// the user isnt logged in to Facebook.
						}
					});					
				};				
				(function(d){
					var js, id = \'facebook-jssdk\'; if (d.getElementById(id)) {return;}
					js = d.createElement(\'script\'); js.id = id; js.async = true;
					js.src = "//connect.facebook.net/en_US/all.js";
					d.getElementsByTagName(\'head\')[0].appendChild(js);
				}(document));
			</script>
			<div class="fb-login-button" scope="email">Login with Facebook</div>';
			
			//issue #1095
			if (is_array($buttons)) { 
				// append
				$buttons [] = $fb_script ;
			}else {
				// append
				$buttons .= $fb_script ;
			}
	}
	// return
	return $buttons ;
}

/**
 * process facebook login
 *
 * @param none
 * @return mixed/object error
 */	
function mgm_process_facebook_login(){
	// system
	$system_obj = mgm_get_class('system');	
	// lib
	@require_once(MGM_LIBRARY_DIR . 'third_party/facebook/facebook.php');
	// fb object			
	$facebook = new Facebook( array( 'appId' => $system_obj->setting['facebook_id'], 'secret' => $system_obj->setting['facebook_key'] ));
	// get user			
	if ($fbuser = $facebook->getUser()) {
		// try
		try {
			// Proceed knowing you have a logged in user who's authenticated.
			$user_profile = $facebook->api('/me');
		} catch (FacebookApiException $e) {
			$user_profile = '';
		}			
		// check
		if (!empty($user_profile )) {
			# User info ok? Let's print it (Here we will be adding the login and registering routines)
			if (isset($user_profile['email']) && !empty($user_profile['email'])) {
				// user
				$user = get_user_by('email', $user_profile['email']);
				// check				
				if($user->ID){
					// member
					$member = mgm_get_member($user->ID);
					// login
					$user_login = $user->data->user_login;
					// pass
					$password = $member->user_password;
					// desc pass
					$password = mgm_decrypt_password($password, $user->ID, $member->rss_token);	
					// process login
					mgm_process_user_login('login', $user_login, $password);
				}
			}			  		
		}
	}
	
	// error, email did not match
	$errors = new WP_Error();
	// url			
	$register_url = mgm_get_custom_url('register');
	// email
	$fb_email =  (isset($user_profile['email'])) ? $user_profile['email'] : 'n/a';
	// add error
	$errors->add('invalid_facebook_user', sprintf(__('<strong>ERROR</strong>: Your Facebook Account "%s" should be linked to your %s Account. Please Register here <a href="%s" target="_blank">%s</a> to avail Facebook Connect', 'mgm'), $fb_email,  get_bloginfo('name'), $register_url, get_bloginfo('name')));
	// return 
	if ( $errors->get_error_code() )
		return $errors;
}

/**
 * logout link, using default menu link, can be placed anywhere via function call or hook
 *
 * @param string default links
 * @return string links
 */	
function mgm_facebook_logout_link($links=''){
	// system
	$system_obj = mgm_get_class('system');
	// check
	if($system_obj->setting['enable_facebook'] == 'Y') {
		// lib
		@require_once(MGM_LIBRARY_DIR . 'third_party/facebook/facebook.php');
		// fb object			
		$facebook = new Facebook( array( 'appId' => $system_obj->setting['facebook_id'], 'secret' => $system_obj->setting['facebook_key'] ));
		// check session
		if($user = $facebook->getUser()){
			// url
			$fb_logout_url = $facebook->getLogoutUrl(array('next'=>wp_logout_url()));
			// add
			$links = sprintf('<a href="%s"><img src="%s" alt="%s" /></a>', $fb_logout_url, MGM_ASSETS_URL . 'images/icons/fb-Logout.png', __('Logout','mgm'));
		}		
	}
	// return 
	return $links;
}

//parse signed request
function parse_signed_request($signed_request, $secret) {
  list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

  // decode the data
  $sig = base64_url_decode($encoded_sig);
  $data = json_decode(base64_url_decode($payload), true);

  if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
    error_log('Unknown algorithm. Expected HMAC-SHA256');
    return null;
  }

  // check sig
  $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
  if ($sig !== $expected_sig) {
    error_log('Bad Signed JSON signature!');
    return null;
  }

  return $data;
}

//fb registration preprocess
function mgm_pre_process_facebook_registration(){
	
	// system
	$system_obj = mgm_get_class('system');

	$user_login = $user_email ='';
	
	if(isset($_GET['connect']) && $_GET['connect'] == 'facebook_registration'){

		if(isset($_REQUEST['signed_request'])) {
			
			$response = parse_signed_request($_REQUEST['signed_request'],$system_obj->setting['facebook_key']);
	  			
	  		if(isset($response['registration']['user_login'])){
	  			$user_login = $response['registration']['user_login'];
	  		}
	  		if(isset($response['registration']['email'])){
	  			$user_email = $response['registration']['email'];
	  		}
	
	  		if(isset($response['registration']['email'])){
	  			$_POST['mgm_subscription']=$response['registration']['mgm_subscription'];
	  		}
	  		// get error
			$errors = mgm_register_new_user($user_login, $user_email, null);

			// no error
			if ( !is_wp_error($errors) ) {
				// get redirect
				$redirect = mgm_get_custom_url('login', $use_default_links, array('checkemail' => 'registered'));	
				// check default
				$redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : $redirect;
				// redirect
				wp_safe_redirect( $redirect_to );
				// exit
				exit();
			}		
			// errors		
			$error_html = mgm_set_errors($errors, true);
		}
	}
	
}
//fb registration form
function mgm_registration_form_facebook_form($fb_registration_form,$callback_url=''){
	
	// system
	$system_obj = mgm_get_class('system');

	// return 
	if(isset($_GET['connect']) && $_GET['connect'] == 'facebook_registration'){
		return $fb_registration_form;
	}

	// url
	if(empty($callback_url))
		$callback_url = mgm_get_custom_url('register', false, array('connect'=>'facebook_registration'));

	$fields="";	
	
	// get custom fields on register page
	$cf_register_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true)));	
	
	// loop to create form template	
	foreach($cf_register_page as $field){
		if ($field['name']=='subscription_options'){
			$opt = mgm_fb_subscription_options_callback($field,'mgm_register_field','');
		}
	}
	
	// registration fields
	$fields ='fields=[
	            {"name":"name"},
	            {"name":"user_login", "description":"Username", "type":"text"},
	            {"name":"email"},
	            {"name":"mgm_subscription",    "description":"Subscription Options","type":"select",    "options":'.$opt.'}]';
	
	
	$fb_registration_form ='<div id="add"></div><div id="container">
        <label>User Registration using <span style="color: #5c75a9">Facebook Registration Plugin</span></label><br/>
        <div id="reg_form">';
	
   	$fb_registration_form .="<iframe src='http://www.facebook.com/plugins/registration.php?
                    client_id=".$system_obj->setting['facebook_id']."&
                    redirect_uri=".$callback_url."&";
   	
   	$fb_registration_form .=$fields." '";
	$fb_registration_form .='scrolling="auto"
                    frameborder="no"
                    style="border:none"
                    allowTransparency="true"
                    width="500"
                    height="600">
            </iframe>
        </div>
    </div>';

	return $fb_registration_form;

}
//base 64 url decode
function base64_url_decode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
}
//  temporaly added subscription_options method for testing purpose - actully we need get for custom fileds
function mgm_fb_subscription_options_callback($field,$name,$value) {	
	
	$options_arr = array();
	// get object
	$packs_obj = mgm_get_class('subscription_packs');	
	// get mgm_system
	$system_obj = mgm_get_class('system');	
	// args
	$args = array();			
	// selected subscription	
	$selected_subs = mgm_get_selected_subscription($args);													
	// packs
	$packs = $packs_obj->get_packs('register', true, $selected_subs);
	// total
	$total_amount = 0;			
	// total calc
	foreach ($packs as $pack) {					
		$total_amount += $pack['cost'];
	}													
	// active payment modules
	$a_payment_modules = $system_obj->get_active_modules('payment');
	// active module
	if (count($a_payment_modules) == 0 && $total_amount > 0) {
		return  sprintf('<p>%s</p>', __('There are no payment gateways active. Please contact the administrator.','mgm'));
	}else{		
		// payment_module
		$payment_modules = array(); 
		// loop
		foreach($a_payment_modules as $payment_module){
			// skip free/trial				
			if(in_array($payment_module, array('mgm_free','mgm_trial'))) continue;											
			// increment 
			$payment_modules[] = $payment_module;
		}								
		// loop packs
		foreach ($packs as $pack) {					
			// reset
			$checked = mgm_select_subscription($pack,$selected_subs);						
			// skip other when a package sent as selected
			if($selected_subs !== false){
				if(empty($checked)) continue;
			}	
			// subs encrypted
			$subs_enc = mgm_encode_package($pack);
			if ((strtolower($pack['membership_type']) == 'free' || ($pack['cost'] == 0 && mgm_get_module('mgm_free')->enabled=='Y')) && in_array('mgm_free', $a_payment_modules)) {
				$options_arr[$subs_enc]	= trim(mgm_stripslashes_deep($packs_obj->get_pack_desc($pack)));
			// trial		  
			}elseif (strtolower($pack['membership_type']) == 'trial' && in_array('mgm_trial', $a_payment_modules)) {
				$options_arr[$subs_enc]	= trim(mgm_stripslashes_deep($packs_obj->get_pack_desc($pack)));				
			}else{										
				// paid subscription active
				if(count($payment_modules)){
					// check cost and hide false
					if ($pack['cost']){
						$options_arr[$subs_enc]	= trim(mgm_stripslashes_deep($packs_obj->get_pack_desc($pack)));						
					}// end if
				}elseif($pack['cost'] > 0){						
					// set message
					//$html .= sprintf('<div class="message" style="margin:10px 0px; overflow: auto;color:red;font-weight:bold">%s</div>',__('Please enable a payment module to allow ' . mgm_stripslashes_deep($packs_obj->get_pack_desc($pack)) ,'mgm'));											
				}// end paid											
			} 	
		}// end pack loop	
	}
	// return
	return json_encode($options_arr);
}
// end file