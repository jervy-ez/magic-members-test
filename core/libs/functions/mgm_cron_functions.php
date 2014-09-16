<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members cron callback functions
 *
 * @package MagicMembers
 * @since 2.5
 */

/**
 * add new schedules
 */
function mgm_add_cron_schedules($schedules){
	// return
	/*
	$new_schedules = array(		
		'every5minute'  => array( 'interval' => 300,  'display' => __('Every 5 Minutes', 'mgm') ),		
		'every10minute' => array( 'interval' => 600,  'display' => __('Every 10 Minutes', 'mgm') ),			
		'every15minute' => array( 'interval' => 900,  'display' => __('Every 15 Minutes', 'mgm') ),
		'every30minute' => array( 'interval' => 1800, 'display' => __('Every 30 Minutes', 'mgm') )
	);
	*/
	// new
	$new_schedules =  array(
		'every2ndhourly' => array( 'interval' => (2 * HOUR_IN_SECONDS), 'display' => __( 'Every 2nd Hourly' ) )
	);			 
	// return merged
	return array_merge($schedules, $new_schedules);	
}

/**
 * Magic Members cron process daily schedule, once daily
 *
 * @package MagicMembers
 * @since 2.5
 * @param none
 * @return none
 */ 
function mgm_process_daily_schedule(){			   
	// object
	$sch = mgm_get_class('schedular');		
	// add
	$sch->add_schedule('daily','reminder_mailer');// reminder mails, run on limited billing user only
	// run
	$sch->run('daily');
}

/**
 * Magic Members cron process twicedaily schedule, twice daily
 *
 * @package MagicMembers
 * @since 2.5
 * @param none
 * @return none
 */ 
function mgm_process_twicedaily_schedule(){			   
	// object
	$sch = mgm_get_class('schedular');		
	// add
	$sch->add_schedule('twicedaily', 'rebill_status_check');// rebill status check, run on rebill status module users only
	// run
	$sch->run('twicedaily');
}

/**
 * Magic Members cron process hourly schedule, once per hour
 *
 * @package MagicMembers
 * @since 2.5
 * @param none
 * @return none
 */ 
function mgm_process_hourly_schedule(){		
	// object
	$sch = mgm_get_class('schedular');	
	// add 		
	$sch->add_schedule('hourly','ongoing_membership_extend');// ongoing extend
	// add
	$sch->add_schedule('hourly', 'epoch_dataplus_transactions'); // epoch dataplus
	// add , moved to hourly transient cache	
	// $sch->add_schedule('hourly','update_widget_data');// recalculate and update widget data
	// add 		
	$sch->add_schedule('hourly','update_transaction_data');// update missing transaction data
	// run
	$sch->run('hourly');
}

/**
 * Magic Members cron process every 2nd hourly schedule, once per hour
 *
 * @package MagicMembers
 * @since 2.5
 * @param none
 * @return none
 */ 
function mgm_process_every2ndhourly_schedule(){	
	// object
	$sch = mgm_get_class('schedular');
	// add 		
	$sch->add_schedule('every2ndhourly','limited_membership_extend');// limited extend	
	// run
	$sch->run('every2ndhourly');
}

/**
 * Magic Members cron process 5 minutes schedule,once every 5 min
 * disabled due to overload
 *
 * @package MagicMembers
 * @since 2.5
 * @param none
 * @return none
 */ 
function mgm_process_every5minute_schedule(){	
	// schedular
	// $sch = mgm_get_class('schedular');
	// add
	// $sch->add_schedule('every5minute', 'rebill_status_check');
	// add test
	// $sch->add_schedule('every5minute', 'email_me');
	// run schedules task
	// $sch->run('every5minute');
}

/**
 * Magic Members cron process 10 minutes schedule, once every 10 min
 * disabled due to overload
 *
 * @package MagicMembers
 * @since 2.5
 * @param none
 * @return none
 */ 
function mgm_process_every10minute_schedule(){	
	// schedular
	// $sch = mgm_get_class('schedular');
	// add
	// $sch->add_schedule('every10minute', 'rebill_status_check');
	// run schedules task
	// $sch->run('every5minute');
}

/**
 * Magic Members cron process 15 minutes schedule, once every 15 min
 * disabled due to overload
 *
 * @package MagicMembers
 * @since 2.5
 * @param none
 * @return none
 */ 
function mgm_process_every15minute_schedule(){	
	// schedular
	// $sch = mgm_get_class('schedular');
	// add
	// $sch->add_schedule('every15minute', 'epoch_dataplus_transactions');
	// add
	// $sch->add_schedule('every15minute', 'rebill_status_check');
	// run schedules task
	// $sch->run('every15minute');
}	

/**
 * Magic Members cron process 30 minutes schedule, once every 30 min
 * disabled due to overload
 *
 * @package MagicMembers
 * @since 2.5
 * @param none
 * @return none
 */ 
function mgm_process_every30minute_schedule(){	
	// schedular
	// $sch = mgm_get_class('schedular');		
	// add 		
	// $sch->add_schedule('every30minute','reset_expiration');// reset expire date
	// run schedules task
	// $sch->run('every30minute');
}	

/* Event calbacks */

/**
 * check expiring memberships, processes only active members with limited cycles  
 * primarily used to send reminder emails
 * 
 * @param void
 * @return void
 */
