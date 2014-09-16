<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members payment buttons & functions
 *
 * @package MagicMembers
 * @subpackage Facebook
 * @since 2.6
 */
 
/**
 * generate subscription buttons for first time payment
 *
 * @param array userdata
 * @retun string html output
 */
function mgm_get_subscription_buttons($user=false){
	global $wpdb;
	// user 
	if( $user === FALSE ){
		// query string
		$user = mgm_get_user_from_querystring();
	} 	
	
	// validate
	if( !$user->ID ) {	
		return __('No such user', 'mgm');
	}
	// packs 	
	$packs_obj = mgm_get_class('subscription_packs');
	// mgm member
	$member = mgm_get_member($user->ID);			

	// check subscription
	if (isset($_GET['subs'])) {
		// init
		$html = '';
		// get		
		$subs_pack = mgm_decode_package(strip_tags($_GET['subs']));
		extract($subs_pack);		
		// validate			
		$pack = $packs_obj->validate_pack($cost, $duration, $duration_type, $membership_type, $pack_id);		
		// error
		if($pack === false){
			// no more process
			return  sprintf(__('Invalid Data Passed. <a href="%1$s">Try again.</a>','mgm'), add_query_arg(array('username'=>$user->user_login), mgm_get_custom_url('transactions')));
		}		
		
		// is using a coupon ? reset prices
		mgm_get_register_coupon_pack($member, $pack);				
		
		//issue #1468
	  	$notify_user = isset($_GET['notify_user']) ? $_GET['notify_user'] : false;	
	  	// get
		$system_obj = mgm_get_class('system');
		// check
		if( bool_from_yn( $system_obj->get_setting('enable_new_user_email_notifiction_after_user_active') ) && ! $notify_user) {
			$notify_user = true;
		}						
		// get active modules
		$a_payment_modules = mgm_get_class('system')->get_active_modules('payment');					
		// free | trial with zero cost | zero coupons |other membership with free module active	-issue #1072 added -manualpay check	
		if ((float)$pack['cost'] == 0.00 && (isset($pack['coupon_id']) || in_array($membership_type, array('free','trial')) || in_array('mgm_free',(array)$pack['modules']) && !in_array('mgm_manualpay',(array)$pack['modules']))) {	
			// payments url
			$payments_url == mgm_get_custom_url('transactions');
			// module 
			$modules = array('mgm_'.$membership_type);
			// other
			$modules[] = ($membership_type=='free') ? 'mgm_trial' : 'mgm_free';
			// init
			$module = '';
			// check if mod available
			foreach($modules as $mod){
				// check
				if(in_array($mod, $a_payment_modules)){	
					$module = $mod;			
					break;
				}
			}
			
			// exit
			if( ! $module ){
				// return
				return __('No Free module active, please activate Trial or Free module.','mgm'); exit;
			}
			
			// redirect
			$redirector = strip_tags($_GET['redirector']);
			// get object
			$mod_obj = mgm_get_module($module,'payment');
			// tran options
			$tran_options = array('is_registration'=>true, 'user_id' => $user->ID, 'notify_user' => $notify_user);
			// is register & purchase
			if(isset($_GET['post_id'])){
				$tran_options['post_id'] = (int)strip_tags($_GET['post_id']);
			}
			// is register & purchase postpack
			if(isset($_GET['postpack_post_id']) && isset($_GET['postpack_id'])){
				$tran_options['postpack_post_id'] = (int)strip_tags($_GET['postpack_post_id']);
				$tran_options['postpack_id'] = (int)strip_tags($_GET['postpack_id']);
			}			
			// tran id
			$transid = mgm_add_transaction($pack, $tran_options);			
			// attempt to redirect to the processor.			
			$redirect = add_query_arg(array('method'=>'payment_return', 'module'=>$module, 'custom' => ($user->ID . '_' . $duration . '_'  . $duration_type . '_' . $pack_id), 'redirector'=>$redirector, 'transid' => mgm_encode_id($transid)), $payments_url);							
			// redirect
			if ( ! headers_sent() ) {									
				@header('location: ' . $redirect);
			}
			// js redirect
			$html .= sprintf( '<script type="text/javascript">window.location = "%s";</script><div>%s</div>', $redirect, $packs_obj->get_pack_desc($pack) );
		
		} else {	
		// paid subscription 		
			// init 
			$payment_modules = array();			
			// when active
			if($a_payment_modules){
				// loop
				foreach($a_payment_modules as $payment_module){
					// not trial
					if(in_array($payment_module, array('mgm_free','mgm_trial'))) continue;
					// modules
					if(isset($pack['modules']) && !in_array($payment_module, (array)$pack['modules'])) continue;
					// store
					$payment_modules[] = $payment_module;					
				}
			}
			// check				
			if(count($payment_modules)==0){
				// error
				$html .= sprintf( '<div>%s</div>', __('No active payment module', 'mgm') );
			}else{	
				//issue #1072
				if ((float)$pack['cost'] == 0.00 && in_array('mgm_manualpay',(array)$pack['modules'])) {										
					// pack desc			
					$html .= sprintf( '<div class="mgm_get_subs_btn" >%s</div>', $packs_obj->get_pack_desc($pack) );
					// coupon				
					if(isset($member->coupon['id'])){
						$html .= sprintf( '<div class="mgm_get_subs_btn" >%s</div>', sprintf(__('Using Coupon "%s" - %s','mgm'), $member->coupon['name'], $member->coupon['description']));
					}
					// html
					$html .= sprintf( '<div class="mgm_get_subs_btn" >%s</div>', __('Please Select from Available Payment Gateways','mgm') );
					// tran id
					$tran_id = 0;
					// generate modules
					foreach($payment_modules as $payment_module){
						// check
						if($payment_module =='mgm_manualpay') {
							// get obj
							$mod_obj = mgm_get_module($payment_module,'payment');
							// create transaction
							if($tran_id==0){
							// set
								// tran options
								$tran_options = array('is_registration'=>true, 'user_id' => $user->ID, 'notify_user' => $notify_user);
								// is register & purchase
								if(isset($_GET['post_id'])){
									$tran_options['post_id'] = (int)strip_tags($_GET['post_id']);
								}
								// is register & purchase postpack
								if(isset($_GET['postpack_post_id']) && isset($_GET['postpack_id'])){
									$tran_options['postpack_post_id'] = (int)strip_tags($_GET['postpack_post_id']);
									$tran_options['postpack_id'] = (int)strip_tags($_GET['postpack_id']);
								}								
								// create
								// $tran_id = $mod_obj->_create_transaction($pack, $tran_options);
								$tran_id = mgm_add_transaction($pack, $tran_options);
							}
							// html
							$html .= sprintf( '<div>%s</div>', $mod_obj->get_button_subscribe(array('pack'=>$pack,'tran_id'=>$tran_id)) );
						}
					}					
				}else {
					// pack desc			
					$html .= sprintf( '<div class="mgm_get_subs_btn" >%s</div>', $packs_obj->get_pack_desc($pack) );
					// coupon				
					if(isset($member->coupon['id'])){
						$html .= sprintf( '<div class="mgm_get_subs_btn" >%s</div>', sprintf(__('Using Coupon "%s" - %s','mgm'), $member->coupon['name'], $member->coupon['description']));
					}
					// html
					$html .= sprintf( '<div class="mgm_get_subs_btn" >%s</div>', __('Please Select from Available Payment Gateways','mgm') );
					// tran id
					$tran_id = 0;
					// generate modules
					foreach($payment_modules as $payment_module){
						// get obj
						$mod_obj = mgm_get_module($payment_module,'payment');
						// create transaction
						if($tran_id==0){
						// set
							// tran options
							$tran_options = array('is_registration'=>true, 'user_id' => $user->ID, 'notify_user' => $notify_user);
							// is register & purchase
							if(isset($_GET['post_id'])){
								$tran_options['post_id'] = (int)strip_tags($_GET['post_id']);
							}
							// is register & purchase postpack
							if(isset($_GET['postpack_post_id']) && isset($_GET['postpack_id'])){
								$tran_options['postpack_post_id'] = (int)strip_tags($_GET['postpack_post_id']);
								$tran_options['postpack_id'] = (int)strip_tags($_GET['postpack_id']);
							}							
							// create
							// $tran_id = $mod_obj->_create_transaction($pack, $tran_options);
							$tran_id = mgm_add_transaction($pack, $tran_options);
						}
						// html
						$html .= sprintf( '<div>%s</div>', $mod_obj->get_button_subscribe(array('pack'=>$pack,'tran_id'=>$tran_id)) );
					}					
				}				
			}
		}
		// return	
		return $html;	
	}
	// error
	return '';
}

/**
 * show buttons of modules available for upgrade/downgrade/complete payment
 *
 * @param array args
 * @return string html
 */
