<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members custom forms functions
 *
 * @package MagicMembers
 * @subpackage Facebook
 * @since 2.6
 */
 
/**
 * custom user login form, output by [user_register] shortcode
 *
 * @param bool $use_default_links
 * @param string $html
 */   
function mgm_user_login_form($use_default_links = true) {	
	//fb logins i.e. facebook connect errors
	global $fb_errors;
	// hide from logged in user	
	if( is_user_logged_in() )	{
		// not logout call to self
		if(  mgm_get_var('action', '', true) != 'logout' ){
			return __('You are already logged in!', 'mgm');
		}		
	}	
	// check auto login
	if($html = mgm_try_auto_login()) return $html;
	// init errors
	$fb_errors = $errors = null;
	// system
	$system_obj = mgm_get_class('system');		
	// process hooked logins i.e. facebook connect
	do_action('mgm_user_login_pre_process');	
	// check security before processing form		
	if(isset($_POST['log'])) {
		if ( !wp_verify_nonce(mgm_post_var('_mgmnonce_user_login'), 'user_login') ) 
			mgm_security_error('user_login'); 
	}
	// issue #1203
	if(empty($fb_errors)) { 
		$errors = mgm_process_user_login(); 
	}else {
		$errors = $fb_errors;
	}

	// action
	$form_action = mgm_get_custom_url('login');		
	// init
	$user_login = $user_pwd	= $html = '';
	
	//check logged in cookie:	
	$rememberme    = !empty( $_POST['rememberme'] );
	$interim_login = isset($_REQUEST['interim-login']);
	
	// login
	if ( isset($_POST['log']) ){ 
		$user_login = esc_attr(stripslashes($_POST['log']));
	// issue# 525	
	}elseif ($cookie_userid = wp_validate_auth_cookie('','logged_in')) {//check a valid logged cookie exists	
		// cookie
		$arr_loggedin_cookie = wp_parse_auth_cookie('', 'logged_in');
		// get mgm_member
		$member = mgm_get_member($cookie_userid);
		// mark checked
		$rememberme = true;	
		// get login from cookie		
		$user_login = esc_attr(stripslashes($arr_loggedin_cookie['username']));			
		// password from member object		
		// issue#: 672
		$user_pwd = mgm_decrypt_password($member->user_password, $cookie_userid);	
	}
	
	// redirect
	$redirect_to = ( isset( $_REQUEST['redirect_to'] ) ) ? $_REQUEST['redirect_to'] : '';		
	
	// start html
	$html = '';
	
	// set error !
	if(isset($errors) && is_object($errors)) {
		// get error
		if($error_html = mgm_set_errors($errors, true)){
			$html .= $error_html;
		}
	}
	
	// check
	if(bool_from_yn($system_obj->get_setting('enable_email_as_username'))){
		$email_username_label = __('Email','mgm');
	}else{
		$email_username_label = __('Username','mgm');
	}
	// start form
	$html .= '<form class="mgm_form" name="loginform" id="loginform" action="'.$form_action.'" method="post">
				<div>
					<label>'.$email_username_label.'<br />
					<input type="text" name="log" id="user_login" class="input" value="'.esc_attr($user_login).'" size="40" tabindex="10" /></label>
				</div>
				<div>
					<label>'.__('Password', 'mgm').'<br />
					<input type="password" name="pwd" id="user_pass" class="input" value="'. esc_attr($user_pwd) .'" size="40" tabindex="20" /></label>
				</div>';
	
	//Issue #782
	$html .= mgm_get_captcha_field('mgm_login_field');
	
	// login form, fetch as return	
	// do_action('login_form');	
	// custom
	$html .= apply_filters('mgm_login_form', $html);
	
	// forget
	$html .= '<div class="forgetmenot">
				 <label>
					<input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" '.checked( $rememberme, true, false).'  /> '.__('Remember Me','mgm').'
				 </label>
			  </div>';			 
	
	// buttons		 
	$buttons = array(sprintf('<input class="button mgm-login-button" type="submit" name="wp-submit" id="wp-submit" value="%s" tabindex="100" />', __('Log In','mgm')));		 
	// apply filters
	$buttons_s = implode( apply_filters('mgm_login_form_buttons_sep', ' &nbsp; '), apply_filters('mgm_login_form_buttons', $buttons));	
	// append
	$html .= sprintf('<div class="login-page-buttons">%s</div>', $buttons_s);
	
	if($system_obj->get_setting('disable_testcookie') =='N'	) {
		// hiddens
		$html .= '<input type="hidden" name="testcookie" value="1" /> ';
	}	
	// intrim
	if ( $interim_login ) {
		$html .= '<input type="hidden" name="interim-login" value="1" />';
	} else {
		$html .= '<input type="hidden" name="redirect_to" value="'.esc_attr($redirect_to).'" />';
	}
	// nonce	
	$html .= wp_nonce_field('user_login', '_mgmnonce_user_login', true, false);	
	
	// end form
	$html .= '</form>';
	
	// after links
	$links = array();
	// interim_login	
	if ( !$interim_login ) {				
		// check mail will not have any
		if ( !isset($_GET['checkemail']) || (isset($_GET['checkemail']) && !in_array( $_GET['checkemail'], array('confirm', 'newpass') ) ) ){
			// register
			if ( get_option('users_can_register') ){
				$links[] = sprintf('<a class="mgm-register-link" href="%s">%s</a>', mgm_get_custom_url('register'), __('Register', 'mgm'));			
			}
			// lostpassword
			$links[] = sprintf('<a class="mgm-lostpassword-link" href="%s" title="%s">%s</a>', mgm_get_custom_url('lostpassword'), __('Password Lost and Found', 'mgm'), __('Lost your password?', 'mgm'));					
		}
	} 
	
	// apply filters
	$links_s = implode(apply_filters('mgm_login_form_after_links_sep', ' | '), apply_filters('mgm_login_form_after_links', $links));
	// appaend
	$html .= sprintf('<div class="login-page-links">%s</div>', $links_s);
	
	// scripts & styles --------------------
	// focus
	$focus = ( $user_login || $interim_login ) ? 'user_pass' : 'user_login';
	// script
	$script = 'function wp_attempt_focus(){setTimeout( function(){ try{ d = document.getElementById("'.$focus.'"); d.focus();} catch(e){}}, 200);}';
	// focus
	if ( @!$error ) {
		$script .= 'wp_attempt_focus();';
	}
	
	// script
	$script = sprintf( '<script type="text/javascript">%s</script>', apply_filters('mgm_login_form_inline_script', $script) );
	
	// scripts
	$html .= apply_filters('mgm_login_form_scripts', $script);
	
	// style
	$style = '.login-page-links, .login-page-buttons{margin-top:10px; clear:both}';
	// style
	$style = sprintf('<style type="text/css">%s</style>', apply_filters('mgm_login_form_inline_style', $style ));
	
	// style
	$html .= apply_filters('mgm_login_form_styles', $style);
	
	// apply filters and return
	return apply_filters('mgm_login_form_html', $html);	
}
  