function mgm_check_expiring_memberships(){
	global $wpdb;
	// system 
	$system_obj = mgm_get_class('system');		
	// packs
	$spacks_obj = mgm_get_class('subscription_packs');	
	// types
	$mtypes_obj = mgm_get_class('membership_types');
	// days_to_start	
	$data['days_to_start'] = (int)$system_obj->get_setting('reminder_days_to_start');	
	// if greater
	if($data['days_to_start'] > 0 ){				
		// settings
		$data['days_incremental'] = $system_obj->get_setting('reminder_days_incremental');
		$data['days_incremental_ranges'] = preg_split('/[,;]/', $system_obj->get_setting('reminder_days_incremental_ranges'));			
		// flag
		$paged_fetch = false;
		//meta query
		$meta_query = array();	
		//loop
		foreach($packages = mgm_get_subscription_packages() as $pack) {		
			$meta_query[] = array(array('key'=>'_mgm_user_status','value'=>MGM_STATUS_ACTIVE,'compare'=>'='),
								  array('key'=>sprintf('mgm_user_billing_num_cycles_%d', $pack['id']),'value'=>'ongoing','compare'=>'!='));
		}		
		//users
		$a_users = array();
		//loop	
		foreach($meta_query as $key) {
			// fetch on meta membership level wise.
			$a_users =  mgm_get_users_with_meta($key, null, null, null); //'OR' );
			// current time	
			$current_date = mgm_get_current_datetime('Y-m-d H:i:s');// with time part #1023 issue	
			// template
			$data['template_subject'] 	= mgm_stripslashes_deep($system_obj->get_template('reminder_email_template_subject', array(), true));
			$data['template_body'] 		= mgm_stripslashes_deep($system_obj->get_template('reminder_email_template_body', array(), true));				
			$data['current_timestamp']  = $current_date['timestamp'];																	
			$data['subscription_types'] = $mtypes_obj->membership_types;
			$checked = $offset = 0;			
			// loop
			foreach($a_users as $user) {
				// set limit
				@set_time_limit(300);//300s
				@ini_set('memory_limit', 134217728);// 128M
				// get member
				$member = mgm_get_member($user->ID);
				// check individual														
				mgm_check_expiring_member($user, $member, $spacks_obj, $data, $system_obj);					
				// check other memberships as well 
				if(isset($member->other_membership_types) && !empty($member->other_membership_types) ) {
					// loop
					foreach ($member->other_membership_types as $key => $val) {
						// convert
						if(is_array($val) ) $val = mgm_convert_array_to_memberobj($val, $user->ID);
						// check individual
						if(isset($val->membership_type) && !empty($val->membership_type) && !in_array($val->membership_type, array('guest'))) { //skip if default value	
							mgm_check_expiring_member($user, $val, $spacks_obj, $data, $system_obj);
						}
					}
				}
				// unset
				unset($member);
				unset($user);		
				// increment
				$offset++;	
			}
			//a small delay of 0.01 second 	
			usleep(10000);	
		}		
		// update
		if( $paged_fetch ){
			mgm_get_users_for_cron_check('update', 'expiring_memberships', $offset);
		}
	}	
}
//patch for onetime expiring memberships - Depricated
function mgm_patch_partial_onetime_members($start,$limit){
	global $wpdb;
	$sql = "SELECT DISTINCT SQL_CALC_FOUND_ROWS {$wpdb->users}.ID, {$wpdb->users}.display_name, {$wpdb->users}.user_email, ";
	$sql .= "(SELECT t.meta_value FROM {$wpdb->usermeta} t WHERE t.user_id = {$wpdb->users}.ID AND t.meta_key =  'mgm_member_options') mgm_member_options ";
	$sql .= "FROM {$wpdb->users} ";
	$sql .= "INNER JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) ";
	$sql .= "INNER JOIN {$wpdb->usermeta} AS mt1 ON ( {$wpdb->users}.ID = mt1.user_id )  ";
	$sql .= "WHERE 1 =1 AND (({$wpdb->usermeta}.meta_key =  '_mgm_user_status' AND CAST( {$wpdb->usermeta}.meta_value AS CHAR ) =  '".MGM_STATUS_ACTIVE."') ";
	$sql .= "AND (mt1.meta_key =  '_mgm_user_billing_num_cycles' AND CAST( mt1.meta_value AS CHAR ) !=  'ongoing')) ORDER BY user_login ASC LIMIT {$start} , {$limit}";
	$result  = $wpdb->get_results($sql);	
	return $result;	
}
/**
 * recursively check each member object of a user
 *
 * @param object $user
 * @param object $member
 * @param array $packs
 * @param array $data
 * @return bool $has_sent
 */
