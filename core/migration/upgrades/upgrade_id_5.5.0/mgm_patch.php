<?php
/** 
 * Apply patch
 */ 	
 
 // get login_errmsg_date_range
 $login_errmsg_date_range = mgm_get_template('login_errmsg_date_range', NULL, 'messages');
 
 // append new if not added
 if(!preg_match('/\[subscription_url\]/',$login_errmsg_date_range)){
 	// add
 	$login_errmsg_date_range .= '<br /> Please make a new <a href=\"[subscription_url]\">subscription payment</a> to re-activate your account.'; 
 	// update
 	mgm_update_template('login_errmsg_date_range', $login_errmsg_date_range, 'messages');
 }
 // end of file
