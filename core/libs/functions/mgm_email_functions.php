<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members email functions
 *
 * @package MagicMembers
 * @since 2.7.2
 */

/**
 * Send Email Notification to Admin
 *
 * @uses mgm_mail()
 * @param string optional $admin_email
 * @param string $subject
 * @param string $message
 * @return bool $send
 */
function mgm_notify_admin($admin_email=null, $subject='You have a notification', $message='Notification for Administrator'){
	// admin email
	if( ! $admin_email ) $admin_email = mgm_get_setting('admin_email');
	
	// log
	// mgm_log( sprintf('%s, %s, %s', $admin_email, $subject, $message), __FUNCTION__ );

	// send
	return @mgm_mail( $admin_email, $subject, $message );	
}

/**
 * Send Email Notification to User
 *
 * @uses mgm_mail()
 * @param string optional $user_email
 * @param string $subject
 * @param string $message
 * @return bool $send
 */
function mgm_notify_user($user_email=null, $subject='You have a notification', $message='Notification for User'){
	// current user email
	if( ! $user_email ) $user_email = mgm_get_current_user_email();
	
	// log
	// mgm_log( sprintf('%s, %s, %s', $user_email, $subject, $message), __FUNCTION__ );

	// send
	return @mgm_mail( $user_email, $subject, $message );	
}

/**
 * Send Email Notification to Admin on Post Purchase
 *
 * @uses mgm_notify_admin()
 * @param string $blogname
 * @param object $user
 * @param object $post
 * @param string $status
 * @return bool $send
 */
function mgm_notify_admin_post_purchase($blogname, $user, $post, $status){
	//post link
	$link =  "<a href=". get_permalink($post->ID).">".$post->post_title."</a>";	
	// not for guest
	if( isset($user->ID) ){
		$subject = sprintf("[%s] Admin Notification - %s purchased post: %s [%d]", 
						   $blogname, $user->user_email, $post->post_title, $post->ID);						
		$message = sprintf("User display name: %s<br />
			                User email: %s<br />
			                User ID: %s<br />
		            		Status: %s<br />
		            		Action: Purchase post <br/>
		            		Post Title: %s 
							Post Link: %s", 
		            		$user->display_name, $user->user_email, $user->ID, $status, $post->post_title,$link);
	}else{
		$subject = sprintf("[%s] Admin Notification - Guest[IP: %s] purchased post: %s [%d]", 
						  $blogname, mgm_get_client_ip_address(), $post->post_title, $post->ID);
						  		
		$message = sprintf("Action: Guest Purchase post <br/>
		            		Post Title: %s 
							Post Link: %s", 
		            		$post->post_title,$link);		
	}

	// return
	return @mgm_notify_admin(null, $subject, $message);
}

/**
 * Send Email Notification to User on Post Purchase
 *
 * @uses mgm_notify_user()
 * @param object $blogname
 * @param object $user
 * @param object $post
 * @param string $status
 * @param object $system_obj
 * @param object $post_obj
 * @param string $status_str
 * @return bool $send
 */