/**
 * custom login form, used in sidebar
 *
 * @param string $register_text
 * @param string $lostpassword_text
 * @return string $form
 */
function mgm_sidebar_user_login_form($register_text='', $lostpassword_text='') {
	// system
	$system_obj = mgm_get_class('system');

	// email as username
	if(bool_from_yn($system_obj->get_setting('enable_email_as_username'))){
		$email_username_label = __('Email','mgm');
	}else{
		$email_username_label = __('Username','mgm');
	}

	// form action
	$form_action = mgm_get_custom_url('login');

	// build html
	$html = '<div class="mgm-sidebar-loginform-wrap">
				<form class="mgm_form" name="mgm_sidebar_loginform" id="mgm_sidebar_loginform" action="' . $form_action . '" method="post">
					<label>' . $email_username_label . ':</label>
					<div>
						<input type="text" name="log" id="user_login" class="input" value="" tabindex="10" size="20" />
					</div>				
					<label>' . __('Password','mgm') . ':</label>
					<div>
						<input type="password" name="pwd" id="user_pass" class="input" value="" tabindex="20" size="20"/>
					</div>';
	// add captcha
	if( $captcha = mgm_get_form_captcha() ){
		$html .= sprintf('<div>%s</div>', $captcha);
	}
	// login form, fetch as return	
	// do_action('login_form');	
	// custom code attach
	$html .= apply_filters('mgm_login_form', $html);			
				
	$html .= '	<div>
					<div id="remember_me_container">
						<input id="rememberme" type="checkbox" tabindex="90" value="forever" name="rememberme"/> '.__('Remember Me','mgm').'
					</div>';
	
	// buttons		 
	$buttons = array(sprintf('<input class="button mgm-login-button" type="submit" name="wp-submit" id="wp-submit" value="%s" tabindex="100" />', __('Log In','mgm')));		 
	// apply filters
	$buttons_s = implode( apply_filters('mgm_login_form_buttons_sep', ' &nbsp; '), apply_filters('mgm_login_form_buttons', $buttons));	
	
	// append
	$html .= sprintf('<div class="login-sidebar-buttons">%s</div>', $buttons_s);
	
	// post redirection
	if( bool_from_yn(mgm_get_setting('enable_post_url_redirection')) ){
		$html .= '  <input type="hidden" name="redirect_to" value="' . get_permalink() . '" />';
	}
		
	// nonce
	$html .= wp_nonce_field('user_login', '_mgmnonce_user_login', true, false);
	
	// html
	$html .=   '</div></form>';
	
	// after links
	$links = array();
		
	// register link	
	if ( get_option('users_can_register') ){
		// has text
		if ($register_text) {
			// get urls from settings		
			$links[] = sprintf('<a class="mgm-register-link" href="%s">%s</a>', mgm_get_custom_url('register'), $register_text);
		}
	}	
	
	// lostpassword link	
	if ($lostpassword_text) {
		// get urls from settings		
		$links[] = sprintf('<a class="mgm-lostpassword-link" href="%s">%s</a>', mgm_get_custom_url('lostpassword'), $lostpassword_text);
	}
	
	// apply filters
	$links_s = implode(apply_filters('mgm_login_form_after_links_sep', ' | '), apply_filters('mgm_login_form_after_links', $links));
	// appaend
	$html .= sprintf('<div class="login-sidebar-links">%s</div>', $links_s);
	
	// end wrap
	$html .= '</div>';
	
	// scripts & styles --------------------
	// focus
	$focus = 'user_login';
	// script
	// $script = 'function wp_attempt_focus(){setTimeout( function(){ try{ d = document.getElementById("'.$focus.'"); d.focus();} catch(e){}}, 200);}';
	// focus
	// if ( @!$error ) {
		// $script .= 'wp_attempt_focus();';
	// }
	$script = '';
	
	// script
	$script = sprintf( '<script type="text/javascript">%s</script>', apply_filters('mgm_login_form_inline_script', $script) );
	
	// scripts
	$html .= apply_filters('mgm_login_form_scripts', $script);
	
	// style
	$style = '.login-sidebar-links, .login-sidebar-buttons{margin-top:10px; clear:both}';
	// style
	$style = sprintf('<style type="text/css">%s</style>', apply_filters('mgm_login_form_inline_style', $style ));
	
	// style
	$html .= apply_filters('mgm_login_form_styles', $style);
	
	// apply filters and return
	return apply_filters('mgm_sidebar_login_form_html', $html);	
}

/**
 * custom register form
 *
 * @param array $args
 * @param bool $use_default_links
 * @return string $form
 */