function mgm_check_expiring_member($user, $member, $spacks_obj, $data, $system_obj) {			
	// only check for Active members
	if($member->status != MGM_STATUS_ACTIVE) return;
	
	// flag
	$has_sent = false;
	
	// check pack		
	$subs_pack = null;			
	// get pack			
	if($member->pack_id){
		$subs_pack = $spacks_obj->get_pack($member->pack_id);
	}/*else{
		$subs_pack = $spacks_obj->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
	}*/
	
	// check empty
	if(empty($subs_pack)){
		$subs_pack = $spacks_obj->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
	}
	
	// log
	// mgm_log('subs_pack'. mgm_pr($subs_pack, true), ($user->ID.'_'.__FUNCTION__));
	
	// check on going
	if(isset($subs_pack['id'])){
		// issue#: 478
		$num_cycles = (isset($member->active_num_cycles) && !empty($member->active_num_cycles)) ? $member->active_num_cycles : $subs_pack['num_cycles'] ;
		// ongoing / lifetime
		if( $num_cycles == 0 ) {			
			// never send mail
			return false;	
		}elseif($num_cycles > 1 || $member->duration_type == 'l'){	// why lifetime included here?			
		// allow onetime subscriptions
			// if already unsubscribed 
			if(isset($member->status_reset_as) && in_array($member->status_reset_as, array(MGM_STATUS_AWAITING_CANCEL,MGM_STATUS_CANCELLED) )){
				// set expire date
				if(empty($member->expire_date)) $member->expire_date = $member->status_reset_on;// fishy @todo check 
				// let it send
			}elseif($member->duration_type == 'l' || (!isset($member->rebilled) || ($member->rebilled <= ($num_cycles - 1 )))){		
			// send email at the end of the subscription			
				return false;
			}
		}
	}		
	
	// expire date		
	$expire_date    = $member->expire_date;				
	$date_diff      = strtotime($expire_date) - $data['current_timestamp'];				
	$days_to_expire = floor($date_diff/(60*60*24));	
	
	// log
	/*mgm_log(sprintf('reminder email: user: %d, days_to_expire: %d, expire_date: %s, current_date: %s', 
			$user->ID, $days_to_expire, $expire_date, date('Y-m-d H:i:s', $data['current_timestamp'])), __FUNCTION__);*/
	
	$email_data = array();
	$email_data['expire_date'] 		 = $expire_date;					
	$email_data['subscription_type'] = mgm_stripslashes_deep($data['subscription_types'][$member->membership_type]);
	$email_data['template_subject']  = $data['template_subject'];						
	$email_data['template_body'] 	 = $data['template_body'];					
				
	// days match					 
	if($days_to_expire == $data['days_to_start']){					
		// send mail			
		$has_sent = mgm_send_reminder_mail($user, $email_data, $system_obj);								
	}else{
	// incremental
		if( bool_from_yn($data['days_incremental']) && is_array($data['days_incremental_ranges'])){
			// loop			
			foreach($data['days_incremental_ranges'] as $range){
				// get int
				$range = (int)$range;									
				// if days match
				if($range > 0){
					// match
					if($days_to_expire == $range){							
						// send mail							
						$has_sent = mgm_send_reminder_mail($user, $email_data, $system_obj);	
					}																							
				}
			}
		}
	}
	
	// return
	return $has_sent;
}

/**
 * send reminder mail
 *
 * @param object $user
 * @param array $data
 * @return bool $has_sent
 */
function mgm_send_reminder_mail($user, $data, $system_obj){
	// format date	
	// issue#: 692
	$sformat = mgm_get_date_format('date_format_long_time2');
	// $expire_date_fmt = date($sformat,strtotime($data['expire_date']));
	// Issue #1178
	$expire_date_fmt = mgm_translate_datestring($data['expire_date'], $sformat);
	
	// $expire_date_fmt = date('m-d-Y',strtotime($data['expire_date']));
	
	// mail body
	$data['template_body'] = str_replace(array('[name]','[expire_date]','[subscription_type]'), array($user->display_name, $expire_date_fmt, $data['subscription_type']), $data['template_body']);	

	//issue #862
	$data['template_subject'] = mgm_replace_email_tags($data['template_subject'],$user->ID);
	$data['template_body'] = mgm_replace_email_tags($data['template_body'],$user->ID);
	
	// @todo add filter
	$template = apply_filters('mgm_reminder_email_template', array('subject'=>$data['template_subject'],'body'=>$data['template_body']));

	// send mail
	return @mgm_mail($user->user_email, $template['subject'], $template['body']);
	// log
	// mgm_log('Sending Reminder email:UID:' .$user->ID. ": Date:". date('Y-m-d H:i:s'), __FUNCTION__ );		
}

/**
 * check ongoing memberships, processes ongoing members cancel or expire where rebill status query
 * not supported or failed
 *
 * @param void
 * @return void
 */
function mgm_check_ongoing_memberships(){
	global $wpdb;	
	// meta query, fetch ongoing members only
	$meta_query = array();	
	//membership level wise
	foreach($packages = mgm_get_subscription_packages() as $pack) {
		$meta_query[] = array('key'=>sprintf('mgm_user_billing_num_cycles_%d', $pack['id']),'value'=>'ongoing','compare'=>'=');
	}
	//users
	$a_users = array();	
	foreach($meta_query as $key) {
		//mgm_log("_key ".print_r($key,true),__FUNCTION__);
		// fetch on meta
		$a_users =  array_merge($a_users, mgm_get_users_with_meta( array($key), null, null, null)); //'OR' );
	}
	// log
	//mgm_log($a_users, __FUNCTION__);
	// process 
	mgm_check_memberships_to_extend($a_users);		
}

/** 
 * check each mgm_member object
 */
function mgm_check_ongoing_member($user, $member, $spacks_obj, $data, $other_purchases = false, $process_inactive_users=false) {	
	// fix rebilled missing
	if( !isset($member->rebilled) ){
		$member->rebilled = 0;			
	}		
	
	// PROCESS CANCEL---------------------------------------------------------------------	
	$member = mgm_ongoing_member_canceled($user, $member, $spacks_obj, $data, $other_purchases);		
	
	// PROCESS EXPIRY --------------------------------------------------------------------
	$member = mgm_ongoing_member_expired($user, $member, $spacks_obj, $data, $other_purchases, $process_inactive_users);				
	
	// return
	return $member;
}

/**
 * ongoing member canceled
 */
function mgm_ongoing_member_canceled($user, $member, $spacks_obj, $data, $other_purchases = false){
	// MARK status reset for manual pay upgrade
	if(isset($member->status_reset_on) && !is_null($member->status_reset_on)) {			
		// date match
		if( $member->status_reset_on == $data['current_date']) {				
			// manual pay
			if($member->payment_info->module == 'mgm_manualpay'){						
				// set as pending again
				$member->status = MGM_STATUS_PENDING;
			}else {					
			// other 
				// set as cancelled or whatever set in "reset_as"
				$member->status = $member->status_reset_as;
				// expire date if cancelled
				if($member->status_reset_as == MGM_STATUS_CANCELLED){
					// expire on 
					$member->expire_date = $data['current_date'];
					// reassign expiry membership pack if exists: issue#: 535
					$member = apply_filters('mgm_reassign_member_subscription', $user->ID, $member, 'CANCEL', true);						
				}	
			}
			
			// save multiple level subscription
			if($other_purchases){
				mgm_save_another_membership_fields($member, $user->ID);
			}else{	
				// update
				$member->save();
			}					
			// recapture
			if($other_purchases){
				$member = mgm_get_member_another_purchase($user->ID, $member->membership_type);
			}else{ 	
				$member = mgm_get_member($user->ID);
			}
		}	
	} 
	// return
	return $member;
}

