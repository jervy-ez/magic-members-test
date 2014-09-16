<?php
/** 
 * Objects merge/update
 * Add shopper_response to Worldpay module settings
 */ 
 // read  
$worldpay = mgm_get_module('worldpay','payment');

if(!isset($worldpay->setting['shopper_response'])) {
	$worldpay->setting['shopper_response'] 	= 'N';		
	$worldpay->save();
}
 // end file