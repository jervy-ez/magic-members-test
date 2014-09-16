<?php
/** 
 * Objects merge/update
 */ 
 
 // system object updates
 $system_obj = new mgm_system();
 
 // saved object
 $system_obj_cached = mgm_get_option('system');
 
 // set new vars
 // membership_contents_url
 if(!isset($system_obj_cached->setting['membership_contents_url'])){
 	$system_obj_cached->setting['membership_contents_url'] = $system_obj->setting['membership_contents_url'];
 }
 
 // date_range_lower
 if(!isset($system_obj_cached->setting['date_range_lower'])){
 	$system_obj_cached->setting['date_range_lower'] = $system_obj->setting['date_range_lower'];
 }
 
 // date_range_upper
 if(!isset($system_obj_cached->setting['date_range_upper'])){
 	$system_obj_cached->setting['date_range_upper'] = $system_obj->setting['date_range_upper'];
 }
 
 // date_format
 if(!isset($system_obj_cached->setting['date_format'])){
 	$system_obj_cached->setting['date_format'] = $system_obj->setting['date_format'];
 }
 
 // date_format_long
 if(!isset($system_obj_cached->setting['date_format_long'])){
 	$system_obj_cached->setting['date_format_long'] = $system_obj->setting['date_format_long'];
 }
 
 // date_format_short
 if(!isset($system_obj_cached->setting['date_format_short'])){
 	$system_obj_cached->setting['date_format_short'] = $system_obj->setting['date_format_short'];
 } 
 // update
 update_option('mgm_system', $system_obj_cached);
 
 // ends