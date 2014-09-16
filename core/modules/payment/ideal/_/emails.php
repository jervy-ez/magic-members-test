<?php

$blogname = get_option('blogname');
		$tran_success = false;

		//getting purchase post title and & price - issue #981				
		$post_obj = mgm_get_post($post_id);
		$purchase_cost     = mgm_convert_to_currency($post_obj->purchase_cost);
		$post    = get_post($post_id);
		$post_title   = $post->post_title;		


$subject = sprintf(__('[%s] Payment receipt','mgm'),$blogname);
				// emails not for guest
				//issue #504
				if($user_id){
					$message = __('This is an automatic notification from %1$s to %2$s (%3$s).<br />This is a notification to inform you that your payment was successful and the post you purchased is now available to view.<br />For more information please contact %4$s','mgm');
					$message = sprintf($message, $blogname, $user->display_name, $user->user_email, $system_obj->setting['admin_email']);
				}

$subject = sprintf(__('[%s] Payment Failed','mgm'), $blogname);
				// emails not for guest
				//issue #504
				if($user_id){
					$message = __('This is an automatic notification from %1$s to %2$s (%3$s).<br />This is a notification to inform you that your post purchase failed for the following reason: %4$s.<br />For more information please contact %5$s.','mgm');
					$message = sprintf($message, $blogname, $user->display_name, $user->user_email, $status_str, $system_obj->setting['admin_email']);
				}	

// emails not for guest
				//issue #504
				if($user_id){
					$subject = "[" . $blogname . "] Payment receipt - Status Pending";
					$message = __('This is an automatic notification from %1$s to %2$s (%3$s).<br />This is a notification to inform you that you payment was received in pending status. Status: %4$s.<br />For more information please contact %5$s','mgm');
					$message = sprintf($message, $blogname, $user->display_name, $user->user_email, $status_str, $system_obj->setting['admin_email']);
				}


// emails not for guest
//issue #504
if($user_id){
	$subject = "[$blogname] Payment receipt";
	$message = __('This is an automatic notification from %1$s to %2$s (%3$s).<br />Status: %4$s.<br />For more information please contact %5$s','mgm');
	$message = sprintf($message, $blogname, $user->display_name, $user->user_email, $status_str, $system_obj->setting['admin_email']);
}

// notify user
if(!$dpne) {
	if($user_id && $this->send_payment_email($_REQUEST['trans_id'])) {
		//issue #862
		$subject = mgm_replace_email_tags($subject,$user_id);
		$message = mgm_replace_email_tags($message,$user_id);
		
		mgm_mail($user->user_email, $subject, $message); //send an email to the buyer
		//update as email sent 
		$this->update_paymentemail_sent($_REQUEST['trans_id']);	
	}
}			

if ($tran_success) {
	//issue #1421
	if($user_id){				
		do_action('mgm_update_coupon_usage', array('user_id' => $user_id));				
	}			
	
	// mark as purchased
	if(isset($guest_token)){
		// issue #1421
		if(isset($coupon_id) && isset($coupon_code)) {
			do_action('mgm_update_coupon_usage', array('guest_token' => $guest_token,'coupon_id' => $coupon_id));
			$this->_set_purchased(NULL, $post_id, $guest_token, $_REQUEST['trans_id'],$coupon_code);
		}else {
			$this->_set_purchased(NULL, $post_id, $guest_token, $_REQUEST['trans_id']);				
		}
	}else{
		$this->_set_purchased($user_id, $post_id, NULL, $_REQUEST['trans_id']);
	}
	// status
	$status = __('The post was purchased successfully', 'mgm');
}		

// notify admin, only if gateway emails on
if (!$dge) {
	// not for guest
	if($user_id){
		$subject = "[" . $blogname . "] Admin Notification: " . $user->user_email . " purchased post " . $post_id;
		$message = "User display name: {$user->display_name}<br />User email: {$user->user_email}<br />User ID: {$user->ID}<br />Status: " . 
					$status . "<br />Action: Purchase post:" . $subject . "<br /><br />" . $message . "<br /><br /><pre>" . print_r($_POST, true) . '</pre>';
	}else{
		$subject = "[" . $blogname . "] Admin Notification: Guest[IP: ".mgm_get_client_ip_address()."] purchased post " . $post_id;
		$message = "Guest Purchase";
	}
	mgm_mail($system_obj->setting['admin_email'], $subject, $message);
}	

if(!$dge){
	$message = 'Could not read membership type in the following POST data. Please debug or contact magic members to fix the problem making sure to pass on the following data. <br /><br /><pre>' . "\n\n" . print_r($_POST, true) . '</pre>';
	mgm_mail($system_obj->setting['admin_email'], 'Error in iDeal membership verification', $message);
}