function mgm_user_register_form($args=array(), $use_default_links = false) {			
	// hide from logged in user	
	if( is_user_logged_in() )	{
		// redirect			
		return __('You are already logged in!', 'mgm');
	}	

	// registration disabled
	if ( ! get_option('users_can_register') ){
		// redirect			
		return __('User registration is currently not allowed.', 'mgm');
	}	
		
	// get system
	$system_obj = mgm_get_class('system');
	// hide flag
	$hide_custom_fields = $system_obj->get_setting('hide_custom_fields');
	// init
	$cf_show_fields = array();
		
	// default_register_fields
	$register_fields = mgm_get_config('default_register_fields',array());
	// get active custom fields on register
	$cf_register_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true)));	
	//issue #1573
	$membership_args_fields =  "";	
	if(isset($args['membership']) && !empty($args['membership'])){	
		//init
		$show_fields_arr = array();
		// membership
		$membership = $args['membership'];
		// get active custom fields on register
		$cf_register_by_membership_types = mgm_get_class('member_custom_fields')->get_fields_where(array('attributes'=>array('register_by_membership_types'=>true)));	
		//check
		if(!empty($cf_register_by_membership_types)){
			//loop
			foreach ($cf_register_by_membership_types as $cf_register_by_membership_type) {
				//membership_type
				$membership_types_string = (isset($cf_register_by_membership_type['attributes']['register_membership_types_field_alias']))?$cf_register_by_membership_type['attributes']['register_membership_types_field_alias']:null;
				//check
				if (preg_match('/\b' . $membership . '\b/', $membership_types_string) && $membership_types_string !=null) {
					$show_fields_arr[]=$cf_register_by_membership_type['name'];
				}
			}	
		}
		//filter if any empty values found check		
		$show_fields_arr = array_filter($show_fields_arr);		
		//check
		if(!empty($show_fields_arr)) {
			$membership_args_fields = implode(',',$show_fields_arr);
		}
	}	
	// # 740
	// Show fields in short code to filter the registration form #Issue 740
	$args_fields  = '';
	if ((isset($args['show_fields']) && !empty($args['show_fields'])) || (isset($membership_args_fields) && !empty($membership_args_fields))) {
		$package  = (isset($args['package'])) ? $args['package'] : null;
		$args_fields  = (isset($args['show_fields'])) ? $args['show_fields'] : $membership_args_fields;
	   if (!empty($args_fields)) {
	   		$cf_register_page = mgm_show_fields_result($args_fields, $cf_register_page, $package);
	   }	   
	}  
	
	// error_html
	$error_html = '';	
		
	// save-------------------------------------------------
	if ( isset($_POST['method']) && $_POST['method'] == 'create_user' ) {	
		// check security before processing form				
		if ( !wp_verify_nonce(mgm_post_var('_mgmnonce_user_register'), 'user_register') ) 
			mgm_security_error('user_register'); 			
		
		// load wp lib for register
		if ( mgm_compare_wp_version('3.1', '<') ){// only before 3.1
			require_once( ABSPATH . WPINC . '/registration.php');
		}
		
		// process hooked registers i.e. facebook connect
		do_action('mgm_user_register_pre_process');
		
		// init
		$user_login = $user_email = '';		
		// loop to check		
		foreach($register_fields as $cfield=>$wfield){
			// set custom
			if(isset($_POST['mgm_register_field'][$cfield])){
				// set from custom
				${$wfield['name']} = $_POST['mgm_register_field'][$cfield];
			}else if(isset($_POST[$wfield['name']])){
			// default field
				${$wfield['name']} = $_POST[$wfield['name']];
			}else{
			// else	
				${$wfield['name']} = '';
			}	
		}	
		
		// user login 
		if(empty($user_login)) $user_login = mgm_generate_user_login($user_email);	
		//issue #1573	
		if(!isset($args['show_fields']) && isset($args['membership'])) {
			$args['show_fields'] = $_REQUEST['show_fields'];
		}
		// get error
		$errors = mgm_register_new_user($user_login, $user_email, (isset($args['show_fields']) ? $args['show_fields'] : NULL));
		
		// no error
		if ( ! is_wp_error($errors) ) {
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
	}// end save-----------------------------------------------	
	
	// issue#: 532
	$form_action = (isset($args['package']) || isset($args['membership'])) ? get_permalink() : mgm_get_custom_url('register');	
		
	// package code:		
	if($package = mgm_request_var('package', '', true)) {
		$form_action = add_query_arg(array('package' => $package), $form_action);
	}
	// membership code:	
	if($membership = mgm_request_var('membership', '', true)) {
		$form_action = add_query_arg(array('membership' => $membership), $form_action);
	}
	// wordpress register
	$wordpres_form = mgm_check_wordpress_login();
	
	// 	get row row template
	$form_row_template = $system_obj->get_template('register_form_row_template');
	
	// get template row filter, mgm_register_form_row_template for custom, mgm_register_form_row_template_wordpress for wordpress
	$form_row_template = apply_filters('mgm_register_form_row_template'.($wordpres_form ? '_wordpress': ''), $form_row_template);	
	
	// form_fields_config
	$form_fields_config = array('wordpres_form'=>(bool)$wordpres_form,'form_row_template'=>$form_row_template,
	                            'cf_register_page'=>$cf_register_page,'args'=>$args);
	// get mgm_form_fields generator
	$form_fields = new mgm_form_fields($form_fields_config);
	
	// default
	$form_html = '';
	
	// register & purchase, purchase options
	if( isset($_GET['show_purchase_options']) && isset($_GET['post_id']) ){
	// set 
		$form_html .= apply_filters('mgm_guest_purchase_register_form_pre_register_html', mgm_get_post_purchase_options((int) strip_tags($_GET['post_id']), 'pre_register'));
	}
	
	// register & purchase, add post id
	if( isset($_GET['post_id']) && (int)$_GET['post_id']>0){
	// set
		$form_html .= sprintf('<input type="hidden" name="post_id" value="%d">', (int)strip_tags($_GET['post_id']));
	}
	//register & purchase postpack
	if( isset($_GET['postpack_id']) && (int)$_GET['postpack_id']>0 &&  isset($_GET['postpack_post_id']) && (int)$_GET['postpack_post_id']>0){
		// set
		// $form_html .= mgm_get_postpack_template($_GET['postpack_id'],false,$_GET['postpack_post_id'],'pre_register');
		$form_html .= sprintf('<input type="hidden" name="postpack_id" value="%d">', (int) strip_tags($_GET['postpack_id']));
		$form_html .= sprintf('<input type="hidden" name="postpack_post_id" value="%d">', (int) strip_tags($_GET['postpack_post_id']));
	}	
	// mgm_pr($register_fields);
	// loop default register fields, create each if they are not defined in custom fields
	foreach($register_fields as $cfield=>$wfield){				
		// set not found
		$captured = false;
		// first check if in custom fields
		foreach($cf_register_page as $rfield){			
			// if default register field  == custom register field, skip
			if($rfield['name'] == $cfield){
				// skip custom fields by settings call
				if(($hide_custom_fields == 'Y') || ($hide_custom_fields == 'W' && $wordpres_form) || ($hide_custom_fields == 'C' && !$wordpres_form)){
				// if($hide_custom_fields && $cfield['name'] != 'subscription_options') continue;
					if(!in_array($field['name'], array('subscription_options','payment_gateways'))) continue;
				}
				// set found
				$captured = true;				
				// do nothing
				break;
			}
			
			// skip username if setting enabled @todo
			if($cfield == 'username' && bool_from_yn($system_obj->get_setting('enable_email_as_username'))){
				// set found
				$captured = true; break;
			}
		}	
		
		// not found		
		if(!$captured){			
			// create element
			$form_html .= str_replace(array('[user_field_wrapper]','[user_field_label]','[user_field_element]'),array($wfield['name'],mgm_stripslashes_deep($wfield['label']),$form_fields->get_field_element($wfield,'mgm_register_field')),$form_row_template);			
		}
	}		
	
	// register custom fields, this will be called via register_form hook
	// $form_html .= mgm_wp_register_form_additional($form_fields, $args, true);
	
	// register button text
	$register_button_text = apply_filters('mgm_register_button_text', $system_obj->get_setting('register_text', __('Register','mgm')) );
	
	// buttons
	$buttons = array(sprintf('<input class="button mgm-register-button" type="submit" name="wp-submit" id="wp-submit" value="%s" tabindex="100" />', $register_button_text));
	
	// apply filters
	$buttons_s = implode( apply_filters('mgm_register_form_buttons_sep', ' &nbsp; '), apply_filters('mgm_register_form_buttons', $buttons));	
	
	// append 
	$buttons_html = sprintf('<div class="register-page-buttons">%s</div>', $buttons_s);		
	
	// nonce
	$nonce = wp_nonce_field('user_register', '_mgmnonce_user_register', true, false);
	
	// this will not work in page shortcde as this does not return form html but directly outputs it
	// do_action('register_form');
	
	// set to globals to be used by "register_form" action hook
	$GLOBALS['form_fields'] = $form_fields;
	
	// attach custom fields via default hook
	$form_html .= apply_filters('mgm_register_form', $form_html);
		
	// output form	
	$html = '<div class="mgm_register_form">
				' . $error_html . '
				<form class="mgm_form" name="registerform" id="registerform"  action="' . $form_action . '" method="post">	               
				   ' . $form_html . $buttons_html . $nonce . '					
					<input type="hidden" name="method" value="create_user">	
					<input type="hidden" name="show_fields" value="'.$args_fields.'">					
				</form>
			 </div>';
	
	// after links	
	$links = array();
	
	// login link
	$links[] = sprintf('<a class="mgm-login-link" href="%s" title="%s">%s</a>', mgm_get_custom_url('login', $use_default_links), __('Log in','mgm'), __('Log in','mgm'));
	// lostpassword link
	if ( get_option('users_can_register') ){			
		$links[] = sprintf('<a class="mgm-lostpassword-link" href="%s" title="%s">%s</a>', mgm_get_custom_url('lostpassword', $use_default_links), __('Password Lost and Found','mgm'), __('Lost your password','mgm'));
	}
	
	// apply filters
	$links_s =  implode(apply_filters('mgm_register_form_after_links_sep', ' | '), apply_filters('mgm_register_form_after_links', $links));
	// append
	$html .= sprintf('<div class="register-page-links">%s</div>', $links_s);
			 
	// attach scripts,		
	$html .= mgm_attach_scripts(true);
	
	// scripts & styles --------------------
	
	$script = "";
	
	//issue #1125
	$script .= "jQuery(document).ready(function() {
					var c ='coupon';
					if(jQuery('.coupon_box input').attr('name') == 'mgm_register_field') {	
	 					jQuery('.coupon_box input').attr('name', 'mgm_register_field['+c+']');
					}
				});";

	// script
	$script = sprintf( '<script type="text/javascript">%s</script>', apply_filters('mgm_register_form_inline_script', $script) );
	
	// scripts
	$html .= apply_filters('mgm_register_form_scripts', $script);
	
	// style
	$style = '.register-page-links, .register-page-buttons{margin-top:10px; clear:both}';
	// style
	$style = sprintf('<style type="text/css">%s</style>', apply_filters('mgm_register_form_inline_style', $style ));
	
	// style
	$html .= apply_filters('mgm_register_form_styles', $style);
	
	// apply filter and return
	return apply_filters('mgm_register_form_html', $html);	
}

