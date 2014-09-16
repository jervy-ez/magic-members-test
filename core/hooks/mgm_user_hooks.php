<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * user hooks and callbacks
 *
 * @package MagicMembers
 * @since 1.0
 */
// register form custom fields
add_action('register_form'                              , 'mgm_wp_register_form_additional');
// mgm custom register
add_filter('mgm_register_form'                          , 'mgm_register_form_additional', 10, 2); 
// other custom pages
add_filter('mgm_login_form'                             , 'mgm_login_form_additional'); 
add_filter('mgm_lostpassword_form'                      , 'mgm_lostpassword_form_additional'); 

// buddypress fix
if( defined('BP_VERSION') ){
	//issue #1510
	
	if (version_compare(BP_VERSION, '1.8', '>=')) {
		add_action('bp_after_signup_profile_fields'     , 'mgm_wp_register_form_additional'); // form 	
	}// issue #: 413
	elseif(mgm_check_theme_register() && version_compare(BP_VERSION, '1.8', '<')) {
		// issue #: 248		
		add_action('bp_after_signup_profile_fields'     , 'mgm_wp_register_form_additional'); // register form custom fields resplace
	}else {
		//remove buddypress redirection if register template is not found
		add_action( 'wp'                                , 'mgm_disable_bp_redirection',100 );
		remove_action( 'wp'                             , 'bp_core_catch_no_access' );
		add_action( 'template_redirect'                 , 'redirect_canonical', 10);
	}	
	
	// test
	// add_action('bp_before_directory_activity_page'      , 'mgm_bp_protect_content_before');
	// add_action('bp_after_directory_activity_page'       , 'mgm_bp_protect_content_after');
	// add_action('bp_before_directory_forums_page'      , 'mgm_bp_protect_content_before');
	// add_action('bp_after_directory_forums_page'       , 'mgm_bp_protect_content_after');
}
// not for users added via admin
add_action('register_post'                              , 'mgm_register_post', 10, 4);// after post
// replace buddypress register url with mgm register url before redirection
add_filter('mgm_bp_register_url'						, 'mgm_bp_register_url', 10,1);
// mgm register should happen after all hook executes, i.e. MGA, as MGM redirects user after processing the hook 
// (returning user_id would let other plugins hook to the same action), any plugin hooks to user_register will not be 
// executed further and consider as broken, with MGM active the MGM hook "mgm_user_register" should be used.
// after register, posted data process and redirect to payment
// bp process, use BP hook
// use the hook bp_core_signup_user only if Buddypress form is active and submitted
// Eg: if a different register page exists for BP
if( mgm_is_plugin_active('buddypress/bp-loader.php') && mgm_is_bp_submitted() ){		
	add_action('bp_core_signup_user'                    , 'mgm_register', 12, 1);
}else{	
	add_action('user_register'                          , 'mgm_register', 12, 1);
}	
// simplepress forum hook
if( mgm_is_plugin_active('simple-press/sp-control.php') ){
	add_action('mgm_user_register'                      , 'sp_create_member_data', 99); 
}
// delete
add_action( 'delete_user'								, 'mgm_delete_user');//just before delete
// -------------------------------------------------------------------------------------------
add_filter( 'wp_authenticate_user'                       , 'mgm_authenticate_user', 20);	
add_action( 'mgm_attach_scripts'                         , 'mgm_attach_scripts');// custom where ever neede
add_action( 'login_head'                                 , 'mgm_attach_scripts');// on wp-login.php only
add_action( 'profile_update'                             , 'mgm_save_custom_fields', 10, 1);// user profile custom fields save callback
add_action( 'show_user_profile'                          , 'mgm_show_custom_fields');// user profile custom fields display
add_action( 'edit_user_profile'                          , 'mgm_edit_custom_fields');// user profile custom fields display/edit
// login redirect
// add_filter('login_redirect'                          , 'mgm_login_form_redirect', 1); // deprecated
add_action( 'wp_login'                                   , 'mgm_login_redirect', 20, 2);
// add_filter('mgm_login_redirect'                      , 'mgm_login_redirect_url_ct', 10, 1);// test
// add_action('wp_login_failed'                         , 'mgm_login_failed', 20);// reserved
add_filter( 'user_contactmethods'                        , 'mgm_updte_contactmethods');//profile:contactmethods
// autoresponder always on payment return
add_action( 'mgm_return_subscription_payment'            , 'mgm_autoresponder_subscribe');
add_action( 'mgm_membership_subscription_cancelled'      , 'mgm_autoresponder_unsubscribe');
// check and reset member canceled flag for renewal
add_action( 'mgm_subscription_purchase_payment_success'  , 'mgm_reset_cancelled_member');
add_filter( 'mgm_validate_reset_password'				 , 'mgm_validate_reset_password', 10, 2);//reset password validation
add_action( 'mgm_reset_password'						 , 'mgm_reset_password', 10, 2);//reset password
add_action( 'mgm_logout'								 , 'mgm_logout');//custom logout hook
add_filter( 'logout_url'								 , 'mgm_logout_url',10,2);//custom logout url
// #1172 we will control this via setting, #1314: used in comments login url as tested
add_filter( 'login_url'								    , 'mgm_login_url',10,2);//custom login url
add_filter( 'lostpassword_url'						    , 'mgm_lostpassword_url',10,2);//custom mgm_lostpassword url
add_filter( 'site_url'								    , 'mgm_register_url',10,4);//custom register url

add_filter( 'get_avatar'								, 'mgm_get_avatar',10,2);
// hook to mgm action
add_filter( 'mgm_reassign_member_subscription'			, 'mgm_reassign_member_subscription',10,4);//reset membership
// add_filter('cron_schedules'						    , 'mgm_custom_schedules'); deprecated, moved to mgm_init
add_action( 'mgm_print_module_data'  					, 'mgm_print_module_data',10,2);
add_filter( 'mgm_module_rebill_status'  			    , 'mgm_module_rebill_status',10, 2);
add_filter( 'mgm_pre_authenticate_user'  				, 'mgm_pre_authenticate_user');
// admin user register
add_action( 'mgm_admin_user_register'                   , 'mgm_admin_user_register_process', 10, 2);
// alter user list, dev feature
add_filter( 'manage_users_columns'                      , 'mgm_manage_users_columns' );// add column
add_filter( 'manage_users_custom_column'                , 'mgm_manage_users_custom_column', 10, 3 );// show column data
// add_filter( 'user_row_actions', 'mgm_user_row_actions', 10, 2);// user_row_actions

// public profile 
add_action( 'template_redirect'                          , 'mgm_public_profile_url_redirect');
// user status change
add_action( 'mgm_user_status_change'                     , 'mgm_user_status_change_process', 10, 5);
//update coupon usage  
add_action( 'mgm_update_coupon_usage'  					 , 'mgm_update_coupon_usage_process');
// login_errors
add_filter( 'login_errors'                               , 'mgm_login_errors');
// read
add_action( 'mgm_user_options_read'                      , 'mgm_user_options_sync_read', 10, 2);
// save
add_action( 'mgm_user_options_save'                      , 'mgm_user_options_sync_save', 10, 2);
//user notification - issue #1468
add_action( 'mgm_register_user_notification'             , 'mgm_new_user_notification', 10, 2);
// add email notification on rebill
add_action( 'mgm_rebill_status_change'                   , 'mgm_notify_on_rebill_status_change', 10, 4);
//user notification - issue #1524
add_action( 'mgm_user_upgrade_notification'              , 'mgm_user_upgrade_notification_process', 10, 1);
// user unsubscribe
add_action( 'edit_user_profile'                          , 'mgm_admin_user_unsubscribe');
//Remote get time out increased
add_filter( 'http_request_timeout'                       , 'mgm_remote_get_timeout_time');
// ------------------------------- callbacks----------------------------------------------
function mgm_email_login_authenticate($user, $username, $password ){
	if ( !empty( $username ) ) {
		$username = str_replace( '&', '&amp;', stripslashes( $username ) );
		$user = get_user_by( 'email', $username );
		if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status )
			$username = $user->user_login;
	}

	return wp_authenticate_username_password( null, $username, $password );
}

function mgm_check_email_as_username(){
	// has enabled
	if(bool_from_yn(mgm_get_setting('enable_email_as_username'))){
		remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
		add_filter( 'authenticate', 'mgm_email_login_authenticate', 20, 3 );
	}
}
mgm_check_email_as_username();
/**
 * remote get time increased
 *
 * @param int $time
 * @return int $time
 */
function mgm_remote_get_timeout_time($time) {
	// get mgm_system
	$system_obj = mgm_get_class('system');
	//new number of seconds
	$request_timeout_time = $system_obj->get_setting('get_http_request_timeout');	
	//mgm_log($request_timeout_time,__FUNCTION__);		
	if(empty($request_timeout_time) || !is_numeric($request_timeout_time)){
		$request_timeout_time =5;
	}
	//mgm_log($request_timeout_time,__FUNCTION__);
	//retutn
	return $request_timeout_time;
}
/**
 * redirect to public profile
 * @todo, use .htaccess update or WP routes to control the url
 */
function mgm_public_profile_url_redirect(){
	// check
	if( bool_from_yn(mgm_get_setting('enable_public_profile')) ){
		// trim
		$current_uri = trim($_SERVER['REQUEST_URI']);
		//list($uri, $qs) = explode('?', $current_uri);
		$uri = explode('?', $current_uri);
		$uriArr = explode('/',$uri[0]);
		$cntArr = count($uriArr);		
		if (!empty($uriArr)){
			if(!empty($uriArr[$cntArr-2]) && $uriArr[$cntArr-2] == 'userprofile' && !empty($uriArr[$cntArr-1])){
				// url
				$url = network_site_url().'/userprofile/?username='.$uriArr[$cntArr-1]; 
				// redirect
				mgm_redirect($url);
			}
		}
	}
}

/**
 * extend register form and add custom user fileds
 * 
 * @uses wp action "register_form"
 * 
 * @param object $form_fields
 * @param array $args
 * @param boolean $return
 * @return string $form_html
 */
function mgm_wp_register_form_additional($form_fields=NULL, $args=NULL, $return=false){		
	// get params	
	$register_form_params = mgm_get_register_form_params($form_fields, $args);
	extract($register_form_params);
		
	// generate default template, this allows to disable it using filters
	$form_template = apply_filters('mgm_generate_register_form_template', $register_form_params);
	
	// get template filter, this allows to customize mgm_register_form_template for custom, mgm_register_form_template_wordpress for wordpress
	$form_template = apply_filters('mgm_register_form_template'.($wordpres_form ? '_wordpress': ''), $form_template);
	
	// generate html with elements, this allows to disable as add custom generator	
	$form_html = apply_filters('mgm_generate_register_form_html', $form_template, $register_form_params);
	
	// apply additional filter
	$form_html = apply_filters('mgm_register_form_additional_html', $form_html);
	
	// return 
	if($return) return $form_html;
		
	// print
	print $form_html; 	
}

/**
 * get register form params
 * 
 * @param object $form_fields
 * @param array $args
 * @return array $params
 * @since 2.6
 */
function mgm_get_register_form_params($form_fields=NULL, $args=NULL){
	// get mgm_system
	$system_obj = mgm_get_class('system');
	// from globals
	if(isset($GLOBALS['form_fields'])){
		$form_fields = $GLOBALS['form_fields'];
		unset($GLOBALS['form_fields']);
	}
	// init
	$params = array();
	// do not repeat if already called, iss #383 related
	if(!$form_fields){
		// obj
		$cf_obj = mgm_get_class('member_custom_fields');
		// get custom fields on register page
		$cf_register_page = $cf_obj->get_fields_where(array('display'=>array('on_register'=>true)));	
	    
		// #739 starts, @ToDO could we do it in one call? (on_register==true && capture_only==false)
		$cf_alias_fields = $cf_obj->get_fields_where(array('attributes'=>array('capture_only'=>true)));
		// check
		if (!empty($cf_alias_fields)) {
			// loop
			foreach ($cf_alias_fields as $key=>$array) {
				if(isset($cf_register_page[$key])){
					unset($cf_register_page[$key]);
				}
			}
		}		
		// #739 ends
		// set
		$params['cf_alias_fields'] = $cf_alias_fields;
		$params['cf_register_page'] = $cf_register_page;
		
		// wordpress register
		$params['wordpres_form'] = mgm_check_wordpress_login();
		
		// 	get row row template
		$params['form_row_template'] = $system_obj->get_template('register_form_row_template');
		
		// get template row filter, mgm_register_form_row_template for custom, mgm_register_form_row_template_wordpress for wordpress
		$params['form_row_template'] = apply_filters('mgm_register_form_row_template'.($params['wordpres_form'] ? '_wordpress': ''), $params['form_row_template']);
		
		// get mgm_form_fields generator
		$params['form_fields'] = new mgm_form_fields(array('wordpres_form'=>$params['wordpres_form'],'form_row_template'=>$params['form_row_template']));
		
		// args
		$params['args'] = $args;
	}else{
		// get mgm_form_fields generator
		$params['form_fields'] = $form_fields;
		// wordpress register
		$params['wordpres_form'] = $form_fields->get_config('wordpres_form');
		// 	get row row template
		$params['form_row_template'] = $form_fields->get_config('form_row_template');
		// cf_register_page		
		$params['cf_register_page'] = $form_fields->get_config('cf_register_page');
		// args
		$params['args'] = $form_fields->get_config('args');
	}	
	
	// return
	return $params;
}


/**
 * generate custom register page default fields layout
 * 
 * @uses mgm filter "mgm_generate_register_form_template"
 *
 * @param array $form_params
 * @return sting $form_template
 * @since 2.6
 */