function mgm_notify_user_post_purchase($blogname, $user, $post, $status, $system_obj, $post_obj, $status_str){
	// emails not for guest
	if( isset($user->ID) ){					
		// purchase status
		switch($status){
			case 'Success':
				// subject
				$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
				// data
				$data = array('blogname'=>$blogname,'name'=>$user->display_name,'post_title'=>$post->post_title,
							  'purchase_cost'=>mgm_convert_to_currency($post_obj->purchase_cost),'email'=>$user->user_email, 
							  'admin_email'=>$system_obj->get_setting('admin_email'));
				// message
				$message = $system_obj->get_template('payment_success_email_template_body', $data, true);
				//
			break;
			case 'Failure':
				// subject
				$subject = $system_obj->get_template('payment_failed_email_template_subject', array('blogname'=>$blogname), true);				
				// data
				$data = array('blogname'=>$blogname,'name'=>$user->display_name,'post_title'=>$post->post_title,
							  'purchase_cost'=>mgm_convert_to_currency($post_obj->purchase_cost),'email'=>$user->user_email, 
							  'payment_type'=>'post purchase payment','reason'=>$status_str,'admin_email'=>$system_obj->get_setting('admin_email')) ;
				// message			
				$message = $system_obj->get_template('payment_failed_email_template_body', $data, true);
			break;
			case '':
				// subject
				$subject = $system_obj->get_template('payment_pending_email_template_subject', array('blogname'=>$blogname), true);
				// data
				$data = array('blogname'=>$blogname, 'name'=>$user->display_name,'post_title'=>$post->post_title,
							  'purchase_cost'=>mgm_convert_to_currency($post_obj->purchase_cost),'email'=>$user->user_email, 
							  'reason'=>$status_str, 'admin_email'=>$system_obj->get_setting('admin_email'));
				// message	
				$message = $system_obj->get_template('payment_pending_email_template_body', $data, true);
			break;
			case 'Unknown':
			default:
				// subject
				$subject = $system_obj->get_template('payment_unknown_email_template_subject', array('blogname'=>$blogname), true);		
				// data
				$data = array('blogname'=>$blogname, 'name'=>$user->display_name, 'post_title'=>$post->post_title,
							  'purchase_cost'=>mgm_convert_to_currency($post_obj->purchase_cost),'email'=>$user->user_email, 
							  'reason'=>$status_str,'admin_email'=>$system_obj->get_setting('admin_email'));
				// message	
				$message = $system_obj->get_template('payment_unknown_email_template_body', $data, true);
			break;
		}		

		// replace tags
		$subject = mgm_replace_email_tags($subject, $user->ID);
		$message = mgm_replace_email_tags($message, $user->ID);

		// return
		return @mgm_notify_user($user->user_email, $subject, $message); // send an email to the buyer	
	}

	// return
	return false;
}

/**
 * Send Email Notification to Admin on Membership Verification failure
 *
 * @uses mgm_notify_admin()
 * @param string $module_name
 * @return bool $send
 */
