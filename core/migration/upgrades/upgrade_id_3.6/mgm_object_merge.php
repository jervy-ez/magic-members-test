<?php
/** 
 * Objects merge/update
 */ 
 // saved object
 $system_obj_cached = mgm_get_option('system');
 
 // set new vars
 if(!isset($system_obj_cached->setting['thumbnail_image_width'])){
 	$system_obj_cached->setting['thumbnail_image_width'] = '32'; 	
 }
  if(!isset($system_obj_cached->setting['thumbnail_image_height'])){
 	$system_obj_cached->setting['thumbnail_image_height'] = '32'; 	
 }
  if(!isset($system_obj_cached->setting['medium_image_width'])){
 	$system_obj_cached->setting['medium_image_width'] = '120'; 	
 }
  if(!isset($system_obj_cached->setting['medium_image_height'])){
 	$system_obj_cached->setting['medium_image_height'] = '120'; 	
 }
  if(!isset($system_obj_cached->setting['image_size_mb'])){
 	$system_obj_cached->setting['image_size_mb'] = '2'; 	
 }	
 // update
 update_option('mgm_system', $system_obj_cached);
 
 // ends