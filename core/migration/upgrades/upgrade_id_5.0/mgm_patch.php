<?php
/** 
 * Patch for updating PAYPAL locale value(issue#: 538)
 */  
$active_modules = mgm_get_class('system')->get_active_modules('payment');
//check modules are enabled:
if(count($active_modules) > 0 && (in_array('mgm_paypal',$active_modules ) || in_array('mgm_paypalpro',$active_modules ) )) {
	$obj_paypalstd = mgm_get_module('paypal', 'payment');
	$obj_paypalpro = mgm_get_module('paypalpro', 'payment');
		
	$arr_locale = mgm_get_locales();
	
	$i = 0;
	foreach ($arr_locale as $code => $locale) {
		//paypal
		if(isset($obj_paypalstd->setting['locale']) && $i == $obj_paypalstd->setting['locale']) {
			$obj_paypalstd->setting['locale'] = $code;
		}
		//paypalpro
		if(isset($obj_paypalpro->setting['locale']) && $i == $obj_paypalpro->setting['locale']) {
			$obj_paypalpro->setting['locale'] = $code;
		}
		$i++;
	}
	
	$obj_paypalstd->save();
	$obj_paypalpro->save();
}