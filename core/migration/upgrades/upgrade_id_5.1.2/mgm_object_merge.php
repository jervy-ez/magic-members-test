<?php
/** 
 * Objects merge/update
 * Add eWay webservice username and password to settings
 */ 
 // read  
$eway = mgm_get_module('eway','payment');

if(!isset($eway->setting['username'])) {
	$eway->setting['username'] 	= '';	
	$eway->setting['password'] 	= '';
	$eway->save();
}
 // end file