switch ($new_status) {
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
				// init
				$subscription = '';
				// add trial 
				if ($subs_pack['trial_on']) {
					// trial
					$subscription = sprintf('%1$s %2$s for the first %3$s %4$s,<br> then ',$member->trial_cost, $member->currency, ($member->trial_duration * $member->trial_num_cycles), $s_packs->get_pack_duration($subs_pack,true)); 
				}
				// subject
				$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
				// on type
				if ($member->payment_type == 'subscription') {
					$payment_type = 'recurring subscription';
					$subscription .= sprintf('%1$s %2$s for each %3$s %4$s, %5$s',$member->amount,$member->currency, 
					                                                              $member->duration,$s_packs->get_pack_duration($subs_pack),
																				  ((int)$member->active_num_cycles > 0 ? sprintf('for %d installments',(int)$member->active_num_cycles):'until cancelled'));
				} else {
					$payment_type = 'one-time payment';
					$subscription .= sprintf('%1$s %2$s for %3$s %4$s',$member->amount, $member->currency, $member->duration,$s_packs->get_pack_duration($subs_pack));					
				}
				// body
				$message = $system_obj->get_template('payment_success_subscription_email_template_body', 
													array('blogname'=>$blogname, 'name'=>$user->display_name, 
														  'email'=>$user->user_email, 'payment_type'=>$payment_type,
														  'subscription'=>$subscription,'admin_email'=>$system_obj->setting['admin_email']), true);
				break;

			case MGM_STATUS_NULL:
				$subject = sprintf(__('[%s] Account inactive','mgm'), $blogname);
				$message = __('This is an automatic notification from %1$s to %2$s (%3$s). This is a notification to inform you that your account is inactive for the following reason: %4$s. For more information please contact %5$s','mgm');
				$message = sprintf($message, $blogname, $user->display_name, $user->user_email, $member->status_str, $system_obj->setting['admin_email']);
				break;

			case MGM_STATUS_PENDING:
				$subject = "[$blogname] Payment receipt - Status Pending";
				$message = __('This is an automatic notification from %1$s to %2$s (%3$s). This is a notification to inform you that you payment was received in pending status. Status: %4$s. For more information please contact %5$s','mgm');
				$message = sprintf($message, $blogname, $user->display_name, $user->user_email, $member->status_str, $system_obj->setting['admin_email']);
				break;

			case MGM_STATUS_ERROR:
				$subject = "[$blogname] Error on payment";
				$message = __('This is an automatic notification from %1$s to %2$s (%3$s). This is a notification to inform you that an error occurred while processing your payment. Details: %4$s. For more information please contact %5$s','mgm');
				$message = sprintf($message, $blogname, $user->display_name, $user->user_email, $member->status_str, $system_obj->setting['admin_email']);
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
				$this->update_paymentemail_sent($_REQUEST['trans_id']);	
			}
		}	

		// notify admin, only if gateway emails on
		if (!$dge && $acknowledge_user) {
			$subject = "[$blogname] {$user->user_email} - {$new_status}";
			$message = "	User display name: {$user->display_name}\n\n<br />
					User email: {$user->user_email}\n\n<br />
					User ID: {$user->ID}\n\n<br />
					Membership Type: {$membership_type}\n\n<br />
					New status: {$new_status}\n\n<br />
					Status message: {$member->status_str}\n\n<br />
					Subscription period: {$member->duration} ". $s_packs->get_pack_duration($subs_pack) ."\n\n<br />
					Subscription amount: {$member->amount} {$member->currency}\n<br />
					Payment Mode: {$member->payment_type}\n\n<br />
					POST Data was: \n\n<br /><br /><pre>" . print_r($_POST, true) . '</pre>';
			mgm_mail($system_obj->setting['admin_email'], $subject, $message);
		}										

// subject
		$subject = $system_obj->get_template('subscription_cancelled_email_template_subject', array('blogname'=>$blogname), true);				
		// t
		$tdata = array('blogname'=>$blogname, 'name'=>$user->display_name,'email'=>$user->user_email, 'admin_email'=>$system_obj->get_setting('admin_email'));
		// body	
		$message = $system_obj->get_template('subscription_cancelled_email_template_body', $tdata, true);// erturn								  
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
					Payment Mode: Cancelled\n\n<br />";
			mgm_mail($system_obj->setting['admin_email'], $subject, $message);
		}		

$user = get_userdata($user_id);
			//send notification email to admin:
			$message = (__('The User: ', 'mgm')). $user->user_email.' ('. $user_id .') '.(__('has upgraded subscription.', 'mgm'));
			$message .= "<br/>" .__('Please unsubscribe the user from Gateway Merchant panel using the below details.', 'mgm');
			if($subscr_id)
				$message .= "<br/><br/>" .__('Subscription Id: ','mgm' ) . $subscr_id;	
			if(isset($member->transaction_id))			
				$message .= "<br/>" .__('MGM Transaction Id:' ,'mgm' ) . $member->transaction_id;		
			//admin email:
			if(!empty($system_obj->setting['admin_email']))
				@mgm_mail($system_obj->setting['admin_email'], sprintf(__('[%s] User Subscription Cancellation'), get_option('blogname')), $message);
					