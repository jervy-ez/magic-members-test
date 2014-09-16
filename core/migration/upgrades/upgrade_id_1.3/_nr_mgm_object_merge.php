<?php
/** 
 * Objects merge/update
 */ 
 
 // system object updates
 $system_obj = new mgm_system();
 
 // saved object
 $system_obj_cached = mgm_get_option('system');
 
 // set new vars
 // subject
 if(!isset($system_obj_cached->setting['reminder_email_template_subject'])){
 	$system_obj_cached->setting['reminder_email_template_subject'] = $system_obj->setting['reminder_email_template_subject'];
 }
 
 // body
 if(!isset($system_obj_cached->setting['reminder_email_template_body'])){
	 $system_obj_cached->setting['reminder_email_template_body'] = $system_obj_cached->setting['reminder_email_template'];
	 unset($system_obj_cached->setting['reminder_email_template']);// unset old
 }
 
 // subject
 if(!isset($system_obj_cached->setting['registration_email_template_subject'])){
 	$system_obj_cached->setting['registration_email_template_subject'] = $system_obj->setting['registration_email_template_subject'];
 }
 
 // body
 if(!isset($system_obj_cached->setting['registration_email_template_body'])){
	 $system_obj_cached->setting['registration_email_template_body'] = $system_obj_cached->setting['registration_email_template'];
	 unset($system_obj_cached->setting['registration_email_template']); // unset old
 }
  
 // update
 // update_option('mgm_system', $system_obj_cached);
 
 // read 