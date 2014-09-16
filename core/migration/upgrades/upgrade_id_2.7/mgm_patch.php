<?php
/** 
 * Apply patch
 */ 	
 
 // get login_errmsg_cancelled
 $login_errmsg_cancelled = mgm_get_template('login_errmsg_cancelled', NULL, 'messages');
 
 // append new if not added
 if(!preg_match('/\[subscription_url\]/',$login_errmsg_cancelled)){
 	// add
 	$login_errmsg_cancelled .= '<br /> Please make a new <a href=\"[subscription_url]\">subscription payment</a> to re-activate your account.'; 
 	// update
 	mgm_update_template('login_errmsg_cancelled', $login_errmsg_cancelled, 'messages');
 }
 // end of file