/**
 * custom register form, used in sidebar
 *
 * @param boolean $use_custom_fields
 * @param boolean $default_subscription_pack
 * @return string $form
 */
function mgm_sidebar_user_register_form($use_custom_fields, $default_subscription_pack){

	// registration disabled
	if ( ! get_option('users_can_register') ){
		// redirect			
		return __('User registration is currently not allowed.', 'mgm');
	}	
	
	// system
	$system_obj = mgm_get_class('system');
	// register button text
	$register_button_text = apply_filters('mgm_register_button_text', $system_obj->get_setting('register_text', __('Register','mgm')) );
	
	// cf
	$cf_register_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true)));
	
	// html form
	$html = '<form name="registerform" id="registerform" action="' . mgm_get_custom_url('register') . '" method="post">';
	
	// email as username
	// if(bool_from_yn($system_obj->get_setting('enable_email_as_username'))){
	// 	$email_username_label = __('Email','mgm');
	// }else{
	// 	$email_username_label = __('Username','mgm');
	// }
	// username
	if( ! bool_from_yn($system_obj->get_setting('enable_email_as_username')) ){
		if( ! mgm_is_customfield_active(array('username'), $cf_register_page) || !$use_custom_fields) {
			$html .= '<p>
						<label>' . __('Username','mgm') . '<br />
						<input type="text" name="user_login" id="user_login" class="input" value="" size="20" tabindex="10" /></label>
					  </p>';
		}
	}
	// email
	if(!mgm_is_customfield_active(array('email'), $cf_register_page) || !$use_custom_fields) {
		$html .= '<p>
					<label>' . __('E-mail','mgm') . '<br />
					<input type="email" name="user_email" id="user_email" class="input" value="" size="20" tabindex="20" /></label>
				  </p>';
	}
	
	// custom
	if ($use_custom_fields) {
		// do_action('register_form');
		// custom
		$html .= apply_filters('mgm_register_form', $html);
	//Issue #777
	}elseif($default_subscription_pack) {
		
		$obj_packs = mgm_get_class('subscription_packs');
		$packs = $obj_packs->get_packs('register');			
		$subs_pack = mgm_decode_package($default_subscription_pack);

		$modules = array();
		foreach ($packs as $pack){
			if($pack['id']==$subs_pack['pack_id']){
				$modules = $pack['modules'];
			}
		}	

		$cnt = count($modules) ;
			
		if($cnt >1){
			for( $i=0; $i < $cnt ; $i++){

				//issue #1298				
				if(in_array($modules[$i], array('mgm_free','mgm_trial'))) continue;
				//issue #1298
				if(($subs_pack['cost'] == 0 && mgm_get_module('mgm_free')->enabled=='Y')) continue;	

				$mod_obj = mgm_get_module($modules[$i], 'payment');		
				$img_url = mgm_site_url($mod_obj->logo);
				$checked = ($i === 0 )?'checked="true"':'';
				
				//module description
				$html .= (sprintf('<div id="%s_container" class="mgm_payment_opt_wrapper" %s>', $mod_obj->code ,$hide_div)).
						 (sprintf('<input type="radio" %s class="checkbox" name="mgm_payment_gateways" value="%s" alt="%s" />', $checked, $mod_obj->code, $mod_obj->name)) .
						 (sprintf('<img class="mgm_payment_opt_customfield_image" src="%s" alt="%s" />',  $img_url, $mod_obj->name )) .
						 (sprintf('<div class="mgm_paymod_description">%s</div>', mgm_stripslashes_deep($mod_obj->description))) .'</div>';			
				
			}
		}else{
			$html .= '<input type="hidden" name="mgm_payment_gateways" value="'.(isset($modules[0]) ? $modules[0] : '' ).'" alt="Paypal Standard"/>';	//@todo need actual first module			
		}
		
		$html .= '<input type="hidden" class="checkbox" name="mgm_subscription" value="'.$default_subscription_pack.'"/>';
		$html .= '<input type="hidden" class="checkbox" name="mgm_custom_fields" value="'.$use_custom_fields.'"/>';
		$html .= '<input type="hidden" class="checkbox" name="mgm_widget_active" value="true"/>';
	}
	
	// nonce
	$nonce = wp_nonce_field('user_register', '_mgmnonce_user_register', true, false);
	
	// html
	$html .= '<p id="reg_passmail">' . __('A password will be e-mailed to you.') . '</p>
		 	  <p><input class="button mgm-register-button" type="submit" name="wp-submit" id="wp-submit" value="' . $register_button_text . '" tabindex="100" /></p>
		  	  <input type="hidden" name="method" value="create_user">
			 ' . $nonce . '
		  </form>';
		  
	// apply filter and return
	return apply_filters('mgm_sidebar_register_form_html', $html);		  
}
/**
 * custom lost password form
 *
 */