function mgm_generate_register_form_template($form_params){
	// get mgm_system
	$system_obj = mgm_get_class('system');
	// no custom fields
	$hide_custom_fields = $system_obj->get_setting('hide_custom_fields');
	// extract
	extract($form_params);		
	// form_template
	$form_template = '';
	$paid_modules = array();	
	$arr_modulestoskip = array('mgm_free', 'mgm_trial');
	$obj_packs = mgm_get_class('subscription_packs');
	// rewrite args if suplied through url		
	if ($args_package = mgm_request_var('package', '', true)) {
		$args['package'] = base64_decode($args_package);
	}		
	// if single package
	if (isset($args['package'])) {		
		
		if(count(explode("#", $args['package'])) == 1) { 
			$pack_list_data = explode("#", $args['package']);
			$pack_list_data[1] = false;
		}else {
			$pack_list_data = explode("#", $args['package']);
		}
		
		list($mtype, $packid) = $pack_list_data;
				
		$mtype = mgm_get_class('membership_types')->get_type_code($mtype);
		if ($packid) {
			$packs = array(0 => $obj_packs->get_pack($packid));
		}else {
/*			$reg_packs = $packs = $obj_packs->get_packs('register');
			$packs = array();
*/			
			//issue #1398
			$packs = array();
			// selected subscription	
			$selected_subs = mgm_get_selected_subscription($args);													
			// packs
			$reg_packs = $packs = $obj_packs->get_packs('register', true, $selected_subs);
			
			foreach ($reg_packs as $rp) {
				if ($rp['membership_type'] == $mtype) {
					$packs[] = $rp;
				}
			}
		}
	}else {
		$packs = $obj_packs->get_packs('register');
	}	
	
	// log
	// mgm_pr($cf_register_page);	
	// loop to create form template	
	foreach($cf_register_page as $field){
		// skip custom fields by settings call
		if(($hide_custom_fields == 'Y') || ($hide_custom_fields == 'W' && $wordpres_form) || ($hide_custom_fields == 'C' && !$wordpres_form)){
			// some fields required irespective of settings
		 	if(!in_array($field['name'], array('subscription_options','payment_gateways'))) continue;		
		} 
		// skip username
		if($field['name'] == 'username' && bool_from_yn($system_obj->get_setting('enable_email_as_username'))){
			continue;
		}
		// gateway 
		if($field['name'] == 'payment_gateways') {			
			$total_amount = 0;			
			foreach ($packs as $pack) {				
				$total_amount += $pack['cost'];
				//check enabled modules
				if (!empty($pack['modules']))
					foreach ($pack['modules'] as $mod) {
						if(!in_array($mod, $arr_modulestoskip)) {
							$paid_modules[$mod] = true;
						}
					}
			}	
			//Skip payment gateways if total amount is 0		
			if(!$total_amount) continue;
		}		
		// field wrapper place holder
		$wrapper_ph = sprintf('[user_field_wrapper_%s]',$field['name']);	
		// field label 
		// if hiddden field, do not show label
		if($field['type'] == 'hidden') {
			$label_ph = '';
		}else {	
			//skip label if only one payment module is enabled, The module will be internally processed in this case
			$label_ph = (count($paid_modules) == 1 && $field['name'] == 'payment_gateways') ? '' : sprintf('[user_field_label_%s]', $field['name']);
		}			
		// field/html element
		$element_ph = sprintf('[user_field_element_%s]',$field['name']);
		// template for autoresponder// TODO check template for each individual field in theme folder
		if($field['name'] == 'autoresponder' || $field['name'] == 'show_public_profile'){
			// template
			$form_row_template_ar = $system_obj->get_template('register_form_row_autoresponder_template');
			// set element place holders
			$form_template .= str_replace(array('[user_field_wrapper]','[user_field_label]','[user_field_element]'),array($wrapper_ph,$label_ph,$element_ph),$form_row_template_ar);
		}else{
			// set element place holders
			$form_template .= str_replace(array('[user_field_wrapper]','[user_field_label]','[user_field_element]'),array($wrapper_ph,$label_ph,$element_ph),$form_row_template);
		}			
	}	
	//issue #1510
	if(defined('BP_VERSION') && BP_VERSION =='1.8'){
		
		$clearboth ='<p style="clear:both;"></p>';	
		
		$f_template = $clearboth;
		
		$f_template .=$form_template;
		// return 
		return $f_template;
	}else {
		// return 
		return $form_template;		
	}

}
// add
add_filter('mgm_generate_register_form_template', 'mgm_generate_register_form_template', 20, 1);

/**
 * generate register form html
 *
 * @param string $form_template
 * @param array $form_params
 * @return string $form_html
 * @since 2.6
 */
function mgm_generate_register_form_html($form_template, $form_params){
	// get mgm_system
	$system_obj = mgm_get_class('system');
	// no custom fields
	$hide_custom_fields = $system_obj->get_setting('hide_custom_fields');
	// extract params
	extract($form_params);
	// init images
	$cf_images = array();
	// init
	$form_html = $form_template;
	// loop to create form html
	foreach($cf_register_page as $field){
		// skip custom fields by settings call
		if(($hide_custom_fields == 'Y') || ($hide_custom_fields == 'W' && $wordpres_form) || ($hide_custom_fields == 'C' && !$wordpres_form)){
			// some fields required irespective of settings
			if(!in_array($field['name'], array('subscription_options','payment_gateways'))) continue;
		}	
		// skip username
		if($field['name'] == 'username' && bool_from_yn($system_obj->get_setting('enable_email_as_username'))){
			continue;
		}
		// image field
		if($field['type'] == 'image')
			if(!in_array($field['name'], $cf_images ))	
				$cf_images[] = $field['name'];	
				
		// field wrapper
		$wrapper_ph = sprintf('[user_field_wrapper_%s]',$field['name']);
		// field label
		$label_ph = sprintf('[user_field_label_%s]',$field['name']);
		// field/html element
		$element_ph = sprintf('[user_field_element_%s]',$field['name']);
		// replace wrapper
		$form_html = str_replace($wrapper_ph, $field['name'].'_box', $form_html);
		// replace label
		$form_html = str_replace($label_ph, ($field['attributes']['hide_label']? '' : mgm_stripslashes_deep($field['label'])), $form_html);
		// replace element
		$form_html = str_replace($element_ph, $form_fields->get_field_element($field, 'mgm_register_field'), $form_html);
	}
	
	// years	
	$yearRange = mgm_get_calendar_year_range();
	
	// append script
	$form_html .= '<script language="javascript">jQuery(document).ready(function(){
					try{mgm_date_picker(".mgm_date",false,{yearRange:"'.$yearRange.'", 
					dateFormat: "'. mgm_get_datepicker_format() .'"});}catch(x){}});</script>';	
	
	// deault action		
	$form_action = mgm_get_custom_url('register');
	
	// membership
	if($membership = mgm_request_var('membership', '', true)) {
		$form_action = add_query_arg(array('membership' => $membership), $form_action);
	}
	
	// package	
	if($package = mgm_request_var('package', '', true)) {
		$form_action = add_query_arg(array('package' => $package), $form_action);
	}
	
	// add script for m/p
	if(!empty($package) || !empty($membership)) {
		//i ssue#: 482		
		$form_html .= '<script language="javascript">jQuery(document).ready(function(){jQuery(\'#registerform\').attr(\'action\',\''.$form_action.'\')});</script>';
	}	
	
	// include scripts for image upload:
	if(!empty($cf_images)) {
		$form_html .= mgm_attach_scripts(true, array()) . mgm_upload_script_js('registerform', $cf_images);
	}	
	
	// return
	return $form_html;
}
// add
add_filter('mgm_generate_register_form_html', 'mgm_generate_register_form_html', 20, 2);


/**
 * register post, validate required custom fields
 *
 * @param string $sanitized_user_login 
 * @param string $user_email
 * @param object $errors
 * @param boolean $show_fields
 * @return object $errors
 */
function mgm_register_post($sanitized_user_login = '', $user_email = '', $errors = null, $show_fields = null) {	
	// get mgm_system
	$system_obj = mgm_get_class('system');
	// hide
	$hide_custom_fields = $system_obj->get_setting('hide_custom_fields');
	// error
	if(is_null($errors)) $errors = new WP_Error();
	// unset old errors
	unset( $errors->errors );	
	// errors
	$error_codes = $errors->get_error_codes();
	// user_login
	if(array_key_exists('user_login', $_POST) ) {			
		$sanitized_user_login = sanitize_user($_POST['user_login'] );			
		if ( $sanitized_user_login == '' ) {
			if(!in_array('empty_username', $error_codes))
				$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Please enter a username.','mgm' ) );
		} elseif ( ! validate_username( $sanitized_user_login ) ) {
			if(!in_array('invalid_username', $error_codes))
				$errors->add( 'invalid_username', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.','mgm' ) );
			$sanitized_user_login = '';
		} elseif ( ! mgm_validate_username( $sanitized_user_login ) ) {
			if(!in_array('invalid_username', $error_codes))
				$errors->add( 'invalid_username', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters, spaces are not allowed. Please enter a valid username.','mgm' ) );
			$sanitized_user_login = '';		
		} elseif ( username_exists( $sanitized_user_login ) ) {
			if(!in_array('username_exists', $error_codes))
				$errors->add( 'username_exists', __( '<strong>ERROR</strong>: This username is already registered, please choose another one.','mgm' ) );
		}			
	}
	
	// user_email
	if(array_key_exists('user_email', $_POST) ) {
		$user_email = apply_filters( 'user_registration_email', $_POST['user_email'] );
		// Check the e-mail address
		if ( $user_email == '' ) {
			if(!in_array('empty_email', $error_codes))
				$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please type your e-mail address.','mgm' ) );
		} elseif ( ! is_email( $user_email ) ) {
			if(!in_array('invalid_email', $error_codes))
				$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.','mgm' ) );
			$user_email = '';
		} elseif ( email_exists( $user_email ) ) {
			if(!in_array('email_exists', $error_codes)){				
				$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.' ,'mgm' ) );
			}
		}
	}	
	
	// check email only #1106
	if(in_array('email_exists', $errors->get_error_codes())){
		$label = 'email';
		$url   = mgm_get_complete_registration_url('email', $user_email);		
		// check
		if($url !== FALSE){
			// unset old errors
			unset( $errors->errors );	
			// set
			$errors->add( 'unfinished_registration', sprintf(__( '<strong>ERROR</strong>: This %s has an unfinished registration. Click here to <a href="%s">complete</a>.' ,'mgm' ), $label, $url) );		
			// return form here
			return $errors;
		}	
	}
	
	// get custom fields	
	$cf_register_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true)));	
	
	//#739 modified starts
	if (empty($show_fields)) {
		$cf_alias_fields = mgm_get_class('member_custom_fields')->get_fields_where(array('attributes'=>array('capture_only'=>true)));	
		if (!empty($cf_alias_fields)) {
			foreach ($cf_alias_fields as $key=>$array) {
				unset($cf_register_page[$key]);
			}
		}
	}
	// #739 modified ends
	
	// #740 starts	
	$args_fields =  "";
	// Show fields in short code to filter the registration form #Issue 740
	if (isset($show_fields)) {
		$package = isset($args['package']) ? $args['package'] : NULL;
		$args_fields = $show_fields;
		if (!empty($args_fields)) {
			$cf_register_page = mgm_show_fields_result($args_fields, $cf_register_page, $package);
		}	
	}  
	// #740 ends

	$check = 0;
	if(isset($_POST['mgm_widget_active'])){
		if(isset($_POST['mgm_custom_fields']) && $_POST['mgm_custom_fields'] == 1) $check = $_POST['mgm_custom_fields'];
	}else {
		$check = 1;
	}
	// wordpress register
	$wordpres_form = mgm_check_wordpress_login();	
	// check
	if ($check) {
		// loop
		foreach($cf_register_page as $field){					
			// skip custom fields by settings call		
			if(($hide_custom_fields == 'Y') || ($hide_custom_fields == 'W' && $wordpres_form) || ($hide_custom_fields == 'C' && !$wordpres_form)){	
				// some are required
				if( !in_array($field['name'], array('subscription_options','payment_gateways'))) continue;
			}
			// skip default fields, validated already
			if( in_array($field['name'], array('username', 'email')) ) continue;
			// by name
			switch( $field['name'] ){
				case 'terms_conditions':
				// terms & conditions
					if ( !isset($_POST['mgm_tos']) || empty($_POST['mgm_tos']) ) {
						$errors->add('mgm_tos',  __('<strong>ERROR</strong>: You must accept the Terms and Conditions.','mgm'));
					}
				break;
				case 'subscription_options':
				// subscription options	
					if ( !isset($_POST['mgm_subscription']) || empty($_POST['mgm_subscription']) ) {
						$errors->add('mgm_subscription', __('<strong>ERROR</strong>: You must select a Subscription Type.','mgm'));
					}
				break;
				case 'payment_gateways':
				// payment gateways
					if ( isset($_POST['mgm_subscription'])) { 			
						// pack	
						$sub_pack = mgm_decode_package($_POST['mgm_subscription']);	
						// check			
						if(isset($sub_pack['pack_id'])) {
							$pack         = mgm_get_class('subscription_packs')->get_pack($sub_pack['pack_id']);
							$pack_modules = array_diff($pack['modules'], array('mgm_free', 'mgm_trial'));// take paid module
							// validate					
							if(!empty($pack_modules) && (!isset($_POST['mgm_payment_gateways']) || (isset($_POST['mgm_payment_gateways']) && empty($_POST['mgm_payment_gateways'])))) {
								$errors->add('mgm_subscription', __('<strong>ERROR</strong>: You must select a Payment Gateway.','mgm'));	
							}
						}				
					}
				break;
				case 'coupon':
					if ( isset($_POST['mgm_register_field']['coupon']) && !empty($_POST['mgm_register_field']['coupon']) ) {
						// coupon
						if($coupon_code = trim($_POST['mgm_register_field']['coupon'])){
							// check if its a valid coupon
							if(!$coupon = mgm_get_coupon_data($coupon_code)){
								$errors->add('mgm_coupon', sprintf(__('<strong>ERROR</strong>: Coupon Code "%s" is not valid, use a valid coupon only.','mgm'), $coupon_code));
							}else{
								// get subs 			
								if( $subs_pack = mgm_decode_package(mgm_post_var('mgm_subscription')) ){						
									// values
									$coupon_values = mgm_get_coupon_values(NULL, $coupon['value'], true);
									// check
									if(isset($coupon_values['new_membership_type']) && $coupon_values['new_membership_type'] != $subs_pack['membership_type']){
										$new_membership_type = mgm_get_membership_type_name($coupon_values['new_membership_type']);
										$errors->add('mgm_coupon', sprintf(__('<strong>ERROR</strong>: Coupon Code "%s" is only available with Membership Type "%s".','mgm'), $coupon_code, $new_membership_type));
									}
								}	
							}	
						}					
					}elseif((bool)$field['attributes']['required'] === true){						
						$errors->add('mgm_coupon', sprintf(__('<strong>ERROR</strong>: Please enter a valid coupon code.','mgm')));
					}
				break;
				case 'birthdate':
					// validate age
					if(isset($_POST['mgm_register_field'][$field['name']]) && !empty($_POST['mgm_register_field'][$field['name']])){
						// format
						$short_format = mgm_get_date_format('date_format_short');
						// date
						$birthdate = mgm_format_inputdate_to_mysql($_POST['mgm_register_field'][$field['name']],$short_format);
						// current date
						$current_date = mgm_get_current_datetime('Y-m-d H:i:s');	
						// add
						if( strtotime($birthdate) > $current_date['timestamp']){
							$errors->add($field['name'], __('<strong>ERROR</strong>: Birthdate should not be in future.','mgm'));
						}else if( isset($field['attributes']['verify_age'])) {
						// age
							$unit = (int)$field['attributes']['verify_age_unit'];
							$period = $field['attributes']['verify_age_period'];
							// check
							if( $field['attributes']['verify_age'] == 1 && (int)$field['attributes']['verify_age_unit'] > 0){
								// verify_age_period
								$birthdate_should = strtotime( sprintf('-%d %s', $unit, $period), $current_date['timestamp']);
								if( strtotime($birthdate) > $birthdate_should ){
									$errors->add($field['name'], sprintf(__('<strong>ERROR</strong>: Birthdate should be on or before %s.','mgm'), date($short_format, $birthdate_should)));
								}	
							} 	
						}
						
					}
					// left other process run
				default:
					// on type
					switch( $field['type'] ){
						case 'captcha':
						// captcha
							if ( (!isset($_POST['recaptcha_response_field'])) || (empty($_POST['recaptcha_response_field'])) ) {
								$errors->add('mgm_captcha', __('<strong>ERROR</strong>: You must enter the Captcha String.','mgm'));
							}else {					
								$captcha = mgm_get_class('recaptcha')->recaptcha_check_answer($_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );				
								if(!isset($captcha->is_valid) || !$captcha->is_valid ) {					
									$errors->add('mgm_captcha', __('<strong>ERROR</strong>: '.(!empty($captcha->error) ? $captcha->error : 'The Captcha String isn\'t correct.') ,'mgm'));	
								}
							}
						break;
						default:
							// check register and required		
							if((bool)$field['attributes']['required'] === true){		
								// error
								$error_codes = $errors->get_error_codes();
								// validate other				
								// confirm password
								if($field['name'] == 'password' || $field['name'] == 'password_conf' )
								{					
									if ( ($field['name'] == 'password' && (!isset($_POST['user_password']) || empty($_POST['user_password']))) || 
										( $field['name'] == 'password_conf' && (!isset($_POST['user_password_conf']) || empty($_POST['user_password_conf']))))
									{						
										// issue #703
										$errors->add($field['name'], __('<strong>ERROR</strong>: You must provide a ','mgm').mgm_stripslashes_deep($field['label']).'.');							
									}
									//issue #973
									elseif($field['name'] == 'password' && !empty($_POST['user_password']) && !empty($_POST['user_password_conf']) && 
											((isset($field['attributes']['password_min_length']) && $field['attributes']['password_min_length'] == true) || 
											 (isset($field['attributes']['password_min_length']) && $field['attributes']['password_max_length'] == true))
										  )
									{
										if(strlen($_POST['user_password']) < $field['attributes']['password_min_length_field_alias'] ||
											strlen($_POST['user_password_conf']) < $field['attributes']['password_min_length_field_alias'] ){
											$errors->add($field['name'], sprintf(__('<strong>ERROR</strong>:%s is too short, minimum %d characters.','mgm'),
											mgm_stripslashes_deep($field['label']),$field['attributes']['password_min_length_field_alias']));
										}elseif(strlen($_POST['user_password']) > $field['attributes']['password_max_length_field_alias'] || 
											strlen($_POST['user_password_conf']) > $field['attributes']['password_max_length_field_alias'] ){
											$errors->add($field['name'], sprintf(__('<strong>ERROR</strong>:%s is too long, maximum %d characters.','mgm'),
											mgm_stripslashes_deep($field['label']),$field['attributes']['password_max_length_field_alias']));
										}
										elseif($field['name'] == 'password' && !empty($_POST['user_password']) && !empty($_POST['user_password_conf']) && 
											$_POST['user_password'] != $_POST['user_password_conf'] ){								
											$errors->add($field['name'], __('<strong>ERROR</strong>: Password does not match. Please re-type.','mgm'));
										}							
									}
									elseif($field['name'] == 'password' && 
										!empty($_POST['user_password']) && 
										!empty($_POST['user_password_conf']) && 
										$_POST['user_password'] != $_POST['user_password_conf'] )
									{
										
										$errors->add($field['name'], 
											__('<strong>ERROR</strong>: Password does not match. Please re-type.','mgm'));
									}													
								}else{
									//issue #1315
									if($field['name'] == 'user_email' || $field['name'] == 'email_conf' ) {
										
										if( $field['name'] == 'email_conf' && (!isset($_POST['user_email_conf']) || empty($_POST['user_email_conf']))) {
											$errors->add($field['name'],__('<strong>ERROR</strong>: Please type your confirm e-mail address.','mgm'));
										}elseif ( ! is_email( $_POST['user_email_conf'] ) ) {
											$errors->add( 'invalid_email_conf', __( '<strong>ERROR</strong>: The confirm email address isn&#8217;t correct.','mgm' ) );
										}elseif ( email_exists( $_POST['user_email_conf'] ) ) {						
											$errors->add( 'email_conf_exists', __( '<strong>ERROR</strong>: This confirm email is already registered, please choose another one.' ,'mgm' ) );
										}elseif (is_email( $_POST['user_email'] ) && $_POST['user_email_conf'] != $_POST['user_email']){
											$errors->add($field['name'], 
												__('<strong>ERROR</strong>: E-mail does not match. Please re-type.','mgm'));										
										}
										
									}elseif ( (!isset($_POST['mgm_register_field'][$field['name']])) || (empty($_POST['mgm_register_field'][$field['name']])) ) {
											//issue #703
											$errors->add($field['name'], __('<strong>ERROR</strong>: You must provide a ','mgm').mgm_stripslashes_deep($field['label']).'.');
									}
								}				
							}
						break;
					}
				break;
			
			}
		}	
	}		
	
	// return	
	return $errors;
}

