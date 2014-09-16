<?php
/** 
 * Objects merge/update
 */ 
 
 // system object updates
 $system_obj = new mgm_system();
 
 // saved object
 $system_obj_cached = mgm_get_option('system');
 
 // set new vars
 // login url
 if(!isset($system_obj_cached->setting['login_url'])){
 	$system_obj_cached->setting['login_url'] = $system_obj->setting['login_url'];
 }
 // profile url
 if(!isset($system_obj_cached->setting['lostpassword_url'])){
 	$system_obj_cached->setting['lostpassword_url'] = $system_obj->setting['lostpassword_url'];
 }
  
 // update
 update_option('mgm_system', $system_obj_cached);
 
 // read 