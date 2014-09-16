<?php
/** 
 * Objects merge/update
 * Add New Module setting: Supported Credit Card types
 * For eWay, Paypal Pro and Authorize.net
 */ 
// read  
$arr_modules = array('authorizenet', 'eway', 'paypalpro');
foreach ($arr_modules as $module) {
	$obj_module = mgm_get_module($module, 'payment');
	if (!isset($obj_module->setting['supported_card_types'])) {
		// set default values
		$obj_module->setting['supported_card_types'] = array_keys($obj_module->card_types);
		// save
		$obj_module->save();
	} 
}
// done