/**
 * register post process
 *
 * @param int $user_id 
 * @return void or int $user_id
 */
function mgm_register($user_id){	
	global $wpdb, $post;	
	
	// check import in action and skip, tools->import calls mgm_register via "user_register" hook, this will help skip
	if(defined('MGM_DOING_USERS_IMPORT') && MGM_DOING_USERS_IMPORT == TRUE) {
	// return
		return $user_id;
	}	 	
	// get mgm_system
	$system_obj = mgm_get_class('system');
	// hide 
	$hide_custom_fields = $system_obj->get_setting('hide_custom_fields');
	// packs
	$packs = mgm_get_class('subscription_packs');	
	// members object
	$member = mgm_get_member($user_id);	
	// set status
	$member->set_field('status', MGM_STATUS_NULL);
	// get custom fields	
	$cf_register_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true)));
	// mgm_subscription
	$mgm_subscription = mgm_post_var('mgm_subscription');
	// get subs 				
	$subs_pack = mgm_decode_package($mgm_subscription);
	// extract
	extract($subs_pack);
	// payment_gateways if set:
	$mgm_payment_gateways = mgm_post_var('mgm_payment_gateways');
	// Eg: $_POST['mgm_payment_gateways'] = mgm_paypal
	$cf_payment_gateways = (!empty($mgm_payment_gateways)) ? $mgm_payment_gateways : NULL;
	// init
	$member_custom_fields = array();
	// wordpress register
	$wordpres_form = mgm_check_wordpress_login();	
	
	// system - issue #1237
	$short_format = (!empty($system_obj->setting['date_format_short'])) ? $system_obj->setting['date_format_short'] : MGM_DATE_FORMAT_SHORT;	
	
	// loop
	foreach($cf_register_page as $field){
		// skip custom fields by settings call
		if(($hide_custom_fields == 'Y') || ($hide_custom_fields == 'W' && $wordpres_form) || ($hide_custom_fields == 'C' && !$wordpres_form)){
			// if($hide_custom_fields && $field['name'] != 'subscription_options') continue;
			if(!in_array($field['name'], array('subscription_options','payment_gateways'))) continue;
		}
		//skip if payment_gateways custom field
		if($field['name'] == 'payment_gateways') continue;
		//
		// do not save html
		if($field['type']=='html' || $field['type']=='label') continue;				
		// save
		switch($field['name']){			
			case 'username':				
				// #739 
			    if (isset($_POST[$field['attributes']['capture_field_alias']])) {
			    	$member_custom_fields[$field['name']] = @$_POST[$field['attributes']['capture_field_alias']];
			    } else {
			    	$member_custom_fields[$field['name']] = @$_POST['user_login'];
			    }
			break;	
			case 'email':				
				// #739 
				if (isset($_POST[$field['attributes']['capture_field_alias']])) {
					$member_custom_fields[$field['name']] = @$_POST[$field['attributes']['capture_field_alias']];
				} else {
					$member_custom_fields[$field['name']] = @$_POST['user_email'];
				}
			break;
			case 'password':
				// #739 
				// check	
				if (isset($field['attributes']['capture_field_alias']) &&  isset($_POST[$field['attributes']['capture_field_alias']])) {
					if(!empty($_POST[$field['attributes']['capture_field_alias']])) {
						$user_password = @$_POST[$field['attributes']['capture_field_alias']];
						$member_custom_fields[$field['name']] = mgm_encrypt_password($user_password, $user_id);
					}
				} else {
					if(!empty($_POST['user_password'])) {
						$user_password = $_POST['user_password'];
						$member_custom_fields[$field['name']] = mgm_encrypt_password($user_password, $user_id);
					}
				}
			break;	
			case 'autoresponder':										
				// #739 
				if (isset($field['attributes']['capture_field_alias']) && isset($_POST[$field['attributes']['capture_field_alias']])) {
					// checked issue #839
					// if(in_array(strtolower($_POST[$field['attributes']['capture_field_alias']]), array('y','yes'))){
					if(!empty($_POST[$field['attributes']['capture_field_alias']]) && $_POST['mgm_register_field'][$field['name']] == $field['value']){
						$member->subscribed    = 'Y';
						$member->autoresponder = $system_obj->active_modules['autoresponder'];
					}
				} else {
					// checked issue #839
					// if(in_array(strtolower($_POST['mgm_register_field'][$field['name']]), array('y','yes'))){
					if(!empty($_POST['mgm_register_field'][$field['name']]) && $_POST['mgm_register_field'][$field['name']] == $field['value']){			
						// set to member, to be used on payment
						$member->subscribed    = 'Y';
						$member->autoresponder = $system_obj->active_modules['autoresponder'];
					}
				}			
			break;
			case 'coupon':
				// #739 
				// check alias
				if (isset($field['attributes']['capture_field_alias']) && isset($_POST[$field['attributes']['capture_field_alias']])) {
					// check
					if(!empty($_POST[$field['attributes']['capture_field_alias']])){
						// validate
						if($coupon = mgm_validate_coupon($_POST[$field['attributes']['capture_field_alias']], $cost)){
							// set
							$member->coupon = $coupon;			
							// update coupon usage							
							mgm_update_coupon_usage($coupon['id'], 'register');							
						}
					}					
				} else {
					// check primary
					if(isset($_POST['mgm_register_field'][$field['name']]) && !empty($_POST['mgm_register_field'][$field['name']])){
						// validate						
						if($coupon = mgm_validate_coupon($_POST['mgm_register_field'][$field['name']], $cost)){
							// set
							$member->coupon = $coupon;			
							// update coupon usage							
							mgm_update_coupon_usage($coupon['id'], 'register');						
						}
					}
				}
			break;	
			case 'birthdate':
				// #739 
				if (isset($field['attributes']['capture_field_alias']) && isset($_POST[$field['attributes']['capture_field_alias']])) {					
					//issue #1237
					$member_custom_fields[$field['name']] = mgm_format_inputdate_to_mysql($_POST[$field['attributes']['capture_field_alias']],$short_format);
				} else {
					//convert from short date format to mysql format - issue #1237
					$member_custom_fields[$field['name']] = mgm_format_inputdate_to_mysql($_POST['mgm_register_field'][$field['name']],$short_format);
				}

			break;									
			default:
				// #739 
				if (isset($field['attributes']['capture_field_alias']) && isset($_POST[$field['attributes']['capture_field_alias']])) {
					$member_custom_fields[$field['name']] = @$_POST[$field['attributes']['capture_field_alias']];
				} elseif($field['type'] == 'checkbox' && is_array(@$_POST['mgm_register_field'][$field['name']])) {
					//$member_custom_fields[$field['name']] = implode(" ", @$_POST['mgm_register_field'][$field['name']]);
					//issue #1070
					$val = @$_POST['mgm_register_field'][$field['name']];
					$member_custom_fields[$field['name']] = serialize($val);
				}else {
					$member_custom_fields[$field['name']] = @$_POST['mgm_register_field'][$field['name']];
				}
			break;
		}
	}// end fields save		
	
	// user password not provided
	/*
	if (!isset( $user_password )){
		$user_password = (isset($_POST['pass1']) && !empty($_POST['pass1'])) ? trim($_POST['pass1']) : substr(md5(uniqid(microtime())), 0, 7);		
	}*/
	
	
	// user password not provided
	if ( !isset( $user_password ) ){
		// take custom password fields, iss#717, consider BP custom password field
		$password_fields = array('pass1', 'signup_password');
		// loop
		foreach($password_fields as $password_field){
			// check if set
			if(isset($_POST[$password_field]) && !empty($_POST[$password_field])){
				$user_password = trim($_POST[$password_field]); break;
			}
		}
	}
	
	// auto generate if still missing
	if ( !isset( $user_password ) ){
		$user_password = substr(md5(uniqid(microtime())), 0, 7);
	}
	
	//encrypt password and save in 
	$member->user_password = mgm_encrypt_password($user_password, $user_id);		
	// md5			
	$user_password_hash = wp_hash_password($user_password);	
	// db update
	$wpdb->query( $wpdb->prepare("UPDATE `{$wpdb->users}` SET `user_pass` = %s WHERE ID = %d", $user_password_hash, $user_id ));	
	// unset label fields
	if(isset($member_custom_fields['password_conf'])){
		unset($member_custom_fields['password_conf']);
	}
	// set custom
	$member->set_custom_fields($member_custom_fields);
	// set pack
	if($pack_id){
		// pack
		$pack = $packs->get_pack($pack_id); 
		// set
		$member->amount = $pack['cost'];
		$member->duration = $pack['duration'];
		$member->duration_type = $pack['duration_type'];
		$member->active_num_cycles = $pack['num_cycles'];
		// set membership type
		$member->membership_type = $membership_type;// from mgm_subscription
		// set in member
		$member->pack_id = $pack_id; // from mgm_subscription
	}
	// set status
	$member->status = MGM_STATUS_NULL;	
	// update option	
	$member->save();
	// update user firstname/last name
	mgm_update_default_userdata($user_id);	
	// admin check
	$is_admin = is_admin() ;//&& current_user_can('manage_options');
	// send
	$notify_user = true;
	// Block registration emails if Buddypress is enabled and disable_registration_email_bp value is Yes
	$block_reg_email = bool_from_yn(mgm_get_class('system')->get_setting('disable_registration_email_bp'));
	// send notification, bp active, do not send password, #739
	if( (!isset($_POST['send_password']) && $is_admin) || (mgm_is_plugin_active('buddypress/bp-loader.php') && $block_reg_email) ) $notify_user = false;	
	
	// send notification - issue #1468
	if($system_obj->setting['enable_new_user_email_notifiction_after_user_active']=='N') {
		if($notify_user) { 
			mgm_new_user_notification($user_id, $user_password, ( $is_admin ? false: true ));
		}
		$notify_user =	false;			
	}	
	// hook for other plugin who wishes to use default "user_register"
	do_action('mgm_user_register', $user_id);	
	
	// process payment only when registered from site, not when user added by admin
	if($is_admin){
		// unset
		unset($_POST['send_password']);//prevent sending user email again
		// assign default pack
		do_action('mgm_admin_user_register', $user_id,$notify_user);
		// return id
		return $user_id;
	}
	
	// if on wordpress page or custompage	
	$post_id = get_the_ID();
	// post custom register		
	if($post_id > 0 && $post->post_type == 'post'){
		$redirect =	get_permalink($post_id);
	}else{
		$redirect = mgm_get_custom_url('transactions');
	}
	// if buddypress url replace by register url : issue#: 791
	$redirect = apply_filters('mgm_bp_register_url', $redirect);	
	// userdata
	$userdata = get_userdata($user_id);			
	// note this fix VERY IMPORTANT, needed for PAYPAL PRO CC POST
	$redirect = add_query_arg(array('username'=>urlencode($userdata->user_login)),$redirect);	
	// add redirect
	if ($redirector = mgm_request_var('mgm_redirector', mgm_request_var('redirect_to', '', true), true)){ 
		$redirect = add_query_arg(array('redirector'=>$redirector), $redirect);
	}	
	// with subscription	            
	if ($mgm_subscription){ 
		$redirect = add_query_arg(array('subs'=>$mgm_subscription,'method'=>'payment_subscribe'), $redirect);                       		
	}	
	// bypass step2 if payment gateway is submitted: issue #: 469
	if(!is_null($cf_payment_gateways)) {		
		// pack		
		$packs_obj = mgm_get_class('subscription_packs');
		// validate			
		$pack = $packs_obj->validate_pack($cost, $duration, $duration_type, $membership_type, $pack_id);		
		// error
		if($pack != false) {			
			// get pack
			mgm_get_register_coupon_pack($member, $pack);
			// cost
			if ((float)$pack['cost'] > 0) {
				//get an object of the payment gateway:
				$mod_obj = mgm_get_module($cf_payment_gateways,'payment');
				// tran options
				$tran_options = array('is_registration'=>true, 'user_id' => $user_id, 'notify_user' => $notify_user);
				// is register & purchase
				if(isset($_POST['post_id'])){
					$tran_options['post_id'] = (int)$_POST['post_id'];
				}
				// is register & purchase postpack
				if(isset($_POST['postpack_post_id']) && isset($_POST['postpack_id'])){
					$tran_options['postpack_post_id'] = (int)$_POST['postpack_post_id'];
					$tran_options['postpack_id'] = (int)$_POST['postpack_id'];
				}				
				// create transaction				
				// $tran_id = $mod_obj->_create_transaction($pack, $tran_options);
				$tran_id = mgm_add_transaction($pack, $tran_options);
				//bypass directly to process return if manual payment:				
				if($cf_payment_gateways == 'mgm_manualpay') {
					// set 
					$_POST['custom'] = $tran_id;
					// direct call to module return function:
					$mod_obj->process_return();				
					// exit	
					exit;
				}
				// encode id:
				$tran_id  = mgm_encode_id($tran_id);
				// redirect - if on wordpress page or custompage - issue #1648
				if($post_id > 0 && $post->post_type == 'post'){
					$redirect = $mod_obj->_get_endpoint('html_redirect', true);	
				}else {
					$redirect = $mod_obj->_get_endpoint('html_redirect', false);						
				}					
				// if buddypress url replace by register url : issue#: 791
				$redirect = add_query_arg(array( 'tran_id' => $tran_id ), apply_filters('mgm_bp_register_url', $redirect)); 									
			}else{
				// issue #1468
				$redirect = add_query_arg(array( 'notify_user' => $notify_user ), $redirect);
			}
		}
	}
	
	// ends custom payment gateway bypassing 	
	// is register & purchase
	if(isset($_POST['post_id'])){
		$redirect = add_query_arg(array( 'post_id' => (int)$_POST['post_id'] ), $redirect);  ;
	}	
	// is register & purchase postpack
	if(isset($_POST['postpack_post_id']) && isset($_POST['postpack_id'])){
		$redirect = add_query_arg(array( 'postpack_id' => (int)$_POST['postpack_id'] , 'postpack_post_id' => (int)$_POST['postpack_post_id'] ), $redirect);  ;
	}
		
	// redirect filter, returing a false can stop the redirect
	$redirect = apply_filters('mgm_after_regiter_redirect', mgm_site_url($redirect));	
	// redirect	
	if( $redirect !== FALSE ){
		// do the redirect to payment
		mgm_redirect($redirect);// this goes to subscribe, mgm_functions.php/mgm_get_subscription_buttons
		// exit						
		exit;
	}
	
	// default
	return $user_id;		
}