/**
 * ongoing member expired
 */
function mgm_ongoing_member_expired($user, $member, $spacks_obj, $data, $other_purchases = false, $process_inactive_users=false){
	// only check for Active members iss#645
	// if($member->status != MGM_STATUS_ACTIVE) return $member;
	
	// find expire date	
	$expire_date = $member->expire_date;
	
	// duration exprs
	$duration_exprs = $spacks_obj->get_duration_exprs();
	
	// active lifetime user:
	// $non_date_expr  = array('l','dr');
	$non_date_expr = array_keys($spacks_obj->get_duration_types('non_date_expr'));
	// log
	// mgm_log('non_date_expr: '. print_r($non_date_expr, true), __FUNCTION__);		
	// exit lifetime users
	if(in_array($member->duration_type, $non_date_expr) || empty($expire_date)){ 
		// before exit update life time users if wrongly processed earlier
		if($member->duration_type == 'l' && $member->status == MGM_STATUS_EXPIRED){
			// set new status
			$member->status = MGM_STATUS_ACTIVE;
			// status string
			$member->status_str = __('Life time membership extended','mgm');
			// save
			if($other_purchases){
				mgm_save_another_membership_fields($member, $user->ID);
			}else {								
				$member->save();
			}
			// log
			// mgm_log(sprintf('life time member %d reactivated: %s', $user->ID, $member->status), __FUNCTION__);
		}			
		// exit
		return $member; // lifetime and date_range				
	}	
	// check on expire date
	// days to expire
	$date_diff        = strtotime($expire_date) - $data['current_timestamp'];				
	$days_to_expire   = floor($date_diff/(60*60*24));	
	$days_to_expire2  = abs($date_diff/(60*60*24));	
	$date_format_time = mgm_get_date_format('date_format_time');
	// log
	/*mgm_log(sprintf('expire users: user: %d, days_to_expire1: %d, days_to_expire2: %d, expire_date: %s, current_date: %s', 
			$user->ID, $days_to_expire, $days_to_expire2, $expire_date, date('Y-m-d H:i:s', $data['current_timestamp'])), (__FUNCTION__ . '_user_' . $user->ID));*/

	// days match, support for expired check, negative days		
	// commented the below line because the membership was expiring one day early.		
	if($days_to_expire <= 0) {			
		// check pack
		$subs_pack = array();			
		// get pack						
		if( isset($member->pack_id) && (int)$member->pack_id > 0 ){
			$subs_pack = $spacks_obj->get_pack($member->pack_id);
		}
		// check pack
		if(empty($subs_pack)){
		// fetch from old data
			$subs_pack = $spacks_obj->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
		}				
		// ok
		if(isset($subs_pack['id'])) {
			//issue#: 478
			$num_cycles = (isset($member->active_num_cycles) && !empty($member->active_num_cycles)) ? $member->active_num_cycles : $subs_pack['num_cycles'] ;
			// lifetime ongoing/fixed cycle ongoing										
			// issue #: 418
			if( $num_cycles == 0 /*Ongoing*/ || ($num_cycles > 1 && (int)$member->rebilled <= (int)$num_cycles) /*fixed cycles*/) {	// 100 to dynamic											
				// make sure scheduler considers it after expiry date only:
				if( $days_to_expire2 >= 1 && $days_to_expire < 0) {	//This will check the next day after expiry date to consider payments happened on expiry date as well 																	
					// member reached rebill cycle, expire now
					// set expired status
					if($num_cycles > 1 && (int)$member->rebilled == $num_cycles ) {
						// old status
						$old_status = $member->status;
						// set new status
						$member->status = $new_status = MGM_STATUS_EXPIRED;	
						// set status
						$member->status_str = sprintf(__('Membership expired on %s - rebill cycle completed','mgm'), date($date_format_time, $data['current_timestamp']));
						// reassign expiry membership pack if exists: issue#: 535
						$member = apply_filters('mgm_reassign_member_subscription', $user->ID, $member, 'EXPIRE', true);							
					}else {							
						// #767, for ongoing membership, set Inactive members as Active, depends on settings
						if($num_cycles == 0){// change, checking all status to allow expire date extend for Active users
							// paid users
							if( !empty($member->last_pay_date) ){
								// change status for Inactive only
								if($member->status == MGM_STATUS_NULL){// inactive
									// only when set to process inactive user	
									if(  $process_inactive_users == true ){
										// old status
										$old_status = $member->status;	

										// set new status	
										$member->status = $new_status = MGM_STATUS_ACTIVE;	

										// status string	
										$member->status_str = __('Last payment cycle processed successfully','mgm');
									}
																				
								}elseif( $member->status == MGM_STATUS_ACTIVE ){// active										
									// current cycle expire date							
									if(!empty($member->expire_date)){	
										// date add
										$date_add = sprintf('+ %d %s', $subs_pack['duration'], $duration_exprs[$subs_pack['duration_type']]);
										// new expire date, calc by cycle so pack is used, 
										$new_expire_date = mgm_get_current_rebill_cycle_expiry_date($member->expire_date, $date_add);	
										// expire date should not overlap, #1223
										if(strtotime($new_expire_date) > strtotime($member->expire_date)){
											// set
											$member->expire_date = $new_expire_date;													
											// last pay
											if(!empty($member->last_pay_date)){
												$member->last_pay_date = date('Y-m-d', strtotime(str_replace('+','-',$date_add), strtotime($member->expire_date)));
											}
											
											// status string	
											$member->status_str = __('Last payment cycle processed successfully - membership expire extended using pack','mgm');
										}		
									}	
								}	// end paid active
							}else{
							// unpaid users
								// old status
								if ( $member->status != MGM_STATUS_NULL ){
									// copy
									$old_status = $member->status;
									// set new status
									$member->status = $new_status = MGM_STATUS_NULL;	
									// set status
									$member->status_str = __('Last payment incomplete','mgm');	
								}
							}// end unpaid
						}// end	ongoing																			
					}
					
					// update member							
					if($other_purchases){
						mgm_save_another_membership_fields($member, $user->ID);
					}else {								
						$member->save();
					}
					
					// action
					if(isset($new_status)){
						do_action('mgm_user_status_change', $user->ID, $new_status, $old_status, 'schedular_reset_ongoing_memberships', $member->pack_id);
					}
				}
			}elseif( (int)$num_cycles == 1 && $data['current_timestamp'] > strtotime($expire_date)) { //one-time billing,double check for date calc bug #1126 	 	
				// old status
				$old_status = $member->status;
				// set new status			
				$member->status = $new_status = MGM_STATUS_EXPIRED;						
				// set status
				$member->status_str = sprintf(__('Membership expired on %s - onetime billing completed','mgm'), date($date_format_time, $data['current_timestamp']));
				// reassign expiry membership pack if exists: issue#: 535
				$member = apply_filters('mgm_reassign_member_subscription', $user->ID, $member, 'EXPIRE', true);							
				// update member		
				if($other_purchases){
					mgm_save_another_membership_fields($member, $user->ID);
				}else{	
					$member->save();
				}	
				// remove role from user:
				mgm_remove_userroles($user->ID, $member);
				// action
				do_action('mgm_user_status_change', $user->ID, $new_status, $old_status, 'schedular_reset_ongoing_memberships', $member->pack_id);						
			}							
		}else{					
		// pack not found manual update, expire users if days negative
			if(empty($subs_pack) && $days_to_expire <= 0){
				// old status
				$old_status = $member->status;
				// set new status						
				$member->status = $new_status = MGM_STATUS_EXPIRED;	
				// set status
				$member->status_str = sprintf(__('Membership expired on %s - no pack found','mgm'), date($date_format_time, $data['current_timestamp']));	
				// update member		
				if($other_purchases){
					mgm_save_another_membership_fields($member, $user->ID);
				}else{	
					$member->save();
				}	
				// remove role from user:
				mgm_remove_userroles($user->ID, $member);	
				// action
				do_action('mgm_user_status_change', $user->ID, $new_status, $old_status, 'schedular_reset_ongoing_memberships', $member->pack_id);	
			}					
		}									
	}
	
	// return
	return $member;
}