function mgm_user_lostpassword_form($use_default_links = true) {			
	// current url	
	$form_action = get_permalink();//use permalink() for #1233 XSS vulnerabilities
	// login
	$user_login = '';
	// submit
	if(isset($_POST['wp-submit-lp'])) {	
		// check security before processing form				
		if ( !wp_verify_nonce(mgm_post_var('_mgmnonce_user_lostpassword'), 'user_lostpassword') ) 
			mgm_security_error('user_lostpassword'); 
				
		// get login - issue #1281
		$user_login = htmlentities(mgm_stripslashes_deep($_POST['user_login']), ENT_QUOTES, "UTF-8");
		// saniize
		$_POST['user_login'] = sanitize_text_field($_POST['user_login']);
		// password or errors
		$errors = mgm_retrieve_password();	
		// validate
		if ( !is_wp_error( $errors ) ) {									
		// redirect 
			mgm_redirect(add_query_arg( array('lp_updated' => 'true'), $form_action));	exit;
		}	
	}
	// start form
	$html = "\n";
	// css
	$css_group = mgm_get_css_group();	
	// issue #867
	if($css_group != 'none') {
		$html .= '<link rel="stylesheet" href="'. MGM_ASSETS_URL . 'css/' . $css_group . '/mgm.messages.css" type="text/css" media="all" />';
	}
	//sys obj
	$system_obj = mgm_get_class('system');	
	// header
	if($system_obj->get_setting('enable_default_wp_lost_password') == "Y") {
		$html .= sprintf('<div class="mgm_message">%s</div>',  __('Please enter your username or email address. You will receive a link to create a new password via email.','mgm'));	
	}else{
		$html .= sprintf('<div class="mgm_message">%s</div>',  __('Please enter your username or e-mail address. You will receive a new password via e-mail.','mgm'));
	}	
	
	// updated 
	if ( isset($_GET['lp_updated']) ){
		// message
		$message = apply_filters('mgm_lostpassword_success_message', __('Check your e-mail for the confirmation link.','mgm'));
		// add
		$html .= sprintf('<div class="mgm_message"><div><strong>%s</strong></div></div>', $message);
	}
	
	// set error !
	if(isset($errors) && is_object($errors)) {
		// error
		$error_html = mgm_set_errors($errors, true);
		// checl
		if($error_html && !empty($error_html))	$html = $error_html . $html;
	}
	
	// form
	$html .= '<form class="mgm_form" name="lostpasswordform" id="lostpasswordform" action="'. $form_action .'" method="post">
			  	<div>
					<label>'.__('Username or E-mail:','mgm').'<br />
					<input type="text" name="user_login" id="user_login" class="input" value="'. esc_attr($user_login) .'" size="40" tabindex="10" /></label>
				</div>';
	// wp action kept			
	// do_action('lostpassword_form');
	// custom
	$html .= apply_filters('mgm_lostpassword_form', $html);
		
	// buttons
	$buttons = array(sprintf('<input class="button mgm-lostpassword-button" type="submit" name="wp-submit-lp" id="wp-submit-lp" value="%s" tabindex="100" />', __('Get New Password','mgm')));
	
	// apply filters
	$buttons_s = implode( apply_filters('mgm_lostpassword_form_buttons_sep', ' &nbsp; '), apply_filters('mgm_lostpassword_form_buttons', $buttons));	
	
	// append 
	$buttons_html = sprintf('<div class="lostpassword-page-buttons">%s</div>', $buttons_s);			
	
	// nonce
	$nonce = wp_nonce_field('user_lostpassword', '_mgmnonce_user_lostpassword', true, false);
	
	// form
	$html .= $buttons_html . '<input type="hidden" name="redirect_to" value="" /> ' . $nonce . '</form>';
	
	// after links
	$links = array();
	
	// login link
	$links[] = sprintf('<a class="mgm-login-link" href="%s">%s</a>', mgm_get_custom_url('login'), __('Log in','mgm'));	
	// register link
	if (get_option('users_can_register')){		
		// add 
		$links[] = sprintf('<a class="mgm-register-link" href="%s">%s</a>',  mgm_get_custom_url('register'), __('Register','mgm'));
	}		
	
	// apply filters
	$links_s =  implode(apply_filters('mgm_lostpassword_form_after_links_sep', ' | '), apply_filters('mgm_lostpassword_form_after_links', $links));
	
	// add links
	$html .= sprintf('<div class="lostpassword-page-links">%s</div>', $links_s);
	
	// scripts & styles --------------------
	
	// focus
	$focus = 'user_login';
	// script
	$script = 'function wp_attempt_focus(){setTimeout( function(){ try{ d = document.getElementById("'.$focus.'"); d.focus();} catch(e){}}, 200);}';
	// focus
	if ( @!$error ) {
		$script .= 'wp_attempt_focus();';
	}
	
	// script
	$script = sprintf( '<script type="text/javascript">%s</script>', apply_filters('mgm_lostpassword_form_inline_script', $script) );
	
	// scripts
	$html .= apply_filters('mgm_lostpassword_form_scripts', $script);
	
	// style
	$style = '.lostpassword-page-links, .lostpassword-page-buttons{margin-top:10px; clear:both}';
	// style
	$style = sprintf('<style type="text/css">%s</style>', apply_filters('mgm_lostpassword_form_inline_style', $style ));
	
	// style
	$html .= apply_filters('mgm_lostpassword_form_styles', $style);
	
	// apply filter and return
	return apply_filters('mgm_lostpassword_form_html', $html);
}



/**
 * Custom user profile form
 */