/**
 * authenticate user
 * 
 * @param object $user 
 * @param boolean $return 
 * @return mixed object or boolean
 */
function mgm_authenticate_user($user, $return=false){
	// user name
	$user_name = $user->user_login;	
	
	// user is administrator, no check applied
	if (is_super_admin($user->ID)) {
		return ($return ? true : $user);
	}
	// issue #1783- user is administrator, no check applied for multisite also
	if ( is_multisite() ) {
		//check
		if ( $user->allcaps['delete_users'] ) {
			return ($return ? true : $user);			
		}
	}	
	// issue #1711	
	if ( isset($_REQUEST['pwd']) && ! wp_check_password($_REQUEST['pwd'], $user->user_pass, $user->ID) ) {
		return new WP_Error('incorrect_password', sprintf(__( '<strong>ERROR</strong>: The password you entered for the username <strong>%s</strong> is incorrect.' ), $user_name ));
	}	
	// apply action
	do_action('mgm_pre_authenticate_user', $user->ID);
	
	// get member
	$member = mgm_get_member($user->ID);	
	
	// check pack access
	if($pack = mgm_get_class('subscription_packs')->get_pack($member->pack_id)){
		// range
		if($pack['duration_type'] == 'dr'){
			if(time() < strtotime($pack['duration_range_start_dt']) || time() > strtotime($pack['duration_range_end_dt'])){				
				// error
				return mgm_get_login_error($user, 'date_range', 'upgrade', $return, $pack); 
			}
		}		

		/// multiple user(IP checking)
		if( mgm_check_multiple_logins_violation($user, $member, $pack) ){
			// error
			return mgm_get_login_error($user, 'multiple_logins', 'upgrade', $return, $pack); 
		} 
	}
	// no status
	if ($member->status === FALSE) {
		return ($return ? true : $user); 
	}	
	
	// allowed statuses
	$allowed_statuses = array(MGM_STATUS_ACTIVE, MGM_STATUS_AWAITING_CANCEL);
	
	// active, awaiting cancelled
	if (in_array($member->status, $allowed_statuses)) {		
		// never expire
		if (empty($member->expire_date)) {
			return ($return ? true : $user);
		}
		// check expire
		if (!empty($member->expire_date) && time() > strtotime($member->expire_date)) {
			// old status
			$old_status = $member->status;
			// set new status						
			$member->status = $new_status = (strtolower($member->membership_type) == 'trial') ? MGM_STATUS_TRIAL_EXPIRED : MGM_STATUS_EXPIRED;			
			// update
			$member->save();
			// action
			do_action('mgm_user_status_change', $user->ID, $new_status, $old_status, 'authenticate_user', $member->pack_id);
		} else {
		// return
			return ($return ? true : $user); // account is current. Let the user login.
		}		
	}else {		
		// multiple membership (issue#: 400) modification
		$others_active = 0;		
		// check any other membership exists with active status
		if(isset($member->other_membership_types) && is_array($member->other_membership_types) && !empty($member->other_membership_types) ) {
			// loop
			foreach ($member->other_membership_types as $key => $mem_obj) {
				// object
				$mem_obj = mgm_convert_array_to_memberobj($mem_obj, $user->ID);
				// check
				if(is_numeric($mem_obj->pack_id) && in_array($mem_obj->status, $allowed_statuses)){
					// check for expiry
					if ( !empty($mem_obj->expire_date) && time() > strtotime($mem_obj->expire_date)) {
						// old status
						$old_status = $mem_obj->status;
						// set new status
						$mem_obj->status = $new_status = MGM_STATUS_EXPIRED;
						// update member object							
						mgm_save_another_membership_fields($mem_obj, $user->ID);
						// action
						do_action('mgm_user_status_change', $user->ID, $new_status, $old_status, 'authenticate_user', $mem_obj->pack_id);								
					}else{ 
						$others_active++;
					}									
				}					
			}
			// one of the other memberships is active. Let the user login.
			if($others_active > 0) {
			// return
				return ($return ? true : $user); 
			}
		}
		// Force upgrade if status: Expired and free user
		$action = mgm_force_upgrade_if_freepack($member);
	}
	
	// if reached this, then there is error ---------------------------------------
	
	// set temp login cookie for editing user data 
	if ( isset($_POST['pwd']) && wp_check_password($_POST['pwd'], $user->user_pass, $user->ID) ){
		setcookie('wp_tempuser_login' , $user->ID, (time() + (60*60)), SITECOOKIEPATH);// 1 hr
	}
	
	// default action
	if(!isset($action)) $action = 'complete_payment';
	
	// error
	return mgm_get_login_error($user, $member->status, $action, $return);
}

/**
 * get error on login
 *
 * @param object $user
 */
function mgm_get_login_error($user, $error_code, $action, $return, $pack=NULL){
	// process error
	$system_obj = mgm_get_class('system');	
	
	// error
	$error_messages = array(
		MGM_STATUS_NULL          => mgm_stripslashes_deep($system_obj->get_template('login_errmsg_null', array(), true)),
		MGM_STATUS_TRIAL_EXPIRED => mgm_stripslashes_deep($system_obj->get_template('login_errmsg_trial_expired', array(), true)),
		MGM_STATUS_EXPIRED       => mgm_stripslashes_deep($system_obj->get_template('login_errmsg_expired', array(), true)),
		MGM_STATUS_PENDING       => mgm_stripslashes_deep($system_obj->get_template('login_errmsg_pending', array(), true)),
		MGM_STATUS_CANCELLED     => mgm_stripslashes_deep($system_obj->get_template('login_errmsg_cancelled', array(), true)),
		'ANY'                    => mgm_stripslashes_deep($system_obj->get_template('login_errmsg_default', array(), true)),
		'date_range'             => mgm_stripslashes_deep($system_obj->get_template('login_errmsg_date_range', array(), true)),
		'multiple_logins'        => mgm_stripslashes_deep($system_obj->get_template('login_errmsg_multiple_logins', array(), true)),
	);

	// error_message
	$error_message = (isset($error_messages[$error_code]) ? $error_messages[$error_code] : $error_messages['ANY']);
	// argas
	$q_args	= array('[[ACTION]]'=>$action);
	// 
	if( bool_from_yn($system_obj->setting['enable_email_as_username']) ){
		$q_args	= array_merge($q_args, array('[[USERID]]'=>$user->ID));
	}else{
		$q_args	= array_merge($q_args, array('[[USERNAME]]'=>$user->user_login));	
	}
	// set
	$error_message = str_replace(array_keys($q_args), array_values($q_args), $error_message);	
	
	// date range
	if($error_code == 'date_range'){
		// format
		$date_fmt = mgm_get_date_format('date_format_short');
		// dates
		$start_dt = date($date_fmt, strtotime($pack['duration_range_start_dt']));
		$end_dt   = date($date_fmt, strtotime($pack['duration_range_end_dt']));
		// set
		$error_message = str_replace(array('[start_date]','[end_date]'), array($start_dt,$end_dt), $error_message);
	}
	// check subscription status
	$error = new WP_Error();
	// add
	$error->add('mgm_login_error', $error_message);
	
	// return
	return ($return ? false : $error);
}
/**
 * User upgrade notification email to admin
 */
function mgm_user_upgrade_notification_process($user_id,$sendto_admin=true){
	// mgm_system	
	$system_obj = mgm_get_class('system');		
	// admin notification		
	if($sendto_admin) {
		//getting email template
		$template_subject = mgm_stripslashes_deep($system_obj->get_template('user_upgrade_notification_email_template_subject', array(), true));
		$template_body    = mgm_stripslashes_deep($system_obj->get_template('user_upgrade_notification_email_template_body', array(), true));	
		//replacing email tags
		$template_subject = mgm_replace_email_tags($template_subject,$user_id);
		$template_body = mgm_replace_email_tags($template_body,$user_id);		
		// admin mail
		$admin_email = trim($system_obj->get_setting('admin_email'));
		// mail	
		@mgm_mail($admin_email, $template_subject,$template_body);			
	}
}
/**
 * new user email
 */