/**
 * check limited memberships, processes limited members cancel or expire where rebill status query
 * not supported or failed
 *
 * @param void
 * @return void
 */
function mgm_check_limited_memberships(){
	global $wpdb;
	// meta query, to fetch active and awaiting cancel for limited billing only 
	$meta_query = array();
	//loop
	foreach($packages = mgm_get_subscription_packages() as $pack) {		
		$meta_query[] = array(array('key'=>'_mgm_user_status','value'=> array(MGM_STATUS_ACTIVE,MGM_STATUS_AWAITING_CANCEL),'type'=> 'CHAR','compare'=>'IN'),
							  array('key'=>sprintf('mgm_user_billing_num_cycles_%d', $pack['id']),'value'=>'ongoing','compare'=>'!='));
	}	
	//users
	$a_users = array();
	//loop	
	foreach($meta_query as $key) {
		// fetch on meta
		$a_users =  array_merge($a_users, mgm_get_users_with_meta($key, null, null, null)); //'OR' );
	}
	// process 
	mgm_check_memberships_to_extend($a_users);	
}

/**
 * check memberships to extend/expire
 *
 * @params array $users
 * @return int $offset
 */
function mgm_check_memberships_to_extend($a_users){
	// found
	if(count($a_users) > 0){	
		// current date: timezone formatted
		$current_date = mgm_get_current_datetime('Y-m-d H:i:s');// with time part #1023 issue		
		// packs
		$spacks_obj = mgm_get_class('subscription_packs');	
		// process_inactive_users
		$process_inactive_users = bool_from_yn(mgm_get_class('system')->get_setting('enable_process_inactive_users'));			
	
		// set
		$data['current_date'] 	   = $current_date['date'];	
		$data['current_timestamp'] = $current_date['timestamp'];								
		$data['duration_exprs']    = $spacks_obj->get_duration_exprs();		
		$checked = $offset = 0;		
		// loop
		foreach($a_users as $user) {		
			// set limit
			@set_time_limit(300);//300s
			@ini_set('memory_limit', 134217728);// 128M
			//mem		
			// get member
			$member = mgm_get_member($user->ID);		
			// reset primary subscripton		
			mgm_check_ongoing_member($user, $member, $spacks_obj, $data, false, $process_inactive_users);				
			// check other memberships as well 
			if(isset($member->other_membership_types) && is_array($member->other_membership_types) && !empty($member->other_membership_types) ) {
				// loop
				foreach ($member->other_membership_types as $key => $val) {
					// convert
					$val = mgm_convert_array_to_memberobj($val, $user->ID);
					// check
					if(isset($val->membership_type) && !empty($val->membership_type) && !in_array($val->membership_type, array('guest'))) { //skip if default value							
						mgm_check_ongoing_member($user, $val, $spacks_obj, $data, true, $process_inactive_users);
					}
				}
			}
			// increment
			$offset++;
		}
		// return
		return $offset;
	}	
	// return
	return 0;
}

/**
 * check membership rebill status
 * @todo should only run on users who are using a payment module with rebill support
 * add _mgm_module_has_rebill_status_check='Y' in usermeta
 */