function mgm_get_upgrade_buttons($args=array()) { 
	global $wpdb;	
	// current user
	$user = wp_get_current_user();
	// get user
	if( ! $user->ID ) {
	// get user from query string
		$user = mgm_get_user_from_querystring();
	}	
	// validate
	if( ! $user->ID ) {
		return __('No such user', 'mgm');
	}
	
	// userdata
	$username  = $user->user_login;
	$mgm_home  = get_option('siteurl');
	// upgrdae multiple
	$multiple_upgrade = false;
	//issue #1511
	$prev_pack_id = mgm_get_var('prev_pack_id', '', true);
	$prev_membership_type = mgm_get_var('membership_type', '', true);
	// get member
	// issue#: 843 (3)
	if(isset($prev_pack_id) && (int)$prev_pack_id > 0 && isset($prev_membership_type) && !empty($prev_membership_type)) {
		// only for multiple membership upgrade
		$multiple_upgrade = true;
		// get member
		$member = mgm_get_member_another_purchase($user->ID, $prev_membership_type, $prev_pack_id);
		// mark status as inactive
		$member->status = MGM_STATUS_NULL;		
	}else {
		$member = mgm_get_member($user->ID);
		
		//this is a fix for issue#: 589, see the notes for details:
		//This is to read saved coupons as array in order to fix the fatal error on some servers.	
		//This will change the object on each users profile view.
		//Also this will avoid using patch for batch update,	
		$old_coupons_found = 0;
		// loop		
		foreach (array('upgrade', 'extend') as $coupon_type) {
			// check
			if(isset($member->{$coupon_type}['coupon']) && is_object($member->{$coupon_type}['coupon'])) {
				// convert
				$member->{$coupon_type}['coupon'] = (array) $member->{$coupon_type}['coupon'];
				// mark
				$old_coupons_found++ ;
			}
		}
		// save if old coupons found
		if($old_coupons_found) $member->save();		
	}
	
	// other objects
	$system_obj = mgm_get_class('system');	
	$packs_obj  = mgm_get_class('subscription_packs');
	// membership_type
	$membership_type = (isset($prev_membership_type) && !empty($prev_membership_type)) ? $prev_membership_type : mgm_get_user_membership_type($user->ID, 'code');// captured above	
	
	// duration	
	$duration_str    = $packs_obj->duration_str;
	$trial_taken     = $member->trial_taken;	
	// pack_id if main mgm_member / multiple membership	
	$pack_id         = (isset($prev_pack_id) && (int)$prev_pack_id > 0) ? $prev_pack_id : (int)$member->pack_id;
	// got pack
	if($pack_id) {
		$pack_details         = $packs_obj->get_pack($pack_id);
		$pack_membership_type = $pack_details['membership_type'];
		$preference 		  = $pack_details['preference'];
	}else {
		$preference = 0;
	}
	
	// action - issue #1275	
	$action = mgm_get_var('action', '', true);
		
	if($action == 'complete_payment') {
		// get active packs on complete payment page	
		$active_packs 	 = $packs_obj->get_packs('register');	
	}else {
		// get active packs on upgrade page	
		$active_packs 	 = $packs_obj->get_packs('upgrade');
		//issue #1368
		// loop and preference		
		foreach ($active_packs as $_pack) {						
			// set preference order for later sort
			$pack_preferences[] = $_pack['preference'];
		}
		
		// preference sort packs
		if(count($pack_preferences)>0){
			// preference sort
			sort($pack_preferences);			
			//preference sorted
			$preferences_sorted = array();
			// loop by preference
			foreach($pack_preferences as $pack_preference){
				//issue #1710
				if($pack_preference >= $preference){
					// loop packs
					foreach ($active_packs as $_pack) {
						// preference order match
						if($_pack['preference'] == $pack_preference){
							// duplicate check
							if(!in_array($_pack['id'], $preferences_sorted)){
								// set pack
								$preference_packs[] = mgm_stripslashes_deep($_pack);							
								// mark as preference sorted
								$preferences_sorted[] = $_pack['id'];
							}
						}
					}
				}
			}
		}			
		
		$active_packs = $preference_packs;			
	}
	
	// issue#: 664
	// action : upgrade/complete_payment. Allow complete payment only if there is an associated $pack_id and the current subscription is not free/trial
	$action = (!empty($action) && (int)$pack_id > 0) ? $action : 'upgrade'; // upgrade or complete_payment	
	// show current
	$show_current_pack = false;	
	// switch
	if($action == 'complete_payment' && isset($pack_membership_type) && in_array($pack_membership_type, array('free', 'trial'))) {
		// upgrade 
		$action = 'upgrade';
		// show current
		$show_current_pack = true;		
	}
	// form action
	// carry forward get params	
	$url_parms  = array('action' => $action, 'user_id'=>$user->ID);// 'username'=> $username,
	// prev_membership_type
	if (isset($prev_membership_type)) $url_parms['membership_type'] = $prev_membership_type;
	// prev_pack_id
	if (isset($prev_pack_id)) $url_parms['prev_pack_id'] = $prev_pack_id;	
	// form action
	$form_action = mgm_get_custom_url('transactions', false, $url_parms);

	// issue 1009
	if( ! $membership_details_url = $system_obj->get_setting('membership_details_url') ){		
		$membership_details_url = get_admin_url() . 'profile.php?page=mgm/profile';
	}
	
	// cancel 
	$cancel_url = ($action == 'upgrade' && $user->ID > 0) ? $membership_details_url : mgm_get_custom_url('login');
	
	// active modules
	$a_payment_modules = $system_obj->get_active_modules('payment');	
		
	// bug from liquid-dynamiks.com theme #779
	if( isset($_POST['wpsb_email']) ) unset($_POST['wpsb_email']);
	
	// posted form-----------------------------------------------------------------------	
	if( ! empty($_POST) || isset($_GET['edit_userinfo'])){			
		// update user data
		if(isset($_POST['method']) && $_POST['method'] == 'update_user'){
			// user lib
			if ( mgm_compare_wp_version('3.1', '<') ){// only before 3.1
				require_once( ABSPATH . WPINC . '/registration.php');
			}
			// callback
			// do_action('personal_options_update', $user->ID);	
			// not multisite, duplicate email allowed ?	
			if ( ! is_multisite() ) {
				// save
				$errors = mgm_user_profile_update($user->ID);
			}else {
			// multi site
				// get user
				$user = get_userdata( $user->ID );
				// update here:
				// Update the email address, if present. duplicate check
				if ( $user->user_login && isset( $_POST[ 'user_email' ] ) && is_email( $_POST[ 'user_email' ] ) && $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->signups} WHERE user_login = %s", $user->user_login ) ) )
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->signups} SET user_email = %s WHERE user_login = %s", $_POST[ 'user_email' ], $user->user_login ) );
				
				// edit 
				if ( !isset( $errors ) || ( isset( $errors ) && is_object( $errors ) && false == $errors->get_error_codes() ) )
					$errors = mgm_user_profile_update($user->ID);
			}
			
			// errors
			if(isset($errors) && !is_numeric($errors)) {				
				// get error
				$error_html = mgm_set_errors($errors, true);
				// edit flag
				$_GET['edit_userinfo'] = 1;
			}	
		}	
		
		// second step for complete payment, userdata edit
		if(isset($_GET['edit_userinfo'])){	
			// error
			if(isset($error_html)){
				$html .= $error_html;
			}		
			// form
			$html .= sprintf('<form action="%s" method="post" class="mgm_form">', $form_action);
			$html .= sprintf('<p>%s</p>', __('Edit Your Personal Information', 'mgm'));
			// get custom fields
			$html .= mgm_user_profile_form($user->ID, true);
			// html
			$html .= '<input type="hidden" name="ref" value="'. md5($member->amount .'_'. $member->duration .'_'. $member->duration_type .'_'. $member->membership_type) .'" />';					
			$html .= '<input type="hidden" name="form_action" value="'. $form_action .'" />';	
			$html .= '<input type="hidden" name="subs_opt" value="'. $_POST['subs_opt'] .'" rel="mgm_subscription_options"/>';	
			// carry forward mgm_payment_gateways field value: issue#: 919
			if(isset($_POST['mgm_payment_gateways']))
				$html .= '<input type="hidden" name="mgm_payment_gateways" value="'. $_POST['mgm_payment_gateways'] .'" />';
			//issue #1236
			if(isset($_POST['mgm_upgrade_field']['coupon']) && !empty($_POST['mgm_upgrade_field']['coupon'])) {
				//issue #1250 - Coupon validation 
				if(!empty($_POST['form_action'])) {				
					//issue #1591
					$coupon_err_redirect_url= $_POST['form_action'];
					if(preg_match('/complete_payment/', $coupon_err_redirect_url)){						
						$coupon_err_redirect_url =	str_replace('&edit_userinfo=1','',$coupon_err_redirect_url);
					}					
					// check if its a valid coupon
					if(!$coupon = mgm_get_coupon_data($_POST['mgm_upgrade_field']['coupon'])){				
						//redirect back to the form							
						$q_arg = array('error_field' => 'Coupon', 'error_type' => 'invalid','error_field_value'=>$_POST['mgm_upgrade_field']['coupon']);
						$redirect = add_query_arg($q_arg, $coupon_err_redirect_url);														
						mgm_redirect($redirect);
						exit;
					}else{
						// get subs 			
						if( $subs_pack = mgm_decode_package(mgm_post_var('subs_opt')) ){	
							// values
							$coupon_values = mgm_get_coupon_values(NULL, $coupon['value'], true);
							// check
							if(isset($coupon_values['new_membership_type']) && $coupon_values['new_membership_type'] != $subs_pack['membership_type']){
								$new_membership_type = mgm_get_membership_type_name($coupon_values['new_membership_type']);							
								$q_arg = array(	'error_field' => 'Coupon', 
											   	'error_type' => 'invalid',
											   	'membership_type' => $coupon_values['new_membership_type'],
											   	'error_field_value'=>$_POST['mgm_upgrade_field']['coupon']);
								$redirect = add_query_arg($q_arg, $coupon_err_redirect_url);														
								mgm_redirect($redirect);
								exit;							
							}
						}	
					}
				}			
				$html .= '<input type="hidden" name="mgm_upgrade_field[coupon]" value="'. $_POST['mgm_upgrade_field']['coupon'] .'" class="mgm_upgrade_field">';
			}
			// set
			$html .= sprintf('<p>
								 <input class="button button-primary" type="button" name="back" onclick="window.location=\'%s\'" value="%s" />						
							 	 <input class="button button-primary" type="submit" name="submit" value="%s" />&nbsp;&nbsp;
						      	 <input class="button button-primary" type="button" name="cancel" onclick="window.location=\'%s\'" value="%s" />&nbsp;					
					          </p>', $form_action, __('Back','mgm'), __('Save & Next','mgm'), $cancel_url, __('Cancel','mgm'));
			// html
			$html .= '</form>';
		// final step, show payment buttons
		}elseif( isset($_POST['submit']) ) {		
			// verify selected
			if( ! isset($_POST['subs_opt']) ){
				// die
				return sprintf(__('Package not selected, <a href="%s">go back</a>.','mgm'), $_POST['form_action']); exit;
			}	
			
			// check and validate passed data		
			if ($_POST['ref'] != md5($member->amount .'_'. $member->duration .'_'. $member->duration_type .'_'. $member->membership_type)) {
				// die
				return __('Package data tampered. Cheatin!','mgm'); exit;				
			}
			
			// get selected pack 			
			$selected_pack = mgm_decode_package($_POST['subs_opt']);
			
			// check selected pack is a valid pack		     
			$valid = false;
			// loop packs
			foreach($active_packs as $pack) {
				// check
				if ($pack['cost'] == $selected_pack['cost'] 
					&& $pack['duration'] == $selected_pack['duration'] 
					&& $pack['duration_type'] == $selected_pack['duration_type'] 
					&& $pack['membership_type'] == $selected_pack['membership_type']
					&& $pack['id'] == $selected_pack['pack_id'] 
					) 
				{
					// valid
					$valid = true; break;
				}
			}
			// error
			if (!$valid) {  
				return __('Invalid package data. Cheatin!','mgm'); exit;	
			}
			
			//update description if not set
			if(!isset($selected_pack['description'])) {
				$selected_pack['description'] = $pack['description'];
			}
			
			//update pack currency - issue #1602
			if(isset($pack['currency']) && !empty($pack['currency'])) {
				$selected_pack['currency'] = $pack['currency'];
			}			
			// num cycle
			if(!isset($selected_pack['num_cycles'])) {
				//Note the above break in for loop:
				$selected_pack['num_cycles'] = $pack['num_cycles'];
			}		
			//issue#: 658
			if(isset($pack['role'])) {
				$selected_pack['role'] = $pack['role'];
			}		
			//applicable modules:
			$selected_pack['modules'] = $pack['modules']; 
			$selected_pack['product'] = $pack['product']; 
			// trial
			if($pack['trial_on']) {
				$selected_pack['trial_on'] 			  = $pack['trial_on']; 
				$selected_pack['trial_duration'] 	  = $pack['trial_duration']; 
				$selected_pack['trial_duration_type'] = $pack['trial_duration_type']; 
				$selected_pack['trial_cost'] 		  = $pack['trial_cost']; 
				$selected_pack['trial_num_cycles'] 	  = $pack['trial_num_cycles']; 
			}
			// save member data including coupon etc, MUST save after all validation passed, we dont want any 
			// unwanted value in member object unless its a valid upgrade			
			// save
			if ($multiple_upgrade) {
				$member = mgm_save_partial_fields(array('on_upgrade'=>true),'mgm_upgrade_field', $selected_pack['cost'], true, strip_tags($_GET['action']), $member);
			}else {
				$member = mgm_save_partial_fields(array('on_upgrade'=>true),'mgm_upgrade_field', $selected_pack['cost'], true, strip_tags($_GET['action']));
			}
			//save custom fields issue #1285
			if(isset($_POST['mgm_upgrade_field']) && !empty($_POST['mgm_upgrade_field'])) {					
				//upgrade custom fileds
				$cfu_fileds = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_upgrade'=>true)));			
				//loop fields
				foreach($cfu_fileds as $cf_filed){
					//skip coupon and autoresponder
					if(in_array($cf_filed['name'], array('coupon','autoresponder')))
						continue;
					//check	
					if(isset($_POST['mgm_upgrade_field'][$cf_filed['name']])){
						// check upgrae and required		
						if((bool)$cf_filed['attributes']['required'] === true){								
							if(empty($_POST['mgm_upgrade_field'][$cf_filed['name']])){
								//redirect back to the form							
								$q_arg = array('error_field' => $cf_filed['label'], 'error_type' => 'empty','error_field_value'=>$_POST['mgm_upgrade_field'][$cf_filed['name']]);
								$redirect = add_query_arg($q_arg, $_POST['form_action']);														
								mgm_redirect($redirect);
								exit;									
							}							
						}
						//appending custom fields
						if(isset($member->custom_fields->$cf_filed['name'])){
							$member->custom_fields->$cf_filed['name'] = $_POST['mgm_upgrade_field'][$cf_filed['name']];
						}else {
							$member->custom_fields->$cf_filed['name'] = $_POST['mgm_upgrade_field'][$cf_filed['name']];
						}											
 					}
				}
				$member->save();					
			}
			
			//issue #860
			if (isset($_POST['mgm_upgrade_field']['autoresponder']) && bool_from_yn($_POST['mgm_upgrade_field']['autoresponder']) ) {
				$member->subscribed    = 'Y';
				$member->autoresponder = $system_obj->active_modules['autoresponder'];
				//issue #1511
				if ($multiple_upgrade){
					mgm_save_another_membership_fields($member, $user->ID);
				}else {
					$member->save();
				}			
			//issue #1276
			}else {
				$member->subscribed    = '';
				$member->autoresponder = '';
				//issue #1511
				if ($multiple_upgrade){
					mgm_save_another_membership_fields($member, $user->ID);
				}else {
					$member->save();
				}			
			}
			//issue #1236
			if(isset($_POST['mgm_upgrade_field']['coupon']) && !empty($_POST['mgm_upgrade_field']['coupon'])) {
				//issue #1250 - Coupon validation 
				if(!empty($_POST['form_action'])) {				
					// check if its a valid coupon
					if(!$coupon = mgm_get_coupon_data($_POST['mgm_upgrade_field']['coupon'])){				
						//redirect back to the form							
						$q_arg = array('error_field' => 'Coupon', 'error_type' => 'invalid','error_field_value'=>$_POST['mgm_upgrade_field']['coupon']);
						$redirect = add_query_arg($q_arg, $_POST['form_action']);														
						mgm_redirect($redirect);
						exit;
					}else{
						// get subs 			
						if( $subs_pack = mgm_decode_package(mgm_post_var('subs_opt')) ){	
							// values
							$coupon_values = mgm_get_coupon_values(NULL, $coupon['value'], true);
							// check
							if(isset($coupon_values['new_membership_type']) && $coupon_values['new_membership_type'] != $subs_pack['membership_type']){
								$new_membership_type = mgm_get_membership_type_name($coupon_values['new_membership_type']);							
								$q_arg = array(	'error_field' => 'Coupon', 
											   	'error_type' => 'invalid',
											   	'membership_type' => $coupon_values['new_membership_type'],
											   	'error_field_value'=>$_POST['mgm_upgrade_field']['coupon']);
								$redirect = add_query_arg($q_arg, $_POST['form_action']);														
								mgm_redirect($redirect);
								exit;							
							}
						}	
					}
				}			
			}
			// payment_gateways if set: Eg: $_POST['mgm_payment_gateways'] = mgm_paypal
			$cf_payment_gateways = (isset($_POST['mgm_payment_gateways']) && !empty($_POST['mgm_payment_gateways'])) ? $_POST['mgm_payment_gateways'] : null;				
			// bypass step2 if payment gateway is submitted: issue #: 469
			// removed complete_payment checking here in order to enable coupon for complete_payment. issue#: 802			
			if(!is_null($cf_payment_gateways)) {				
				// get pack				
				mgm_get_upgrade_coupon_pack($member, $selected_pack, strip_tags($_GET['action']));				
				// cost
				if ((float)$selected_pack['cost'] > 0) {
					//get an object of the payment gateway:
					$mod_obj = mgm_get_module($cf_payment_gateways,'payment');
					// tran options
					$tran_options = array('user_id' => $user->ID);
					// is register & purchase
					if(isset($_POST['post_id'])){
						$tran_options['post_id'] = (int)$_POST['post_id'];
					}
					// if multiple membership
					if ($multiple_upgrade) {
						$tran_options['is_another_membership_purchase'] = true; 
						// This is to replace current mgm_member object with new mgm_member object of the upgrade pack
						$tran_options['multiple_upgrade_prev_packid'] = mgm_get_var('prev_pack_id', '', true); 
					}
					// upgrade flag
					if($action == 'upgrade'){
						$tran_options['subscription_option'] = 'upgrade';
					}
					// create transaction				
					// $tran_id = $mod_obj->_create_transaction($selected_pack, $tran_options);
					$tran_id = mgm_add_transaction($selected_pack, $tran_options);
					
					//bypass directly to process return if manual payment:				
					if($cf_payment_gateways == 'mgm_manualpay') {
						// set 
						$_POST['custom'] = $tran_id;
						// direct call to module return function:
						$mod_obj->process_return();				
						// exit	
						exit;
					}
					// set redirect
					$redirect = add_query_arg(array( 'tran_id' => mgm_encode_id($tran_id) ), $mod_obj->_get_endpoint('html_redirect', true)); 	
					// redirect	
					mgm_redirect($redirect);// this goes to subscribe, mgm_functions.php/mgm_get_subscription_buttons
					// exit						
					exit;						
				}
			}// end gateway
			// get coupon pack
			mgm_get_upgrade_coupon_pack($member, $selected_pack, strip_tags($_GET['action']));			
			// start html
			$html = '<div>';
			// free package
			if (($selected_pack['cost'] == 0 || $selected_pack['membership_type'] == 'free') && in_array('mgm_free', $a_payment_modules) && mgm_get_module('mgm_free')->enabled=='Y') {	
				// html		
				$html .= sprintf('<div>%s - %s</div>', __('Create a free account ','mgm'), ucwords($selected_pack['membership_type']));			
				// module
				$module = 'mgm_free';
				// payments url
				$payments_url = mgm_get_custom_url('transactions');			
				// if tril module selected and cost is 0 and free moduleis not active
				if($selected_pack['membership_type'] == 'trial'){
					// check
					if(in_array('mgm_trial', $a_payment_modules)){
						// module
						$module = 'mgm_trial';
					}
				}
				// query_args -issue #1005
				$query_args = array('method' => 'payment_return', 'module'=>$module, 
									'custom' => implode('_', array($user->ID, $selected_pack['duration'], $selected_pack['duration_type'], $selected_pack['pack_id'],'N',$selected_pack['membership_type'])));
				// redirector
				if(isset($_REQUEST['redirector'])){
					// set
					$query_args['redirector'] = $_REQUEST['redirector'];
				}
				// redirect to module to mark the payment as complete
				$redirect = add_query_arg($query_args, $payments_url);			
				// redirect
				if (!headers_sent()) {							
					@header('location: ' . $redirect);
				}else{
				// js redirect
					$html .= sprintf('<script type="text/javascript">window.location = "%s";</script><div>%s</div>', $redirect, $packs_obj->get_pack_desc($pack));
				}			
			} else {		
			// paid package, generate buy buttons
				// set html	
				$html .= sprintf('<div class="mgm_get_subs_btn">%s</div>', $packs_obj->get_pack_desc($selected_pack));
				// coupon			
				if(isset($member->upgrade) && is_array($member->upgrade) && isset($member->upgrade['coupon']['id'])){	
					// set html 
					$html .= sprintf('<div class="mgm_get_subs_btn">%s</div>', sprintf(__('Using Coupon "%s" - %s','mgm'), $member->upgrade['coupon']['name'], $member->upgrade['coupon']['description']));
				}
				// set html
				$html .= sprintf('<div class="mgm_get_subs_btn">%s</div>', __('Please Select from Available Payment Gateways','mgm'));
			}			
			// init 
			$payment_modules = array();			
			// active
			if(count($a_payment_modules)>0){
				// loop
				foreach($a_payment_modules as $payment_module){
					// not trial
					if(in_array($payment_module, array('mgm_free','mgm_trial'))) continue;	
					// consider only the modules assigned to pack
					if(isset($selected_pack['modules']) && !in_array($payment_module, (array)$selected_pack['modules'])) continue;			
					// store
					$payment_modules[] = $payment_module;					
				}
			}
			
			// loop payment module if not free		
			if (count($payment_modules) && $selected_pack['cost']) {
				// transaction
				$tran_id = false;
				$tran_options = array('user_id' => $user->ID);	
				// if multiple membership					
				if ($multiple_upgrade) {
					// another
					$tran_options['is_another_membership_purchase'] = true; 
					// This is to replace current mgm_member object with new mgm_member object of the upgrade pack
					$tran_options['multiple_upgrade_prev_packid'] = mgm_get_var('prev_pack_id', '', true); 
				}	
				// upgrade
				if($action == 'upgrade'){
					$tran_options['subscription_option'] = 'upgrade';
				}
				// loop
				foreach($payment_modules as $module) {
					// module
					$mod_obj = mgm_get_module($module,'payment');	
					// create transaction
					// if(!$tran_id) $tran_id = $mod_obj->_create_transaction($selected_pack, $extra_options);
					if(!$tran_id) $tran_id = mgm_add_transaction($selected_pack, $tran_options);
					// set html				
					$html .= sprintf('<div>%s</div>', $mod_obj->get_button_subscribe(array('pack'=>$selected_pack,'tran_id'=>$tran_id)));
				}
				// mgm_pr($_REQUEST);
				// profile edit #698
				if($_GET['action'] == 'complete_payment'){
					// update $form_action for user data edit
					if(isset($_COOKIE['wp_tempuser_login']) && $_COOKIE['wp_tempuser_login'] == $user->ID && !isset($_GET['edit_userinfo'])){
						// form action
						$form_action = add_query_arg(array('edit_userinfo'=>1), $form_action);
						// action
						$html .= sprintf('<form action="%s" method="post" class="mgm_form">', $form_action);						
						$html .= '<input type="hidden" name="ref" value="'. md5($member->amount .'_'. $member->duration .'_'. $member->duration_type .'_'. $member->membership_type) .'" />';					
						$html .= '<input type="hidden" name="form_action" value="'. $form_action .'" />';	
						$html .= '<input type="hidden" name="subs_opt" value="'. $_POST['subs_opt'] .'" rel="mgm_subscription_options"/>';	
						// set
						$html .= sprintf('<p><input type="button" name="back" onclick="window.location=\'%s\'" value="%s" class="button-primary" />	
											 <input type="button" name="cancel" onclick="window.location=\'%s\'" value="%s" class="button-primary" />&nbsp;					
										  </p>', $form_action, __('Edit Personal Information','mgm'), $cancel_url, __('Cancel','mgm'));
						// html
						$html .= '</form>';
					}					
				}
			} else {
			// no module error
				if($selected_pack['cost']){		
					// set html	
					$html .= sprintf('<div>%s</div>', __('Error, no payment gateways active on upgrade page, notify administrator.','mgm'));
				}
			}
			// html
			$html .= '</div>';
		}// end final step post 
	}else{
	// generate upgrade/complete payment form ----------------------------------------------------------	
		// selected subscription, from args (shortcode) or get url	
		$selected_pack = mgm_get_selected_subscription($args);		
		$css_group = mgm_get_css_group();				
		// upgrade_packages
		$upgrade_packages = '';		
		// pack count
		$pack_count = 0;
		// pack to modules
		$pack_modules = array();	
		//mgm_pr($active_packs);
		//issue #1553		
		if(!empty($active_packs)) {
			// loop	packs	
			foreach($active_packs as $pack) {	
				// mgm_pr($pack);			
				// default			
				$checked = '';
				// for complete payment only show purchased pack
				if($action == 'complete_payment'){
					// pack selected
					if(isset($pack_id)){
						// leave other pack, if not show other packs
						if($pack['id'] != $pack_id && !isset($_GET['show_other_packs'])) continue;									
	
						// select 
						if($pack['id'] == $pack_id) $checked='checked="checked"';
					}
				}else{//  'upgrade':
				// upgrade
					// echo '<br>pack#' . $pack['id'] . ' step1';
					// leave current pack, it will goto extend
					if(isset($pack_id)){						
						if(!$show_current_pack && $pack['id'] == $pack_id) continue;	
					}
					
					// echo '<br>pack#' . $pack['id'] . ' step2';
					// skip trial or free packs
					if(in_array($pack['membership_type'], array('trial','free'))) continue;
					
					// echo '<br>pack#' . $pack['id'] . ' step3';
					// skip if not allowed
					if(!mgm_pack_upgrade_allowed($pack)) continue;		
					
					// echo '<br>pack#' . $pack['id'] . ' step4';
					
					// selected pack
					if($selected_pack !== false){
						// checked
						$checked = mgm_select_subscription($pack, $selected_pack);																				
						// skip other when a package sent as selected
						if( empty($checked) ) {
							continue; 					
						}	
					}
					
					// echo '<br>pack#' . $pack['id'] . ' step5';				
				}				
				
				// checked
				if(!$checked) $checked = ((int)$pack['default'] == 1 ? ' checked="checked"': ''); 
				
				// duration                      
				if ($pack['duration'] == 1) {
					$dur_str = rtrim($duration_str[$pack['duration_type']], 's');
				} else {
					$dur_str = $duration_str[$pack['duration_type']];
				}
				
				// encode pack
				$subs_opt_enc = mgm_encode_package($pack);			
				
				// set 
				$pack_modules[$subs_opt_enc] = $pack['modules'];

				// free
				if (($pack['cost'] == 0 || strtolower($pack['membership_type']) == 'free') && in_array('mgm_free', $a_payment_modules) && mgm_get_module('mgm_free')->is_enabled()) {
					// input
					$input = sprintf('<input type="radio" %s class="checkbox" name="subs_opt" value="%s" rel="mgm_subscription_options"/>', $checked, $subs_opt_enc);
					// html				
					$upgrade_packages .= '  
						<div class="mgm_subs_wrapper '.$pack['membership_type'].'">
							<div class="mgm_subs_option '.$pack['membership_type'].'">
								' . $input . '
							</div>
							<div class="mgm_subs_pack_desc '.$pack['membership_type'].'">							
								' . $packs_obj->get_pack_desc($pack) . '
							</div>
							<div class="clearfix"></div>
							<div class="mgm_subs_desc '.$pack['membership_type'].'">
								' . mgm_stripslashes_deep($pack['description']) . '
							</div>
						</div>';
				} else {
					// input
					$input = sprintf('<input type="radio" %s class="checkbox" name="subs_opt" value="%s" rel="mgm_subscription_options"/>', $checked, $subs_opt_enc);
					// html
					$upgrade_packages .= '  
						<div class="mgm_subs_wrapper '.$pack['membership_type'].'">
							<div class="mgm_subs_option '.$pack['membership_type'].'">
								' . $input . '
							</div>
							<div class="mgm_subs_pack_desc '.$pack['membership_type'].'">
								' . $packs_obj->get_pack_desc($pack) . '
							</div>
							<div class="clearfix"></div>
							<div class="mgm_subs_desc '.$pack['membership_type'].'">
								' . mgm_stripslashes_deep($pack['description']) . '
							</div>
						</div>';				
				}	
				// count
				$pack_count++;		
			}
		}
		// start
		$html = '';
		
		// html
		if($pack_count > 1){
			$html .= sprintf('<p class="message register">%s</p>', __('Please Select from Available Membership Packages','mgm'));	
		}	

		// add pack_modules as json data, may consider jquery data later
		if( ! empty( $pack_modules ) ){
			$html .= sprintf('<script language="javascript">var mgm_pack_modules = %s</script>', json_encode($pack_modules));
		}

		//issue #867
		if($css_group != 'none') {		
			// set css
			$html .= sprintf('<link rel="stylesheet" href="%s/css/%s/mgm.form.fields.css" type="text/css" media="all" />', untrailingslashit(MGM_ASSETS_URL), $css_group);
		}
		
		// show error when no upgrde
		if( ! $upgrade_packages ){
			// html
			$html .= '<div class="mgm_subs_wrapper">
						<div  class="mgm_subs_pack_desc">
							' . __('Sorry, no upgrades available.','mgm') . '
						</div>
					  </div>
					  <p>						
					  	  <input type="button" name="cancel" onclick="window.location=\''.$cancel_url.'\'" value="'.__('Cancel','mgm').'" class="button-primary" />&nbsp;					
					  </p>';
		}else{									
			// edit/other pack link
			$edit_button  = $other_packs_button = '';
			// issue #: 675, issue #1279
			if($action == 'complete_payment' || (isset($_REQUEST['action']) && $_REQUEST['action'] =='complete_payment')){
				// issue#: 416
				// mgm_pr($_GET);
				if(isset($_GET['show_other_packs'])){
					// other packs url - issue #1279, #1215 update, other packs url missed username
					$other_packs_url   = add_query_arg(array('action' => 'complete_payment','username' => $username), mgm_get_custom_url('transactions'));	// mgm_get_current_url()
					//$other_packs_url   = str_replace('&show_other_packs=1', '', $other_packs_url);
					$other_packs_label = __('Show subscribed package','mgm') . '';
				}else{
					// other packs url - issue #1279, #1215 update, other packs url missed username
					$other_packs_url   = add_query_arg(array('action' => 'complete_payment','show_other_packs'=>1,'username' => $username),  mgm_get_custom_url('transactions'));// mgm_get_current_url()	
					$other_packs_label = __('Show other packages','mgm');					
				}		
				// issue#: 710
				if(count($active_packs) > 1){
					// button			
					$other_packs_button = sprintf('<input type="button" value="%s" class="button-primary" onclick="window.location=\'%s\'">', $other_packs_label, $other_packs_url);
				}
				
				// update $form_action for user data edit
				if(isset($_COOKIE['wp_tempuser_login']) && $_COOKIE['wp_tempuser_login'] == $user->ID && !isset($_GET['edit_userinfo'])){
					$form_action = add_query_arg(array('edit_userinfo'=>1), $form_action);
				}else {
					//issue #1279
					$form_action = add_query_arg(array('action' => 'complete_payment','username' => $username,'edit_userinfo'=>1), mgm_get_current_url());			
				}
			}			
			
			// echo $form_action;
			
			// check errors if any:
			$html .= mgm_subscription_purchase_errors();			
			
			// form
			$html .= sprintf('<form action="%s" method="post" class="mgm_form">', $form_action);
			$html .= sprintf('<div class="mgm_get_pack_form_container">%s', $upgrade_packages);
			//issue #1285
			$html .= mgm_get_custom_fields($user->ID,array('on_upgrade'=>true),'mgm_upgrade_field');
			$html .= '<input type="hidden" name="ref" value="'. md5($member->amount .'_'. $member->duration .'_'. $member->duration_type .'_'. $member->membership_type) .'" />';					
			$html .= '<input type="hidden" name="form_action" value="'. $form_action .'" />';	
			// set
			$html .= sprintf('<p>%s						
							 	 <input class="button button-primary" type="submit" name="submit" value="%s" />&nbsp;&nbsp;
						      	 <input class="button button-primary" type="button" name="cancel" onclick="window.location=\'%s\'" value="%s" />&nbsp;					
					          </p>', $other_packs_button, __('Next','mgm'), $cancel_url, __('Cancel','mgm'));
			// html
			$html .= '</div></form>';
		}
		// end generate form 		
	}// end	
    
	// return    	
	return $html;	
}

/**
 * Magic Members get extend buttons/packs
 * display the current pack for extend if the pack is set as renewable/ active on extend page
 * show all packs active on extend page otherwise
 *
 * @package MagicMembers
 * @since 2.0
 * @param none
 * @return formatted html
 */ 
function mgm_get_extend_button() {
	global $wpdb;    
	// current user
	$user = wp_get_current_user();
	// validate user
	if( !$user->ID ) {	
		// query string
		$user = mgm_get_user_from_querystring();
	}
	// validate
	if( !$user->ID ) {	
		return __('No such user', 'mgm');
	}
	// set	
	$username = $user->user_login;	
	// get member
	$member = mgm_get_member($user->ID);
	//this is a fix for issue#: 589, see the notes for details:
	//This is to read saved coupons as array in order to fix the fatal error on some servers.	
	//This will change the object on each users profile view.
	//Also this will avoid using patch for batch update,	
	$arr_coupon = array('upgrade', 'extend');
	$oldcoupon_found = 0;
	foreach ($arr_coupon as $cpn_type) {
		if(isset($member->{$cpn_type}['coupon']) && is_object($member->{$cpn_type}['coupon'])) {
			$member->{$cpn_type}['coupon'] = (array) $member->{$cpn_type}['coupon'];
			$oldcoupon_found++ ;
		}
	}
	if($oldcoupon_found) {		
		$member->save();
	}
	// other objects
	$system_obj = mgm_get_class('system');	
	$packs_obj  = mgm_get_class('subscription_packs');		
	// selected pack
	$pack_id = (int)strip_tags($_GET['pack_id']);
	// action 
	$action = (isset( $_GET['action'])) ? strip_tags($_GET['action']) : 'extend'; // extend
	// query_arg
	$form_action = mgm_get_custom_url('transactions', false, array('action'=>$action, 'pack_id'=>$pack_id, 'username'=> $username));
	// if extending multiple purchase subscriptions
	if (isset($_GET['multiple_purchase']))
		$form_action = add_query_arg(array('multiple_purchase' => strip_tags($_GET['multiple_purchase'])), $form_action); 
	
	//$cancel_url = mgm_get_custom_url('membership_details');
	//issue 1009
	if(isset($system_obj->setting['membership_details_url'])){
		$membership_details_url = $system_obj->setting['membership_details_url'];
	}else {
		$membership_details_url = get_admin_url().'profile.php?page=mgm/profile';;
	}

	// cancel 
	$cancel_url = $membership_details_url;
	
	// active modules
	$a_payment_modules = $system_obj->get_active_modules('payment');	
	// active packs
	$active_packs = array();
	// init html
	$html = $error = '';
	// pack id passed in get, coming from profile membership details
	if($pack_id = (int)strip_tags($_GET['pack_id'])){
		// get selected pack
		$selected_pack = $packs_obj->get_pack($pack_id); 
		// validate
		if($selected_pack !== false){
			// check if extend allowed on the current pack of subscriber/user
			if(!mgm_pack_extend_allowed($selected_pack)){
				// error
				$error = __('Renewal of the current subscription is not allowed.','mgm'); 				
				// get packs
				$active_packs = $packs_obj->get_packs('extend');
				// check
				if(count($active_packs) > 0){
					// error
					$error = ''; // reset error
					// html head
					$html  = sprintf('<p class="message register">%s</p>', __('Choose a subscription package','mgm') );						 
				}				
			}else{
				// html
				$html  = sprintf('<p class="message register">%s</p>', __('Extend current subscription', 'mgm') );	
				// active packs
				$active_packs[] = $selected_pack;
			}			
		}
	}	
	
	// post form---------------------------------------------------------------------
	if($_POST && isset($_POST['submit']) && isset($_POST['subs_opt']) ){
		//mgm_pr($_POST);die;
	// process post	
		// get pack data			
		$selected_pack = mgm_decode_package($_POST['subs_opt']);
		// check selected pack		     
		$valid = false;
		// loop packs
		foreach ($active_packs as $pack) {
			// check
			//check pack id as well: issue#: 580
			if ($pack['cost'] == $selected_pack['cost'] 
			    && $pack['duration'] == $selected_pack['duration'] 
				&& $pack['duration_type'] == $selected_pack['duration_type'] 
				&& $pack['membership_type'] == $selected_pack['membership_type']
				&& $pack['id'] == $selected_pack['pack_id']  
				) {
				// valid
				$valid = true; break;
			}
		}
		// error
		if ( ! $valid ) { wp_die(__('Invalid data. Cheatin!','mgm'));}	
		
		//update description if not set
		if( ! isset($selected_pack['description'])) {
			$selected_pack['description'] = $pack['description'];
		}
						
		//update num_cycles if not set
		if(!isset($selected_pack['num_cycles'])) {
			//Note the above break in for loop:
			$selected_pack['num_cycles'] = $pack['num_cycles'];
		}
		//issue#: 658
		if(isset($pack['role'])) {
			$selected_pack['role'] = $pack['role'];
		}
		//applicable modules
		$selected_pack['modules'] = $pack['modules']; 
		$selected_pack['product'] = $pack['product']; 
						
		if($pack['trial_on']) {
    		$selected_pack['trial_on'] 			= $pack['trial_on']; 
    		$selected_pack['trial_duration'] 	= $pack['trial_duration']; 
    		$selected_pack['trial_duration_type']= $pack['trial_duration_type']; 
    		$selected_pack['trial_cost'] 		= $pack['trial_cost']; 
    		$selected_pack['trial_num_cycles'] 	= $pack['trial_num_cycles']; 
    	}
		// save member data including coupon etc, MUST save after all validation passed, we dont want any 
		// unwanted value in member object unless its a valid upgrdae
		$member = mgm_save_partial_fields(array('on_extend'=>true),'mgm_extend_field', $selected_pack['cost'], true, 'extend');	
		
		// is using a coupon ? reset prices	- issue #1226
		if(isset($_POST['mgm_extend_field']) && !empty($_POST['mgm_extend_field']['coupon']))	{			
			//issue #1250 - Coupon validation 
			if(!empty($_POST['form_action'])) {				
				// check if its a valid coupon
				if(!$coupon = mgm_get_coupon_data($_POST['mgm_extend_field']['coupon'])){				
					//redirect back to the form							
					$q_arg = array('error_field' => 'Coupon', 'error_type' => 'invalid','error_field_value'=>$_POST['mgm_extend_field']['coupon']);
					$redirect = add_query_arg($q_arg, $_POST['form_action']);														
					mgm_redirect($redirect);
					exit;
				}else{
					// get subs 			
					if( $subs_pack = mgm_decode_package(mgm_post_var('subs_opt')) ){	
						// values
						$coupon_values = mgm_get_coupon_values(NULL, $coupon['value'], true);
						// check
						if(isset($coupon_values['new_membership_type']) && $coupon_values['new_membership_type'] != $subs_pack['membership_type']){
							$new_membership_type = mgm_get_membership_type_name($coupon_values['new_membership_type']);							
							$q_arg = array(	'error_field' => 'Coupon', 
										   	'error_type' => 'invalid',
										   	'membership_type' => $coupon_values['new_membership_type'],
										   	'error_field_value'=>$_POST['mgm_extend_field']['coupon']);
							$redirect = add_query_arg($q_arg, $_POST['form_action']);														
							mgm_redirect($redirect);
							exit;							
						}
					}	
				}
			}			

			if($coupon) {
				mgm_get_extend_coupon_pack($member, $selected_pack);
			}
		}		
		// Eg: $_POST['mgm_payment_gateways'] = mgm_paypal
		$cf_payment_gateways = (isset($_POST['mgm_payment_gateways']) && !empty($_POST['mgm_payment_gateways'])) ? $_POST['mgm_payment_gateways'] : null;				
		// bypass step2 if payment gateway is submitted: issue #: 469
		if(!is_null($cf_payment_gateways)) {							
			// cost
			if ((float)$selected_pack['cost'] > 0) {
				//get an object of the payment gateway:
				$mod_obj = mgm_get_module($cf_payment_gateways,'payment');
				// tran options
				$tran_options = array('user_id' => $user->ID, 'subscription_option' => 'extend');
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
				// $tran_id = $mod_obj->_create_transaction($selected_pack, $tran_options);
				$tran_id = mgm_add_transaction($selected_pack, $tran_options);
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
				$redirect = $mod_obj->_get_endpoint('html_redirect', true);	
				$redirect = add_query_arg(array( 'tran_id' => $tran_id ), $redirect);
				// redirect	
				mgm_redirect($redirect);// this goes to subscribe, mgm_functions.php/mgm_get_subscription_buttons
				// exit						
				exit;						
			}
		}				
		
		$html = '<div>';
	
		// free package
		if (($selected_pack['cost'] == 0 || $selected_pack['membership_type'] == 'free') && in_array('mgm_free', $a_payment_modules) && mgm_get_module('mgm_free')->enabled=='Y') {	
			// html		
			$html .= sprintf('<div>%s - %s</div>', __('Create a free account ','mgm'), ucwords($selected_pack['membership_type']));
					
			// module
			$module = 'mgm_free';
			// payments url
			$payments_url = mgm_get_custom_url('transactions');			
			// if tril module selected and cost is 0 and free moduleis not active
			if($selected_pack['membership_type'] == 'trial'){
				// check
				if(in_array('mgm_trial', $a_payment_modules)){
					// module
					$module = 'mgm_trial';
				}
			}
			
			$arr_custom = array($user->ID, $selected_pack['duration'], $selected_pack['duration_type'], $pack_id);
			// if multiple purchase
			if (isset($_GET['multiple_purchase']) && $_GET['multiple_purchase'] == 'Y') {
				$arr_custom['is_another_membership_purchase'] = 'Y'; 
			}
			// query_args
			$query_args = array('method' => 'payment_return', 'module'=>$module, 
			                    'custom' => implode('_', $arr_custom ));
			// redirector
			if(isset($_REQUEST['redirector'])){
				// set
				$query_args['redirector'] = $_REQUEST['redirector'];
			}
			// redirect to module to mark the payment as complete
			$redirect = add_query_arg($query_args, $payments_url);			
			// redirect
			if (!headers_sent()) {							
				@header('location: ' . $redirect);
			}else{
			// js redirect
				$html .= sprintf('<script type="text/javascript">window.location = "%s";</script><div>%s</div>', $redirect, $packs_obj->get_pack_desc($pack) );
			}			
		} else {		
		// paid package, generate payment buttons
			// set html	
			$html .= sprintf('<div class="mgm_get_subs_btn">%s</div>', $packs_obj->get_pack_desc($selected_pack) );			
			// coupon - issue #1226		
			if(isset($member->extend['coupon']['id']) && isset($_POST['mgm_extend_field']) && !empty($_POST['mgm_extend_field']['coupon']) && mgm_get_coupon_data($_POST['mgm_extend_field']['coupon'])){	
				// set html 
				$html .= sprintf('<div class="mgm_get_subs_btn">%s</div>', sprintf(__('Using Coupon "%s" - %s','mgm'),$member->extend['coupon']['name'], $member->extend['coupon']['description']));
			}
			// set html
			$html .= sprintf('<div class="mgm_get_subs_btn" >%s</div>', __('Please Select from Available Payment Gateways','mgm') );
		}
		
		
		// init 
		$payment_modules = array();			
		// active
		if(count($a_payment_modules)>0){
			// loop
			foreach($a_payment_modules as $payment_module){
				// not trial
				if(in_array($payment_module, array('mgm_free','mgm_trial'))) continue;	
				// consider only the modules assigned to pack
				if(isset($selected_pack['modules']) && !in_array($payment_module, (array)$selected_pack['modules'])) continue;			
				// store
				$payment_modules[] = $payment_module;					
			}
		}
		// loop payment module if not free		
		if (count($payment_modules) && $selected_pack['cost']) {
			// transaction
			$tran_id = false;
			// loop
			foreach($payment_modules as $module) {
				// module
				$mod_obj = mgm_get_module($module,'payment');	
				// create transaction
				if(!$tran_id) { 	
					// args				
					$args = array('user_id' => $user->ID, 'subscription_option' => 'extend');
					// if extending multiple purchase subscription
					if (isset($_GET['multiple_purchase']) && $_GET['multiple_purchase'] == 'Y') {
						$args['is_another_membership_purchase'] = true; 
					}
					// tran id
					// $tran_id = $mod_obj->_create_transaction($selected_pack, $args);
					$tran_id = mgm_add_transaction($selected_pack, $args);
				}
				// set html				
				$html .= sprintf('<div class="mgm_get_subs_btn" >%s</div>', $mod_obj->get_button_subscribe(array('pack'=>$selected_pack,'tran_id'=>$tran_id)));				
			}
		} else {
		// no module error
			if($selected_pack['cost']){		
				// set html	
				$html .= sprintf('<div class="mgm_get_subs_btn" >%s</div>', __('Error, no payment gateways active on extend page, notify administrator.','mgm'));
			}
		}
		// html
		$html .= '</div>';
	// end post form	
	}else{
	// generate form ----------------------------------------------------------------
		// check error
		if($error){
			// html
			$html .= $error;
		}else{
			// generate 				
			// extend packages
			$extend_packages = '';		
			// pack to modules
			$pack_modules = array();			
			// loop		
			foreach ($active_packs as $pack) {				
				// default			
				$checked = '';	
				
				// checked			
				if($pack['id'] == $pack_id) {
					$checked = ' checked="checked"'; 
				}elseif((int)$pack['default'] == 1) {
					$checked = ' checked="checked"'; 
				}						
				
				// duration                      
				if ($pack['duration'] == 1) {
					$dur_str = rtrim($duration_str[$pack['duration_type']], 's');
				} else {
					$dur_str = $duration_str[$pack['duration_type']];
				}
				
				// encode pack
				$subs_opt_enc = mgm_encode_package($pack);
				
				// set 
				$pack_modules[$subs_opt_enc] = $pack['modules'];

				// css
				$css_group = mgm_get_css_group();	
				
				//issue #867
				if($css_group !='none') {
					//expand this if needed
					$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';				
					$css_file = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.form.fields.css';
					$extend_packages .= sprintf($css_link_format, $css_file);
				}

				// free
				if (($pack['cost'] == 0 || strtolower($pack['membership_type']) == 'free') && in_array('mgm_free', $a_payment_modules) && mgm_get_module('mgm_free')->enabled=='Y') {
					// input
					$input = sprintf('<input type="radio" %s class="mgm_subs_radio" name="subs_opt" id="subs_opt_%d" value="%s" rel="mgm_subscription_options"/>', $checked, $pack['id'], $subs_opt_enc);
					// html
					$extend_packages .= '  
						<div class="mgm_subs_wrapper '.$pack['membership_type'].'">
							<div class="mgm_subs_option '.$pack['membership_type'].'">
								' . $input . '
							</div>
							<div class="mgm_subs_pack_desc '.$pack['membership_type'].'">
								' . $packs_obj->get_pack_desc($pack) . '
							</div>
							<div class="clearfix"></div>
							<div class="mgm_subs_desc '.$pack['membership_type'].'">
								' . mgm_stripslashes_deep($pack['description']) . '
							</div>
						</div>';
				} else {
					// input
					$input = sprintf('<input type="radio" %s class="mgm_subs_radio" name="subs_opt" id="subs_opt_%d" value="%s" rel="mgm_subscription_options"/>', $checked, $pack['id'], $subs_opt_enc);
					// html
					$extend_packages .= '  
						<div class="mgm_subs_wrapper '.$pack['membership_type'].'">
							<div class="mgm_subs_option '.$pack['membership_type'].'">
								' . $input . '
							</div>
							<div class="mgm_subs_pack_desc '.$pack['membership_type'].'">
								' . $packs_obj->get_pack_desc($pack) . '
							</div>
							<div class="clearfix"></div>
							<div class="mgm_subs_desc '.$pack['membership_type'].'">
								' . mgm_stripslashes_deep($pack['description']) . '
							</div>
						</div>';
				}
			}
			
			// show error
			if(!$extend_packages){
				// html
				$html .= '<div class="mgm_subs_wrapper">
							<div class="mgm_subs_pack_desc">
								' . __('Sorry, no extend available.','mgm') . '
							</div>
						</div>';
				$html .= '<p>						
							<input type="button" name="cancel" onclick="window.location=\''.$cancel_url.'\'" value="'.__('Cancel','mgm').'" class="button-primary" />&nbsp;					
						  </p>';
			}else{						
				// check errors if any:
				$html .= mgm_subscription_purchase_errors();			
				
				// form
				$html .= '<form action="'.$form_action .'" method="post" class="mgm_form"><div class="mgm_get_pack_form_container">';
				$html .= $extend_packages;
				// get coupon field
				$html .= mgm_get_partial_fields(array('on_extend'=>true),'mgm_extend_field');
				// html
				// $html .= '<input type="hidden" name="ref" value="'. md5($member->amount .'_'. $member->duration .'_'. $member->duration_type .'_'. $member->membership_type) .'" />';					
				$html .= '<input type="hidden" name="form_action" value="'. $form_action .'" />';					
						
				// set
				$html .= '<p>							
							<input class="button button-primary" type="submit" name="submit" value="'.__('Next','mgm').'" />&nbsp;&nbsp;
							<input class="button button-primary" type="button" name="cancel" onclick="window.location=\''.$cancel_url.'\'" value="'.__('Cancel','mgm').'" />&nbsp;					
						  </p>';
				// html
				$html .= '</div></form>';
			}	

			// add pack_modules as json data, may consider jquery data later
			if( ! empty( $pack_modules ) ){
				$html .= sprintf('<script language="javascript">var mgm_pack_modules = %s</script>', json_encode($pack_modules));
			}
			// end generate
		}
	}	
	
	// return
	return $html;
}	

/**
 * create purchase another button
 *
 * @param array userdata
 * @retun string html output
 */
function mgm_get_purchase_another_subscription_button($args = array()) {
	global $wpdb;	
	
	//ceck settings
	$settings = mgm_get_class('system')->get_setting();	
	// check
	if( !isset($settings['enable_multiple_level_purchase']) || (isset($settings['enable_multiple_level_purchase']) && !bool_from_yn($settings['enable_multiple_level_purchase']))) {
		return;
	}
	// current user
	$user = wp_get_current_user();
	// validate
	if( !$user->ID ) {		
		// query string
		$user = mgm_get_user_from_querystring();			
	}
			
	// validate
	if( !$user->ID ) {	
		return __('No such user', 'mgm');
	}	
	
	// userdata
	$username        = $user->user_login;
	$mgm_home        = get_option('siteurl');
	$member          = mgm_get_member($user->ID);
	$system_obj      = mgm_get_class('system');
	$membership_type = mgm_get_user_membership_type($user->ID, 'code');
	$packs_obj       = mgm_get_class('subscription_packs');
	$packs           = $packs_obj->get_packs('upgrade');
	$duration_str    = $packs_obj->duration_str;
	$trial_taken     = $member->trial_taken;	
	// pack_ids	
	$pack_ids 		 = mgm_get_members_packids($member);
	$pack_membership_types = mgm_get_subscribed_membershiptypes($user->ID, $member);
	
	// query_arg
	$form_action = mgm_get_custom_url('transactions', false, array('action'=>'purchase_another', 'username'=> $username));

	//issue 1009
	if(isset($settings['membership_details_url'])){
		$membership_details_url = $settings['membership_details_url'];
	}else {
		$membership_details_url = get_admin_url().'profile.php?page=mgm/profile';
	}

	// cancel 
	$cancel_url = $membership_details_url;
	// $cancel_url = mgm_get_custom_url('membership_details');
	// active modules
	$a_payment_modules = $system_obj->get_active_modules('payment');
	
	// 	selected_subscription	
	$selected_subs = mgm_get_selected_subscription($args);

	// second step, after post
	if (isset($_POST['submit'])) {
		// verify selected
		if( !isset($_POST['subs_opt']) ){
			// die
			return sprintf(__('Package not selected, <a href="%s">go back</a>.','mgm'), $_POST['form_action']); exit;
		}	
		
		// get subs data			
		$subs_opt_pack = mgm_decode_package($_POST['subs_opt']);
		extract($subs_opt_pack);
					
		// check		     
		$valid = false;
		// loop packs
		foreach ($packs as $pack) {
			// check
			//check pack id as well: issue#: 580
			if ($pack['cost'] == $cost && $pack['duration'] == $duration && $pack['duration_type'] == $duration_type 
				&& $membership_type == $pack['membership_type'] && $pack_id == $pack['id']) {
				$valid = true;				
				break;
			}
		}
		// error
		if (!$valid) {
		    return __('Invalid data passed','mgm'); exit;
		}	
		
		// get object
		$member =  new mgm_member($user->ID);
		$temp_membership = $member->_default_fields();
		$temp_membership['membership_type'] = $membership_type;
		$temp_membership['pack_id'] = $pack_id;

		//issue #860
		//if (isset($_POST['mgm_upgrade_field']['autoresponder']) && ($_POST['mgm_upgrade_field']['autoresponder'])=='Y') {
		if (isset($_POST['mgm_upgrade_field']['autoresponder']) && substr($_POST['mgm_upgrade_field']['autoresponder'],0,1) == 'Y') {	
			$temp_membership['subscribed'] 	  = 'Y';
			$temp_membership['autoresponder'] = $system_obj->active_modules['autoresponder'];
		}
		//issue #1236
		if(isset($_POST['mgm_upgrade_field']['coupon']) && !empty($_POST['mgm_upgrade_field']['coupon'])) {
			//issue #1250 - Coupon validation 
			if(!empty($_POST['form_action'])) {				
				// check if its a valid coupon
				if(!$coupon = mgm_get_coupon_data($_POST['mgm_upgrade_field']['coupon'])){				
					//redirect back to the form							
					$q_arg = array('error_field' => 'Coupon', 'error_type' => 'invalid','error_field_value'=>$_POST['mgm_upgrade_field']['coupon']);
					$redirect = add_query_arg($q_arg, $_POST['form_action']);	
					// redirect													
					mgm_redirect($redirect); exit;
				}else{ // membership type check
					// get subs
					if( $subs_pack = mgm_decode_package(mgm_post_var('subs_opt')) ){						
						// values
						$coupon_values = mgm_get_coupon_values(NULL, $coupon['value'], true);
						// check
						if(isset($coupon_values['new_membership_type']) && $coupon_values['new_membership_type'] != $subs_pack['membership_type']){
							$new_membership_type = mgm_get_membership_type_name($coupon_values['new_membership_type']);							
							$q_arg = array(	'error_field' => 'Coupon', 
										   	'error_type' => 'invalid',
										   	'membership_type' => $coupon_values['new_membership_type'],
										   	'error_field_value'=>$_POST['mgm_upgrade_field']['coupon']);
							$redirect = add_query_arg($q_arg, $_POST['form_action']);		
							// redirect										
							mgm_redirect($redirect); exit;							
						}
					}					
				}
			}			
		}
				
		//inserted an incomplete entry for the selected subscription type
		mgm_save_another_membership_fields($temp_membership, $user->ID);			
		
		// save coupon fields and update member object		
		$member = mgm_save_partial_fields_purchase_more($user->ID, $membership_type, $cost);
		
		// coupon
		$purchase_another_coupon = false;
		// array
		if(isset($member->upgrade)){
			if(is_array($member->upgrade) && isset($member->upgrade['coupon']['id'])){
				$purchase_another_coupon = $member->upgrade['coupon'];
			}elseif(is_object($member->upgrade) && isset($member->upgrade->coupon->id)){
				$purchase_another_coupon = mgm_object2array($member->upgrade->coupon);
			}	
			// coupon
			mgm_get_purchase_another_coupon_pack($purchase_another_coupon, $pack);	
		}
		//save custom fields -issue #1285
		if(isset($_POST['mgm_upgrade_field']) && !empty($_POST['mgm_upgrade_field'])) {					
			//member
			$cf_member = mgm_get_member($user->ID);
			//upgrade custom fileds
			$cfu_fileds = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_multiple_membership_level_purchase'=>true)));			
			//loop fields
			foreach($cfu_fileds as $cf_filed){
				//skip coupon and autoresponder
				if(in_array($cf_filed['name'], array('coupon','autoresponder')))
					continue;
				//check	
				if(isset($_POST['mgm_upgrade_field'][$cf_filed['name']])){
					// check upgrae and required		
					if((bool)$cf_filed['attributes']['required'] === true){								
						if(empty($_POST['mgm_upgrade_field'][$cf_filed['name']])){
							//redirect back to the form							
							$q_arg = array('error_field' => $cf_filed['label'], 'error_type' => 'empty','error_field_value'=>$_POST['mgm_upgrade_field'][$cf_filed['name']]);
							$redirect = add_query_arg($q_arg, $_POST['form_action']);														
							mgm_redirect($redirect);
							exit;									
						}							
					}
					//appending custom fields
					if(isset($cf_member->custom_fields->$cf_filed['name'])){
						$cf_member->custom_fields->$cf_filed['name'] = $_POST['mgm_upgrade_field'][$cf_filed['name']];
					}else {
						$cf_member->custom_fields->$cf_filed['name'] = $_POST['mgm_upgrade_field'][$cf_filed['name']];
					}											
				}
			}
			$cf_member->save();					
		}		
		// start html
		$html = '<div>';
		// free
		if (($pack['cost'] == 0 || $membership_type == 'free') && in_array('mgm_free', $a_payment_modules) && mgm_get_module('mgm_free')->is_enabled()) {			
			$html .= sprintf('<div>%s - %s</div>', __('Create a free account ','mgm'), ucwords($membership_type));		
			$module = 'mgm_free';
			// payments url
			$payments_url = mgm_get_custom_url('transactions');			
			// if tril module selected and cost is 0 and free moduleis not active
			if($membership_type == 'trial'){
				if(in_array('mgm_trial', $a_payment_modules)){
					$module = 'mgm_trial';
				}
			}
			//Purchase Another Membership Level problem : issue #: 752				
			$redirect = add_query_arg(array('method'=>'payment_return', 'module'=>$module, 'custom' => ($user->ID . '_' . $duration . '_'  . $duration_type . '_' . $pack_id. '_Y'), 'redirector'=>$redirector), $payments_url);			
			// redirect
			if (!headers_sent()) {							
				@header('location: ' . $redirect); exit;
			}
			// js redirect
			$html .= sprintf( '<script type="text/javascript">window.location = "%s";</script><div>%s</div>', $redirect, $packs_obj->get_pack_desc($pack) );
			
		} else {			
			$html .= sprintf( '<div class="mgm_get_subs_btn">%s</div>', $packs_obj->get_pack_desc($pack) );
			// coupon
			if(isset($purchase_another_coupon['id'])){
				$html .= sprintf( '<div class="mgm_get_subs_btn">%s</div>', sprintf(__('Using Coupon "%s" - %s','mgm'), $purchase_another_coupon['name'], $purchase_another_coupon['description']) );
			}
			$html .= sprintf( '<div class="mgm_get_subs_btn">%s</div>', __('Please Select from Available Payment Gateways','mgm') );
		}
		
		//bypass if payment gateway field is selected -issue #1764
		if ((float)$pack['cost'] > 0 && isset($_POST['mgm_payment_gateways']) && !empty($_POST['mgm_payment_gateways'])){
			//init
			$tran_id = 0;				
			if(!$tran_id) $tran_id = mgm_add_transaction($pack, array('is_another_membership_purchase' => true, 'user_id' => $user->ID));			
			// module
			$mod_obj = mgm_get_module($_POST['mgm_payment_gateways'],'payment');			
			// module end point
			$redirect = $mod_obj->_get_endpoint('html_redirect', false);														// encode id:
			//encode transaction id
			$encode_tran_id  = mgm_encode_id($tran_id);	
			//args
			$redirect = add_query_arg(array( 'tran_id' => $encode_tran_id ), $redirect);  ;
			// do the redirect to payment
			mgm_redirect($redirect);						
		}
				
		// init 
		$payment_modules = array();			
		// when active
		if($a_payment_modules){
			// loop
			foreach($a_payment_modules as $payment_module){
				// not trial
				if(in_array($payment_module, array('mgm_free','mgm_trial'))) continue;	
				//consider only the modules assigned to pack
				if(isset($pack['modules']) && !in_array($payment_module, (array)$pack['modules'])) continue;			
				// store
				$payment_modules[] = $payment_module;					
			}
		}
		
		// loop payment mods if not free		
		if (count($payment_modules) && $cost) {
			// transaction
			$tran_id = 0;
			// loop
			foreach ($payment_modules as $module) {
				// module
				$mod_obj = mgm_get_module($module,'payment');	
				// create transaction
				// if(!$tran_id) $tran_id = $mod_obj->_create_transaction($pack, array('is_another_membership_purchase' => true, 'user_id' => $user->ID));
				if(!$tran_id) $tran_id = mgm_add_transaction($pack, array('is_another_membership_purchase' => true, 'user_id' => $user->ID));
				// button				
				$html .= sprintf('<div class="mgm_get_subs_btn">%s</div>', $mod_obj->get_button_subscribe(array('pack'=>$pack,'tran_id'=>$tran_id)));			
			}
		} else {
			if($cost){			
				$html .= sprintf( '<div class="mgm_get_subs_btn">%s</div>', __('There are no payment gateways available at this time.','mgm') );
			}
		}
		// html
		$html .= '</div>';	
	}else {
	// first step show upgrade options		
		// html
		$html  = sprintf('<p class="message register">%s</p>', __('Please Select from Available Membership Packages','mgm'));		
 		// upgrade_packages
		$upgrade_packages = '';
		// pack to modules
		$pack_modules = array();		
		// loop		
		foreach ($packs as $pack) {					
			// default
			$checked = '';			
			// skip already purchased packs
		    if(in_array($pack['id'], $pack_ids)) continue;	
		    //skip same membership level subscriptions		   
		    if(in_array($pack['membership_type'], $pack_membership_types)) continue;			   
		    // do not show trial or free as upgradre
		    if($pack['membership_type'] == 'trial' || $pack['membership_type'] == 'free') continue;			
			// reset
			$checked = mgm_select_subscription($pack,$selected_subs);						
			// skip other when a package sent as selected
			if($selected_subs !== false){
				if(empty($checked)) continue;
			}			
			// checked
			if(!$checked){
            	$checked = ((int)$pack['default'] == 1 ? ' checked="checked"':''); 
			}
			// duration                      
			if ($pack['duration'] == 1) {
				$dur_str = rtrim($duration_str[$pack['duration_type']], 's');
			} else {
				$dur_str = $duration_str[$pack['duration_type']];
			}		
			$css_group = mgm_get_css_group();		
			// encode pack
			$subs_opt_enc = mgm_encode_package($pack);
			// set 
			$pack_modules[$subs_opt_enc] = $pack['modules'];
			//issue #867
			if($css_group !='none') {
				//expand this if needed
				$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';				
				$css_file = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.form.fields.css';
				$upgrade_packages .= sprintf($css_link_format, $css_file);
			}

			// free
			if (($pack['cost'] == 0 || strtolower($pack['membership_type']) == 'free') && in_array('mgm_free', $a_payment_modules) && mgm_get_module('mgm_free')->enabled=='Y') {
				// input
				$input = sprintf('<input type="radio" %s class="checkbox" name="subs_opt" value="%s" rel="mgm_subscription_options"/>', $checked, $subs_opt_enc);
				// html
				$upgrade_packages .= '  
							<div class="mgm_subs_wrapper '.$pack['membership_type'].'">
								<div class="mgm_subs_option '.$pack['membership_type'].'">
									' . $input . '
								</div>
								<div class="mgm_subs_pack_desc '.$pack['membership_type'].'">
									' . $packs_obj->get_pack_desc($pack) . '
								</div>
								 <div class="clearfix"></div>
								 <div class="mgm_subs_desc '.$pack['membership_type'].'">
									' . mgm_stripslashes_deep($pack['description']) . '
								 </div>
							</div>';
			} else {
				// input
				$input = sprintf('<input type="radio" %s class="checkbox" name="subs_opt" value="%s" rel="mgm_subscription_options"/>', $checked, $subs_opt_enc);
				// html
				$upgrade_packages .= '  
							<div class="mgm_subs_wrapper '.$pack['membership_type'].'">
								<div class="mgm_subs_option '.$pack['membership_type'].'">
									' . $input . '
								</div>
								<div class="mgm_subs_pack_desc '.$pack['membership_type'].'">
									' . $packs_obj->get_pack_desc($pack) . '
								</div>
								 <div class="clearfix"></div>
								 <div class="mgm_subs_desc '.$pack['membership_type'].'">
									' . mgm_stripslashes_deep($pack['description']) . '
								 </div>
							</div>';
			}
		}
		
		// add pack_modules as json data, may consider jquery data later
		if( ! empty( $pack_modules ) ){
			$html .= sprintf('<script language="javascript">var mgm_pack_modules = %s</script>', json_encode($pack_modules));
		}

		// show error
		if(!$upgrade_packages){
			// html
			$html .= '<div class="mgm_subs_wrapper">
						<div class="mgm_subs_pack_desc">
							' . __('Sorry, no packages available.','mgm') . '
						</div>
					 </div>
					 <p>						
						<input type="button" name="cancel" onclick="window.location=\''.$cancel_url.'\'" value="'.__('Cancel','mgm').'" class="button-primary" />&nbsp;					
					 </p>';
		}else {
/*			$error_field = mgm_request_var('error_field'); 
			if(!empty($error_field)) {
				$errors = new WP_Error();
				switch (mgm_request_var('error_type')) {
					case 'empty':
						$error_string = 'You must provide a ';
						break;
					case 'invalid':
						$error_string = 'Invalid ';
						break;	
				}				
				//issue #703
				$errors->add( $error_field, __( '<strong>ERROR</strong>: '.$error_string, 'mgm' ).$error_field );
				$html .= mgm_set_errors($errors, true);					
			}*/
			// check errors if any:
			$html .= mgm_subscription_purchase_errors();			

			// form
			$html .= '<form action="'.$form_action .'" method="post" class="mgm_form"><div class="mgm_get_pack_form_container">';
			$html .= '<input type="hidden" name="form_action" value="'. $form_action .'" />';
			$html .= $upgrade_packages;
			//issue #1285
			$html .= mgm_get_custom_fields($user->ID,array('on_multiple_membership_level_purchase'=>true),'mgm_upgrade_field');
			// html
			$html .= '<input type="hidden" name="ref" value="'. md5($member->amount .'_'. $member->duration .'_'. $member->duration_type .'_'. $member->membership_type) .'" />';													
			// set
			$html .= '<p>						
						<input class="button" type="submit" name="submit" value="'.__('Next','mgm').'" />&nbsp;&nbsp;
						<input class="button" type="button" name="cancel" onclick="window.location=\''.$cancel_url.'\'" value="'.__('Cancel','mgm').'"/>&nbsp;					
					  </p>';
			// html
			$html .= '</div></form>';
		}
	}	
	// return    	
	return $html;
}

/**
 * get post purchase buttons
 * final step for post purchase
 *
 * @param void
 * @return $html
 */
function mgm_get_post_purchase_buttons(){
	
	// get current user data - issue #1421    
	$user = wp_get_current_user();	
	// pack
	$pack = NULL;	
	// addon options
	if($addon_option_ids = mgm_post_var('addon_options')){
		$addon_options = mgm_get_addon_options_only($addon_option_ids);
		// mgm_pr($addon_options);
	}
	// post purchase
	if(isset($_POST['post_id'])){

		//issue #1250
		if(isset($_POST['mgm_postpurchase_field']['coupon']) && !empty($_POST['mgm_postpurchase_field']['coupon'])) {
			//issue #1250 - Coupon validation 
			if(!empty($_POST['form_action'])) {				
				// check if its a valid coupon
				if(!$coupon = mgm_get_coupon_data($_POST['mgm_postpurchase_field']['coupon'])){				
					//redirect back to the form							
					$q_arg = array('error_field' => 'Coupon', 'error_type' => 'invalid','error_field_value'=>$_POST['mgm_postpurchase_field']['coupon']);
					$redirect = add_query_arg($q_arg, $_POST['form_action']);														
					mgm_redirect($redirect);
					exit;
				}
			}			
		}		
		
		// post id
		$post_id = $_POST['post_id'];
		// gete mgm data
		$post_obj = mgm_get_post($post_id);
		$cost     = mgm_convert_to_currency($post_obj->purchase_cost);
		$product  = $post_obj->product;
		$allowed_modules = $post_obj->allowed_modules;
		// post data
		$post    = get_post($post_id);
		$title   = $post->post_title;
		// item name -issue #1380
		$item_name = apply_filters('mgm_post_purchase_itemname',sprintf(__('Purchase Post - %s','mgm'), $title)) ;
		// set pack
		$pack = array('duration'=>1,'item_name'=>$item_name,'buypost'=>1,'cost'=>$cost,'title'=>$title, 
					  'product'=>$product,'post_id'=>$post_id, 'allowed_modules'=>$allowed_modules);
	}else if(isset($_POST['postpack_id'])){
	// post pack purchase
		//issue #1250
		if(isset($_POST['mgm_postpurchase_field']['coupon']) && !empty($_POST['mgm_postpurchase_field']['coupon'])) {
			//issue #1250 - Coupon validation 
			if(!empty($_POST['form_action'])) {				
				// check if its a valid coupon
				if(!$coupon = mgm_get_coupon_data($_POST['mgm_postpurchase_field']['coupon'])){				
					//redirect back to the form							
					$q_arg = array('error_field' => 'Coupon', 'error_type' => 'invalid','error_field_value'=>$_POST['mgm_postpurchase_field']['coupon']);
					$redirect = add_query_arg($q_arg, $_POST['form_action']);														
					mgm_redirect($redirect);
					exit;
				}
			}			
		}		
		
		// post pack purchase 
		$postpack_id      = $_POST['postpack_id'];// pcak id
		$postpack_post_id = $_POST['postpack_post_id'];// post id where pack is listed, redirect here
		// get pack
		$postpack = mgm_get_postpack($postpack_id);
		$cost     = mgm_convert_to_currency($postpack->cost);
		$product  = json_decode($postpack->product, true);
		$modules = json_decode($postpack->modules, true);
		//mgm_pr($postpack);
		
		// item name -issue #1380
		$item_name = apply_filters('mgm_postpack_purchase_itemname', sprintf(__('Purchase Post Pack - %s','mgm'), $postpack->name));
		// post id
		$post_id   = mgm_get_postpack_posts_csv($postpack_id);
		// set pack
		$pack = array('duration'=>1,'item_name'=>$item_name,'buypost'=>1,'cost'=>$cost,'title'=>$postpack->name, 
		              'product'=>$product,'post_id'=>$post_id,'postpack_id'=>$postpack_id, 
					  'postpack_post_id'=>$postpack_post_id, 'allowed_modules'=>$modules);
	}	
	
	// check
	if(!$pack){
		return __('Error in Payment! No data available '); exit;
	}
		
	// guest token	-issue #1421
	if(isset($_POST['guest_purchase']) && $_POST['guest_purchase'] == TRUE && $user->ID <= 0){
		$pack['guest_token'] = sanitize_title_for_query(mgm_create_token());
	}
	// addon options
	if(isset($addon_options) && !empty($addon_options)){
		$pack['addon_options'] = $addon_options;
	}	
	// get coupon
	$post_purchase_coupon = mgm_save_partial_fields(array('on_postpurchase'=>true),'mgm_postpurchase_field',$pack['cost'],false, 'postpurchase');
	// alter
	mgm_get_post_purchase_coupon_pack($post_purchase_coupon, $pack);
	
	// Eg: $_POST['mgm_payment_gateways'] = mgm_paypal
	$cf_payment_gateways = (isset($_POST['mgm_payment_gateways']) && !empty($_POST['mgm_payment_gateways'])) ? $_POST['mgm_payment_gateways'] : null;				
	// bypass step2 if payment gateway is submitted: issue #: 469	
	if(!is_null($cf_payment_gateways)) {					
		// get pack
		// mgm_get_upgrade_coupon_pack($member, $selected_pack);
		// cost		
		if ((float)$pack['cost'] > 0) {			
			//get an object of the payment gateway:
			$mod_obj = mgm_get_module($cf_payment_gateways,'payment');
			// tran options
			$tran_options = array('user_id' => $user->ID);
			// is register & purchase
			if(isset($_POST['post_id'])){
				$tran_options['post_id'] = (int)$_POST['post_id'];
			}
			// postpack id
			if(isset($_POST['postpack_id'])){
				$tran_options['postpack_id'] = (int)$_POST['postpack_id'];
			}					
			// is register & purchase postpack
			if(isset($_POST['postpack_post_id']) && isset($_POST['postpack_id'])){
				$tran_options['postpack_post_id'] = (int)$_POST['postpack_post_id'];
				$tran_options['postpack_id'] = (int)$_POST['postpack_id'];
			}	
			// create transaction				
			$tran_id = mgm_add_transaction($pack, $tran_options);
			// bypass directly to process return if manual payment:				
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
			$redirect = $mod_obj->_get_endpoint('html_redirect', true);	
			$redirect = add_query_arg(array( 'tran_id' => $tran_id ), $redirect); 	
			// redirect	
			mgm_redirect($redirect);// this goes to subscribe, mgm_functions.php/mgm_get_subscription_buttons
			// exit						
			exit;						
		}
	}	
	// get payment modules
	$a_payment_modules = mgm_get_class('system')->get_active_modules('payment');
	// init 
	$payment_modules = array();			
	// when active
	if($a_payment_modules){
		// loop
		foreach($a_payment_modules as $payment_module){
			// not trial
			if(in_array($payment_module, array('mgm_free','mgm_trial'))) continue;				
			// store
			$payment_modules[] = $payment_module;					
		}
	}
	// init
	$button = '';
	// transaction 
	$tran_id = NULL;
	$button_printed = 0;
	// loop modules
	foreach ($payment_modules as $module) {						
		// object
		$mod_obj = mgm_get_module($module, 'payment');						
		// check buypost support 
		if(in_array('buypost',$mod_obj->supported_buttons)){								
			// create transaction
			if(!$tran_id) $tran_id = mgm_add_transaction($pack);			
			// button code
			if( isset( $pack['allowed_modules'] ) ){
				// Issue #1562: If no payment module is selected, display all supported modules
				if (!empty($pack['allowed_modules']) && FALSE === in_array($module, $pack['allowed_modules'])) continue;
			}
			$button_code = $mod_obj->get_button_buypost(array('pack'=>$pack,'tran_id'=>$tran_id), true);		
			$button_printed++;					 
			// get button
			$button .= "<div class='mgm_custom_filed_table'>" . $button_code . "</div>";
		}
	} 
	// none active
	if( $button_printed == 0 ){
		$button .= sprintf('<p class="mgm-no-module"> %s </p>',__('No Payment module active for this Content Purchase.', 'mgm') );
	}
	// if Cost is zero, then process using free module.: issue#: 883
	if ($tran_id && ($pack['cost'] == 0) && in_array('mgm_free', $a_payment_modules) && mgm_get_module('mgm_free')->is_enabled()) {
		// module
		$module = 'mgm_free';
		// payments url
		$payments_url = mgm_get_custom_url('transactions');
		// query_args
		$query_args = array('method' => 'payment_return', 'module'=>$module, 'custom' => $tran_id);
		// redirector
		if(isset($_REQUEST['redirector'])){
			// set
			$query_args['redirector'] = $_REQUEST['redirector'];
		}
		// redirect to module to mark the payment as complete
		$redirect = add_query_arg($query_args, $payments_url);
		// redirect
		mgm_redirect($redirect);			
	}	
	// html
	$return = '<div class="post_purchase_select_gateway">' . __('Please Select a Payment Gateway.','mgm') . '</div>' . $button;
	// return 
	return $return;
}
// end file /core/libs/functions/mgm_payment_buttons.php