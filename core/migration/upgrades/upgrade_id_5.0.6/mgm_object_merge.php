<?php
/** 
 * Objects merge/update
 * update settings with redirection_method
 */ 
 // read  
$settings = mgm_get_class('system');

if(!isset($settings->setting['redirection_method'])) {
	$settings->setting['redirection_method'] = 'header';
	$settings->save();
}
 // end file