function mgm_check_membership_rebill_status(){
	global $wpdb;	
	// flag
	$paged_fetch = false;
	// check user with meta only
	// if( ($a_users = mgm_get_users_with_meta('_mgm_module_has_rebill_status_check','Y')) === FALSE ){
	// 		// old fetch
	// 		// $a_users = mgm_get_users_for_rebill_status_check('fetch');
	// 		$a_users = mgm_get_users_for_cron_check('fetch', 'rebill_status');
	// 		// flag
	// 		$paged_fetch = true;
	// 	}
	// fetch on meta, with rebill check module only
	$a_users = mgm_get_users_with_meta('_mgm_module_has_rebill_status_check','Y');	
	// current time	
	$current_date = mgm_get_current_datetime('Y-m-d H:i:s');// with time part #1023 issue	
	// log
	// mgm_log($a_users, __FUNCTION__);
	// loop
	if($a_users){			
		// objects
		$spacks_obj = mgm_get_class('subscription_packs');	
		// $mtypes_obj = mgm_get_class('membership_types');	
		$duration_exprs = $spacks_obj->get_duration_exprs();	
		$checked = $offset = 0;
		// define
		if(!defined('DOING_QUERY_REBILL_STATUS')) define('DOING_QUERY_REBILL_STATUS', 'cron');
		// loop
		foreach($a_users as $user){		
			// set limit
			@set_time_limit(300);//300s
			@ini_set('memory_limit', 134217728);// 128M	
			// get member
			$member = mgm_get_member($user->ID);
			// get rebill cycle				
			if((int)$member->pack_id>0){			
				// get member subscribed  pack
				$pack = $spacks_obj->get_pack($member->pack_id); 					
				// member pack cycle
				$num_cycles = (isset($member->active_num_cycles) && !empty($member->active_num_cycles)) ? (int)$member->active_num_cycles : (int)$pack['num_cycles'] ;
				// lifetime ongoing/fixed cycle ongoing			
				if( $num_cycles == 0 /*Ongoing*/ || ($num_cycles > 1 && (int)$member->rebilled < (int)$num_cycles) /*fixed cycles*/) {	// 100 to dynamic																
					// when current date is later than expire date, we will run rebill check after expire date, not before
					// payment status available after scheduled transaction date
					if( strtotime($current_date['date']) > strtotime($member->expire_date) ) {
						// check already run
						if(!isset($member->last_payment_check_date) || (isset($member->last_payment_check_date) && $current_date['date'] != $member->last_payment_check_date)){						
							// apply rebill filter
							if(apply_filters('mgm_module_rebill_status', $user->ID, $member)){									
								// success
								$checked++;							
							}			
						}				
						// update type
						mgm_update_payment_check_state($user->ID, 'cron');						
					}	
				}	
			}									
			// unset
			unset($member);
			// increase $offset
			$offset++;
		}
	}
	
	// update
	if( $paged_fetch ){
		// mgm_get_users_for_rebill_status_check('update', $offset);
		mgm_get_users_for_cron_check('update', 'rebill_status', $offset);
	}	
}



/**
 * update epoch transactions data
 */
function mgm_epoch_update_dataplus_transactions(){
	// current
	$current_date = mgm_get_current_datetime('Y-m-d H:i:s');// with time part #1023 issue
	
	// module		
	$epoch = mgm_get_module('epoch', 'payment');
	
	// if active
	if( $epoch->is_enabled() ){
		// check and update transactions:
		$epoch->update_dataplus_transactions();
		
		// check and update transactions:
		$epoch->update_dataplus_cancellations();
	}
}

/**
 * Update dashboard widget data
 * @deprecated kept for reference
 */
function mgm_update_dashboard_widget_data(){
	// set
	// $widget_data = mgm_set_dashboard_widget_data();
	// Save as an option
	// update_option('mgm_widget_data', $widget_data);
}

/**
 * update transactions missing data
 */
function mgm_update_transaction_data(){
	// db
	global $wpdb;
	// 
	// sql
	$sql = "SELECT id,user_id,module,data,transaction_dt FROM `".TBL_MGM_TRANSACTION."` 
	         WHERE 1 AND (`user_id` IS NULL OR `user_id` = 0) AND module IS NOT NULL LIMIT 0, 50";
	//  check missing user_id
	if( $transactions = $wpdb->get_results($sql) ){
		// log
		// mgm_log($transactions, __FUNCTION__);
		// loop
		foreach($transactions as $transaction){
			// pack
			$pack = json_decode($transaction->data, true);
			// check
			if(isset($pack['user_id']) && (int)$pack['user_id'] > 0){
				// id
				$user_id = $pack['user_id'];
				// update
				$wpdb->update(TBL_MGM_TRANSACTION, array('user_id'=>$user_id), array('id'=>$transaction->id) );
				// log
				mgm_log($wpdb->last_query, __FUNCTION__);
			}		
		}
	}
	
	// authorizenet module		
	$authorizenet = mgm_get_module('authorizenet', 'payment');

	// if active
	if( $authorizenet->is_enabled() ){
		// fetch module transactions
		// mgm_log('Enabled', __FUNCTION__);
		// 1699,1700,1711,1712,1714,1716,1718,1721,1725,1734,1735,1724,1722
		mgm_fetch_authorizenet_missing_txn_id($authorizenet);
	}
}

/**
 * fetch authorizenet missing txn id
 *
 * @param object $authorizenet
 * @param array $user_ids 
 * @return void
 * @since 2.7 
 */