function mgm_notify_admin_membership_verification_failed( $module_name ){
	// subject
	$subject = sprintf('Error in membership verification using %s Gateway', $module_name);
	// message
	$message = sprintf('Could not read membership type in the following POST data. <br>				
						Please debug or contact magicmembers to fix the problem making sure to pass on the following data.<br>
						POST DATA: %s<br>', print_r($_POST, true));
	// send
	return @mgm_notify_admin(null, $subject, $message);
}

/**
 * Send Email Notification to Admin on Membership Purchase
 *
 * @uses mgm_notify_admin()
 * @param string $blogname
 * @param object $user
 * @param object $member
 * @param string $pack_duration
 * @return bool $send
 */
function mgm_notify_admin_membership_purchase($blogname, $user, $member, $pack_duration){
	// subject
	$subject = sprintf("[%s] Admin Notification - %s purchased membership: %s [%d] - [%s]", 
						$blogname, $user->user_email, $member->membership_type, $member->pack_id, $member->status);
	// message
	$message = sprintf("User display name: %s<br />
						User email: %s<br />
						User ID: %s<br />
						Membership Type: %s<br />
						New status: %s<br />
						Status message: %s<br />
						Subscription period: %s %s<br />
						Subscription amount: %s %s<br />
						Payment Mode: %s", 
						$user->display_name, $user->user_email, $user->ID, $member->membership_type, $member->status, 
						$member->status_str, $member->duration, $pack_duration, $member->amount, $member->currency, 
						$member->payment_type);

	// return
	return @mgm_notify_admin(null, $subject, $message);
}

/**
 * Send Email Notification to User on Membership Purchase
 *
 * @uses mgm_notify_user()
 * @param string $blogname
 * @param object $user
 * @param object $member
 * @param array $custom
 * @param array $subs_pack
 * @param object $s_packs
 * @param object $system_obj
 * @return bool $send
 */
function mgm_notify_user_membership_purchase($blogname, $user, $member, $custom, $subs_pack, $s_packs, $system_obj){
	// local var
	extract($custom);
	// on status
	switch ($member->status) {
		case MGM_STATUS_ACTIVE:
			//Sending notification email to user - issue #1468
			if( isset($notify_user) && isset($is_registration) && bool_from_yn($is_registration) ){
				// get pass
				$user_pass = mgm_decrypt_password($member->user_password, $user->ID);
				// action				
				// send notification only once - issue #1601
				if($system_obj->setting['enable_new_user_email_notifiction_after_user_active'] == 'Y') {
					do_action('mgm_register_user_notification', $user->ID, $user_pass);
				}
			}
			//sending upgrade notifaction email to admin
			if(isset($subscription_option) && $subscription_option =='upgrade'){
				do_action('mgm_user_upgrade_notification', $user_id);
			}			
			// init
			$subscription = '';
			// add trial 
			if ( isset($subs_pack['trial_on']) && (int)$subs_pack['trial_on'] == 1 ) {
				// trial
				$subscription = sprintf('%1$s %2$s for the first %3$s %4$s,<br> then ', $member->trial_cost, $member->currency, 
										($member->trial_duration * $member->trial_num_cycles), $s_packs->get_pack_duration($subs_pack,true)); 
			}
			
			// on type
			if ($member->payment_type == 'subscription') {
				$payment_type = 'recurring subscription';
				$subscription .= sprintf('%1$s %2$s for each %3$s %4$s, %5$s',
										$member->amount,$member->currency,$member->duration,$s_packs->get_pack_duration($subs_pack),
										((int)$member->active_num_cycles > 0 ? sprintf('for %d installments',(int)$member->active_num_cycles) : 'until cancelled'));
			} else {
				$payment_type = 'one-time payment';
				$subscription .= sprintf('%1$s %2$s for %3$s %4$s',$member->amount, $member->currency, $member->duration,$s_packs->get_pack_duration($subs_pack));					
			}
			// subject
			$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
			// data
			$data = array('blogname'=>$blogname,'name'=>$user->display_name, 'email'=>$user->user_email,'payment_type'=>$payment_type,
						  'subscription'=>$subscription,'admin_email'=>$system_obj->get_setting('admin_email'));
			// message
			$message = $system_obj->get_template('payment_success_subscription_email_template_body', $data, true);
		break;

		case MGM_STATUS_NULL:
			// subject
			$subject = $system_obj->get_template('payment_failed_email_template_subject', array('blogname'=>$blogname), true);		
			// data
			$data = array('blogname'=>$blogname,'name'=>$user->display_name,'email'=>$user->user_email, 'payment_type'=>'subscription payment',
									  'reason'=>$member->status_str,'admin_email'=>$system_obj->get_setting('admin_email'));	
			// message
			$message = $system_obj->get_template('payment_failed_email_template_body', $data, true);
		break;

		case MGM_STATUS_PENDING:
			// subject
			$subject = $system_obj->get_template('payment_pending_email_template_subject', array('blogname'=>$blogname), true);
			// data
			$data = array('blogname'=>$blogname, 'name'=>$user->display_name, 'email'=>$user->user_email, 'reason'=>$member->status_str,
						  'admin_email'=>$system_obj->get_setting('admin_email'));
			// body
			$message = $system_obj->get_template('payment_pending_email_template_body', $data, true);
		break;

		case MGM_STATUS_ERROR:
			// subject
			$subject = $system_obj->get_template('payment_error_email_template_subject', array('blogname'=>$blogname), true);	
			// data
			$data = array('blogname'=>$blogname, 'name'=>$user->display_name, 'email'=>$user->user_email,'reason'=>$member->status_str,
						  'admin_email'=>$system_obj->get_setting('admin_email'));			
			// body	
			$message = $system_obj->get_template('payment_error_email_template_body', $data, true);
		break;
	}

	// replace tags
	$subject = mgm_replace_email_tags($subject, $user->ID);
	$message = mgm_replace_email_tags($message, $user->ID);

	// return
	return @mgm_notify_user($user->user_email, $subject, $message);
}

/**
 * Send Email Notification to Admin on Membership Cancellation
 *
 * @uses mgm_notify_admin()
 * @param string $blogname
 * @param object $user
 * @param object $member
 * @param string $new_status
 * @param string $membership_type
 * @return bool $send
 */
function mgm_notify_admin_membership_cancellation($blogname, $user, $member, $new_status, $membership_type){
	// subject
	$subject = sprintf("[%s] %s - %s", $blogname, $user->user_email, $new_status);
	// message
	$message = sprintf("User display name: %s<br />
					    User email: %s<br />
						User ID: %s<br />
						Membership Type: %s<br />
						New status: %s<br />
						Status message: %s<br />					
						Payment Mode: Cancelled",
						$user->display_name,$user->user_email,$user->ID,$membership_type,$new_status,$member->status_str);
	// return
	return @mgm_notify_admin(null, $subject, $message);
}

/**
 * Send Email Notification to User on Membership Cancellation
 *
 * @uses mgm_notify_user()
 * @param string $blogname
 * @param string $user
 * @param string $member
 * @param string $system_obj
 * @return bool $send
 */
function mgm_notify_user_membership_cancellation($blogname, $user, $member, $system_obj){
	// subject
	$subject = $system_obj->get_template('subscription_cancelled_email_template_subject', array('blogname'=>$blogname), true);	
	// data
	$data = array('blogname'=>$blogname,'name'=>$user->display_name,'email'=>$user->user_email, 
				  'admin_email'=>$system_obj->get_setting('admin_email'));			
	// body	
	$message = $system_obj->get_template('subscription_cancelled_email_template_body', $data, true);

	//issue #862
	$subject = mgm_replace_email_tags($subject, $user->ID);
	$message = mgm_replace_email_tags($message, $user->ID);

	// mail
	return @mgm_notify_user($user->user_email, $subject, $message);	
}

/**
 * Send Email Notification to Admin on Membership Cancellation manual removal required
 *
 * @uses mgm_notify_admin()
 * @param string $blogname
 * @param object $user
 * @param object $member
 * @return bool $send
 */
function mgm_notify_admin_membership_cancellation_manual_removal_required($blogname, $user, $member){
	// subject
	$subject = sprintf(__('[%s] User Subscription Cancellation', 'mgm'), $blogname);	
	// message
	$message = sprintf(__('The User: %s (%d) has upgraded/cancelled subscription.<br/>
						  Please unsubscribe the user subscription from Gateway Merchant panel.<br/>
						  MGM Transaction Id: %d', 'mgm'), 
						  $user->user_email,$user_id,$member->transaction_id);
	// send			
	return @mgm_notify_admin(null, $subject, $message);
}

/**
 * Send Email Notification to Admin on passthrough verification failed
 *
 * @uses mgm_notify_admin()
 * @param string $passthrough
 * @param string $module
 * @return bool @send
 */
function mgm_notify_admin_passthrough_verification_failed($passthrough, $module){
	// system
	$system_obj = mgm_get_class('system');
	$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
	
	// notify admin, only if gateway emails on
	if( ! $dge ){		
		// subject		
		$subject = sprintf('Error in %s custom passthrough verification', ucwords($module));
		// message
		$message = sprintf('Could not read custom passthrough:<br />passthrough: %s;<br>request: %s', $passthrough, print_r($_REQUEST, true) );
		// mail
		return @mgm_notify_admin(null, $subject, $message);
	}
	// error
	return false;
}

/**
 * Send Email Notification to Admin on ARB Creation failure
 *
 * @uses mgm_notify_admin()
 * @param string optional user_email
 * @param string @subject
 * @param string @message
 * @return bool @send
 */
function mgm_notify_admin_arb_creation_failed( $blogname, $post_data, $arb_data ){
	// subject
	$subject  = sprintf( '[%s] Admin Notification: Authorize.Net ARB Creation Failure(%s)', $blogname, $post_data['x_email'] );
	// message
	$message  = sprintf( 'Authorize.Net ARB Creation failed for the below user:<br/>ID: %s<br/>Email: %s<br/>MGM Transaction Id: %s<br/>
						  ARB Response: %s<br>', 
						 $post_data['x_cust_id'], $post_data['x_email'], $post_data['x_custom'], print_r($arb_data, true) );
	// send
	return @mgm_notify_admin(null, $subject, $message);
}

/**
 * Send Email Notification to Admin on IPN verification failed
 *
 * @uses mgm_notify_admin()
 * @param string $module
 * @return bool @send
 */
function mgm_notify_admin_ipn_verification_failed( $module ){		
	// subject		
	$subject = sprintf('Error in %s IPN verification', ucwords($module));
	// message
	$message = sprintf('Could not verify IPN:<br />post data: %s;', print_r($_POST, true) );
	// mail
	return @mgm_notify_admin(null, $subject, $message);	
}
// end file /core/libs/functions/mgm_email_functions.php					