function mgm_user_profile_form($user_id=NULL, $temp_edit=false, $args=array()) {
	global $wpdb;
	// get mgm_system
	$system_obj = mgm_get_class('system');
	
	// current user
	$current_user = $user_id ? get_userdata($user_id) : wp_get_current_user();
	// current or voew
	if($current_user->ID){
		// current
		$user = mgm_get_userdata($current_user->ID);
	}else{
		// query string
		$user = mgm_get_user_from_querystring();
	}
	
	// if no user
	if(!isset($user) || !$user->ID || is_super_admin($user->ID)){
		return mgm_user_login_form(); exit;
	}
	
	// mgm member
	$member = mgm_get_member($user->ID);
	// edit mode, on for current user
	$edit_mode = ($current_user->ID == $user->ID) ? true : false;	
	
	$temp = 0;
	// form action	
	$form_action = get_permalink();	
	// reset	
	if($form_action == null) {
		$form_action = mgm_get_current_url(); 
		$form_action = str_replace(array('&updated=true', '?updated=true'),'', $form_action);
	}
	//init - issue #1573
	$show_membership_fields_arr = array();		
	if(isset($args['membership']) && !empty($args['membership'])){	
		// membership
		$membership = $args['membership'];
		// get active custom fields on register
		$cf_profile_by_membership_types = mgm_get_class('member_custom_fields')->get_fields_where(array('attributes'=>array('profile_by_membership_types'=>true)));
		//check
		if(!empty($cf_profile_by_membership_types)){
			//loop
			foreach ($cf_profile_by_membership_types as $cf_profile_by_membership_type) {
				//membership_type
				$membership_types_string = (isset($cf_profile_by_membership_type['attributes']['profile_membership_types_field_alias']))?$cf_profile_by_membership_type['attributes']['profile_membership_types_field_alias']:null;
				//check
				if (preg_match('/\b' . $membership . '\b/', $membership_types_string) && $membership_types_string !=null) {
					$show_fields_arr[]=$cf_profile_by_membership_type['name'];
					$show_membership_fields_arr[]=$cf_profile_by_membership_type;
					if($cf_profile_by_membership_type['name'] =='password'){
						foreach ($cf_profile_by_membership_types as $cf_profile_by_membership) {
							if($cf_profile_by_membership['name'] =='password_conf'){							
								$show_membership_fields_arr[]=$cf_profile_by_membership;
							}
						}						
					}
				}
			}	
		}
	}	
	// get default fields
	$profile_fields = mgm_get_config('default_profile_fields', array());
	// get active custom fields on profile page
	$cf_profile_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_profile'=>true)));
	
	$cf_noton_profile = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_profile'=> false)));
	
	//merge - issue #1573
	if(isset($show_membership_fields_arr) && is_array($show_membership_fields_arr) && !empty($show_membership_fields_arr)){
		$cf_profile_page = array_merge($cf_profile_page,$show_membership_fields_arr);
		$cf_noton_profile = array_merge($cf_noton_profile,$show_membership_fields_arr);
	}
		
	$error_html ='';
	//issue #867
	$css_group = mgm_get_css_group();
	if($css_group !='none') {
		// error_html
		$error_html .= '<link rel="stylesheet" href="'. MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.messages.css'.'" type="text/css" media="all" />';
	}
	
	// update
	if($edit_mode){
		// updated
		if ( isset($_POST['method']) && $_POST['method'] == 'update_user' ) {	
			// check security before processing form				
			if ( !wp_verify_nonce(mgm_post_var('_mgmnonce_user_profile'), 'user_profile') ) 
				mgm_security_error('user_profile'); 
					
			// user lib
			if ( mgm_compare_wp_version('3.1', '<') ){// only before 3.1
				require_once( ABSPATH . WPINC . '/registration.php');
			}
			// callback
			do_action('personal_options_update', $current_user->ID);	
			// not multisite, duplicate email allowed ?	
			if ( !is_multisite() ) {
				// save
				$errors = mgm_user_profile_update($current_user->ID);
			}else {
			// multi site
				// get user
				$user = get_userdata( $current_user->ID );
				// update here:
				// Update the email address, if present. duplicate check
				if ( $user->user_login && isset( $_POST[ 'user_email' ] ) && is_email( $_POST[ 'user_email' ] ) && $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->signups} WHERE user_login = %s", $user->user_login ) ) )
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->signups} SET user_email = %s WHERE user_login = %s", $_POST[ 'user_email' ], $user->user_login ) );
				
				// edit 
				if ( !isset( $errors ) || ( isset( $errors ) && is_object( $errors ) && false == $errors->get_error_codes() ) )
					$errors = mgm_user_profile_update($current_user->ID);
			}
			// trap erros
			if ( !is_wp_error( $errors ) ) {
				// redirect							
				mgm_redirect(add_query_arg( array('updated' => 'true'), $form_action));				
			}	
			
			// errors
			if(isset($errors) && !is_numeric($errors)) {				
				// get error
				$error_html .= mgm_set_errors($errors, true);
			}	
		}
	}	
	
	// updated
	if ($edit_mode && isset($_GET['updated']) ){
		$error_html  .= '<div class="mgm_message_success">';
		$message     = apply_filters('mgm_profile_edit_message', __('User updated.', 'mgm'));
		$error_html .= '<div><strong>'.$message.'</strong></div></div>';
	}
	
	// 	get row row template
	$form_row_template = $system_obj->get_template('profile_form_row_template');
	
	// get template row filter, mgm_profile_form_row_template for edit, mgm_profile_form_row_template_view for public view
	$form_row_template = apply_filters('mgm_profile_form_row_template'.(!$edit_mode ? '_view': ''), $form_row_template);		
	
	$cf_order = array();
	foreach($cf_profile_page as $fld){
		$cf_order[] = array('field' => $fld['name']);
	}
	// auto generate form template
	// form_template
	$form_template = '';
	// captured 
	$fields_captured = array();	
	// get field_groups
	$field_groups = mgm_get_config('profile_field_groups', array());
	// loop groups
	foreach($field_groups as $group=>$group_fields){
		if($group == 'Photo') {
			$photo_exists = false;
			foreach($cf_profile_page as $photo){				
				if($photo['name'] == 'photo') {
					$photo_exists = true;
					break;
				}
			}
			if(!$photo_exists) continue;
		}
		
		$fields_with_order = array();
		
		//issue #1197
		$css_title = function_exists('mb_strtolower') ? @mb_strtolower($group) : strtolower($group);
		$css_title = str_replace(' ','_',$css_title);
		// group
		// PREV CODE
		$form_template .= sprintf('<span class="profile_group_%s">%s</span>',$css_title,$group);
		// loop to create form template
		foreach($group_fields as $group_field) {
			// skip password
			//if(!$edit_mode && $group_field == 'password') continue;		
			if(!$edit_mode && in_array($group_field, array('password','password_conf'))) continue;		
			// set not found
			$captured = false;
			// first check if in custom fields
			foreach($cf_profile_page as $field){
				// skip password in non edit mode				
				if($field['name'] == $group_field){
					// set found
					$captured = true;
					// skip password
					//if(!$edit_mode && $field['name'] == 'password') continue;	
					if(!$edit_mode && in_array($field['name'],array('password','password_conf'))) continue;	
					// store for no repeat
					$fields_captured[] = $field['name'];
					// field wrapper
					$wrapper_ph = sprintf('[user_field_wrapper_%s]',$field['name']);	
					// field label
					$label_ph = sprintf('[user_field_label_%s]',$field['name']);		
					// field/html element
					$element_ph = sprintf('[user_field_element_%s]',$field['name']);
					// set element name
					// PREV CODE
					//$form_template .= str_replace(array('[user_field_wrapper]','[user_field_label]','[user_field_element]'),array($wrapper_ph,$label_ph,$element_ph),$form_row_template);
					// Issue #1149
					foreach ($cf_order as $index => $cfo) {
						if ($cfo['field'] == $field['name']) {
							$fields_with_order[$index] = str_replace(array('[user_field_wrapper]','[user_field_label]','[user_field_element]'),array($wrapper_ph,$label_ph,$element_ph),$form_row_template);
							break;	
						}
					}
					// break;
					break;
				}
			}
			
			// if not captured
			if(!$captured){
				$continue = false;
				foreach($cf_noton_profile as $cffield){			
					if($cffield['name'] == $group_field) {
						$continue = true;				
						break;
					}
				}
				// break;
				if($continue) continue;
				// check set
				if( !isset($profile_fields[$group_field]['name'])) continue;			
				// field wrapper
				$wrapper_ph = sprintf('[user_field_wrapper_%s]',$profile_fields[$group_field]['name']);						
				// field label
				$label_ph = sprintf('[user_field_label_%s]',$profile_fields[$group_field]['name']);		
				// field/html element
				$element_ph = sprintf('[user_field_element_%s]',$profile_fields[$group_field]['name']);
				// set element name
				// PREV CODE
				// $form_template .= str_replace(array('[user_field_wrapper]','[user_field_label]','[user_field_element]'),array($wrapper_ph,$label_ph,$element_ph),$form_row_template);
				// Issue #1149
				$field_added = false;
				foreach ($cf_order as $index => $cfo) {
					if ($cfo['field'] == $group_field) {
						$fields_with_order[$index] = str_replace(array('[user_field_wrapper]','[user_field_label]','[user_field_element]'),array($wrapper_ph,$label_ph,$element_ph),$form_row_template);
						$field_added = true;
						break;	
					}
				}
				if (!$field_added) {
					/*$fields_with_order[$index+1] = str_replace(array('[user_field_wrapper]','[user_field_label]','[user_field_element]'),array($wrapper_ph,$label_ph,$element_ph),$form_row_template);*/
					
					// default profile instalation fields - issue #1207
					if(in_array($group_field,array('username','email','password','password','password_conf'))) {					
						$fields_with_order[$temp++] = str_replace(array('[user_field_wrapper]','[user_field_label]','[user_field_element]'),array($wrapper_ph,$label_ph,$element_ph),$form_row_template);				
					}					
				}				
			}
		}
		// Issue #1149
		// Process custom field form_template with order
		if (!empty($fields_with_order)) {
			// Sort by key
			ksort($fields_with_order);
			// Loop through fields and attach html
			foreach ($fields_with_order as $fworder) {
				$form_template .= $fworder;
			}
		}
	}
	// other
	$other_header = false;
	// loop to create form template
	foreach($cf_profile_page as $field){
		// skip password in non edit mode
		//if(!$edit_mode && $field['name'] == 'password') continue;		
		if(!$edit_mode && in_array($field['name'],array('password','password_conf'))) continue;		
		// skip captured
		if(in_array($field['name'],$fields_captured)) continue;		
		// header
		if(!$other_header){
			// rest
			$form_template .= sprintf('<span class="profile_group_others">%s</span>',__('Others','mgm'));
			$other_header = true;
		}
		// field wrapper
		$wrapper_ph = sprintf('[user_field_wrapper_%s]',$field['name']);
		// field label
		$label_ph = sprintf('[user_field_label_%s]',$field['name']);		
		// field/html element
		$element_ph = sprintf('[user_field_element_%s]',$field['name']);
		
		// template for show_public_profile
		if($field['name'] == 'show_public_profile'){
			// template
			$form_row_template_pf = $system_obj->get_template('register_form_row_autoresponder_template');
			// set element place holders
			$form_template .= str_replace(array('[user_field_wrapper]','[user_field_label]','[user_field_element]'),array($wrapper_ph,$label_ph,$element_ph),$form_row_template_pf);
		}else{
			// set element name
			$form_template .= str_replace(array('[user_field_wrapper]','[user_field_label]','[user_field_element]'),array($wrapper_ph,$label_ph,$element_ph),$form_row_template);
		}		
				
	}
	// get template filter, mgm_profile_form_template for edit, mgm_profile_form_template_view for public view
	$form_template = apply_filters('mgm_profile_form_template'.(!$edit_mode ? '_view': ''), $form_template);
	
	// now replace and create the fields
	$form_html = $form_template;
	
	// get mgm_form_fields generator
	$form_fields = new mgm_form_fields(array('wordpres_form'=>false));
	
	$arr_images = array();
	// loop custom fields to replace form labels/elements
	foreach($cf_profile_page as $field){
		// skip password in non edit mode
		//if(!$edit_mode && $field['name'] == 'password') continue;	
		if(!$edit_mode && in_array($field['name'],array('password','password_conf'))) continue;	
		
		if($edit_mode && $field['type'] == 'image')
			if(!in_array($field['name'], $arr_images ))	
				$arr_images[] = $field['name'];
		
		// field wrapper
		$wrapper_ph = sprintf('[user_field_wrapper_%s]',$field['name']);		
		// field label
		$label_ph = sprintf('[user_field_label_%s]', $field['name']);
		// field/html element
		$element_ph = sprintf('[user_field_element_%s]',$field['name']);
		
		// edit mode
		if($edit_mode){	
			// for username 
			if($field['name'] =='username'){
				
				//localazing the label  issue# 617
				$label_lcz = mgm_stripslashes_deep($field['label']);
				$label_lcz = __($label_lcz,'mgm');
				$field['label'] = sprintf('%s (<em>%s</em>)',$label_lcz,__('Username not changeable','mgm'));

			}elseif($field['name'] =='password'){

				//localazing the label  issue# 617
				$label_lcz = mgm_stripslashes_deep($field['label']);
				$label_lcz = __($label_lcz,'mgm');
				$field['label'] = sprintf('%s (<em>%s</em>)',$label_lcz,__('Leave blank if don\'t wish to update','mgm'));
			}
		}else{
			// for display_name 
			if($field['name'] == 'display_name'){
				$field['label'] = __('Display Name','mgm');
			}
		}	
		
		// replace wrapper
		$form_html = str_replace($wrapper_ph, $field['name'].'_box', $form_html);

		//localazing the label  issue# 617
		$label_lcz = mgm_stripslashes_deep($field['label']);
		$label_lcz = __($label_lcz,'mgm');

		// replace label(hidden) - issue #1050
		$form_html = str_replace($label_ph, ($field['attributes']['hide_label']?'': mgm_stripslashes_deep($field['label'])), $form_html);
		
		// replace label
		$form_html = str_replace($label_ph, $label_lcz, $form_html);		
	
		// selected value
		if(isset($profile_fields[$field['name']]) && isset($user->$profile_fields[$field['name']]['name'])){ // wp alias'
			// value
			$value = $user->$profile_fields[$field['name']]['name'];
			// birthdate
			if($field['name'] == 'birthdate') {
				// convert saved date to input field format
				$value = mgm_get_datepicker_format('date', $value);
			}elseif ($field['type'] == 'checkbox') {
				//$options = preg_split('/[;,]/', $field['options']); 
				//$value  = preg_split('/[;,\s]/', $value);
				//issue #1070
				$value = @unserialize($value);
				// pass " " as value to prevent the default value getting selected, if no option is selected
				$value = empty($value) ? " " : $value;
			}		
		}else if(isset($member->custom_fields->$field['name'])){// custom field
			// value
			$value = $member->custom_fields->$field['name'];
			// birthdate
			if($field['name'] == 'birthdate') {		
				// convert saved date to input field format		
				$value = mgm_get_datepicker_format('date', $value);
			}elseif ($field['type'] == 'checkbox') {
				//$options = preg_split('/[;,]/', $field['options']); 
				//$value  = preg_split('/[;,\s]/', $value);
				//issue #1070
				$value = @unserialize($value);
				// pass " " as value to prevent the default value getting selected, if no option is selected
				$value = empty($value) ? " " : $value;
			}
			//issue #1484
			if($field['name'] == 'show_public_profile' && $field['type'] == 'checkbox') {
				$value = $member->custom_fields->$field['name'];
			}
		
		}else if(isset($user->$field['name'])){// object var	
			// value
			$value = $user->$field['name'];
		}else{// none
			// default
			$value = '';
		}	
		
		// dont set value for password
		if(in_array($field['name'],array('password','password_conf'))) $value = '';
		
		// disable username
		if($field['name'] == 'username') $field['attributes']['readonly'] = true;
		
		// nickname
		if($field['name'] == 'nickname') $field['attributes']['required'] = true;
		
		// edit mode
		if($edit_mode){
			
			if($field['name'] == 'show_public_profile') {
				//echo "xxx".$form_fields->get_field_element($field,'mgm_profile_field',$value);
			}
		// replace element
			$form_html = str_replace($element_ph,$form_fields->get_field_element($field,'mgm_profile_field',$value),$form_html);
		}else{
		// view		
			// country
			if($field['name'] == 'country') {
				$value = mgm_country_from_code($value);
			}elseif ($field['name'] == 'photo' && !empty($value)) {
				$value = sprintf('<img src="%s" alt="%s" >', $value, basename($value) );
			}
			// replace element	
			$form_html = str_replace($element_ph,$value,$form_html);
		}
	}	
	
	// loop default fields to replace form elements
	foreach($profile_fields as $field_key=>$field){
		// skip password in non edit mode
		//if(!$edit_mode && $field['name'] == 'user_password') continue;	
		if(!$edit_mode && in_array($field['name'],array('user_password','user_password_conf'))) continue;	
		$continue = false;
		foreach($cf_noton_profile as $cffield){			
			if($cffield['name'] == $field['name']) {
				$continue = true;				
				break;
			}
		}
		if($continue) continue;		
		
		// field wrapper
		$wrapper_ph = sprintf('[user_field_wrapper_%s]',$field['name']);	
		// field label
		$label_ph = sprintf('[user_field_label_%s]', $field['name']);
		// field/html element
		$element_ph = sprintf('[user_field_element_%s]', $field['name']);
		
		// edit mode
		if($edit_mode){	
			// for username 
			if($field['name'] =='user_login'){

				//localazing the label  issue# 617
				$label_lcz = mgm_stripslashes_deep($field['label']);
				$label_lcz = __($label_lcz,'mgm');

				$field['label'] = sprintf('%s (<em>%s</em>)',$label_lcz,__('Username not changeable','mgm'));
			}elseif($field['name'] =='user_password'){

				//localazing the label  issue# 617
				$label_lcz = mgm_stripslashes_deep($field['label']);
				$label_lcz = __($label_lcz,'mgm');

				$field['label'] = sprintf('%s (<em>%s</em>)',$label_lcz,__('Leave blank if don\'t wish to update','mgm'));
			}
		}else{
			// for display_name 
			if($field['name'] == 'display_name'){
				$field['label'] = __('Display Name','mgm');
			}
		}
		
		// replace wrapper
		$form_html = str_replace($wrapper_ph, $field['name'].'_box', $form_html);
			
		//localazing the label  issue# 617
		$label_lcz = mgm_stripslashes_deep($field['label']);
		$label_lcz = __($label_lcz,'mgm');
		
		// replace label
		$form_html = str_replace($label_ph, $label_lcz, $form_html);
		
		// selected value
		if(isset($user->$field['name'])){ // wp alias
			$value = $user->$field['name'];
		}else if(isset($member->custom_fields->$field_key)){// custom field
			$value = $member->custom_fields->$field_key;		
		}else{// none
			$value = '';
		}
		// dont set value for password
		//if($field['name'] == 'user_password') $value = '';	
		if(in_array($field['name'],array('user_password','user_password_conf'))) $value = '';
		// edit mode
		if($edit_mode){
		// replace element			
			$form_html = str_replace($element_ph,$form_fields->get_field_element($field,'mgm_profile_field',$value),$form_html);
		}else{				
			// country
			if($field_key == 'country'){
				$value = mgm_country_from_code($value);
			}	
			// set		
			$form_html = str_replace($element_ph,$value,$form_html);
		}
	}	
	// attach scripts	
	$form_html .= mgm_attach_scripts(true, array());
	
	// range
	$yearRange = mgm_get_calendar_year_range();
	
	// append script
	$form_html .= '<script language="javascript">jQuery(document).ready(function(){try{mgm_date_picker(".mgm_date",false,{yearRange:"'.$yearRange.'", dateFormat: "'. mgm_get_datepicker_format() .'"});}catch(x){}});</script>';
		
	//include scripts for image upload:
	if(!empty($arr_images)) {		
		$form_html .= mgm_upload_script_js('profileform', $arr_images);
	}
	
	// buttun
	$button_html = '';
	// button on edit
	if($edit_mode && !$temp_edit){
		// default
		$button_html = '<div><input class="button mgm-profile-button" type="submit" name="wp-submit" id="wp-submit" value="' . __('Update','mgm') . '" /></div>';
		// apply button filter
		$button_html = apply_filters('mgm_profile_form_button', $button_html);
	}
	//profile by membership - issue #1573
	if(isset($args['membership']) && !empty($args['membership'])){	
		// hidden 		
		$button_html .= sprintf('<input type="hidden" name="membership" value="%s">', $args['membership']);
	}	
	// hidden 
	$button_html .= '<input type="hidden" name="method" value="update_user">';
	
	// nonce
	$button_html .= wp_nonce_field('user_profile', '_mgmnonce_user_profile', true, false);
	
	// temp
	if(!$temp_edit){
	// open
		$form_open  = sprintf('<form class="mgm_form" name="profileform" id="profileform" action="%s" method="post">', $form_action);
		$form_close = '</form>';
	}else{
		$form_open = $form_close = '';
	}
	// output form	
	$html = sprintf('<div class="mgm_prifile_form">%s %s %s %s %s</div>', $error_html, $form_open, $form_html, $button_html, $form_close);	
	
	//issue #1113
	$html = mgm_stripslashes_deep($html);
	
	// filter
	$html = apply_filters('mgm_user_profile_form_html', $html, $current_user);
		
	//issue #1635
	$user_profile_html = '<div class="mgm_user_profile_container">'.$html.'</div>';	
	// return 	
	return $user_profile_html;
}

/**
 * get captcha
 *
 * @param void
 * @return string
 */
function mgm_get_form_captcha(){
	// int
	$captcha_html = '';
	// custom fields
	$cf_register_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_login_widget'=>true),'name'=>'captcha'));	
	// loop
	foreach ($cf_register_page as $field){
		// captcha
		if($field['name'] == 'captcha'){			
			$captcha_html = mgm_get_class('mgm_form_fields')->field_type_captcha($field,'mgm_login_field'); break;
		}
	}
	// return 
	return $captcha_html;
}
// end file /core/libs/functions/mgm_forms.php