function mgm_fetch_authorizenet_missing_txn_id($authorizenet, $user_ids=null){
	// db
	global $wpdb;
	// for
	$where = "AND B.option_value = ''";
	// users
	if(is_null($user_ids)){
	// fetch first 10	
		 $and_where = $where . " ORDER BY transaction_dt DESC LIMIT 0, 10";
	}elseif( is_array($user_ids) && !empty($user_ids)){
	// fetch as requested	
		$and_where = $where . " AND `user_id` IN(" . mgm_map_for_in($user_ids) . ")";
	}elseif( (int)$user_ids > 0 ){
	// fetch one	
		$and_where = "AND `user_id` = '" . (int)$user_ids . "'";
	}
	// sql
	$sql = "SELECT `A`.`id`,user_id,data,transaction_dt,`B`.`id` AS `option_id`,`B`.`option_name`,`B`.`option_value` 
			FROM `".TBL_MGM_TRANSACTION."` A JOIN `".TBL_MGM_TRANSACTION_OPTION."` B ON(A.id=B.transaction_id)
	        WHERE 1 AND `user_id` IS NOT NULL AND module ='authorizenet' 
	        AND B.option_name ='authorizenet_transaction_id' {$and_where}";
	// log
	mgm_log($sql, __FUNCTION__);
	// return
	$authorizenet_transaction_id = false;    
	//  check missing user_id
	if( $an_transactions = $wpdb->get_results($sql) ){
		// jus use the settings
		// authorize.net specific				
		$an_loginid	 = $authorizenet->setting['loginid']; 
		$an_tran_key = $authorizenet->setting['tran_key'];
		// types
		$duration2days = array('d'=>'DAY', 'm'=>'MONTH', 'w'=>'WEEK', 'y'=>'YEAR');
		// log
		mgm_log($an_transactions, __FUNCTION__);
		// this will only miss when pack with trial cost 0.00 
		// loop
		foreach($an_transactions as $transaction){
			// pack
			$pack = json_decode($transaction->data, true);
			// log
			mgm_log($pack, __FUNCTION__);

			// check
			if(isset($pack['trial_on']) && (int)$pack['trial_on'] == 1 && (float)$pack['trial_cost'] == 0.00){
				$trial_duration_type = $pack['trial_duration_type'];
				$trial_duration      = (int)$pack['trial_duration'];
				$trial_num_cycles    = (int)$pack['trial_num_cycles'];
				// calc
				$trial_length        = ($trial_num_cycles * $trial_duration);// 1 * 10
				$trial_days          = $duration2days[$trial_duration_type];// DAY,MONTH
				// first transaction
				$an_transaction_dt   = $transaction->transaction_dt;
				// billing start date
				$an_billing_start_dt = date( 'Y-m-d H:i:s', strtotime("+{$trial_length} {$trial_days}", strtotime($transaction->transaction_dt))) ;
			}else{
				$duration_type = $pack['duration_type'];

				$duration      = (int)$pack['duration'];

				$num_cycles    = (int)$pack['num_cycles'];

				// calc

				$length        = ($num_cycles == 0) ? $duration : ($num_cycles * $duration);// 1 * 10

				$days          = $duration2days[$duration_type];// DAY,MONTH

				// first transaction

				$an_transaction_dt   = $transaction->transaction_dt;

				// billing start date

				$an_billing_start_dt = date( 'Y-m-d H:i:s', strtotime("+{$length} {$days}", strtotime($an_transaction_dt))) ;

				// log
				mgm_log("+{$length} {$days}", __FUNCTION__);

				// test
				if( strtotime($an_billing_start_dt) < time() ){
					// temp
					$temp_an_billing_start_dt = $an_billing_start_dt;
					// loop
					while( strtotime($temp_an_billing_start_dt) < time() ){
						$temp_an_billing_start_dt = date( 'Y-m-d H:i:s', strtotime("+{$length} {$days}", strtotime($temp_an_billing_start_dt))) ;
					}
					// reduce one unit if next date fetched
					if( strtotime($temp_an_billing_start_dt) > time() ){
						$temp_an_billing_start_dt = date( 'Y-m-d H:i:s', strtotime("-{$length} {$days}", strtotime($temp_an_billing_start_dt))) ;
					}	
					// log
					mgm_log('temp_an_billing_start_dt: ' . $temp_an_billing_start_dt, __FUNCTION__);	
					// copy
					$an_billing_start_dt = $temp_an_billing_start_dt;
				}
			}	

			// log
			mgm_log($an_transaction_dt . ' ' . $an_billing_start_dt, __FUNCTION__);	
			
			// fetch
			$transactions = $authorizenet->get_settled_transactions($an_billing_start_dt);

			// log
			// mgm_log('transactions: '. mgm_pr($transactions, true), __FUNCTION__);	

			// check
			if(in_array($transaction->id, array_keys($transactions))){
				// log
				mgm_log($transaction->id . ' => ' . $transactions[$transaction->id], __FUNCTION__);	
				// update
				$wpdb->update(TBL_MGM_TRANSACTION_OPTION, array('option_value'=>$transactions[$transaction->id]), array('id'=>$transaction->option_id));
				// log
				mgm_log($wpdb->last_query, __FUNCTION__);
				// return 
				$authorizenet_transaction_id = $transactions[$transaction->id];
			}			
		}		
	}	

	// return
	return $authorizenet_transaction_id;
}

// get user old style backup
/**
 * fetch users for expiring memberships check
 * 
 * @param string $act (fetch | update )
 * @param int $offset
 * @return void
 * @since 2.7 
 * @deprecated marked for history
 */
