<?php
/** 
 * update system key name for auto login redirect url
 */  
$system_obj = mgm_get_class('system');
// check
if(isset($system_obj->setting['enable_autologin_url']) && !empty($system_obj->setting['enable_autologin_url'])){
	// autologin_redirect_url
	$system_obj->setting['autologin_redirect_url'] = $system_obj->setting['enable_autologin_url'];
	// save changes
	$system_obj->save();
}
// unset
unset($system);
