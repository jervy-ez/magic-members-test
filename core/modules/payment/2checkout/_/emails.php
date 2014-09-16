<?php 
$blogname = get_option('blogname');
$tran_success = false;

//getting purchase post title and & price - issue #981				
$post_obj = mgm_get_post($post_id);
$purchase_cost     = mgm_convert_to_currency($post_obj->purchase_cost);
$post    = get_post($post_id);
$post_title   = $post->post_title;	

// emails not for guest
//issue #504
if($user_id){
	
	// subject
	$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
	// body
	$message = $system_obj->get_template('payment_success_email_template_body', 
							array('blogname'=>$blogname, 'name'=>$user->display_name, 
								  'post_title'=>$post_title,'purchase_cost'=>$purchase_cost, 
								  'email'=>$user->user_email, 
								  'admin_email'=>$system_obj->setting['admin_email']), true);
}


// emails not for guest
//issue #504
if($user_id){
	
	// subject
	$subject = $system_obj->get_template('payment_failed_email_template_subject', array('blogname'=>$blogname), true);
	 
	// body			
	$message = $system_obj->get_template('payment_failed_email_template_body', 
							array('blogname'=>$blogname, 'name'=>$user->display_name,
							      'post_title'=>$post_title,'purchase_cost'=>$purchase_cost,  
								  'email'=>$user->user_email, 'payment_type'=>'post purchase payment','reason'=>$status_str,
								  'admin_email'=>$system_obj->setting['admin_email']), true);
}	

// emails not for guest
//issue #504
if($user_id){
	
	// subject
	$subject = $system_obj->get_template('payment_pending_email_template_subject', array('blogname'=>$blogname), true);
	// body	
	$message = $system_obj->get_template('payment_pending_email_template_body', 
							array('blogname'=>$blogname, 'name'=>$user->display_name, 
							      'post_title'=>$post_title,'purchase_cost'=>$purchase_cost, 
								  'email'=>$user->user_email, 'reason'=>$status_str,
								  'admin_email'=>$system_obj->setting['admin_email']), true);
}

// emails not for guest
//issue #504
if($user_id){
	// subject
	$subject = $system_obj->get_template('payment_unknown_email_template_subject', array('blogname'=>$blogname), true);				
	// body	
	$message = $system_obj->get_template('payment_unknown_email_template_body', 
								array(	'blogname'=>$blogname, 'name'=>$user->display_name, 
										'post_title'=>$post_title,'purchase_cost'=>$purchase_cost, 
	                                    'email'=>$user->user_email, 'reason'=>$status_str,
	                                    'admin_email'=>$system_obj->setting['admin_email']), true);
}	

// notify user
if(!$dpne) {
	if($user_id && $this->send_payment_email($alt_tran_id)) {
		
		//issue #862
		$subject = mgm_replace_email_tags($subject,$user_id);
		$message = mgm_replace_email_tags($message,$user_id);
		
		mgm_mail($user->user_email, $subject, $message); //send an email to the buyer
		//update as email sent 
		$this->update_paymentemail_sent($alt_tran_id);
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
			$this->_set_purchased(NULL, $post_id, $guest_token, $alt_tran_id,$coupon_code);
		}else {
			$this->_set_purchased(NULL, $post_id, $guest_token, $alt_tran_id);				
		}
	}else{
		$this->_set_purchased($user_id, $post_id,NULL,$alt_tran_id);
	}
	// status
	$status = __('The post was purchased successfully', 'mgm');
}

// notify admin, only if gateway emails on
if (!$dge) {
	
	// not for guest
	if($user_id){
		$subject = "[" . $blogname . "] Admin Notification: " . $user->user_email . " purchased post " . $post_id;
		$message = "User display name: {$user->display_name}<br />User email: {$user->user_email}<br />
		            User ID: {$user->ID}<br />Status: " . $status . "<br />Action: Purchase post:" . $subject . "<br /><br />" . 
					$message . "<br /><br /><pre>" . print_r($_POST, true) . '</pre>';
	}else{
		$subject = "[" . $blogname . "] Admin Notification: Guest[IP: ".mgm_get_client_ip_address()."] purchased post " . $post_id;
		$message = "Guest Purchase";
	}
				
	mgm_mail($system_obj->setting['admin_email'], $subject, $message);
}

/*if(!isset($member->payment_info))	
			$member->payment_info    = new stdClass;
		$member->payment_info->module = $this->code;
		// message type
		if(isset($_POST['message_type'])){
			$member->payment_info->txn_type = $_POST['message_type'];
		}	
		// subscr_id 
		if(isset($_POST['order_number'])){
			$member->payment_info->subscr_id = $_POST['order_number'];		
		}elseif(isset($_POST['sale_id'])){		
			$member->payment_info->subscr_id = $_POST['sale_id'];	
		}	
		// txn_id
		if(isset($_POST['order_number'])){	
			$member->payment_info->txn_id = $_POST['order_number'];	
		}elseif(isset($_POST['sale_id'])){		
			$member->payment_info->txn_id = $_POST['sale_id'];	
		}	*/	

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
		//mail
		mgm_mail($user->user_email, $subject, $message);
		//update as email sent 
		$this->update_paymentemail_sent($alt_tran_id);	
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
			Subscription period: {$member->duration} ". $s_packs->get_pack_duration($subs_pack) ."\n\n<br />
			Subscription amount: {$member->amount} {$member->currency}\n<br />
			Payment Mode: {$member->payment_type}\n\n<br />
			POST Data was: \n\n<br /><br /><pre>" . print_r($_POST, true) . '</pre>';
	mgm_mail($system_obj->setting['admin_email'], $subject, $message);

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
		// eamil
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
}		
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

/**** deprecated
	// curl post
	function _curl_post($url, $post_fields=NULL, $auth, $http_header=array()){
		// init
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
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); 
		// post
		if($post_fields){
			curl_setopt($ch, CURLOPT_POST, 1); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
		curl_setopt($ch, CURLOPT_USERAGENT, 'Magic Members Membership Software'); 
		curl_setopt($ch, CURLOPT_REFERER, get_option('siteurl')); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// auth
		if($auth){
			curl_setopt($ch, CURLOPT_USERPWD, $auth);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}	
		// buffer
		$buffer = curl_exec($ch);
		curl_close($ch);		
		// return
		return $buffer;		
	}
	*/
if(!$dge){
	$message = 'Could not read membership type in the following POST data. Please debug or contact magic members to fix the problem making sure to pass on the following data. <br /><br /><pre>' . "\n\n" . print_r($_POST, true) . '</pre>';
	mgm_mail($system_obj->setting['admin_email'], 'Error in 2Checkout membership verification', $message);
}	