function mgm_new_user_notification($user_id, $user_pass='', $sendto_admin=true){
	// mgm_system	
	$system_obj = mgm_get_class('system');		
	// template
	$template_subject = mgm_stripslashes_deep($system_obj->get_template('registration_email_template_subject', array(), true));
	$template_body    = mgm_stripslashes_deep($system_obj->get_template('registration_email_template_body', array(), true));	
	// default
	if(empty($template_body)){
		 wp_new_user_notification($user_id, $user_pass);	
	}else{
		// get user
		$user = new WP_User($user_id);	
		// mgm member
		$member = mgm_get_member($user_id);
		
		// user data
		$user_login = stripslashes($user->user_login);
	    $user_email = stripslashes($user->user_email);
	    // admin notification		
		if($sendto_admin) {
			// template -issue #1069
			$notification_email_subject = mgm_stripslashes_deep($system_obj->get_template('new_user_notification_email_template_subject', array(), true));
			$notification_email_body    = mgm_stripslashes_deep($system_obj->get_template('new_user_notification_email_template_body', array(), true));	
			
			// template
			$template = array('subject'=>$notification_email_subject ,'message'=>$notification_email_body);

			// send to admin 
			$template['subject'] = mgm_replace_email_tags($template['subject'],$user_id);
			$template['message'] = mgm_replace_email_tags($template['message'],$user_id);
			
			// apply filter
			$template = apply_filters('mgm_new_user_notification_email_template', $template, $user_id);	

			//issue #1144
			$admin_email = trim($system_obj->get_setting('admin_email'));

			// mail	
			@mgm_mail($admin_email, $template['subject'], $template['message']);
		}
		
		// no pass
		if ( empty($user_pass) ) return;

		// first name
		if(isset($user->first_name)){
			$display_name = $user->first_name;
		}elseif(isset($member->custom_fields->first_name)){
			$display_name = $member->custom_fields->first_name;
		}else{					
		 	$display_name = $user->display_name;
		}
		// format
		$display_name = stripslashes($display_name);	

		// subject
		if($template_subject != '')
		 	$subject = $template_subject;
		else		
		    $subject = sprintf(__('[%s] Your username and password'), get_option('blogname'));
		 
		// body
		$message = $template_body;	
		$message = str_replace('[name]',$display_name,$message);
		$message = str_replace('[username]',$user_login,$message);
		//issue #1359
		$message = str_replace('[email]',$user_email,$message);
		$message = str_replace('[password]',$user_pass,$message);
		// $message = str_replace('[expire_date]',$expire_date,$message);
		$message = str_replace('[login_url]', sprintf('<a href="%s">%s</a>', wp_login_url(), __('Login','mgm')),$message);	
		//issue #1418
		$message = mgm_replace_email_tags($message,$user_id);
		// template
		$template = array('subject'=>$subject,'message'=>$message);
		
		//issue #862
		$template['subject'] = mgm_replace_email_tags($template['subject'],$user_id);
		$template['message'] = mgm_replace_email_tags($template['message'],$user_id);

		// apply filter
		$template = apply_filters('mgm_registration_email_template', $template, $user_id);
		
		// send
		@mgm_mail($user_email, $template['subject'], $template['message']);
	}	
	
	// action trigger after new user notifications sent
	do_action('mgm_new_user_notification', $user_id, $user_pass);	
}

/**
 * attach scripts to pages required
 * @todo need recode
 */
// login head 
function mgm_attach_scripts($return = false, $exclude = array('jquery.ajaxfileupload.js')){ 	
	global $mgm_scripts;		
	// wp login form 
	$wordpres_login_form = mgm_check_wordpress_login();
	// int css array
	$css_files = array();
	// subscribe page css, loaded from wp-admin
	/*
	if( mgm_get_query_var('purchase_subscription') || mgm_get_query_var('payment_return')):
		$css_files[] = admin_url('/css/login.css');
		$css_files[] = admin_url('/css/colors-fresh.css'); 
	endif; 
	*/
	// group
	$css_group = mgm_get_css_group();
	
	//issue #867
	if($css_group !='none') {
		// other, loaded from mgm custom
		$css_files[] = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.form.fields.css'; 
		$css_files[] = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.site.css';
		$css_files[] = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.cc.fields.css';
		$css_files[] = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm/jquery.ui.css';  
		$css_files[] = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.pages.css';  
	}

	// disable
	$disable_jquery = false;
	//this is for blocking loading jquery externally, to disable jquery add_filter and modify disable_jquery to return true 	
	$disable_jquery = apply_filters('disable_jqueryon_page', $disable_jquery);	
	// init js array
	$js_files = array();	
	$arr_default_pages = array('wp-login.php', 'user-edit.php', 'profile.php');
	$default_page = (in_array(basename($_SERVER['SCRIPT_FILENAME']), $arr_default_pages )) ? true : false ;
	
	// jquery from wp distribution	
	if(($default_page && !in_array('jquery.js', (array)$mgm_scripts )) || (!wp_script_is('jquery') && !$disable_jquery)) {		
		if(($default_page && !in_array('jquery.js', (array)$mgm_scripts )) || !mgm_is_script_already_included('jquery.js')) {			
			$js_files[] = includes_url( '/js/jquery/jquery.js');				
			$mgm_scripts[] = 'jquery.js';
		}		
	}
	// custom
	//if(!wp_script_is('mgm-jquery-validate'))
	//	if(!mgm_is_script_already_included('jquery.validate.pack.js')) {
		if(($default_page && !in_array('jquery.validate.pack.js', (array)$mgm_scripts )) || (!wp_script_is('mgm-jquery-validate') && !mgm_is_script_already_included(MGM_ASSETS_URL . 'js/jquery/jquery.validate.pack.js', true))) {
			$js_files[] = MGM_ASSETS_URL . 'js/jquery/jquery.validate.pack.js';
			$mgm_scripts[] = 'jquery.validate.pack.js';
		}
	//if(!wp_script_is('mgm-jquery-metadata'))	
	//	if(!mgm_is_script_already_included('jquery.metadata.js')) {
		if(($default_page && !in_array('jquery.metadata.js', (array)$mgm_scripts )) || (!wp_script_is('mgm-jquery-metadata') && !mgm_is_script_already_included(MGM_ASSETS_URL . 'js/jquery/jquery.metadata.js', true))) {
			$js_files[] = MGM_ASSETS_URL . 'js/jquery/jquery.metadata.js';
			$mgm_scripts[] = 'jquery.metadata.js';
		}
	//if(!wp_script_is('mgm-helpers'))	
	//	if(!mgm_is_script_already_included('helpers.js', true)) {
		if( ($default_page && !in_array('helpers.js', (array)$mgm_scripts )) || (!wp_script_is('mgm-helpers') && !mgm_is_script_already_included(MGM_ASSETS_URL . 'js/helpers.js', true)) ) {
			$js_files[] = MGM_ASSETS_URL . 'js/helpers.js';
			$mgm_scripts[] = 'helpers.js';
		}
	// ui on wp version			
	$jqueryui_version = mgm_get_jqueryui_version();	
	// add to array	
	//if(!wp_script_is('mgm-jquery-ui')) {		
		//if(!mgm_is_script_already_included('jquery-ui-'.$jqueryui_version.'.min.js')) {
		if( ($default_page && !in_array('jquery-ui-'.$jqueryui_version.'.min.js', (array)$mgm_scripts )) || ( !wp_script_is('mgm-jquery-ui') && !mgm_is_script_already_included('jquery-ui-'.$jqueryui_version.'.min.js'))) {
			$js_files[] = MGM_ASSETS_URL . 'js/jquery/jquery.ui/jquery-ui-'.$jqueryui_version.'.min.js';
			$mgm_scripts[] = 'jquery-ui-'.$jqueryui_version.'.min.js';
		}			
	//}	
	//if(!wp_script_is('mgm-jquery-ajaxupload')) {		
	//	if(!mgm_is_script_already_included('jquery.ajaxfileupload.js')) {					
		if(($default_page && !in_array('jquery.ajaxfileupload.js', (array)$mgm_scripts )) || (!mgm_is_script_already_included(MGM_ASSETS_URL . 'js/jquery/jquery.ajaxfileupload.js', true) && !wp_script_is('mgm-jquery-ajaxupload') )) {			
			$js_files[] = MGM_ASSETS_URL . 'js/jquery/jquery.ajaxfileupload.js';
			$mgm_scripts[] = 'jquery.ajaxfileupload.js';
		}	
	//}
	// if(!wp_script_is('mgm-jquery-watermarkinput'))
		// $js_files[] = MGM_ASSETS_URL . 'js/jquery/jquery.watermarkinput.js';
	// init
	$scripts = '';	
	// css format
	$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';
	// add
	foreach($css_files as $css_file){
		$scripts .= sprintf($css_link_format, $css_file);
	}	
	// js format
	$js_script_format = '<script type="text/javascript" src="%s"></script>';
	
	// add
	if($js_files)
		foreach($js_files as $js_file){
			$scripts .= sprintf($js_script_format, $js_file);
		}
	// return	
	if($return) 
		return $scripts;
	else		
		echo $scripts;	
}

/**
 * after login redirect
 * 
 * @param string user login
 * @param object user
 * @return object user
 */
function mgm_login_redirect($user_login, $user=NULL){	
	// get user	
	if(!$user) $user = get_user_by('login', $user_login);	
	
	// if super admin	
	if(is_super_admin($user->ID)) {	
	// redirect 
		mgm_redirect(admin_url()); exit;
	}		
	
	// check doing auto login from register and skip if true
	if(defined('MGM_DOING_REGISTER_AUTO_LOGIN') && MGM_DOING_REGISTER_AUTO_LOGIN == TRUE) {
	// return
		return $user;
	}
	
	// custom hook
	do_action('mgm_before_login_redirect', $user);
	
	// get setting	
	$system_obj = mgm_get_class('system');			
	
	// issue #503,allow redirecting back to post url: @depends on  "enable_post_url_redirection" in misc setting
	$enable_post_url_redirection = bool_from_yn($system_obj->get_setting('enable_post_url_redirection'));	
	// check
	if($enable_post_url_redirection) {		
		// redirect_to
		if($redirect_to = mgm_request_var('redirect_to', '', true)){
			// flag
			$do_redirect = true;			
			// loop custom pages		
			foreach($system_obj->get_custom_pages_url() as $page_url){
				// if not same				
				if(!empty($page_url) && trailingslashit($redirect_to) == trailingslashit($page_url)){	
					// check, matched both full url or part /%postname%/ url
					if(trailingslashit($redirect_to) == trailingslashit($page_url) || site_url($redirect_to) == trailingslashit($page_url)){	
					// reset				
						$do_redirect = false; break;
					}
				}			
			}
			// OK
			if(!empty($redirect_to) && $do_redirect) mgm_redirect($redirect_to); exit;	
		}	
	}			
	
	// apply filter
	$login_redirect_url = apply_filters('mgm_login_redirect', mgm_login_redirect_url($user));	
		
	// check
	if(!empty($login_redirect_url)){
		mgm_redirect($login_redirect_url); exit();	
	}
	
	// return 
	return $user;
}
/**
 * login redirect check
 */
function mgm_login_redirect_url($user){
	// get system
	$system_obj = mgm_get_class('system');	
		
	// issue# 464: package redirect, @depends on package login redirect setting			
	if ($redirect_url = mgm_get_user_package_redirect_url($user->ID)) {			
		// redirect								
		return $redirect_url;						
	}		
	
	// get$user->user_login default login redirect, @depends on login redirect setting in misc setings
	if ($redirect_url = $system_obj->get_setting('login_redirect_url')) {			
		//short code support
		if(!empty($user->user_login)) {
			$redirect_url = str_replace('[username]',$user->user_login,$redirect_url);
		}
		// redirect								
		return trim($redirect_url); 
	}
	
	// default: redirect to profile	
	if ($redirect_url = trim($system_obj->get_setting('profile_url'))) {			
		// redirect							
		return $redirect_url;
	}	
	// return 
	return '';
}
// set field value
function mgm_set_field_value($name, $key, $default=''){	
	// isset						
	if(isset($_POST[$name][$key])){
		return mgm_stripslashes_deep($_POST[$name][$key]);
	}							
	// return
	return mgm_stripslashes_deep($default);
}