function mgm_get_users_for_cron_check($act='fetch', $key='expiring_memberships', $offset=NULL){
	global $wpdb;	
	// date again
	$current_date = mgm_get_current_datetime('Y-m-d H:i:s');// with time part #1023 issue	
	// get option 
	$cron_record = get_option('mgm_cron_record');
	// check	
	if( !isset($cron_record['run_date']) ){
		$cron_record = array('run_date'=>date('Y-m-d',$current_date['timestamp']), $key => array('user_offset'=>0));
	}elseif( !isset($cron_record[$key]['user_offset']) ){
		$cron_record[$key] = array('user_offset'=>0);
	}	
	// act
	switch($act){
		case 'fetch':			
			// user_offset
			$user_offset = (int)$cron_record[$key]['user_offset'];	
			// sql
			$sql_str = "SELECT ID,user_email,display_name FROM `{$wpdb->users}` WHERE ID <> 1 ORDER BY ID LIMIT %d, 50"; 
			// get 50 users
			$users = $wpdb->get_results(sprintf($sql_str, $user_offset));		
			// check users count
			if(count($users) == 0){
				// reset and refect
				$user_offset = 0;
				// get 50 users
				$users = $wpdb->get_results(sprintf($sql_str, $user_offset));
			}
			// init
			$a_users = array();
			// check
			if($users){	
				// loop
				foreach($users as $user){
				// skip admin 
					if (is_super_admin($user->ID)) continue;
					// set
					$a_users[] = $user;
				}
			}
			return $a_users;			
		break;
		case 'update':					
			// set		
			if( $offset ){
				// set		
				$cron_record['run_date'] = date('Y-m-d',$current_date['timestamp']);
				$cron_record[$key]['user_offset'] = (int)$cron_record[$key]['user_offset'] + $offset;
				// update
				update_option('mgm_cron_record', $cron_record);
			}
		break;
	}	
}

/**
 * fetch users for ongoing memberships check
 * 
 * @param string $act (fetch | update )
 * @param int $offset
 * @return void
 * @since 2.7 
 * @deprecated marked for history
 */
function mgm_get_users_for_ongoing_memberships_check($act='fetch', $offset=NULL){
	global $wpdb;	
	// reset_expiration renamed to ongoing_memberships
	// date again
	$current_date = mgm_get_current_datetime('Y-m-d H:i:s');// with time part #1023 issue	
	// get option 
	$cron_record = get_option('mgm_cron_record');
	// check	
	if( !isset($cron_record['run_date']) ){
		$cron_record = array('run_date'=>date('Y-m-d',$current_date['timestamp']), 'ongoing_memberships' => array('user_offset'=>0));
	}elseif( !isset($cron_record['ongoing_memberships']['user_offset']) ){
		$cron_record['ongoing_memberships'] = array('user_offset'=>0);
	}	
	// act
	switch($act){
		case 'fetch':			
			// user_offset
			$user_offset = (int)$cron_record['ongoing_memberships']['user_offset'];	
			// sql
			$sql_str = "SELECT ID,user_email,display_name FROM `{$wpdb->users}` WHERE ID <> 1 ORDER BY ID LIMIT %d, 50"; 
			// get 50 users
			$users = $wpdb->get_results(sprintf($sql_str, $user_offset));		
			// check users count
			if(count($users) == 0){
				// reset and refect
				$user_offset = 0;
				// get 50 users
				$users = $wpdb->get_results(sprintf($sql_str, $user_offset));
			}
			// init
			$a_users = array();
			// check
			if($users){	
				// loop
				foreach($users as $user){
				// skip admin 
					if (is_super_admin($user->ID)) continue;
					// set
					$a_users[] = $user;
				}
			}
			return $a_users;			
		break;
		case 'update':					
			// set		
			if( $offset ){
				// set		
				$cron_record['run_date'] = date('Y-m-d',$current_date['timestamp']);
				$cron_record['ongoing_memberships']['user_offset'] = (int)$cron_record['ongoing_memberships']['user_offset'] + $offset;
				// update
				update_option('mgm_cron_record', $cron_record);
			}
		break;
	}	
}

/**
 * fetch users for rebill check
 * 
 * @param string $act (fetch | update )
 * @param int $offset
 * @return void
 * @since 2.7 
 * @deprecated marked for history
 */
function mgm_get_users_for_rebill_status_check($act='fetch', $offset=NULL){
	global $wpdb;	
	// date again
	$current_date = mgm_get_current_datetime('Y-m-d H:i:s');// with time part #1023 issue	
	// get option 
	$cron_record = get_option('mgm_cron_record');
	// check
	if( !isset($cron_record['run_date']) ){
		$cron_record = array('run_date'=>date('Y-m-d',$current_date['timestamp']), 'rebill_status_check' => array('user_offset'=>0));
	}elseif( !isset($cron_record['rebill_status_check']['user_offset']) ){
		$cron_record['rebill_status_check'] = array('user_offset' => 0);
	}
	// act
	switch($act){
		case 'fetch':						
			// user_offset
			$user_offset = (int)$cron_record['rebill_status_check']['user_offset'];	
			// sql
			$sql_str = "SELECT ID,user_email,display_name FROM `{$wpdb->users}` WHERE ID <> 1 ORDER BY ID LIMIT %d, 50"; 
			// get 50 users
			$users = $wpdb->get_results(sprintf($sql_str, $user_offset));		
			// check users count
			if(count($users) == 0){
				// reset and refect
				$user_offset = 0;
				// get 50 users
				$users = $wpdb->get_results(sprintf($sql_str, $user_offset));
			}
			// init
			$a_users = array();
			// check
			if($users){	
				// loop
				foreach($users as $user){
				// skip admin 
					if (is_super_admin($user->ID)) continue;
					// set
					$a_users[] = $user;
				}
			}
			return $a_users;			
		break;
		case 'update':					
			// set		
			if( $offset ){
				$cron_record['run_date'] = date('Y-m-d',$current_date['timestamp']);
				$cron_record['rebill_status_check']['user_offset'] = (int)$cron_record['rebill_status_check']['user_offset'] + $offset;
				// update
				update_option('mgm_cron_record', $cron_record);
			}
		break;
	}	
}
// end file /core/libs/functions/mgm_cron_callbacks.php