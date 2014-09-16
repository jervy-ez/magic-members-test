<?php
/** 
 * Objects merge/update
 */ 
 // read  
//update nested shortcode parsing:
$system_obj = mgm_get_class('system');
if(!isset($system_obj->setting['enable_post_url_redirection'])) {
	$system_obj->setting['enable_post_url_redirection'] = 'N';
	$system_obj->save();
}
 // end file