// subscribe to autoresponder
function mgm_autoresponder_subscribe($args){	
	// fix for #865, acknowledge_ar is not populated
	$acknowledge_user = isset($args['acknowledge_ar']) ? $args['acknowledge_ar'] : true;		
	// acknowledge_user: to send only once after registration
	if(isset($args['user_id']) && $acknowledge_user){		
		// pass mgm_member object if already passed
		$member = isset($args['mgm_member']) ? $args['mgm_member'] : null; 
		// subscribe
		return mgm_autoresponder_send_subscribe($args['user_id'], $member);
	}
}
// unsubscribe from autoresponder
function mgm_autoresponder_unsubscribe($args){		
	// issue #861 - Autoresponder Unsubscribe
	// $autoresponder_unsubscribe = mgm_get_setting('autoresponder_unsubscribe');
	// && bool_from_yn($autoresponder_unsubscribe)
	// acknowledge_user: to send only once after registration
	if(isset($args['user_id'])){
		// unsubscribe
		return mgm_autoresponder_send_unsubscribe($args['user_id']);		
	}
}
// update
function mgm_updte_contactmethods($methods) {	
	// issue#: 255(Disable contact methods)
	return array();
}
// reset cancelled member
function mgm_reset_cancelled_member($args) {
	// check
	if(isset($args['user_id'])) {
		// user_id
		$user_id = $args['user_id'];
		// get member
		//another_subscription modification
		if(isset($args['another_membership'])) 
			$member = mgm_get_member_another_purchase($user_id, $args['another_membership']);
		else 
			$member = mgm_get_member($user_id);
			
		// check
		if(isset($member->status_reset_as) && $member->status_reset_as == MGM_STATUS_CANCELLED){
			// unset
			unset($member->status_reset_as,$member->status_reset_on);
			// update user
			//another_subscription modification
			if(isset($args['another_membership'])) 
				mgm_save_another_membership_fields($member, $user_id);
			else 			
				// update_user_option($user_id, 'mgm_member', $member, true);	
				$member->save();			
		}		
	}	
}
//reset password validation hook
function mgm_validate_reset_password($key, $login) {
	global $wpdb;
	//issue #1700
	//$key = preg_replace('/[^a-z0-9]/i', '', $key);

	if ( empty( $key ) || !is_string( $key ) )
		return new WP_Error('invalid_key', __('Invalid key','mgm'));

	if ( empty($login) || !is_string($login) )
		return new WP_Error('invalid_key', __('Invalid key','mgm'));

	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));
	if ( empty( $user ) )
		return new WP_Error('invalid_key', __('Invalid key','mgm'));
	
	return $user;
}
//reset password action hook
function mgm_reset_password($key, $user) {
	global $current_site;
	if(isset($user->ID) && $user->ID > 0) {
		$new_pass = wp_generate_password();
		do_action('password_reset', $user, $new_pass);
		wp_set_password($new_pass, $user->ID);
		update_user_option($user->ID, 'default_password_nag', true, true); //Set up the Password change nag.
		//get custom title/messages
		$title = apply_filters('password_reset_title', '');
		$message = apply_filters('password_reset_message', '', $new_pass);

		if ( $message && !mgm_mail($user->user_email, $title, $message) )
	  		wp_die( __('The e-mail could not be sent.','mgm') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.','mgm') );
	
		wp_password_change_notification($user);
		wp_safe_redirect(mgm_get_custom_url('login', false, array('checkemail' => 'newpass')));
		exit();
	}
}
//logout
function mgm_logout() {
	// check
	check_admin_referer('log-out');	
	// record
	mgm_record_logout_at();
	// wp
	wp_logout();	
	// redirect
	$redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : mgm_get_custom_url('login', false, array('loggedout' => 'true'));
	// redirect - issue #1245
	wp_redirect($redirect_to);
	// exit
	exit();
}

/** 
 * logout url hook
 */
function mgm_logout_url($logout_url, $redirect) {
	
	//issue #1296
	//if(is_super_admin()) return $logout_url;
	
	$login = mgm_get_custom_url('login');	
	$login = str_replace('?action=login','', $login);//if default login is again loaded
	
	if(preg_match('/wp-login.php/', $logout_url )) {
		$arr_queries = explode("?",$logout_url,2);	
		//$login = trailingslashit($login);
		$logout_url = $login . "?". $arr_queries[1];		
	}elseif($logout_url == '') {
		$logout_url = $login;
	}
	
	//the below line is not really required, just to ensure that it contains action = logout
	$logout_url = add_query_arg(array('action' => 'logout'), $logout_url);
	if($custom_redirect = mgm_logout_redirect_url()) {		
		$logout_url = html_entity_decode($logout_url);				
		$logout_url = remove_query_arg(array('redirect_to'), $logout_url);						
		$logout_url = add_query_arg(array('redirect_to' => $custom_redirect), $logout_url);		
		//issue #1281
		$logout_url = htmlentities($logout_url, ENT_QUOTES, "UTF-8");			
	}
	
	return $logout_url;
}

/**
 * custom login url
 */
function mgm_login_url($login_url, $redirect = '') {
	$login = mgm_get_custom_url('login');	
	$login = str_replace('?action=login','', $login);
	if(!preg_match('/wp-login.php/', $login)) {
		$arr_queries = explode("?", $login_url, 2);	
		if(!empty($arr_queries[1]))
			$login = $login . "?". $arr_queries[1];
		if(!preg_match('/redirect_to=/', $login) && !empty($redirect))
			$login = add_query_arg(array('redirect_to' => $redirect), $login);		
		$login_url = $login;
	}	
	return 	$login_url;
}

/**
 * custom lostpassword url
 */
function mgm_lostpassword_url($lostpassword_url, $redirect = '') {	
	// return
	return mgm_get_custom_url('lostpassword');	
}

/**
 * custom register url
 */
function mgm_register_url($url, $path, $scheme=NULL, $blog_id=NULL) {		
	// return
	if( 'wp-login.php?action=register' == $path ){
		return mgm_get_custom_url('register');	
	}
	// default
	return $url;
	
	// not login
	/*if ( ! is_user_logged_in() ) {
		// custom url	
		$custom_url = mgm_get_custom_url('register');	
		// replace		
		$register_link = preg_replace('/href="(.*)"/',sprintf('href="%s"',$custom_url),$register_link);		
	} else {
		// custom url	
		$custom_url = mgm_get_custom_url('profile');	
		// replace
		$register_link = preg_replace('/<a(.*)a>/',sprintf('<a href="%s">%s</a>',$custom_url,__('Profile','mgm')),$register_link);	
	}	
	// return
	return $register_link;*/
}
// mgm_sanitize_user
function mgm_sanitize_user($username){
	// Consolidate contiguous whitespace, lowercase
	return strtolower(preg_replace( '|\s+|', '_', $username ));
}
// mgm_validate_username
function mgm_validate_username($username){	
	// check if space
	return (preg_match('/\s/',$username)) ? false : true;
}
//remove the buddypress hook: bp_core_do_catch_uri from global filter array 
function mgm_disable_bp_redirection() {	
	//This doesn't work
	remove_action( 'template_redirect', 'bp_core_do_catch_uri' );
	global $wp_filter;	
	if(!empty($wp_filter['template_redirect'])) {				
		foreach ($wp_filter['template_redirect'] as $key => $val ) {
			if(isset($val['bp_core_do_catch_uri'])) {				
				unset($wp_filter['template_redirect'][$key]);
			}			
		}
	}
}
//check register.php exists in the selected theme
function mgm_check_theme_register() {
	$theme_dir = get_template();
	return ((file_exists(get_theme_root(). '/' . $theme_dir . '/registration/register.php') || $theme_dir == 'bp-default') ? true : false);
	
}
//replace avatar with custom photo if any
function mgm_get_avatar($avatar, $id_or_email) {	
	$user_id = 0;
	$email = '';
	if ( is_numeric($id_or_email) ) {
		$user_id = (int) $id_or_email;	
	} elseif ( is_object($id_or_email) ) {
	 	if ( isset($id_or_email->user_id) && !empty($id_or_email->user_id) ) {
	 		$user_id = $id_or_email->user_id;
	 	}elseif (!empty($id_or_email->comment_author_email)) {
			$email = $id_or_email->comment_author_email;
	 	}
	}else {
		$email = $id_or_email;
	}
	if(!empty($email)) {
		//find user id from email:
		$user = get_user_by('email', $email);
		if(isset($user->ID))
			$user_id = $user->ID;
	}
	
	if($user_id > 0) {
		$member = mgm_get_member($user_id);
		if(isset($member->custom_fields) && !empty($member->custom_fields)) {			
			foreach ($member->custom_fields as $field => $value) {
				if($field == 'photo' && !empty($value)) {								
					//use thumb image:
					$value = preg_replace("/_medium/", "_thumb", $value);					
					$arr_size = @getimagesize(MGM_FILES_UPLOADED_IMAGE_DIR . basename($value));
					$avatar = preg_replace("/src='(.*?)'/i", "src='".$value."'", $avatar);
					//select width:
					if($arr_size[0] >= $arr_size[1]) {
						//format: height='32' width='32'
						$avatar = preg_replace("/width='(.*?)'/i", "width='".$arr_size[0]."'", $avatar);		
						$avatar = preg_replace("/height='(.*?)'/i", "", $avatar);		
					}else {//select height
						$avatar = preg_replace("/width='(.*?)'/i", "", $avatar);		
						$avatar = preg_replace("/height='(.*?)'/i", "width='".$arr_size[1]."'", $avatar);	
					}					
					
					break;
				}				
			}
		}
	}
		
	return $avatar;
}
//delete user uploaded photo
function mgm_delete_user($user_id) {
	if($user_id > 0) {
		$member = mgm_get_member($user_id);
		if(isset($member->custom_fields) && !empty($member->custom_fields)) {			
			foreach ($member->custom_fields as $field => $value) {
				if($field == 'photo' && !empty($value)) {
					$medium 	= MGM_FILES_UPLOADED_IMAGE_DIR . basename($value); 
					$thumbnail 	= MGM_FILES_UPLOADED_IMAGE_DIR . basename( str_replace('_medium', '_thumb', $value) ); 
					if(is_file($medium)) unlink($medium);
					if(is_file($thumbnail)) unlink($thumbnail);
					break;
				}
			}
		}
	}
}

/**
 * Reassign member's pack to a specified pack[as per pack setting: move_members_pack value](issue#: 535)
 * This will be invoked in Expiry/User initiated Cancellation
 *
 * @param int $user_id
 * @param obj $member
 * @param string $type
 * @param boolean $return
 * @return obj depending on $return value
 */
function mgm_reassign_member_subscription($user_id, $member, $type, $return  = true) {
	if(isset($member->pack_id) && is_numeric($member->pack_id)) {
		$obj_pack = mgm_get_class('subscription_packs');
		$prev_pack = $obj_pack->get_pack($member->pack_id);
		//if move_members_pack (id) is set:
		if(isset($prev_pack['move_members_pack']) && is_numeric($prev_pack['move_members_pack'])) {
			$system_obj = mgm_get_class('system');	
			$current_time = time();
			$subs_pack = $obj_pack->get_pack($prev_pack['move_members_pack']);
			//assign new pack:	
			// if trial on			
			$member->trial_on            = ($subs_pack['trial_on']) ? $subs_pack['trial_on'] 			: 0 ;
			$member->trial_cost          = ($subs_pack['trial_on']) ? $subs_pack['trial_cost'] 			: 0;
			$member->trial_duration      = ($subs_pack['trial_on']) ? $subs_pack['trial_duration'] 		: 0;
			$member->trial_duration_type = ($subs_pack['trial_on']) ? $subs_pack['trial_duration_type'] : 'd';
			$member->trial_num_cycles    = ($subs_pack['trial_on']) ? $subs_pack['trial_num_cycles'] 	: 0;
			
			// duration
			$member->duration        = $subs_pack['duration'];
			$member->duration_type   = strtolower($subs_pack['duration_type']);
			$member->amount          = $subs_pack['cost'];
			
			//issue #1602
			if(!isset($subs_pack['currency']) || empty($subs_pack['currency'])){
				$currency  = $system_obj->setting['currency'];
			}else {
				$currency  = $subs_pack['currency'];				
			}			
			
			if(!isset($member->currency)) $member->currency = $currency;
			$member->membership_type = $subs_pack['membership_type'];		
			//set new pack id
			$member->pack_id         = $subs_pack['id'];						
			$member->active_num_cycles = $subs_pack['num_cycles']; 
			$member->payment_type    = ((int)$subs_pack['num_cycles'] == 1) ? 'one-time' : 'subscription';
			//reset joining date as current time
			$member->join_date = $current_time;
			// duration_exprs
			$duration_exprs = mgm_get_class('subscription_packs')->get_duration_exprs();	
			// check
			if(in_array($member->duration_type, array_keys($duration_exprs))) {// take only date exprs
				// time
				$time = strtotime("+{$member->duration} {$duration_exprs[$member->duration_type]}", $current_time);										
				// formatted
				$time_str = date('Y-m-d', $time);
				$member->expire_date = $time_str;
			}else{				
				$member->expire_date = '';
			}
			// set last pay
			$member->last_pay_date = date('Y-m-d', $current_time);
			//reset as active
			$member->status 	= MGM_STATUS_ACTIVE;			
			$member->status_str = sprintf('%s (%s)', $member->status_str, sprintf(__('Reassigned new pack on [%s]', 'mgm'), $type)); 
					
			//unset vars:
			if(isset($member->rebilled)) unset($member->rebilled);
			// payment info for unsubscribe		
			if(isset($member->payment_info)) unset($member->payment_info);
			//unset transaction id - let's keep the old for ref.
			//if(isset($member->transaction_id)) unset($member->transaction_id);
						
			//add new role/remove old role										
			if($prev_pack['role'] != $subs_pack['role']) {				
				$obj_role = mgm_get_class('roles'); 
				$obj_role->add_user_role($user_id, $subs_pack['role'], true, false );
				//remove user role:
				$obj_role->remove_userrole($user_id, $prev_pack['role']);			
			}
			//done
		}		
	}
	
	if($return)
		return $member;
	else 
		$member->save();	
		
}
/**
 * Custom schedule intervals
 *
 * @param array $schedules : WP schedules
 * @return array
 */
function mgm_custom_schedules($schedules) {
	
	$schedules['quarterhourly'] = array( 'interval' => (15 * 60), 'display' => __('Every 15 minutes', 'mgm') );
	
	return $schedules;
}
/**
 * Hook to print POST/GET data in modules
 *
 * @param string $module
 * @param string $action
 */
function mgm_print_module_data($module, $action) {
	// skip credit card details submitted from credit card form:
	if(isset($_POST['mgm_card_number'])) return;
	// log file
	$log_filename = ($module . '_' . $action);
	// post
	if(isset($_POST)) mgm_log($module . ' ' . $action . ' POST DATA:' . mgm_array_dump($_POST, true), $log_filename);
	// get	
	if(isset($_GET)) mgm_log($module . ' ' . $action . ' GET DATA:' . mgm_array_dump($_GET, true), $log_filename);	
}
/**
 * Hook to specifically check rebill status 
 *
 * @param int $user_id
 * @param object $member
 * @return boolean
 */
function mgm_module_rebill_status($user_id, $member=NULL) {	
	// object
	if(!isset($member) && !is_object($member)) $member = mgm_get_member($user_id); 
	// return 
	$return = false;

	//Give exceptions to check rebill status for express checkout,mgm_authorizenet and epoch modules even AWAITING_CANCEL status also.
	$rebill_module_exceptions= array('mgm_paypalexpresscheckout','mgm_epoch','mgm_authorizenet');

	// do not process if user is awaiting cancel #1259
	if($member->status == MGM_STATUS_AWAITING_CANCEL ){
		// before reset date, DO NOT CHECK
		if( time() < strtotime(	$member->status_reset_on) ){
			return false;	
		}
		// excluded modules, DO NOT CHECK, better use $module_obj->is_rebill_status_check_supported()
		if( ! empty($member->payment_info->module) && ! in_array($member->payment_info->module, $rebill_module_exceptions)){
			return false;	
		}
	}
	// do not run on expire date	
	if( isset($member->expire_date) && ! empty($member->expire_date) ){
		// current tim
		$c_date = mgm_get_current_datetime('Y-m-d', false, 'date');
		$e_date = date('Y-m-d', strtotime($member->expire_date));
		// mgm_log( 'date run: '. $c_date . ' ' . $e_date, $this->module . '_' . __FUNCTION__);	
		// skip
		if( $e_date == $c_date ){
			return false;
		}
	}
	// if not disabled or manual
	if(!isset($member->last_payment_check) || (isset($member->last_payment_check) && $member->last_payment_check != 'disabled')){			
		// check	
		if(!empty($member->payment_info->module) && ($module_obj = mgm_is_valid_module($member->payment_info->module, 'payment', 'object'))) {	
			// check is supported
			if($module_obj->is_rebill_status_check_supported()){
				// query api, triggered by MGM, 
				// module::process_rebill_status() will be triggered via module::process_status_notify() web hook triggered by gateway	
				if($module_obj->query_rebill_status($user_id, $member)) {								
					// return 
					$return = true;
				}
			}
		}
	}	
	
	// nothing
	return $return;
}

/**
 * login failed
 *
 * @param string user login
 */ 
function mgm_login_failed($user_login){
 	// return 
	return $user_login;
} 
 
/**
 * add column to users list
 *
 * @param array $columns
 * @return array $columns
 */
function mgm_manage_users_columns( $columns ) {
	// add new columns
	$new_columns = array('subscription' => __('Subscription','mgm'));
	
 	// return
    return array_merge( $columns, $new_columns);	
}

/**
 * add row to users list
 *
 * @param mixed $column_data
 * @param string $column
 * @param int $user_id
 * @return void
 */
function mgm_manage_users_custom_column( $column_data, $column, $user_id ) {
    // column 
    switch ($column) {
        case 'subscription' :
			// nothing for admin
			if(is_super_admin($user_id)) {	
				return __('n/a','mgm');
			}
			// date format
			$date_format = mgm_get_date_format('date_format');
			// user object
			$user = get_userdata($user_id);
			// member
			$member = mgm_get_member( $user_id );
			// packs
			$packs = mgm_get_class('subscription_packs');	
			// pack
			if(isset($member->pack_id)){
				$membership = $packs->get_pack_desc($packs->get_pack($member->pack_id));
			}else{
				$membership = __('Guest','mgm');
			}			
			// set 
			$subscription  = sprintf('<div>%s</div>', $membership);
			$subscription .= sprintf('<div><span class="overline">%s:</span> %s</div>', __('REGISTER','mgm'), date($date_format, strtotime($user->user_registered)));			
			$subscription .= sprintf('<div><span class="mgm_color_gray">%s:</span> %s</div>', __('EXPIRY','mgm'), (empty($member->expire_date) ? __('N/A','mgm'):date($date_format, strtotime($member->expire_date))));
			$subscription .= sprintf('<div><span class="mgm_color_gray">%s:</span> %s</div>', __('PACK JOIN','mgm'), (empty($member->join_date) ? __('N/A','mgm'):date($date_format, $member->join_date)));
			$subscription .= sprintf('<div><span class="mgm_color_gray">%s:</span> <span class="%s"><b>%s</b></span> %s</div>', __('STATUS','mgm'), mgm_get_status_css_class($member->status), esc_html($member->status), ((!empty($member->status_str))? '<br />' . esc_html($member->status_str) : ''));
			$subscription .= sprintf('<div><span class="mgm_color_gray">%s:</span> %s</div>', __('LAST PAY','mgm'), (empty($member->last_pay_date) ? __('N/A','mgm'):date($date_format, strtotime($member->last_pay_date))));				
			
			// return 
            return $subscription;
        break; 
    }
 	// return
    return $column_data;
}
 
/**
 * user row actions
 *
 * @param array $actions
 * @param object $user
 * @return array $actions
 */ 
function mgm_user_row_actions($actions, $user){
 	// return	
	return $actions;
}

/**
 * callback to replace buddypress url with register url
 *
 * @param string $redirect
 * @return string $redirect
 */
function mgm_bp_register_url($redirect) { 	
 	// check
 	if( mgm_is_plugin_active('buddypress/bp-loader.php') ){	
		// getpages	 		
	 	$bp_pages = get_option('bp-pages');
		// check
		if (isset($bp_pages['register']) && !empty($bp_pages['register'])) {
			// list
			list($url, $qs) = explode('?', $redirect, 2);
			// trim
			$url = trailingslashit($url);			
			// compare with bussypress register url
			// if same replace base url with custom register url
			if ($url == trailingslashit(get_permalink($bp_pages['register']))) {
				// replace buddy press register url with register url
				$redirect = trailingslashit(mgm_get_custom_url('register'));
				// set qs back
				if (!empty($qs)) $redirect .= '?' . $qs; 
			}
		}
 	}
 	// return
 	return $redirect;
}

/**
 * Pre process authenticate, to hook realtime transaction checking
 *
 * @param int user id
 * @return none
 */
function mgm_pre_authenticate_user($user_id){
  	// get member
	$member = mgm_get_member($user_id);
	
	// check
	$checked = false;
	
	// skip auto login
	if(defined('MGM_DOING_REGISTER_AUTO_LOGIN')) return $checked;
		
	// skip is disabled
	if(isset($member->last_payment_check) && $member->last_payment_check == 'disabled')
		return $checked;
			
	// check status is not active OR last payment check not done OR last payment check date is not TODAY
	if($member->status != MGM_STATUS_ACTIVE || !isset($member->last_payment_check_date) || (isset($member->last_payment_check_date) && $member->last_payment_check_date != date('Y-m-d')) ){ 							
		// define
		if(!defined('DOING_QUERY_REBILL_STATUS')) define('DOING_QUERY_REBILL_STATUS', 'login');
		// check 
		if( apply_filters('mgm_module_rebill_status', $user_id, $member) ){
			$checked = true;
		}	
		// update
		if($checked) mgm_update_payment_check_state($user_id, 'login');
	}
	// IF user package is active and expiry date is over, reassign different pack as per package settings
	// This happens when user tries login for the first time after expiry date reached
	// Issue #1044
	if ($member->status == MGM_STATUS_ACTIVE && !empty($member->expire_date) && strtotime($member->expire_date) < time()) {
		apply_filters('mgm_reassign_member_subscription', $user_id, $member, 'EXPIRE', false);
	}	
	// return 
	return $checked;
}  
  
/**
 * Process admin user add
 *
 * @param int $user_id
 * @return none
 */
function mgm_admin_user_register_process($user_id,$notify_user=false){
	// get packs
	$pack = mgm_get_default_subscription_package();	
	//is admin
	$is_admin = is_admin();
	// check
	if(isset($pack['id'])){		
		// system
		$system_obj = mgm_get_class('system');	
		// member
		$member = mgm_get_member($user_id);
		//issue #1602
		if(!isset($pack['currency']) || empty($pack['currency'])){
			$currency  = $system_obj->setting['currency'];
		}else {
			$currency  = $pack['currency'];			
		}		
		// if trial on		
		if ($pack['trial_on']) {
			$member->trial_on            = $pack['trial_on'];
			$member->trial_cost          = $pack['trial_cost'];
			$member->trial_duration      = $pack['trial_duration'];
			$member->trial_duration_type = $pack['trial_duration_type'];
			$member->trial_num_cycles    = $pack['trial_num_cycles'];
		}
		// duration
		$member->duration                = $pack['duration'];
		$member->duration_type           = strtolower($pack['duration_type']);
		$member->active_num_cycles       = $pack['num_cycles'];
		$member->amount                  = $pack['cost'];
		$member->currency        		 = $currency;
		$member->membership_type 		 = $pack['membership_type'];	
		//$member->pack_id                 = $pack['pack_id'];
		//issue #1076
		$member->pack_id                 = $pack['id'];	
	
		// status
		$member->status                  = MGM_STATUS_ACTIVE;
		$member->status_str              = __('Last payment was successful','mgm');			
		// join
		$member->join_date               = time(); 					
		// old content hide
		$member->hide_old_content        = $pack['hide_old_content']; 		
		// time
		$time = time();			
		// last pay date
		$member->last_pay_date           = date('Y-m-d', $time);			
		// expire					
		if ($member->expire_date && $member->last_pay_date != date('Y-m-d', $time)) {
			// expiry
			$expiry = strtotime($member->expire_date);
			// greater
			if ($expiry > 0) {
				// time check
				if ($expiry > $time) {
					// update
					$time = $expiry;
				}
			}
		}				
		
		// duration types expanded
		$duration_exprs = mgm_get_class('subscription_packs')->get_duration_exprs();
		// time
		if(in_array($member->duration_type, array_keys($duration_exprs))) {
			// time 
			$time = strtotime("+{$member->duration} {$duration_exprs[$member->duration_type]}", $time);							
			// formatted
			$time_str = date('Y-m-d', $time);				
			// date extended				
			if (!$member->expire_date || strtotime($time_str) > strtotime($member->expire_date)) {
				// This is to make sure that expire date is not copied from the selected members if any
				$member->expire_date = $time_str;										
			}
		}
		//user pass
		$user_password =mgm_decrypt_password($member->user_password,$user_id);		
		// save
		$member->save();
		//after active
		if($notify_user) { 			
			mgm_new_user_notification($user_id, $user_password, ( $is_admin ? false: true ));
		}						
	}
	// return	
	return $user_id;
}
  
/**
 * account recover
 */   
function mgm_get_complete_registration_url($key='email', $key_value){
	// get user
	if($user = get_user_by($key, $key_value)){			
		// member
		$member = mgm_get_member($user->ID);			
		// return
		if($member->status == MGM_STATUS_NULL && empty($member->last_pay_date)){
			$args = array('action' => 'complete_payment');
			if( bool_from_yn(mgm_get_config('enable_email_as_username')) ){
				$args = array_merge($args, array('user_id'=>$user->ID));
			}else{
				$args = array_merge($args, array('username'=>$user->user_login));	
			}
			// return 
			return add_query_arg($args, mgm_get_custom_url('transactions'));
		}
	}
	// false
	return false;
}

/**
 * Returns action parameter for Expired Free users
 * @param object $member
 */
function mgm_force_upgrade_if_freepack($member) {
	// default
	$action = 'complete_payment';		
	// not set
	if(empty($member->pack_id))	return $action;
	// pack	
	$pack = mgm_get_class('subscription_packs')->get_pack($member->pack_id);		   		
	// if free pack and renewal not allowed, allow the user to upgrade
	if ($member->status == MGM_STATUS_EXPIRED && $pack['cost'] == 0 && $pack['allow_renewal'] == 0) 
	{
		$action = 'upgrade';			
	}	
	// return
	return $action;
} 

/**
 * User status change event, tracks exipre and update autoresponder
 *
 * @param int $user_id
 * @param string $new_status
 * @param string $old_status
 * @param string $context
 * @param int $pack_id
 * @return void
 */   
function mgm_user_status_change_process($user_id, $new_status, $old_status, $context=NULL, $pack_id=NULL){
	// args
	$args = func_get_args();		
	// check
	if($new_status == MGM_STATUS_EXPIRED){		
		// if setting
		if( bool_from_yn(mgm_get_setting('unsubscribe_autoresponder_on_expire', 'N')) ){
			// get member
			$member = mgm_get_member($user_id);// @todo get member by pack
			// get another
			if((int)$pack_id > 0 && $member->pack_id != $pack_id){
				// get another member
				$member = mgm_get_member_another_purchase($user_id, NULL, $pack_id);
			}		
			// check
			if(bool_from_yn($member->subscribed)){
				// call @todo
				mgm_autoresponder_send_unsubscribe($user_id, $member);
			}
		}
	}
}   

/**
 * hook to mgm register form and print other plugin generated fields
 *
 * @param string $form_html
 * @return string $form_html
 * @since 2.7
 */
function mgm_register_form_additional($form_html){
	// return
	return $form_html = mgm_get_action_hook_output('register_form');
}

/**
 * hook to mgm login form and print other plugin generated fields
 *
 * @param string $form_html
 * @return string $form_html
 * @since 2.7
 */
function mgm_login_form_additional($form_html){
	// return
	return $form_html = mgm_get_action_hook_output('login_form');
}

/**
 * hook to mgm login form and print other plugin generated fields
 *
 * @param string $form_html
 * @return string $form_html
 * @since 2.7
 */
function mgm_lostpassword_form_additional($form_html){
	// return
	return $form_html = mgm_get_action_hook_output('lostpassword_form');
}

/**
 * login errors display fix for email as username
 *
 * @param string $errors
 * @return string $errors
 * @since 2.7
 */
function mgm_login_errors($errors){
	
	if(bool_from_yn(mgm_get_setting('enable_email_as_username'))){				
		$errors = preg_replace('#username <strong>(.*)</strong> is#', 'email <strong>' . mgm_post_var('log') . '</strong> is', $errors);
		//issue #1412
		if(!preg_match('#' . __('unfinished','mgm') . '#', $errors)){
			$errors = preg_replace('#' . __('username','mgm') . '#', __('Email','mgm'), $errors);
		}
	}
			
	return $errors;
} 

/**
 * member object read action, used to copy member data to metadata for easy user query
 * 
 * @param array $options saved in usermeta
 * @param int user_id
 * @return void
 * @since 2.7
 */
function mgm_user_options_sync_read($options, $user_id){
	// name
	//$filename = ('user_' . $user_id . '_' .__FUNCTION__);
	// log
	//mgm_log($options, $filename);
	
	// not in meta, this should happen once as MGM does not have option to switch modules
	if( mgm_get_user_option('_mgm_module_has_rebill_status_check', $user_id) === FALSE ){
		// init
		$has_rebill_status_check = 'N';
		// check module
		if( isset($options['payment_info']['module']) && !empty($options['payment_info']['module']) ){
			// get module data
			if( mgm_is_valid_module($options['payment_info']['module']) ){
				if( mgm_get_module($options['payment_info']['module'])->is_rebill_status_check_supported() ){
					$has_rebill_status_check = 'Y';
				}		
			}	
		}
		// update
		update_user_option($user_id, '_mgm_module_has_rebill_status_check', $has_rebill_status_check, true);		
	}
	
	// not in meta, also mark this in save as status changes occur often
	if( mgm_get_user_option('_mgm_user_status', $user_id) === FALSE ){
		// init
		$mgm_user_status = MGM_STATUS_NULL;
		// check module
		if( isset($options['status']) && ! empty($options['status']) ){
			// set			
			$mgm_user_status = $options['status'];						
		}
		// update
		update_user_option($user_id, '_mgm_user_status', $mgm_user_status, true);		
	}

	// not in meta, also mark this in save as manual pack assign can alter it  - issue #1515
	if( mgm_get_user_option('_mgm_user_billing_num_cycles', $user_id) === FALSE && !empty($options)){
		// init
		$user_billing_num_cycles = 'ongoing';// ongoing
		// check limited
		if( isset($options['active_num_cycles']) && (int)$options['active_num_cycles'] >= 1 ){
			// set			
			$user_billing_num_cycles = (int)$options['active_num_cycles'];	
		}else{
			// check pack
			if( isset($options['pack_id']) && (int)$options['pack_id'] > 0 ){
				// set		
				if( $pack = mgm_get_class('subscription_packs')->get_pack($options['pack_id']) ){
					if ( isset($pack['num_cycles']) && (int)$pack['num_cycles'] >= 1 ){
						$user_billing_num_cycles = (int)$pack['num_cycles'];				
					}		
				}
			}
		}
		// update
		update_user_option($user_id, '_mgm_user_billing_num_cycles', $user_billing_num_cycles, true);		
	}
	//membership level wise user meta - issue #1681	
	if(!empty($options) && isset($options['pack_id']) && (int)$options['pack_id'] > 0 ){
		// init
		$user_billing_num_cycles = 'ongoing';// ongoing
		// check limited
		if( isset($options['active_num_cycles']) && (int)$options['active_num_cycles'] >= 1 ){
			// set			
			$user_billing_num_cycles = (int)$options['active_num_cycles'];	
		}else{
			// check pack
			if( isset($options['pack_id']) && (int)$options['pack_id'] > 0 ){
				// set		
				if( $pack = mgm_get_class('subscription_packs')->get_pack($options['pack_id']) ){
					if ( isset($pack['num_cycles']) && (int)$pack['num_cycles'] >= 1 ){
						$user_billing_num_cycles = (int)$pack['num_cycles'];				
					}		
				}
			}
		}
		// update
		update_user_option($user_id,sprintf('mgm_user_billing_num_cycles_%d', $options['pack_id']), $user_billing_num_cycles, true);	
	}	

	// not in meta, also mark this in save as status changes occur often
	if( mgm_get_user_option('_mgm_user_register_coupon', $user_id) === FALSE ){
		// check module
		if( isset($options['coupon']['id']) && !empty($options['coupon']['id']) ){
			// set			
			$mgm_user_register_coupon = (int)$options['coupon']['id'];			

			// update
			update_user_option($user_id, '_mgm_user_register_coupon', $mgm_user_register_coupon, true);				
		}			
	}

	// not in meta, also mark this in save as status changes occur often
	if( mgm_get_user_option('_mgm_user_upgrade_coupon', $user_id) === FALSE ){
		// check module
		if( isset($options['upgrade']['coupon']['id']) && !empty($options['upgrade']['coupon']['id']) ){
			// set			
			$mgm_user_upgrade_coupon = (int)$options['upgrade']['coupon']['id'];		

			// update
			update_user_option($user_id, '_mgm_user_upgrade_coupon', $mgm_user_upgrade_coupon, true);					
		}			
	}

	// not in meta, also mark this in save as status changes occur often
	if( mgm_get_user_option('_mgm_user_extend_coupon', $user_id) === FALSE ){
		// check module
		if( isset($options['extend']['coupon']['id']) && !empty($options['extend']['coupon']['id']) ){
			// set			
			$mgm_user_extend_coupon = (int)$options['extend']['coupon']['id'];		

			// update
			update_user_option($user_id, '_mgm_user_register_coupon', $mgm_user_extend_coupon, true);			
		}			
	}
	// othe membership level coupon usage purchase not in meta, also mark this in save as status changes occur often
	if(isset($options['other_membership_types']) && !empty($options['other_membership_types'])) {
		
		foreach ($options['other_membership_types'] as $other_membership_type) {
			
			if(!empty($other_membership_type)) {
				//othermembership level wise user meta - issue #1681		
				if(isset($other_membership_type['pack_id']) && (int)$other_membership_type['pack_id'] > 0 ){
					// init
					$user_billing_num_cycles = 'ongoing';// ongoing
					// check limited
					if( isset($other_membership_type['active_num_cycles']) && (int)$other_membership_type['active_num_cycles'] >= 1 ){
						// set			
						$user_billing_num_cycles = (int)$other_membership_type['active_num_cycles'];	
					}else{
						// check pack
						if( isset($other_membership_type['pack_id']) && (int)$other_membership_type['pack_id'] > 0 ){
							// set		
							if( $pack = mgm_get_class('subscription_packs')->get_pack($other_membership_type['pack_id']) ){
								if ( isset($pack['num_cycles']) && (int)$pack['num_cycles'] >= 1 ){
									$user_billing_num_cycles = (int)$pack['num_cycles'];				
								}		
							}
						}
					}
					// update
					update_user_option($user_id, sprintf('mgm_user_billing_num_cycles_%d', $other_membership_type['pack_id']), $user_billing_num_cycles, true);	
				}								
				//other members upgrade
				if(isset($other_membership_type['upgrade']['coupon']['id']) && !empty($other_membership_type['upgrade']['coupon']['id'])){
					
					$coupon_id = (int)$other_membership_type['upgrade']['coupon']['id'];
					
					if( mgm_get_user_option($coupon_id.'_mgm_user_upgrade_coupon', $user_id) === FALSE ){
						// update
						update_user_option($user_id, $coupon_id.'_mgm_user_upgrade_coupon', $coupon_id, true);
					}
				}
				//other members extend
				if(isset($other_membership_type['extend']['coupon']['id']) && !empty($other_membership_type['extend']['coupon']['id'])){
					
					$coupon_id = (int)$other_membership_type['extend']['coupon']['id'];
					
					if( mgm_get_user_option($coupon_id.'_mgm_user_extend_coupon', $user_id) === FALSE ){
						// update
						update_user_option($user_id, $coupon_id.'_mgm_user_extend_coupon', $coupon_id, true);
					}
				}
			}			
		}		
	}
	// not in meta, also mark this in save as status changes occur often
	// rss token
	if( mgm_get_user_option('_mgm_user_rss_token', $user_id) === FALSE ){
		// check options for existing rss token
		if( isset($options['rss_token']) && ! empty($options['rss_token']) ){
			// update meta
			update_user_option($user_id, '_mgm_user_rss_token', $options['rss_token'], true);
		}	
	}else{
		if( isset($options['rss_token']) && ! empty($options['rss_token']) ){
			// mismatch
			if( mgm_get_user_option('_mgm_user_rss_token', $user_id) != $options['rss_token'] ){
				// update meta
				update_user_option($user_id, '_mgm_user_rss_token', $options['rss_token'], true);
			}
		}		
	}
}

/** 
 * member object write action, used to copy member data to metadata for easy user query
 * 
 * @param array $options saved in usermeta
 * @param int user_id
 * @return void	
 * @since 2.7
 */
function mgm_user_options_sync_save($options, $user_id){
	// name
	// $filename = ('user_' . $user_id . '_' .__FUNCTION__);
	// log
	// mgm_log($options, $filename);
	
	// set status if changed	
	if( isset($options['status']) && ! empty($options['status']) ){
		// check
		if( $options['status'] != mgm_get_user_option('_mgm_user_status', $user_id) ){
		// update
			update_user_option($user_id, '_mgm_user_status', $options['status'], true);	
		}	
	}
}

/** 
 * rebill notify on rebill status change
 * 
 * @param array $options saved in usermeta
 * @param int user_id
 * @return void	
 * @since 2.7
 */
function mgm_notify_on_rebill_status_change($user_id, $new_status, $old_status, $context){
	// system	
	$system_obj = mgm_get_class('system');			
	$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
	$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
	
	// get member
	$member = mgm_get_member( $user_id );
	
	// log
	// mgm_log($member, __FUNCTION__);

	// notify
	$notify = false;// @todo check by last_pay_date
	// check
	if( ! $last_email_notify_date = get_user_option( '_mgm_last_email_notify_date', $user_id ) ){
		// check
		$notify = true;					
		// log
		// mgm_log('Block1: ' . $last_email_notify_date . ' - ' . $member->last_pay_date . ' ' . $notify, __FUNCTION__);
	}else{
	// check	
		if( ! empty($member->last_pay_date) && $last_email_notify_date != $member->last_pay_date ){
			$notify = true;
		}
		// log
		// mgm_log('Block2: ' . $last_email_notify_date . ' - ' . $member->last_pay_date . ' ' . $notify, __FUNCTION__);
	}
	// notify user
	if( $notify ){
		// packs
		$s_packs = mgm_get_class('subscription_packs');
		// get user
		$user = get_userdata( $user_id );
		// blog
		$blogname = get_option('blogname');
		// notify user
		if ( ! $dpne ) {	
			// get pack
			if($member->pack_id){
				$subs_pack = $s_packs->get_pack($member->pack_id);
			}else{
				$subs_pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
			}
			// custom			
			$custom = mgm_get_transaction( $member->transaction_id );
			// notify
			if( mgm_notify_user_membership_purchase($blogname, $user, $member, $custom, $subs_pack, $s_packs, $system_obj) ){		
				// tracking
				update_user_option( $user_id, '_mgm_last_email_notify_date', $member->last_pay_date, true);		
				// log
				// mgm_log('mgm_last_email_notify_date updated: ' . $member->last_pay_date, __FUNCTION__);		
				// update as email sent 
				/*				
				if( $module = mgm_is_valid_module($member->payment_info->module) ){
					$module->update_paymentemail_sent( $member->transaction_id );	
				}*/	
				// check
				if( $module = $member->payment_info->module ) {
					// if a valid module
					if( $obj_module = mgm_is_valid_module($module, 'payment', 'object') ){
						$obj_module->update_paymentemail_sent( $member->transaction_id );
					}
				}				
			}	
		}
		// notify admin
		if ( ! $dge ) {			
			// pack duration
			$pack_duration = $s_packs->get_pack_duration($subs_pack);
			// notify admin,
			mgm_notify_admin_membership_purchase($blogname, $user, $member, $pack_duration);	
		}		
	}
}


/**
 * checks multiple logins from different IPs
 *
 * @since 1.8.38
 */
function mgm_check_multiple_logins_violation($user, $member, $pack){
	global $wpdb;
	// ip
	$ip_address = mgm_get_client_ip_address();
	// time period
	$time_period = mgm_get_setting('multiple_login_time_span');// 1 HOUR
	// datetime
	$current_time = strtotime(current_time('mysql', 1));
	// last time
	$last_time = strtotime('-' . $time_period, $current_time);
	// sql
	$sql = "SELECT COUNT(*) AS _C FROM `". TBL_MGM_MULTIPLE_LOGIN_RECORDS ."` WHERE 1
	       AND `user_id`='{$user->ID}' AND `pack_id`='{$member->pack_id}' AND `logout_at` IS NULL
	       AND `login_at` >= FROM_UNIXTIME({$last_time}) AND `login_at` <= FROM_UNIXTIME({$current_time})";
	// check
	$login_count = $wpdb->get_var( $sql );
	// check
	// mgm_log( $pack, __FUNCTION__);
	// check
	if( isset($pack['multiple_logins_limit']) && (int)$pack['multiple_logins_limit'] > 0 ){
		if( $login_count >= (int)$pack['multiple_logins_limit'] ){
			return true;// error
		}
	}
	// check
	// mgm_log( $wpdb->last_query .' -- LOGIN COUNT: ' . $login_count, __FUNCTION__);

	// insert only if not done yet
	$sql = "SELECT COUNT(*) AS _C FROM `". TBL_MGM_MULTIPLE_LOGIN_RECORDS ."` WHERE 
	       `user_id`='{$user->ID}' AND `pack_id`='{$member->pack_id}' AND `ip_address`='{$ip_address}'";
	$count = $wpdb->get_var( $sql );       
	// check
	// mgm_log( $wpdb->last_query .' -- PREV RECORD COUNT: ' . $count, __FUNCTION__);
	// record	
	if( $count == 0 ){// first
		$sql= "INSERT INTO `". TBL_MGM_MULTIPLE_LOGIN_RECORDS ."` SET `user_id`='{$user->ID}',
		      `pack_id`='{$member->pack_id}',`ip_address`='{$ip_address}',`login_at`=NOW(),
		      `logout_at`=NULL";
	}else{// next
		$sql= "UPDATE `". TBL_MGM_MULTIPLE_LOGIN_RECORDS ."` SET `login_at`=NOW(),
		      `logout_at`=NULL WHERE `user_id`='{$user->ID}' AND `pack_id`='{$member->pack_id}' 
		      AND `ip_address`='{$ip_address}'";
	}

	// execute
	$wpdb->query($sql);	

	// check
	// mgm_log( $wpdb->last_query, __FUNCTION__);

	// return 
	return false;
}

/**
 * record logout at
 *
 * @since 1.8.38 
 */
function mgm_record_logout_at(){
	//logout pack-user(multiple)
    global $wpdb; 
    // ip
	$ip_address = mgm_get_client_ip_address();
	// user
	$user = wp_get_current_user();
	$member = mgm_get_member($user->ID);
	
	// update at logout time	
	$sql= "UPDATE  `".TBL_MGM_MULTIPLE_LOGIN_RECORDS."` SET `logout_at` = NOW() 
	      WHERE `user_id` = '{$user->ID}' AND `pack_id` = '{$member->pack_id}' 
	      AND `ip_address` = '{$ip_address}'";
    $wpdb->query($sql); 
}
/*
function mgm_bp_protect_content_before(){
	echo '<br>BEFORE----';
}

function mgm_bp_protect_content_after(){
	echo '<br>AFTER----';
}*/
// end file