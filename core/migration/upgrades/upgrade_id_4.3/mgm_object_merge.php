<?php
// reconfigure auth class
$mgm_auth = new mgm_auth();
// get auth cached
$mgm_auth_cached = mgm_get_option('auth');
// product_info
if(!isset($mgm_auth_cached->product_info) || empty($mgm_auth_cached->product_info)){
	$mgm_auth_cached->product_info = $mgm_auth->product_info;
}
// cache_timeout
if(!isset($mgm_auth_cached->cache_timeout) || empty($mgm_auth_cached->cache_timeout)){
	$mgm_auth_cached->cache_timeout = $mgm_auth->cache_timeout;
}
// save
update_option('mgm_auth',$mgm_auth_cached);
// end upgrade 4.3